<?php
/**
 * Member Frontend.
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl\Classes;

use WP;
use WP_Post;
use WP_User;
use WP_Error;

defined( 'ABSPATH' ) || die;

/**
 * Class MemberFrontend
 *
 * @package App\Plugins\Pvtl
 */
class Member_Frontend {
	/**
	 * The single instance of the class.
	 *
	 * @var Member_Frontend
	 */
	protected static $instance;

	/**
	 * The page for the members template.
	 *
	 * @var WP_Post
	 */
	public $member_page;

	/**
	 * The admin manager.
	 *
	 * @var Admin
	 */
	protected $admin;

	/**
	 * The actions handler.
	 *
	 * @var Actions
	 */
	public $actions;

	/**
	 * The role manager.
	 *
	 * @var Role_Manager
	 */
	public $role_manager;

	/**
	 * MemberFrontend constructor.
	 */
	public function __construct() {
		static::start_session();

		$this->admin        = new Admin();
		$this->actions      = new Actions();
		$this->role_manager = new Role_Manager();

		$this->init();

		do_action( 'mf_loaded' );
	}

	/**
	 * Main WooCommerce Instance.
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @return Member_Frontend
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Start secure session for flashing data.
	 */
	protected static function start_session() {
		session_set_cookie_params( 3600, '/', '', is_ssl(), true );
		session_start();
	}

	/**
	 * Hook into WP actions and adds plugin shortcodes.
	 */
	protected function init() {
		// Catch failed login attempts.
		add_action( 'wp_authenticate', array( $this, 'catch_empty_login' ), 1, 2 );
		add_action( 'wp_login_failed', array( $this, 'intercept_failed_login' ) );

		// Remove the admin bar for members.
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );

