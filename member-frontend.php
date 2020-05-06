<?php
/**
 * Plugin Name: Member Frontend & API by Pivotal
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Adds a member frontend custom post type, taxonomy and fields.
 *              Also opens API endpoints for the abetterchoice mobile app.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Version: 0.4.0
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl;

use WP_User;
use WP_Customize_Manager;

defined( 'ABSPATH' ) || die;

/**
 * Main MemberFrontend Class
 *
 * @class MemberFrontend
 */
class MemberFrontend {
	/**
	 * Redirect URL for forms.
	 *
	 * @var string
	 */
	protected $redirect_to;

	/**
	 * List of custom member pages.
	 *
	 * @var array
	 */
	protected $pages = array();

	/**
	 * The dashboard page ID
	 *
	 * @var int
	 */
	public $dashboard_page;

	/**
	 * The register page ID
	 *
	 * @var int
	 */
	public $register_page;

	/**
	 * MemberFrontend constructor.
	 */
	public function __construct() {
		// Start a session for flashing data.
		session_set_cookie_params( 3600, '/', '', true, true );

		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}

		// If redirect_to is set, update the redirect URL.
		// phpcs:disable WordPress.Security.NonceVerification
		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : null;
		$referrer    = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null;

		$this->set_redirect_url( $redirect_to ? $redirect_to : $referrer );
		$this->init();

