<?php
/**
 * Plugin Name: GeoScale – Programmatic SEO Engine
 * Plugin URI:  https://github.com/romosamatya/geoscale-seo-engine
 * Description: Programmatic SEO engine to generate virtual landing pages from CSV data without cluttering wp_posts.
 * Version:     1.0.0
 * Author:      romosamatya
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: geoscale-programmatic-seo-engine
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

if ( function_exists( 'wg_fs' ) ) {
	wg_fs()->set_basename( true, __FILE__ );
} else {
	// DO NOT REMOVE THIS IF. Essential for Freemius auto-deactivate mechanics.
	if ( ! function_exists( 'wg_fs' ) ) {
		// Create a helper function for easy SDK access.
		function wg_fs() {
			global $wg_fs;

			if ( ! isset( $wg_fs ) ) {
				// SDK is auto-loaded through Composer
				$wg_fs = fs_dynamic_init( array(
					'id'                  => '30540',
					'slug'                => 'wp-geoscale',
					'type'                => 'plugin',
					'public_key'          => 'pk_96c205fef23171eb3cfd72b82e77d',
					'is_premium'          => true,
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'is_org_compliant'    => true,
					'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
					'menu'                => array(
						'slug'           => 'wp-geoscale',
						'first-path'     => 'admin.php?page=wp-geoscale',
						'support'        => false,
					),
				) );
			}

			return $wg_fs;
		}

		wg_fs(); // Init Freemius
		do_action( 'wg_fs_loaded' ); // Signal that SDK was initiated
	}

	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-db.php';
	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-router.php';
	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-shortcodes.php';
	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-api.php';
	require_once GEOSCALE_PLUGIN_DIR . 'includes/class-geoscale-admin.php';

	// Conditional Pro Loader tied to Freemius License
	if ( wg_fs()->can_use_premium_code() && file_exists( GEOSCALE_PLUGIN_DIR . 'includes/pro/class-geoscale-pro.php' ) ) {
		require_once GEOSCALE_PLUGIN_DIR . 'includes/pro/class-geoscale-pro.php';
	}

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

		if ( wg_fs()->can_use_premium_code() && class_exists( 'GeoScale_Pro' ) ) {
			$geoscale_pro = new GeoScale_Pro();
			$geoscale_pro->init();
		}
	}
	add_action( 'plugins_loaded', 'geoscale_run' );
}
