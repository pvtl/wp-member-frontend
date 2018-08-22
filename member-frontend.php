<?php
// phpcs:disable PSR1.Files.SideEffects
/**
 * Plugin Name:     Member Frontend by Pivotal
 * Plugin URI:      https://github.com/pvtl/wp-member-frontend.git
 * Description:     Adds a member frontend custom post type, taxonomy and fields
 * Author:          Pivotal Agency
 * Author URI:      http://pivotal.agency
 * Text Domain:     member-frontend
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Member_Frontend
 */

namespace App\Plugins\Pvtl;

class MemberFrontend
{
    // The name of the plugin (for cosmetic purposes)
    protected $pluginName = 'Member Frontend';

    // Redirect URL for forms
    protected $redirectURL = '';

    public function __construct()
    {
        $this->redirectURL =(isset($_REQUEST['redirect_to'])) ? $_REQUEST['redirect_to'] : $_SERVER['HTTP_REFERER'];

        // Call the actions/hooks
        add_action('wp_login_failed', array($this, 'interceptFrontendFailedLoginURL'));
        add_action('after_setup_theme', array($this, 'removeAdminBar'));
        add_action('template_redirect', array($this, 'handleDataSubmission'));

        // Shortcodes
        add_shortcode('member-dashboard', array($this, 'displayMemberDashboard'));
        add_shortcode('member-register', array($this, 'displayRegisterForm'));
    }

    /**
     * Removes member top bar for front-end users
     */
    public function removeAdminBar() {
        if (!current_user_can('administrator') && !is_admin()) {
          show_admin_bar(false);
        }
    }

    /**
     * When the front-end login form fails, make sure it goes back to the front-end form
     * @param  str $username - the username submitted
     * @return redirect
     */
    public function interceptFrontendFailedLoginURL($username) {
        if (
            !empty($this->redirectURL)
            && !strstr($this->redirectURL, 'wp-login')
            && !strstr($this->redirectURL,'wp-admin')
        ) {
           wp_redirect($this->redirectURL . '/?message-error=failed');
           exit;
        }
    }

    /**
     * Get the Logout URL
     * @return str - the url
     */
    private function getLogoutURL() {
        $logout_url = wp_logout_url();
        $logout_params = array('redirect_to' => get_permalink());
        return esc_url(add_query_arg($logout_params, $logout_url));
    }

    public function displayRegisterForm() {
        return $this->displayMemberDashboard('register');
    }

    /**
     * Display the login form - used by a shortcode [member-dashboard]
     * @return str - html form
     */
    public function displayMemberDashboard($type = '') {
        ob_start();

        // Show update profile form
        if (is_user_logged_in()) {
            $this->includeTemplate('update-profile.php');

        } else {
            // Show sign up Form
            if (isset($type) && $type === 'register') {
                $this->includeTemplate('register.php');

            // Show login form
            } else {
                $this->includeTemplate('login.php');
            }
        }

        return ob_get_clean();
    }

    private function includeTemplate($template) {
        $current_user = wp_get_current_user(); // Current member

        if ($overridden_template = locate_template('member-frontend/' . $template)) {
            require($overridden_template);
        } else {
            require('resources/views/' . $template);
        }
    }

    /**
     * Main router for data handling (eg. update account)
     * @return redirects to respective page
     */
    public function handleDataSubmission() {
        if (isset($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'update-profile' :
                    return $this->updateProfile();
                    break;
                case 'register' :
                    return $this->register();
                    break;
            }

            exit;
        }
    }

    /**
     * Sign Up for new account
     * @return redirects to respective page
     */
    public function register() {
        // Basic Fields to update
        $userData['first_name'] = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $userData['last_name'] = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

        // Email Validation
        $userData['user_email'] = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (!$this->validateEmail($userData['user_email'])) return wp_redirect($this->redirectURL);

        // Username Validation
        $userData['username'] = $userData['user_email'];
        if (username_exists($userData['username'])) {
            $this->redirectURL = esc_url(add_query_arg('message-error', 'username/email is already in use', $url));
            return wp_redirect($this->redirectURL);
        }

        // Password Validation
        $userData['user_pass'] = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        // Only validate when user input a password to update
        if (!$this->validatePassword($userData['user_pass'], $_POST['pass2'])) return wp_redirect($this->redirectURL);

        // Create User
        $user_id = wp_create_user($userData['username'], $userData['user_pass'], $userData['user_email']);

        // Update to DB Failed
        if (is_wp_error($user_id)) {
            $this->redirectURL = esc_url(add_query_arg('updated', 'failed', $this->redirectURL));

        // Overall Success
        } else {
            $this->redirectURL = esc_url(add_query_arg('message-success', 'profile created', $this->redirectURL));
        }

        // Update user with full details (name)
        $userData['ID'] = $user_id;
        wp_update_user($userData);

        // Auto-login
        wp_signon(array('user_login' => $userData['username'], 'user_password' => $userData['user_pass']), false);

        wp_redirect($this->redirectURL);
    }

    /**
     * Update User Profile
     * @return redirects to respective page
     */
    public function updateProfile() {
        // Current member
        $current_user = wp_get_current_user();
        $userData = array('ID' => $current_user->ID);

        // Basic Fields to update
        $userData['first_name'] = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $userData['last_name'] = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

        // Email Validation
        $userData['user_email'] = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (!$this->validateEmail($userData['user_email'])) return wp_redirect($this->redirectURL);

        // Password Validation
        $userData['user_pass'] = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        // Only validate when user input a password to update
        if (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
            if (!$this->validatePassword($userData['user_pass'], $_POST['pass2'])) {
                return wp_redirect($this->redirectURL);
            }
        }

        // Send to WP to update
        $user_id = wp_update_user($userData);

        // Update to DB Failed
        if (is_wp_error($user_id)) {
            $this->redirectURL = esc_url(add_query_arg('updated', 'failed', $this->redirectURL));

        // Overall Success
        } else {
            $this->redirectURL = esc_url(add_query_arg('message-success', 'profile updated', $this->redirectURL));
        }

        wp_redirect($this->redirectURL);
    }

    /**
     * Validate Email Input
     * @param str email to validate
     * @return bool - true = successfully validated
     */
    public function validateEmail($email) {
        $current_user = wp_get_current_user();

        // Email can't be empty
        if (!$email || empty($email)) {
            $this->redirectURL = esc_url(add_query_arg('message-error', 'email cannot be empty', $this->redirectURL));

        // Needs to be correct format
        } elseif (!is_email($email)) {
            $this->redirectURL = esc_url(add_query_arg('message-error', 'email is not a correct format', $this->redirectURL));

        // Email can't be on another account
        } elseif (($email != $current_user->user_email) && email_exists($email)) {
            $this->redirectURL = esc_url(add_query_arg('message-error', 'another user is using this email address', $this->redirectURL));

        // Successful validation
        } else {
            return true;
        }

        return false;
    }

    /**
     * Validate Password Input
     * @param str password to validate
     * @return bool - true = successfully validated
     */
    public function validatePassword($pass1, $pass2) {
        if (isset($pass1) && !empty($pass1)) {
            // Password and Confirm Password don't match
            if (!isset($pass2) || (isset($pass2) && $pass2 != $pass1)) {
                $this->redirectURL = esc_url(add_query_arg('message-error', 'passwords do not match', $url));

            // Successful validation
            } else {
                return true;
            }
        } else {
            $this->redirectURL = esc_url(add_query_arg('message-error', 'password/s missing', $url));
        }

        return false;
    }
}

if (!defined('ABSPATH')) {
    exit;  // Exit if accessed directly
}

$pvtlMemberFrontend = new MemberFrontend();
