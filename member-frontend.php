<?php
/**
 * Plugin Name: Member Frontend & API by Pivotal
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Add a members area to WordPress with unlimited custom views.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Version: 1.2.2
 *
 * @package MemberFrontend
 */

use App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die();

// Path to plugin directory.
define( 'MF_PATH', __DIR__ );

// Autoload plugin classes.
require_once MF_PATH . '/autoload.php';

/**
 * Returns the main instance of MF.
 *
 * @return Classes\Member_Frontend
 */
function MF() { // phpcs:ignore WordPress.NamingConventions
	return Classes\Member_Frontend::instance();
}

MF();
