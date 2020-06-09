<?php
/**
 * Plugin Name: Member Frontend & API by Pivotal
 * Plugin URI: https://github.com/pvtl/wp-member-frontend
 * Description: Adds a member frontend custom post type, taxonomy and fields.
 *              Also opens API endpoints for the abetterchoice mobile app.
 * Author: Pivotal Agency
 * Author URI: https://pivotal.agency
 * Text Domain: member-frontend
 * Version: 1.0.0
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl;

defined( 'ABSPATH' ) || die();

// Path to plugin directory.
define( 'MF_PATH', __DIR__ );

// Autoload plugin classes.
require_once MF_PATH . '/autoload.php';

new Classes\Member_Frontend();
