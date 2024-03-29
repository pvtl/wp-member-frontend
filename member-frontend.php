<?php
/**
 * Plugin Name: Member Frontend
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Add a members area and functionality to WordPress.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Version: 1.10.0
 *
 * @package Member_Frontend
 */

use App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die();

// Path to plugin directory.
define( 'MF_PATH', __DIR__ );

// URL to plugin directory.
define( 'MF_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Autoload plugin classes.
require_once MF_PATH . '/autoload.php';

// Load helper functions.
require_once MF_PATH . '/helpers.php';

/**
 * Returns the main instance of MF.
 *
 * @return Classes\Member_Frontend
 */
function MF() { // phpcs:ignore WordPress.NamingConventions
	return Classes\Member_Frontend::instance();
}

MF();
