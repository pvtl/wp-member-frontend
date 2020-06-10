<?php
/**
 * Member Frontend.
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl\Classes;

use WP_Post;
use WP_User;

defined( 'ABSPATH' ) || die;

/**
 * Class MemberFrontend
 *
 * @package App\Plugins\Pvtl
 */
class Member_Frontend {
	/**
	 * The page for the members template.
	 *
	 * @var WP_Post
	 */
	protected $member_page;

	/**
	 * The actions handler.
	 *
	 * @var Actions
	 */
	protected $actions;

	/**
	 * MemberFrontend constructor.
	 */
	public function __construct() {
		static::start_session();

		$this->actions = new Actions();

		$this->init();

		do_action( 'mf_loaded' );
	}

	/**
	 * Start secure session for flashing data.
	 */
	protected static function start_session() {
		if ( PHP_SESSION_NONE === session_status() && ! is_admin() ) {
			session_set_cookie_params( 3600, '/', '', true, true );
			session_start();
		}
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

		// Add the member template to the list of WordPress theme templates.
		add_filter( 'theme_templates', array( $this, 'register_templates' ) );

		// Set up the members page.
		add_action( 'init', array( $this, 'setup_members_page' ) );
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'template_redirect', array( $this, 'render' ) );

		// Default handlers.
		add_action( 'mf_action_register', array( $this, 'handle_register' ), 10, 1 );
		add_action( 'mf_action_forgot_password', array( $this, 'handle_forgot_password' ), 10, 1 );
		add_action( 'mf_action_reset_password', array( $this, 'handle_reset_password' ), 10, 1 );
	}

	/**
	 * Add custom rewrite rules.
	 */
	public function rewrites() {
		if ( ! $this->member_page ) {
			return;
		}

		$path = get_page_uri( $this->member_page );

		add_rewrite_rule(
			"^{$path}/([a-z\-]+)?",
			'index.php?page_id=' . $this->member_page->ID . '&mf_action=$matches[1]',
			'top'
		);

		add_rewrite_tag( '%mf_action%', '([^&]+)' );

		do_action( 'mf_after_rewrites' );
	}

	/**
	 * Return the URL for an action.
	 *
	 * @param string $action The action for the URL.
	 * @param array  $params Optional query parameters.
	 *
	 * @return string
	 */
	protected function url( $action = null, $params = array() ) {
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
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$_post = $wpdb->get_row(
				$wpdb->prepare(
					"
						SELECT * FROM {$wpdb->posts}
						INNER JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.`post_id` = {$wpdb->posts}.`ID`
						WHERE {$wpdb->postmeta}.`meta_key` = '_wp_page_template'
						AND {$wpdb->postmeta}.`meta_value` = %s
						LIMIT 1
					",
					'resources/templates/members.php'
				)
			);

			if ( ! $_post ) {
				return;
			}

			$_post = sanitize_post( $_post, 'raw' );

			wp_cache_add( 'mf_members_page', $_post, 'posts' );
		}

		$this->member_page = get_post( $_post );
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
			)
		);

		// Redirect to login on unauthorised access.
		if ( ! $this->get_current_user() && ! in_array( $action, $allowed, true ) ) {
			$this->set_flash( 'error', 'You must be logged in to access this area' );
			$this->redirect( 'login' );
		}

		add_filter(
			'the_content',
			function ( $content ) use ( $action ) {
				$user = $this->get_current_user();
				$vars = apply_filters(
					"mf_render_vars_{$action}",
					array(
						'content' => $content,
						'user'    => $user,
					)
				);

				return $this->view( $action, $vars );
			},
			99
		);

		$this->forget_flash( 'input' );
	}

	/**
	 * Add the member page template.
	 *
	 * @param array $post_templates The current theme page templates.
	 *
	 * @return array
	 */
	public function register_templates( $post_templates ) {
		return array_merge( $post_templates, array( 'resources/templates/members.php' => 'Members Template' ) );
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
		$name = str_replace( '_', '-', $name );

		$include_path = MF_PATH . "/resources/views/{$name}.php";

		$override = locate_template( "member-frontend/{$name}.php" );

		if ( $override ) {
			$include_path = $override;
		}

		// phpcs:ignore WordPress.PHP.DontExtract
		extract( $vars, EXTR_SKIP );

		ob_start();

		require $include_path;

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
	 * Catch empty login attempts.
	 *
	 * @param string $username The username.
	 * @param string $password The password.
	 */
	public function catch_empty_login( $username, $password ) {
		if ( empty( $username ) || empty( $password ) ) {
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
		$logout_params = array( 'redirect_to' => get_permalink() );

		return esc_url( add_query_arg( $logout_params, $logout_url ) );
	}

	/**
	 * Redirect to an action.
	 *
	 * @param string $action     The action to redirect to.
	 * @param array  $with_input Redirect with stored input.
	 */
	protected function redirect( $action = null, $with_input = array() ) {
		if ( ! empty( $with_input ) ) {
			$this->set_flash( 'input', $with_input );
		}

		wp_safe_redirect( $this->url( $action ) );
		die();
	}

	/**
	 * Handle member registration.
	 *
	 * @param array $data The post data.
	 */
	public function handle_register( $data ) {
		$user_data = $this->save_user( $data );

		if ( is_wp_error( $user_data ) ) {
			$this->set_flash( 'error', $user_data->get_error_message() );
			$this->do_redirect( null, true );
		}

		do_action( 'mf_user_register', $user, $user_data );

		$success_message = apply_filters( 'mf_registered_success_message', 'Account successfully created' );
		$this->set_flash( 'success', $success_message );
		$this->set_redirect_url( $this->redirect_to, '' );

		$user              = get_user_by( 'id', $user_data['ID'] );
		$auto_login        = apply_filters( 'mf_auto_login', true );
		$register_redirect = apply_filters( 'mf_register_redirect', null, $user );

		if ( $auto_login ) {
			wp_signon(
				array(
					'user_login'    => $user_data['username'],
					'user_password' => $user_data['user_pass'],
				)
			);
		}

		$this->do_redirect( $register_redirect );
	}

	/**
	 * Sign Up for new account.
	 */
	public function save_user( $data ) {
		// Basic Fields to update.
		$user_data['first_name'] = isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : '';
		$user_data['last_name']  = isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : '';
		$user_data['user_email'] = isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '';
		$user_data['user_pass']  = isset( $data['user_pass'] ) ? wp_unslash( $data['user_pass'] ) : '';
		$user_data['username']   = $user_data['user_email'];

		// Filter the user data array.
		$user_data = apply_filters( 'mf_user_before_save', $user_data );

		add_filter( 'mf_validate_user', array( $this, 'validate_user' ), 5, 2 );

		$errors = apply_filters( 'mf_validate_user', array(), $user_data );

		if ( count( $errors ) ) {
			return new \WP_Error( 'invalid_data', $errors );
		}

		// Create User.
		$user_id = wp_create_user( $user_data['username'], $user_data['user_pass'], $user_data['user_email'] );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Update user meta.
		$user_data['ID'] = $user_id;

		// Update user data, minus password.
		$update_data = $user_data;
		unset( $update_data['user_pass'] );
		wp_update_user( $update_data );
		unset( $update_data );

		$user = get_user_by( 'id', $user_id );

		do_action( 'mf_user_update', $user, $user_data );

		return $user_data;
	}

	/**
	 * Update User Profile.
	 */
	public function update_profile() {
		// Current member.
		$current_user = wp_get_current_user();

		if ( ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'mf_update_' . $current_user->ID ) ) {
			wp_nonce_ays( '' );
			die();
		}

		$user_id = $this->save_profile_updates( $current_user->ID );

		if ( is_wp_error( $user_id ) ) {
			$this->set_flash( 'error', $user_id->get_error_message() );
			$this->do_redirect( null, true );
		} else {
			$this->set_flash( 'success', 'Account updated successfully' );
			$this->set_redirect_url( $this->redirect_to, '' );
		}

		$this->do_redirect();
	}

	public function save_profile_updates( $user_id ) {
		$user_data = array( 'ID' => $user_id );

		// Basic Fields to update.
		$user_data['first_name'] = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$user_data['last_name']  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';

		$user_data['user_email'] = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$user_data['user_pass']  = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';

		// Filter the user data array.
		$user_data = apply_filters( 'mf_user_before_save', $user_data );

		add_filter( 'mf_validate_user', array( $this, 'validateUser' ), 5, 2 );

		$errors = apply_filters( 'mf_validate_user', array(), $user_data );

		if ( count( $errors ) ) {
			return new \WP_Error( 'invalid_data', $errors );
		}

		// Send to WP to update.
		$user_id = wp_update_user( $user_data );
		$user    = get_user_by( 'id', $user_id );

		do_action( 'mf_user_update', $user, $user_data );

		return $user_id;
	}

	/**
	 * Send Password Reset Email.
	 *
	 * @param array $data The post data.
	 */
	public function handle_forgot_password( $data ) {
		$email = isset( $data['email'] ) ? sanitize_text_field( $data['email'] ) : null;

		if ( ! $email ) {
			$this->set_flash( 'error', 'Email is required' );
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
		$this->redirect( 'login' );
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
	 * Validate user data.
	 *
	 * @param array $errors    The existing errors.
	 * @param array $user_data The user data.
	 *
	 * @return array
	 */
	public function validate_user( $errors, $user_data ) {
		$email_error = $this->validate_email( $user_data['user_email'] );

		if ( true !== $email_error ) {
			$errors['email'] = $this->validate_email( $user_data['user_email'] );
		}

		if ( ! isset( $user_data['ID'] ) || ! empty( $user_data['user_pass'] ) ) {
			$password_error = $this->validate_password( $user_data['user_pass'], $_POST['confirm_password'] );
			if ( true !== $password_error ) {
				$errors['user_pass'] = $this->validate_password( $user_data['user_pass'], $_POST['confirm_password'] );
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
	 * @return bool
	 */
	public function validate_email( $email ) {
		$current_user = wp_get_current_user();

		if ( empty( $email ) ) {
			return 'Please enter a valid email address';
		} elseif ( ! is_email( $email ) ) {
			return 'Please enter a valid email address';
		} elseif ( $email !== $current_user->user_email && ( email_exists( $email ) || username_exists( $email ) ) ) {
			return 'Email address already in use';
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

		// Password and Confirm Password don't match.
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
}
