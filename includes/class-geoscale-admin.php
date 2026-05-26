<?php
/**
 * Admin functionality for the plugin.
 *
 * @package GeoScale
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_react_app' ) );
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'GeoScale – Programmatic SEO Engine',
			'GeoScale',
			'manage_options',
			'geoscale-engine',
			array( $this, 'display_react_container' ),
			'dashicons-admin-site-alt3',
			80
		);
	}

	public function enqueue_react_app( $hook_suffix ) {
		if ( $hook_suffix !== 'toplevel_page_geoscale-engine' ) {
			return;
		}

		$asset_file_path = GEOSCALE_PLUGIN_DIR . 'build/index.asset.php';
		if ( ! file_exists( $asset_file_path ) ) {
			return; // React build not found
		}

		$asset_file = require $asset_file_path;

		wp_enqueue_script(
			'geoscale-react-app',
			GEOSCALE_PLUGIN_URL . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script( 'geoscale-react-app', 'geoscaleApiData', array(
			'root'       => esc_url_raw( rest_url() ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'is_premium' => wg_fs()->can_use_premium_code(),
		) );
	}

	public function display_react_container() {
		echo '<div id="geoscale-admin-app"></div>';
	}
}
