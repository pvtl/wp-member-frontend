<?php
/**
 * Plugin Name: Member Frontend & API by Pivotal
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Add a members area to WordPress with unlimited custom views.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Version: 1.4.1
 *
 * @package Member_Frontend
 */

use App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die();

// Path to plugin directory.
define( 'MF_PATH', __DIR__ );

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