		do_action( 'mf_loaded' );
	}

	/**
	 * Hook into WP actions and adds plugin shortcodes.
	 */
	protected function init() {
		$this->dashboard_page = get_theme_mod( 'mf_dashboard' );
		$this->register_page  = get_theme_mod( 'mf_register' );

		// Prevent members from accessing WordPress dashboard.
		add_action(
			'init',
			function () {
				if ( is_admin() && current_user_can( 'subscriber' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					$page_url = apply_filters( 'mf_page_url', $this->get_page_url( 'dashboard' ), 'dashboard' );

					$this->do_redirect( $page_url );
				}
			}
		);

		add_action( 'customize_register', array( $this, 'customize_register' ), 10, 1 );
		add_action( 'wp_login_failed', array( $this, 'intercept_failed_login' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		add_action( 'template_redirect', array( $this, 'handle_data_submission' ) );
		add_action( 'wp_authenticate', array( $this, 'catch_empty_login' ), 1, 2 );

		add_shortcode( 'member-dashboard', array( $this, 'display_member_dashboard' ) );
		add_shortcode( 'member-register', array( $this, 'display_registration_form' ) );
	}

	/**
	 * Check the current request method.
	 *
	 * @param string $method The method to check.
	 *
	 * @return bool
	 */
	protected function is( $method ) {
		return $method === $this->method();
	}

	/**
	 * Get the current request method.
	 *
	 * @return string
	 */
	protected function method() {
		return isset( $_SERVER['REQUEST_METHOD'] )
			? strtolower( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
			: null;
	}

	/**
	 * Add MemberFrontend options to the customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize WP_Customize_Manager instance.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_section(
			'mf_section',
			array(
				'title'    => __( 'Member Frontend', 'member-frontend' ),
				'priority' => 30,
			)
		);

		$wp_customize->add_setting(
			'mf_dashboard',
			array(
				'default'   => '',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_control(
			'mf_dashboard',
			array(
				'type'     => 'dropdown-pages',
				'label'    => __( 'Dashboard Page', 'member-frontend' ),
				'section'  => 'mf_section',
				'settings' => 'mf_dashboard',
			)
		);

		$wp_customize->add_setting(
			'mf_register',
			array(
				'default'   => '',
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_control(
			'mf_register',
			array(
				'type'     => 'dropdown-pages',
				'label'    => __( 'Register Page', 'member-frontend' ),
				'section'  => 'mf_section',
				'settings' => 'mf_register',
			)
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
		$include_path = "resources/views/{$name}.php";

		$override = locate_template( "member-frontend/{$name}.php" );

		if ( $override ) {
			$include_path = $override;
		}

		extract( $vars );

		ob_start();
		require $include_path;
		$view = ob_get_clean();

		$this->forget_flash( 'input' );

		return $view;
	}

	/**
	 * Renders a view partial.
	 *
	 * @param string $name Path to the partial.
	 *
	 * @return string
	 */
	protected function partial( $name ) {
		$include_path = "resources/views/partials/{$name}.php";
		$override     = locate_template( "member-frontend/partials/{$name}.php" );

		if ( $override ) {
			$include_path = $override;
		}

		ob_start();
		require $include_path;
		$view = ob_get_clean();

		return $view;
	}

	/**
	 * Safely perform redirect and die.
	 *
	 * @param string $url        The URL to redirect to.
	 * @param bool   $with_input Whether to keep post data in session.
	 */
	public function do_redirect( $url = '', $with_input = false ) {
		if ( $with_input && $this->is( 'post' ) ) {
			$this->set_flash( 'input', wp_unslash( $_POST ) );
		}

		wp_safe_redirect( $url ? $url : $this->redirect_to );
		die();
	}

	/**
	 * Build the redirect URL.
	 *
	 * @param string $path         URL path.
	 * @param string $action       Action to run.
	 * @param array  $add_params    Parameters to add to the query string.
	 * @param array  $remove_params Parameters to remove from the query string.
	 *
	 * @return string
	 */
	public function set_redirect_url( $path, $action = '', $add_params = array(), $remove_params = array() ) {
		$url = $path;

		// Add action.
		if ( trim( $action ) !== '' ) {
			$add_params['action'] = $action;
		}

		// Add parameters to the URL.
		if ( count( $add_params ) ) {
			$url = add_query_arg( $add_params, $url );
		}

		// Remove parameters from the URL.
		if ( count( $remove_params ) ) {
			$url = remove_query_arg( $remove_params, $url );
		}

		$this->redirect_to = esc_url_raw( $url );

		return $url;
	}

	/**
	 * Get the current user.
	 *
	 * @return WP_User
	 */
	public function get_current_user() {
		return wp_get_current_user();
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
		if (
			! empty( $this->redirect_to )
			&& ! strstr( $this->redirect_to, 'wp-login' )
			&& ! strstr( $this->redirect_to, 'wp-admin' )
		) {
			$this->set_flash( 'error', 'Sorry, login failed' );

			$page_url = apply_filters( 'mf_page_url', $this->get_page_url( 'dashboard' ), 'dashboard' );

			$this->set_redirect_url( $page_url );
			$this->do_redirect();
		}
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
	 * Get a page URL.
	 *
	 * @param string $name The page slug.
	 *
	 * @return string
	 */
	public function get_page_url( $name ) {
		switch ( $name ) {
			case 'dashboard':
				$url = get_page_link( $this->dashboard_page );
				break;
			case 'register':
				$url = get_page_link( $this->register_page );
				break;
			case 'logout':
				$url = $this->get_logout_url();
				break;
			default:
				$url = add_query_arg( 'page', $name, get_page_link( $this->dashboard_page ) );
		}

		return esc_url( $url );
	}

	/**
	 * Display the member registration view.
	 *
	 * @return string
	 */
	public function display_registration_form() {
		return $this->view( 'register' );
	}

	/**
	 * Display the member dashboard. Handles all logged in member pages.
	 *
	 * @return string
	 */
	public function display_member_dashboard() {
		$view = '';

		if ( is_user_logged_in() ) {

			$this->pages = apply_filters( 'mf_setup_pages', $this->pages );

			// Get the page template name.
			$template = isset( $_GET['page'] ) && in_array( $_GET['page'], $this->pages, true )
				? sanitize_text_field( wp_unslash( $_GET['page'] ) )
				: 'update-profile';

			// Run custom actions on post.
			if ( 'update-profile' !== $template && $this->is( 'post' ) ) {
				do_action( "mf_post_{$template}" );
			}

			$view = $this->view(
				$template,
				array(
					'user' => $this->get_current_user(),
				)
			);
		} else {
			$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null;

			if ( ! $action ) {
				$view = $this->view( 'login' );
			} elseif ( 'forgot' === $action ) {
				$view = $this->view( 'forgot' );
			} elseif ( 'reset' === $action ) {
				$view = $this->view( 'reset' );
			}
		}

		return $view;
	}

	/**
	 * Main router for data handling (eg. update account).
	 */
	public function handle_data_submission() {
		if ( is_user_logged_in() && (int) get_the_ID() === (int) $this->register_page ) {
			$this->do_redirect( get_page_link( $this->dashboard_page ) );
		}

		if ( isset( $_REQUEST['action'] ) ) {
			switch ( $_REQUEST['action'] ) {
				case 'update-profile':
					$this->update_profile();
					break;
				case 'register':
					$this->register();
					break;
				case 'forgot':
					// Only run if post.
					if ( $this->is( 'post' ) ) {
						$this->forgot_password();
					}

					break;
				case 'reset':
					// Check password reset key is valid.
					$this->validate_password_key();

					if ( $this->is( 'post' ) ) {
						$this->reset_password();
					}

					break;
			}
		}
	}

	/**
	 * Handle registration.
	 */
	public function register() {
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mf_register' )
		) {
			wp_nonce_ays( '' );
			die();
		}

		$user_data = $this->save_user();

		if ( is_wp_error( $user_data ) ) {
			$this->set_flash( 'error', $user_data->get_error_message() );
			$this->do_redirect( null, true );
		}

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
	public function save_user() {
		// Basic Fields to update.
		$user_data['first_name'] = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$user_data['last_name']  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';

		$user_data['user_email'] = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$user_data['user_pass']  = isset( $_POST['user_pass'] ) ? wp_unslash( $_POST['user_pass'] ) : '';

		$user_data['username'] = $user_data['user_email'];

		// Filter the user data array.
		$user_data = apply_filters( 'mf_user_before_save', $user_data );

		add_filter( 'mf_validate_user', array( $this, 'validateUser' ), 5, 2 );

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
		do_action( 'mf_user_register', $user, $user_data );

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
	 */
	public function forgot_password() {
		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( $_POST['user_login'] ) : '';

		if ( empty( $user_login ) ) {
			$this->set_flash( 'error', 'Email is required' );
			$this->set_redirect_url( $this->redirect_to, 'forgot' );
			$this->do_redirect();
		}

		$user_data = get_user_by( 'email', $user_login );

		if ( false !== $user_data ) {
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key        = get_password_reset_key( $user_data );

			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			// Load the email view.
			$message = $this->view(
				'email/forgot',
				array(
					'site_name'  => $site_name,
					'user_login' => $user_login,
					'key'        => $key,
				)
			);

			// translators: Password reset email subject. %s: Site name.
			$title = sprintf( __( '[%s] Password Reset' ), $site_name );
			$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

			$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

			wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
		}

		$this->set_flash( 'success', 'Please check your email for the confirmation link' );
		$this->set_redirect_url( $this->redirect_to, '' );
		$this->do_redirect();
	}

	/**
	 * Reset Password.
	 */
	protected function reset_password() {
		$username = isset( $_POST['login'] ) ? urldecode( $_POST['login'] ) : '';
		$user     = get_user_by( 'login', $username );

		if ( false !== $user ) {
			$new_password         = isset( $_POST['password'] ) ? $_POST['password'] : '';
			$new_password_confirm = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';

			$valid = $this->validate_password( $new_password, $new_password_confirm );

			if ( true === $valid ) {
				reset_password( $user, $new_password );
			} else {
				$this->set_flash( 'error', $valid );
				$this->set_redirect_url(
					$this->redirect_to,
					'reset',
					array(
						'key'   => $_POST['key'],
						'login' => $_POST['login'],
					)
				);
				$this->do_redirect();
			}

			$this->set_flash( 'success', 'Password reset successfully' );
			$this->set_redirect_url( $this->redirect_to, '' );
		} else {
			$this->set_flash( 'error', 'User not found' );
			$this->set_redirect_url( $this->redirect_to, 'reset' );
		}

		$this->do_redirect();
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
	 */
	protected function validate_password_key() {
		$key   = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : null;
		$login = isset( $_REQUEST['login'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['login'] ) ) : null;
		$user  = null;

		if ( isset( $key, $login ) ) {
			$user = check_password_reset_key( $key, $login );
		}

		// If password reset key is invalid display an error.
		if ( is_wp_error( $user ) || null === $user ) {
			$this->set_flash( 'error', 'Security token has expired' );
			$this->set_redirect_url( '', '' );
			$this->do_redirect();
		}
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
	 * @param string $password The password to validate.
	 * @param string $confirm_password The confirm password to validate.
	 *
	 * @return bool
	 */
	public function validate_password( $password, $confirm_password ) {
		if ( isset( $password ) && ! empty( $password ) && isset( $confirm_password ) && ! empty( $confirm_password ) ) {
			// Password and Confirm Password don't match.
			if ( ! isset( $confirm_password ) || ( isset( $confirm_password ) && $confirm_password !== $password ) ) {
				return 'Passwords do not match';
			}

			return true;
		}

		if ( ! isset( $password ) || empty( $password ) ) {
			return 'Please enter a password';
		} elseif ( ! isset( $confirm_password ) || empty( $confirm_password ) ) {
			return 'Please confirm your password';
		}

		return 'Please enter a password';
	}

	/**
	 * Set flash data.
	 *
	 * @param string $name The flash key.
	 * @param mixed  $data The flash data.
	 */
	public function set_flash( $name, $data ) {
		$_SESSION[ "flash_{$name}" ] = serialize( $data );
	}

	/**
	 * Check if flash data exists.
	 *
	 * @param string $name The flash key.
	 *
	 * @return bool
	 */
	public function has_flash( $name ) {
		return isset( $_SESSION[ "flash_{$name}" ] );
	}

	/**
	 * Retrieve flash data.
	 *
	 * @param string $name The flash key.
	 *
	 * @return mixed
	 */
	public function get_flash( $name ) {
		$data = isset( $_SESSION[ "flash_{$name}" ] ) ? $_SESSION[ "flash_{$name}" ] : false;

		if ( $data ) {
			unset( $_SESSION[ "flash_{$name}" ] );

			return unserialize( $data );
		}

		return false;
	}

	/**
	 * Delete flash data.
	 *
	 * @param string $name The flash key.
	 */
	public function forget_flash( $name ) {
		$data = isset( $_SESSION[ "flash_{$name}" ] ) ? $_SESSION[ "flash_{$name}" ] : false;

		if ( $data ) {
			unset( $_SESSION[ "flash_{$name}" ] );
		}
	}

	/**
	 * Retrieve old post data.
	 *
	 * @param string $name The post data key.
	 *
	 * @return mixed
	 */
	public function old( $name ) {
		$data = isset( $_SESSION['flash_input'] ) ? $_SESSION['flash_input'] : false;

		if ( $data ) {
			$data = unserialize( $data );

			if ( isset( $data[ $name ] ) ) {
				return esc_attr( $data[ $name ] );
			}
		}

		return '';
	}
}

new MemberFrontend();