		// Set up the members page.
		add_action( 'init', array( $this, 'setup_members_page' ) );
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'template_redirect', array( $this, 'render' ), 10 );
		add_action( 'mf_before_render', array( $this, 'before_render' ), 10, 1 );
		add_action( 'parse_request', array( $this, 'parse_request_overrides' ) );
		add_filter( 'mf_render_vars', array( $this, 'render_vars' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'body_class' ), 10, 2 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		// Default handlers.
		add_action( 'mf_action_profile', array( $this, 'handle_profile' ), 10, 1 );
		add_action( 'mf_action_register', array( $this, 'handle_register' ), 10, 1 );
		add_action( 'mf_action_reset_password', array( $this, 'handle_reset_password' ), 10, 1 );
		add_action( 'mf_action_forgot_password', array( $this, 'handle_forgot_password' ), 10, 1 );
	}

	/**
	 * Add custom query vars to request.
	 *
	 * @param array $query_vars The existing query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'mf_action';

		return $query_vars;
	}

	/**
	 * Check if a page exists before rendering
	 * member templates.
	 *
	 * @param \WP $request The request object.
	 *
	 * @return \WP
	 */
	public function parse_request_overrides( WP $request ) {
		if ( isset( $request->query_vars['mf_action'] ) ) {
			$page = get_page_by_path( $request->request );

			if ( $page && ! empty( $page->post_content ) ) {
				$action = str_replace( '-', '_', $request->query_vars['mf_action'] );

				$this->before_render( $action );

				$request->query_vars['page_id'] = $page->ID;
				unset( $request->query_vars['mf_action'] );
			}
		}

		return $request;
	}

	/**
	 * Add custom rewrite rules.
	 */
	public function rewrites() {
		if ( ! $this->member_page || get_the_ID() ) {
			return;
		}

		$path = get_page_uri( $this->member_page );

		// Match first level paths.
		add_rewrite_rule(
			"^{$path}\/([a-z0-9\-]+)?$",
			'index.php?page_id=' . $this->member_page->ID . '&mf_action=$matches[1]',
			'top'
		);

		// Match first level paths.
		add_rewrite_rule(
			"^{$path}\/([a-z0-9\-]+)(?:\/page\/([0-9]+))$",
			'index.php?page_id=' . $this->member_page->ID . '&mf_action=$matches[1]&paged=$matches[2]',
			'top'
		);

		// Match second level paths.
		add_rewrite_rule(
			"^{$path}\/([a-z0-9\-]+)(?:\/([a-z0-9\-]+))$",
			'index.php?page_id=' . $this->member_page->ID . '&mf_action=$matches[1]/$matches[2]',
			'top'
		);

		// Match second level paths.
		add_rewrite_rule(
			"^{$path}\/([a-z0-9\-]+)(?:\/([a-z0-9\-]+))(?:\/page\/([0-9]+))$",
			'index.php?page_id=' . $this->member_page->ID . '&mf_action=$matches[1]/$matches[2]&paged=$matches[3]',
			'top'
		);

		add_rewrite_tag( '%mf_action%', '([^&]+)' );

		do_action( 'mf_after_rewrites' );
	}

	/**
	 * Return the URL for an action.
	 *
	 * @param ?string $action The action for the URL.
	 * @param array   $params Optional query parameters.
	 *
	 * @return string
	 */
	public function url( $action = null, $params = array() ) {
		$url = get_permalink( $this->member_page );

		if ( $action ) {
			$url .= str_replace( '_', '-', $action ) . '/';
		}

		$url = esc_url( $url );

		if ( ! empty( $params ) ) {
			$url .= '?' . http_build_query( $params );
		}

		return $url;
	}

	/**
	 * Setup the member page WP_Post.
	 */
	public function setup_members_page() {
		$_post = wp_cache_get( 'mf_members_page', 'posts' );

		if ( ! $_post ) {
			$_post = (int) get_option( 'mf_page_for_members' );

			if ( ! $_post ) {
				return;
			}

			wp_cache_add( 'mf_members_page', $_post, 'posts' );
		}

		$this->member_page = get_post( $_post );
	}

	/**
	 * Filter the render vars.
	 *
	 * @param array  $vars   The vars to be passed to the view.
	 * @param string $action The action that will be rendered.
	 *
	 * @return array
	 */
	public function render_vars( $vars, $action ) {
		// Set the password reset key and login parameters.
		if ( 'reset_password' === $action ) {
			// Set the vars from query parameters.
			$key   = isset( $_GET['key'] ) ? wp_unslash( $_GET['key'] ) : null; // phpcs:ignore
			$login = isset( $_GET['login'] ) ? wp_unslash( $_GET['login'] ) : null; // phpcs:ignore

			// If the query parameters aren't set, attempt to get
			// them from the session data.
			if ( ! $key && ! $login ) {
				$key   = $this->old( 'key' );
				$login = $this->old( 'login' );
			}

			$vars['key']   = $key;
			$vars['login'] = $login;
		}

		return $vars;
	}

	/**
	 * Runs before the page is rendered.
	 *
	 * @param string $action The action that will be rendered.
	 */
	public function before_render( $action ) {
		// Throw 404 page.
		if ( ! $action ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );

			return;
		}

		// Get the allowed actions for unauthorised users.
		$allowed = apply_filters(
			'mf_allowed_actions',
			array(
				'login',
				'register',
				'reset_password',
				'forgot_password',
			)
		);

		// Redirect to login if not logged in.
		if ( ! $this->get_current_user() && ! in_array( $action, $allowed, true ) ) {
			$this->set_flash( 'error', 'You must be logged in to access this area' );
			$this->redirect( 'login' );
		}

		// Redirect to dashboard if already logged in.
		if ( $this->get_current_user() && in_array( $action, $allowed, true ) ) {
			$this->redirect( 'dashboard' );
		}

		// Verify the password reset key.
		if ( 'reset_password' === $action ) {
			// Set the vars from query parameters.
			$key   = isset( $_GET['key'] ) ? wp_unslash( $_GET['key'] ) : null; // phpcs:ignore
			$login = isset( $_GET['login'] ) ? wp_unslash( $_GET['login'] ) : null; // phpcs:ignore

			// If the query parameters aren't set, attempt to get
			// them from the session data.
			if ( ! $key && ! $login ) {
				$key   = $this->old( 'key' );
				$login = $this->old( 'login' );
			}

			if ( ! $this->validate_password_key( $key, $login ) ) {
				$this->set_flash( 'error', 'The password rest link has expired' );
				$this->redirect( 'login' );
			}
		}
	}

	/**
	 * Render the member frontend.
	 */
	public function render() {
		if ( is_admin() || ! is_page( $this->member_page ) ) {
			return;
		}

		// Get the current action.
		$action = $this->actions->action();

		// Perform pre-render checks and tasks.
		do_action( 'mf_before_render', $action );

		// Replace the page content with the view.
		add_filter(
			'the_content',
			function ( $content ) use ( $action ) {
				if ( ! in_the_loop() ) {
					return $content;
				}

				$user = $this->get_current_user();
				$vars = apply_filters(
					'mf_render_vars',
					array(
						'content' => $content,
						'user'    => $user,
					),
					$action
				);

				$view = $this->view( $action, $vars );

				$this->forget_flash( 'input' );
				$this->forget_flash( 'error' );

				return $view;
			},
			99
		);
	}

	/**
	 * Renders a view.
	 *
	 * @param string $name Path to the view.
	 * @param array  $vars Optional variables to pass to scope.
	 *
	 * @return string
	 */
	public function view( $name, $vars = array() ) {
		$name = str_replace(
			array(
				'_',
				'/',
			),
			'-',
			$name
		);

		$include_path = MF_PATH . "/resources/views/{$name}.php";

		$override = locate_template( "member-frontend/{$name}.php" );

		if ( $override ) {
			$include_path = $override;
		}

		$include_path = apply_filters( 'mf_view', $include_path, $name );

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( $vars, EXTR_SKIP );

		ob_start();

		include $include_path;

		return ob_get_clean();
	}

	/**
	 * Renders a view partial.
	 *
	 * @param string $name Path to the partial.
	 */
	protected function partial( $name ) {
		$include_path = MF_PATH . "/resources/views/partials/{$name}.php";
		$override     = locate_template( "member-frontend/partials/{$name}.php" );

		if ( $override ) {
			$include_path = $override;
		}

		$include_path = apply_filters( 'mf_partial', $include_path, $name );

		$user = $this->get_current_user();
		$vars = apply_filters( 'mf_render_vars', array( 'user' => $user ), null );

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( $vars, EXTR_SKIP );

		require $include_path;
	}

	/**
	 * Get the current user.
	 *
	 * @return WP_User
	 */
	public function get_current_user() {
		if ( is_user_logged_in() ) {
			return wp_get_current_user();
		}

		return null;
	}

	/**
	 * Removes member top bar for front-end users.
	 */
	public function remove_admin_bar() {
		if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * When the front-end login form fails, make sure
	 * it goes back to a front-end view.
	 */
	public function intercept_failed_login() {
		$this->set_flash( 'error', 'The email and/or password is incorrect. Please try again.' );
		$this->redirect( 'login' );
	}

	/**
	 * Catch empty login attempts from member login.
	 *
	 * @param string $username The username.
	 * @param string $password The password.
	 */
	public function catch_empty_login( $username, $password ) {
		$is_empty    = empty( $username ) || empty( $password );
		$is_wp_login = isset( $_SERVER['HTTP_REFERER'] ) ? wp_login_url() === $_SERVER['HTTP_REFERER'] : false;

		if ( $is_empty && ! $is_wp_login && $this->actions->is( 'post' ) ) {
			$this->intercept_failed_login();
		}
	}

	/**
	 * Get the Logout URL.
	 *
	 * @return string
	 */
	public function get_logout_url() {
		$logout_url    = wp_logout_url();
		$logout_params = array( 'redirect_to' => $this->url() );

		return esc_url( add_query_arg( $logout_params, $logout_url ) );
	}

	/**
	 * Redirect to an action.
	 *
	 * @param ?string $action     The action to redirect to.
	 * @param array   $with_input Redirect with stored input.
	 */
	public function redirect( $action = null, $with_input = array() ) {
		if ( ! empty( $with_input ) ) {
			$this->set_flash( 'input', $with_input );
		}

		wp_safe_redirect( $this->url( $action ) );
		die();
	}

	/**
	 * Redirect to an action.
	 *
	 * @param array $with_input Redirect with stored input.
	 */
	public function back( $with_input = array() ) {
		if ( ! empty( $with_input ) ) {
			$this->set_flash( 'input', $with_input );
		}

		wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
		die();
	}

	/**
	 * Handle member registration.
	 *
	 * @param array $data The post data.
	 */
	public function handle_register( $data ) {
		$user = $this->save_user( $data );

		if ( is_wp_error( $user ) ) {
			$this->set_flash( 'error', $user->get_error_message() );
			$this->back( $data );
		}

		do_action( 'mf_after_register_user', $user, $data );

		$auto_login        = apply_filters( 'mf_auto_login', true, $user );
		$success_message   = apply_filters( 'mf_registered_success_message', 'Account created successfully' );
		$register_redirect = apply_filters( 'mf_register_redirect', $this->url( 'dashboard' ), $user );

		if ( $auto_login ) {
			wp_set_auth_cookie( $user->ID, true );
		}

		$this->set_flash( 'success', $success_message );

		// Redirect may be non member action.
		wp_safe_redirect( $register_redirect );
		die();
	}

	/**
	 * Sign Up for new account.
	 *
	 * @param array $data The user data.
	 *
	 * @return WP_User|WP_Error
	 */
	public function save_user( $data ) {
		$user_data = array();

		if ( isset( $data['ID'] ) && (int) $data['ID'] ) {
			$user_data['ID'] = (int) $data['ID'];
		}

		$user_data['first_name'] = isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : '';
		$user_data['last_name']  = isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : '';
		$user_data['user_email'] = isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '';
		$user_data['user_pass']  = isset( $data['password'] ) ? wp_unslash( $data['password'] ) : '';

		if ( ! isset( $data['ID'] ) ) {
			$duplicate = 1;
			$user_name = sanitize_title_with_dashes( $user_data['first_name'] . ' ' . $user_data['last_name'], null, 'save' );

			$user_data['user_login'] = $user_name;

			while ( username_exists( $user_data['user_login'] ) ) {
				$user_data['user_login'] = $user_name . '-' . ( ++$duplicate );
			}
		}

		// Filter the user data array.
		$user_data = apply_filters( 'mf_user_data', $user_data, $data );

		// Add the main validation filter.
		add_filter( 'mf_validate_user', array( $this, 'validate_user' ), 5, 3 );

		// Apply validation filters.
		$errors = apply_filters( 'mf_validate_user', array(), $user_data, $data );

		if ( count( $errors ) ) {
			return new \WP_Error( 'invalid_data', $errors );
		}

		// Create the user.
		if ( isset( $user_data['ID'] ) ) {
			$user_id = wp_update_user( $user_data );
		} else {
			$user_id = wp_insert_user( $user_data );
		}

		// Return a WP_Error.
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Get the WP_User.
		$user = get_user_by( 'id', $user_id );

		do_action( 'mf_after_save_user', $user, $user_data );

		return $user;
	}

	/**
	 * Handle Update Profile action.
	 *
	 * @param array $data The post data.
	 */
	public function handle_profile( $data ) {
		$current_user = $this->get_current_user();

		if ( ! $current_user ) {
			$this->set_flash( 'error', 'An error occurred' );
			$this->redirect( 'login' );
		}

		$data['ID'] = $current_user->ID;

		$user = $this->save_user( $data );

		if ( is_wp_error( $user ) ) {
			$this->set_flash( 'error', $user->get_error_message() );
			$this->redirect( 'profile', $data );
		}

		$this->set_flash( 'success', 'Profile updated successfully' );
		$this->redirect( 'profile' );
	}

	/**
	 * Send Password Reset Email.
	 *
	 * @param array $data The post data.
	 */
	public function handle_forgot_password( $data ) {
		$email = isset( $data['email'] ) ? sanitize_text_field( $data['email'] ) : null;

		if ( ! $email ) {
			$this->set_flash( 'error', 'Please enter a valid email address' );
			$this->redirect( 'forgot_password' );
		}

		$user_data = get_user_by( 'email', $email );

		if ( $user_data ) {
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$reset_key  = get_password_reset_key( $user_data );
			$site_name  = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			// Email notification body.
			$message = $this->view(
				'email/forgot',
				array(
					'site_name'  => $site_name,
					'user_login' => $user_login,
					'reset_url'  => $this->url(
						'reset_password',
						array(
							'key'   => $reset_key,
							'login' => $user_login,
						)
					),
				)
			);

			// translators: Password reset email subject. %s: Site name.
			$title   = sprintf( __( '[%s] Password Reset' ), $site_name );
			$title   = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
			$message = apply_filters( 'retrieve_password_message', $message, $reset_key, $user_login, $user_data );

			wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
		}

		$this->set_flash( 'success', 'Please check your email for the confirmation link' );
		$this->redirect( 'forgot_password' );
	}

	/**
	 * Reset Password.
	 *
	 * @param array $data The post data.
	 */
	public function handle_reset_password( $data ) {
		$username = isset( $data['login'], $data['key'] ) ? $data['login'] : null;
		$valid    = $this->validate_password_key( $data['key'], $data['login'] );
		$user     = get_user_by( 'login', $username );

		if ( ! $user || ! $valid ) {
			$this->set_flash( 'error', 'Security token has expired' );
			$this->redirect( 'login' );
		}

		$new_password         = isset( $data['password'] ) ? $data['password'] : null;
		$new_password_confirm = isset( $data['confirm_password'] ) ? $data['confirm_password'] : null;

		$valid = $this->validate_password( $new_password, $new_password_confirm );

		if ( is_string( $valid ) ) {
			$this->set_flash( 'error', $valid );
			$this->redirect( 'reset_password', $data );
		}

		reset_password( $user, $new_password );

		$this->set_flash( 'success', 'Password reset successfully' );
		$this->redirect( 'login' );
	}

	/**
	 * Validate user data using a filter.
	 *
	 * @param array $errors    The existing errors.
	 * @param array $user_data The user data.
	 * @param array $post_data The post data.
	 *
	 * @return array
	 */
	public function validate_user( $errors, $user_data, $post_data = array() ) {
		if ( empty( $user_data['first_name'] ) ) {
			$errors['first_name'] = 'Please enter your first name';
		}

		if ( empty( $user_data['last_name'] ) ) {
			$errors['last_name'] = 'Please enter your last name';
		}

		$email_valid = $this->validate_email( $user_data['user_email'] );

		if ( true !== $email_valid ) {
			$errors['email'] = $email_valid;
		}

		// Don't validate non-existent passwords for profile updates.
		if ( ! isset( $user_data['ID'] ) || ! empty( $user_data['user_pass'] ) ) {
			$password_valid = $this->validate_password( $user_data['user_pass'], $post_data['confirm_password'] );

			if ( true !== $password_valid ) {
				$errors['password'] = $password_valid;
			}
		}

		return $errors;
	}

	/**
	 * Validate the user password reset key.
	 *
	 * @param string $key   The security token.
	 * @param string $login The username to validate.
	 *
	 * @return bool
	 */
	protected function validate_password_key( $key, $login ) {
		$user = check_password_reset_key( $key, $login );

		return ! is_wp_error( $user );
	}

	/**
	 * Validate Email Input.
	 *
	 * @param string $email The email to validate.
	 *
	 * @return bool|string
	 */
	public function validate_email( $email ) {
		$current_user = $this->get_current_user();

		if ( empty( $email ) || ! is_email( $email ) ) {
			return 'Please enter a valid email address';
		}

		// Check that the email isn't currently in use.
		$email_exists = email_exists( $email ) || username_exists( $email );

		if ( $email_exists ) {
			if ( $current_user ) {
				if ( $email !== $current_user->user_email ) {
					return 'Email address already in use';
				}
			} else {
				return 'Email address already in use';
			}
		}

		return true;
	}

	/**
	 * Validate Password Input.
	 *
	 * @param string $password         The password to validate.
	 * @param string $confirm_password The password confirmation.
	 *
	 * @return bool|string
	 */
	public function validate_password( $password, $confirm_password ) {
		if ( empty( $password ) ) {
			return 'Please enter a password';
		}

		if ( empty( $confirm_password ) ) {
			return 'Please confirm your password';
		}

		if ( $confirm_password !== $password ) {
			return 'Passwords do not match';
		}

		return true;
	}

	/**
	 * Set flash data.
	 *
	 * @param string $name The flash key.
	 * @param mixed  $data The flash data.
	 */
	public function set_flash( $name, $data ) {
		if ( ! isset( $_SESSION['flash'] ) ) {
			$_SESSION['flash'] = array();
		}

		$_SESSION['flash'][ $name ] = maybe_serialize( $data );
	}

	/**
	 * Check if flash data exists.
	 *
	 * @param string $name The flash key.
	 *
	 * @return bool
	 */
	public function has_flash( $name ) {
		return isset( $_SESSION['flash'][ $name ] );
	}

	/**
	 * Retrieve flash data.
	 *
	 * @param string $name The flash key.
	 *
	 * @return mixed
	 */
	public function get_flash( $name ) {
		$data = isset( $_SESSION['flash'][ $name ] ) ? $_SESSION['flash'][ $name ] : false;

		if ( $data ) {
			unset( $_SESSION['flash'][ $name ] );

			return maybe_unserialize( $data );
		}

		return false;
	}

	/**
	 * Delete flash data.
	 *
	 * @param string $name The flash key.
	 */
	public function forget_flash( $name ) {
		unset( $_SESSION['flash'][ $name ] );
	}

	/**
	 * Retrieve old post data.
	 *
	 * @param string $name The post data key.
	 *
	 * @return mixed
	 */
	public function old( $name ) {
		$data = isset( $_SESSION['flash']['input'] ) ? $_SESSION['flash']['input'] : false;

		if ( $data ) {
			$data = maybe_unserialize( $data );

			if ( isset( $data[ $name ] ) ) {
				return esc_attr( $data[ $name ] );
			}
		}

		return '';
	}

	/**
	 * Get the error message for a form field.
	 *
	 * @param string $name The field name.
	 *
	 * @return string
	 */
	public function get_error( $name ) {
		if ( ! isset( $_SESSION['flash']['error'] ) ) {
			return null;
		}

		$errors = maybe_unserialize( $_SESSION['flash']['error'] );

		if ( ! is_array( $errors ) || ! isset( $errors[ $name ] ) ) {
			return null;
		}

		return $errors[ $name ];
	}

	/**
	 * Add member class to body.
	 *
	 * @param array $classes The existing classes.
	 *
	 * @return array;
	 */
	public function body_class( $classes ) {
		if ( $this->member_page && get_the_ID() === $this->member_page->ID ) {
			$classes[] = 'member-frontend-page';
		}

		return $classes;
	}
}
