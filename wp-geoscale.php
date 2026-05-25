<?php
/**
 * Plugin Name: WP GeoScale
 * Plugin URI:  https://example.com/
 * Description: Programmatic SEO engine to generate virtual landing pages from CSV data without cluttering wp_posts.
 * Version:     1.0.0
 * Author:      Elite Plugin Developer
 * Text Domain: wp-geoscale
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants.
 */
define( 'WP_GEOSCALE_VERSION', '1.0.0' );
define( 'WP_GEOSCALE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_GEOSCALE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load core dependencies.
 */
if ( file_exists( WP_GEOSCALE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once WP_GEOSCALE_PLUGIN_DIR . 'vendor/autoload.php';
}

// Action Scheduler requires explicit initialization when bundled via Composer
if ( file_exists( WP_GEOSCALE_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
	require_once WP_GEOSCALE_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
}

require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-db.php';

/**
 * Plugin activation hook.
 */
function activate_wp_geoscale() {
	GeoScale_DB::create_table();
	
	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-router.php';
	$router = new GeoScale_Router();
	$router->add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_wp_geoscale' );

/**
 * Plugin deactivation hook.
 */
function deactivate_wp_geoscale() {
	// Typically, we do not drop the table on deactivation to preserve user data.
}
register_deactivation_hook( __FILE__, 'deactivate_wp_geoscale' );

/**
 * Initialize the plugin.
 */
function run_wp_geoscale() {
	if ( is_admin() ) {
		require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-admin.php';
		$plugin_admin = new GeoScale_Admin();
		$plugin_admin->init();
	}

	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-api.php';
	$plugin_api = new GeoScale_API();
	$plugin_api->init();

	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-batch.php';
	$plugin_batch = new GeoScale_Batch();
	$plugin_batch->init();

	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-router.php';
	$plugin_router = new GeoScale_Router();
	$plugin_router->init();

	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-shortcodes.php';
	$plugin_shortcodes = new GeoScale_Shortcodes();
	$plugin_shortcodes->init();

	require_once WP_GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-seo.php';
	$plugin_seo = new GeoScale_SEO();
	$plugin_seo->init();
}
add_action( 'plugins_loaded', 'run_wp_geoscale' );
