<?php
/**
 * Plugin Name: GeoScale – Programmatic SEO Engine
 * Plugin URI:  https://github.com/romosamatya/geoscale-seo-engine
 * Description: Programmatic SEO engine to generate virtual landing pages from CSV data without cluttering wp_posts.
 * Version:     1.0.0
 * Author:      romosamatya
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: geoscale
 *
 * @package GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants.
 */
define( 'GEOSCALE_VERSION', '1.0.0' );
define( 'GEOSCALE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GEOSCALE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load core dependencies via Composer Autoloader.
 */
if ( file_exists( GEOSCALE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once GEOSCALE_PLUGIN_DIR . 'vendor/autoload.php';
}

// Action Scheduler requires explicit initialization when bundled via Composer.
if ( file_exists( GEOSCALE_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
	require_once GEOSCALE_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
}

require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-db.php';
require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-batch.php';
require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-router.php';
require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-shortcodes.php';
require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-api.php';
require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-admin.php';

/**
 * Plugin activation hook.
 */
function geoscale_activate() {
	GeoScale_DB::create_table();

	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-router.php';
	$router = new GeoScale_Router();
	$router->add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'geoscale_activate' );

/**
 * Plugin deactivation hook.
 */
function geoscale_deactivate() {
	// Do not drop the table on deactivation to preserve user data.
}
register_deactivation_hook( __FILE__, 'geoscale_deactivate' );

/**
 * Initialize the plugin.
 */
function geoscale_run() {
	$plugin_batch = new GeoScale_Batch();
	$plugin_batch->init();

	$plugin_router = new GeoScale_Router();
	$plugin_router->init();

	$plugin_shortcodes = new GeoScale_Shortcodes();
	$plugin_shortcodes->init();

	if ( is_admin() ) {
		$plugin_admin = new GeoScale_Admin();
		$plugin_admin->init();
	}

	$plugin_api = new GeoScale_API();
	$plugin_api->init();
}
add_action( 'plugins_loaded', 'geoscale_run' );
