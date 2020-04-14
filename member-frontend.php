<?php
/**
 * Plugin Name: Member Frontend & API by Pivotal
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Adds a member frontend custom post type, taxonomy and fields. Also opens API endpoints for the abetterchoice mobile app.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Domain Path: /languages/
 * Version: 0.3.1
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl;

defined( 'ABSPATH' ) || die;

/**
 * Main MemberFrontend Class
 *
 * @class MemberFrontend
 */
class MemberFrontend {

    /**
     * MemberFrontend version.
     *
     * @var string
     */
    public $version = '0.3.0';

	/**
	 * Redirect URL for forms.
	 *
	 * @var string
	 */
	protected $redirectTo = '';

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
	public $dashboardPage;

    /**
     * The register page ID
     *
     * @var int
     */
    public $registerPage;

	public function __construct() {

	    // Start a session for flashing data
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}

		// If redirect_to is set, update the redirect URL.
		$this->setRedirectUrl(
			isset( $_REQUEST['redirect_to'] )
				? $_REQUEST['redirect_to']
				: ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' )
		);

		// WP actions and hooks
		$this->init();

        do_action( 'mf_loaded' );
	}

	/**
	 * Hook into WP actions and adds plugin shortcodes.
	 */
	protected function init() {

        $this->dashboardPage = get_theme_mod( 'mf_dashboard' );
        $this->registerPage = get_theme_mod( 'mf_register' );

        // Prevent members from accessing WordPress dashboard
        add_action( 'init', function () {
            if ( is_admin() && current_user_can( 'subscriber' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                $this->doRedirect( $this->getPageURL( 'dashboard' ) );
            }
        } );

        add_action( 'customize_register', array( $this, 'customizeRegister' ), 10, 1 );
		add_action( 'wp_login_failed', array( $this, 'interceptFailedLogin' ) );
		add_action( 'after_setup_theme', array( $this, 'removeAdminBar' ) );
		add_action( 'template_redirect', array( $this, 'handleDataSubmission' ) );
        add_action( 'wp_authenticate', array( $this, 'catchEmptyLogin' ), 1, 2 );

		add_shortcode( 'member-dashboard', array( $this, 'displayMemberDashboard' ) );
        add_shortcode( 'member-register', array( $this, 'displayRegisterForm' ) );
	}

	/**
	 * Check the current request method.
	 *
	 * @param string $method
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
		return strtolower( $_SERVER['REQUEST_METHOD'] );
	}

    /**
     * Add MemberFrontend options to the customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function customizeRegister( $wp_customize ) {

        $wp_customize->add_section( 'mf_section' , array(
            'title'      => __( 'Member Frontend', 'member-frontend' ),
            'priority'   => 30,
        ) );

        $wp_customize->add_setting( 'mf_dashboard' , array(
            'default'   => '',
            'transport' => 'refresh'
        ) );

        $wp_customize->add_control('mf_dashboard', array(
            'type'       => 'dropdown-pages',
            'label'      => __( 'Dashboard Page', 'member-frontend' ),
            'section'    => 'mf_section',
            'settings'   => 'mf_dashboard',
        ) );

        $wp_customize->add_setting( 'mf_register' , array(
            'default'   => '',
            'transport' => 'refresh'
        ) );

        $wp_customize->add_control('mf_register', array(
            'type'       => 'dropdown-pages',
            'label'      => __( 'Register Page', 'member-frontend' ),
            'section'    => 'mf_section',
            'settings'   => 'mf_register',
        ) );
    }

	/**
	 * Renders a view.
	 *
	 * @param string $name path to the view
	 * @param array  $vars optional variables to pass to scope
	 *
	 * @return string
	 */
	public function view( $name, $vars = array() ) {
		$includePath = "resources/views/{$name}.php";

		// Check for local override
		if ( $override = locate_template( "member-frontend/{$name}.php" ) ) {
			$includePath = $override;
		}

		// Extract the view vars
		extract( $vars );

		ob_start();

		require $includePath;

		$view = ob_get_clean();

		$this->forgetFlash( 'input' );

		return $view;
	}

    /**
     * Renders a view partial.
     *
     * @param string $name path to the partial
     *
     * @return string
     */
    protected function partial( $name ) {
        $includePath = "resources/views/partials/{$name}.php";

        // Check for local override
        if ( $override = locate_template( "member-frontend/partials/{$name}.php" ) ) {
            $includePath = $override;
        }

        ob_start();

        require $includePath;

        $view = ob_get_clean();

        return $view;
    }

	/**
	 * Safely perform redirect and die.
	 *
	 * @param string $url
	 * @param bool   $with_input
	 */
	public function doRedirect( $url = '', $with_input = false ) {
		if ( $with_input && $this->is( 'post' ) ) {
			$this->setFlash( 'input' , $_POST );
		}

		wp_safe_redirect( $url != '' ? $url : $this->redirectTo );
		die;
	}

	/**
	 * Build the redirect URL.
	 *
	 * @param string $path         URL path
	 * @param string $action       action to run
	 * @param array  $addParams    parameters to add to the query string
	 * @param array  $removeParams parameters to remove from the query string
	 *
	 * @return string
	 */
	public function setRedirectUrl( $path, $action = '', $addParams = array(), $removeParams = array() ) {
		$url = $path;

		// Add action
		if ( trim( $action ) !== '' ) {
			$addParams['action'] = $action;
		}

		// Add parameters to the URL
		if ( count( $addParams ) ) {
			$url = add_query_arg( $addParams, $url );
		}

		// Remove parameters from the URL
		if ( count( $removeParams ) ) {
			$url = remove_query_arg( $removeParams, $url );
		}

		$this->redirectTo = esc_url_raw( $url );

		return $url;
	}

	/**
	 * Get the current user.
	 *
	 * @return \WP_User
	 */
	public function getCurrentUser() {
		return wp_get_current_user();
	}

	/**
	 * Removes member top bar for front-end users.
	 */
	public function removeAdminBar() {
		if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * When the front-end login form fails, make sure
	 * it goes back to a front-end view.
	 */
	public function interceptFailedLogin() {
		if ( ! empty( $this->redirectTo )
		     && ! strstr( $this->redirectTo, 'wp-login' )
		     && ! strstr( $this->redirectTo, 'wp-admin' ) ) {
			$this->setFlash( 'error', 'Sorry, login failed' );
			$this->setRedirectUrl( $this->getPageURL( 'dashboard' ) );
			$this->doRedirect();
		}
	}

    /**
     * Catch empty login attempts.
     *
     * @param string $username
     * @param string $password
     */
    public function catchEmptyLogin( $username, $password ) {
        if ( empty( $username ) || empty( $password ) ) {
            $this->interceptFailedLogin();
        }
    }

	/**
	 * Get the Logout URL.
	 *
	 * @return string
	 */
	public function getLogoutURL() {
		$logout_url    = wp_logout_url();
		$logout_params = array( 'redirect_to' => get_permalink() );

		return esc_url( add_query_arg( $logout_params, $logout_url ) );
	}

    /**
     * Get a page URL.
     *
     * @param string $name Page slug
     *
     * @return string
     */
    public function getPageURL( $name ) {
        switch ( $name ) {
            case 'dashboard':
                $url = get_page_link( $this->dashboardPage );
                break;
            case 'register':
                $url = get_page_link( $this->registerPage );
                break;
            case 'logout':
                $url = $this->getLogoutURL();
                break;
            default:
                $url = add_query_arg( 'page', $name, get_page_link( $this->dashboardPage ) );
        }

        return esc_url( $url );
    }

	/**
	 * Display the member registration view.
	 *
	 * @return string
	 */
	public function displayRegisterForm() {
		return $this->view( 'register' );
	}

	/**
	 * Display the member dashboard.
     *
     * Handles all logged in member pages.
	 *
	 * @return string
	 */
	public function displayMemberDashboard() {

		$view = '';

		if ( is_user_logged_in() ) {

            $this->pages = apply_filters( 'mf_setup_pages', $this->pages );

		    // Get the page template name
		    $template = isset( $_GET['page'] ) && in_array( $_GET['page'], $this->pages )
                ? $_GET['page']
                : 'update-profile';

		    // Run custom actions on post
		    if ( $template !== 'update-profile' && $this->is( 'post' ) ) {
		        do_action( "mf_post_{$template}" );
            }

			$view = $this->view( $template, array(
				'user' => $this->getCurrentUser()
			) );

		} else {

            $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;

            if ( ! $action ) {
                $view = $this->view( 'login' );
            } elseif ( $action === 'forgot' ) {
                $view = $this->view( 'forgot' );
            } elseif ( $action === 'reset' ) {
                $view = $this->view( 'reset' );
            }

		}

		return $view;
	}

	/**
	 * Main router for data handling (eg. update account).
	 */
	public function handleDataSubmission() {

        if ( is_user_logged_in() && get_the_ID() == $this->registerPage ) {
            $this->doRedirect( get_page_link( $this->dashboardPage ) );
        }

		if ( isset( $_REQUEST['action'] ) ) {
			switch ( $_REQUEST['action'] ) {
				case 'update-profile':
					$this->updateProfile();
					break;
				case 'register':
					$this->register();
					break;
				case 'forgot':

					// Only run if post
					if ( $this->is( 'post' ) ) {
						$this->forgotPassword();
					}

					break;
				case 'reset':

					// Check password reset key is valid
					$this->validatePasswordKey();

					if ( $this->is( 'post' ) ) {
						$this->resetPassword();
					}

					break;
			}
		}
	}


    public function register()
    {
		if (! wp_verify_nonce( $_POST['_wpnonce'], 'mf_register' ) ) {
			wp_nonce_ays( '' );
			die();
        }

        $user_data = $this->saveUser();

        if ( is_wp_error( $user_data ) ) {
			$this->setFlash( 'error', $user_data->get_error_message() );
			$this->doRedirect( null, true );
        }

        $success_message = apply_filters( 'mf_registered_success_message', 'Account successfully created' );
        $this->setFlash( 'success', $success_message );
        $this->setRedirectUrl( $this->redirectTo, '' );

		$user 			   = get_user_by( 'id', $user_data['ID'] );
        $auto_login 	   = apply_filters( 'mf_auto_login', true );
        $register_redirect = apply_filters( 'mf_register_redirect', null, $user );

        if ( $auto_login ) {
            wp_signon( array(
                'user_login'    => $user_data['username'],
                'user_password' => $user_data['user_pass']
            ) );
        }

        $this->doRedirect( $register_redirect );
    }

	/**
	 * Sign Up for new account.
	 */
    public function saveUser()
    {
		// Basic Fields to update
		$user_data['first_name'] = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$user_data['last_name']  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';

		$user_data['user_email'] = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$user_data['user_pass'] = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';

        $user_data['username'] = $user_data['user_email'];

		// Filter the user data array
		$user_data = apply_filters( 'mf_user_before_save', $user_data );

		add_filter( 'mf_validate_user', array( $this, 'validateUser' ), 5, 2 );

        $errors = apply_filters( 'mf_validate_user', array(), $user_data );

		if ( count( $errors ) ) {
            return new \WP_Error('invalid_data',  $errors);
		}

		// Create User
		$user_id = wp_create_user( $user_data['username'], $user_data['user_pass'], $user_data['user_email'] );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;$this->setRedirectUrl( $this->redirectTo, '', array( 'updated' => 'failed' ) );
		}
		// Update user meta
		$user_data['ID'] = $user_id;

        // Update user data, minus password
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
	public function updateProfile() {
        // Current member
        $current_user = wp_get_current_user();

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mf_update_' . $current_user->ID ) ) {
			wp_nonce_ays( '' );
			die();
        }

        $userId = $this->saveProfileUpdates($current_user->ID);

        if ( is_wp_error( $userId ) ) {
            $this->setFlash( 'error', $userId->get_error_message() );
            $this->doRedirect( null, true );
		} else {
			$this->setFlash( 'success', 'Account updated successfully' );
			$this->setRedirectUrl( $this->redirectTo, '' );
        }

		$this->doRedirect();
    }

    public function saveProfileUpdates($userId)
    {
        $user_data = array( 'ID' => $userId );

		// Basic Fields to update
		$user_data['first_name'] = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$user_data['last_name']  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';

		$user_data['user_email'] = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$user_data['user_pass'] = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';

        // Filter the user data array
        $user_data = apply_filters( 'mf_user_before_save', $user_data );

        add_filter( 'mf_validate_user', array( $this, 'validateUser' ), 5, 2 );

        $errors = apply_filters( 'mf_validate_user', array(), $user_data );

        if ( count( $errors ) ) {
            return new \WP_Error('invalid_data', $errors);
        }

		// Send to WP to update
        $user_id = wp_update_user( $user_data );
        $user = get_user_by( 'id', $userId );

        do_action( 'mf_user_update', $user, $user_data );

        return $user_id;
    }

	/**
	 * Send Password Reset Email.
	 */
	public function forgotPassword() {
		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( $_POST['user_login'] ) : '';

		if ( empty( $user_login ) ) {
			$this->setFlash( 'error', 'Email is required' );
			$this->setRedirectUrl( $this->redirectTo, 'forgot' );
			$this->doRedirect();
		}

		$user_data = get_user_by( 'email', $user_login );

		if ( $user_data !== false ) {
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key        = get_password_reset_key( $user_data );

			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			// Load the email view
			$message = $this->view( 'email/forgot', array(
				'site_name'  => $site_name,
				'user_login' => $user_login,
				'key'        => $key
			) );

			/* translators: Password reset email subject. %s: Site name */
			$title = sprintf( __( '[%s] Password Reset' ), $site_name );
			$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

			$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

			wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
		}

		$this->setFlash( 'success', 'Please check your email for the confirmation link' );
		$this->setRedirectUrl( $this->redirectTo, '' );
		$this->doRedirect();
    }

	/**
	 * Reset Password.
	 */
	protected function resetPassword() {
		$username = isset( $_POST['login'] ) ? urldecode( $_POST['login'] ) : '';
		$user     = get_user_by( 'login', $username );

		if ( $user !== false ) {
			$newPassword      = isset( $_POST['pass1'] ) ? $_POST['pass1'] : '';
			$newPasswordCheck = isset( $_POST['pass2'] ) ? $_POST['pass2'] : '';

			$valid = $this->validatePassword( $newPassword, $newPasswordCheck );

			if ( $valid === true ) {
				reset_password( $user, $newPassword );
			} else {
			    $this->setFlash( 'error', $valid );
				$this->setRedirectUrl( $this->redirectTo, 'reset', array(
					'key'   => $_POST['key'],
					'login' => $_POST['login']
				) );
				$this->doRedirect();
			}

			$this->setFlash( 'success', 'Password reset successfully' );
			$this->setRedirectUrl( $this->redirectTo, '' );
		} else {
			$this->setFlash( 'error', 'User not found' );
			$this->setRedirectUrl( $this->redirectTo, 'reset' );
		}

		$this->doRedirect();
	}

    /**
     *
     *
     * @param $user_data
     * @param array $errors
     *
     * @return array
     */
    public function validateUser( $errors, $user_data ) {

        $email_error = $this->validateEmail( $user_data['user_email'] );
        if ( $email_error !== true ) {
            $errors['email'] = $this->validateEmail( $user_data['user_email'] );
        }

        if ( ! isset( $user_data['ID'] ) || ! empty( $user_data['user_pass'] ) ) {
            $password_error = $this->validatePassword( $user_data['user_pass'], $_POST['pass2'] );
            if ( $password_error !== true ) {
                $errors['user_pass'] = $this->validatePassword( $user_data['user_pass'], $_POST['pass2'] );
            }
        }

        return $errors;
    }

	/**
	 * validate the user password reset key.
	 */
	protected function validatePasswordKey() {
		$key   = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : null;
		$login = isset( $_REQUEST['login'] ) ? sanitize_text_field( $_REQUEST['login'] ) : null;
		$user  = null;

		if ( isset( $key, $login ) ) {
			$user = check_password_reset_key( $key, $login );
		}

		// If password reset key is invalid display an error
		if ( is_wp_error( $user ) || $user === null ) {
			$this->setFlash( 'error', 'Security token has expired' );
			$this->setRedirectUrl( '', '' );
			$this->doRedirect();
		}
	}

	/**
	 * Validate Email Input.
	 *
	 * @param string $email email to validate
	 *
	 * @return bool
	 */
	public function validateEmail( $email ) {
		$current_user = wp_get_current_user();

		if ( empty( $email ) ) {
			return 'Please enter a valid email address';
		} elseif ( ! is_email( $email ) ) {
			return 'Please enter a valid email address';
		} elseif ( $email != $current_user->user_email && ( email_exists( $email ) || username_exists( $email ) ) ) {
			return 'Email address already in use';
		}

		return true;
	}

	/**
	 * Validate Password Input.
	 *
	 * @param string $pass1 password to validate
	 * @param string $pass2 confirm password
	 *
	 * @return bool
	 */
	public function validatePassword( $pass1, $pass2 ) {

		if ( isset( $pass1 ) && ! empty( $pass1 ) && isset( $pass2 ) && ! empty( $pass2 ) ) {
			// Password and Confirm Password don't match
			if ( ! isset( $pass2 ) || ( isset( $pass2 ) && $pass2 != $pass1 ) ) {
				return 'Passwords do not match';
			}

			return true;
		}

		if ( ! isset( $pass1 ) || empty( $pass1 ) ) {
			return 'Please enter a password';
		} elseif ( ! isset( $pass2 ) || empty( $pass2 ) ) {
			return 'Please confirm your password';
		}

		return 'Please enter a password';
	}

	/**
	 *
	 *
	 * @param string $name
	 * @param mixed  $data
	 */
	public function setFlash( $name, $data ) {
		$_SESSION["flash_{$name}"] = serialize( $data );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasFlash( $name ) {
		return isset( $_SESSION["flash_{$name}"] );
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getFlash( $name ) {
		$data = isset( $_SESSION["flash_{$name}"] ) ? $_SESSION["flash_{$name}"] : false;

		if ( $data ) {
			unset( $_SESSION["flash_{$name}"] );
			return unserialize( $data );
		}

		return false;
	}

	/**
	 * @param string $name
	 */
	public function forgetFlash( $name ) {
		$data = isset( $_SESSION["flash_{$name}"] ) ? $_SESSION["flash_{$name}"] : false;

		if ( $data ) {
			unset( $_SESSION["flash_{$name}"] );
		}
	}

	/**
	 * @param $name
	 *
	 * @return string
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
