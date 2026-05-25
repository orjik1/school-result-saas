<?php
/**
 * Plugin Name: School Result SaaS
 * Plugin URI: https://school-result-saas.example.com
 * Description: A production-ready multi-tenant Exam & Result Management SaaS WordPress plugin
 * Version: 1.0.0
 * Author: Your Company
 * Author URI: https://example.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: school-result-saas
 * Domain Path: /languages
 * Requires PHP: 7.6
 * Requires WP: 5.0
 *
 * @package SchoolResultSaaS
 * @subpackage Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants
 */
define( 'SCHOOL_RESULT_SAAS_VERSION', '1.0.0' );
define( 'SCHOOL_RESULT_SAAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCHOOL_RESULT_SAAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCHOOL_RESULT_SAAS_INCLUDES_DIR', SCHOOL_RESULT_SAAS_PLUGIN_DIR . 'includes/' );
define( 'SCHOOL_RESULT_SAAS_TEMPLATES_DIR', SCHOOL_RESULT_SAAS_PLUGIN_DIR . 'templates/' );
define( 'SCHOOL_RESULT_SAAS_ASSETS_URL', SCHOOL_RESULT_SAAS_PLUGIN_URL . 'assets/' );

/**
 * Include the main plugin class
 */
require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'core/class-plugin-loader.php';

/**
 * Initialize the plugin
 *
 * @return void
 */
function school_result_saas_init() {
	$plugin = new \SchoolResultSaaS\Core\PluginLoader();
	$plugin->run();
}

add_action( 'plugins_loaded', 'school_result_saas_init' );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, function() {
	require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'core/class-activator.php';
	\SchoolResultSaaS\Core\Activator::activate();
} );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, function() {
	require_once SCHOOL_RESULT_SAAS_INCLUDES_DIR . 'core/class-deactivator.php';
	\SchoolResultSaaS\Core\Deactivator::deactivate();
} );
