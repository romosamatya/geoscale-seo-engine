<?php
/**
 * SEO plugin integrations (Yoast, RankMath, Sitemaps).
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_SEO {

	/**
	 * Initialize SEO hooks.
	 */
	public function init() {
		// Yoast SEO integrations
		add_filter( 'wpseo_title', array( $this, 'override_title' ), 99 );
		add_filter( 'wpseo_metadesc', array( $this, 'override_description' ), 99 );

		// RankMath integrations
		add_filter( 'rank_math/frontend/title', array( $this, 'override_title' ), 99 );
		add_filter( 'rank_math/frontend/description', array( $this, 'override_description' ), 99 );

		// Core WordPress Sitemaps Integration
		add_action( 'init', array( $this, 'register_sitemap_provider' ) );
	}

	/**
	 * Override the title tag.
	 * Note: Expects the CSV to optionally contain a column named `seo_title`.
	 *
	 * @param string $title
	 * @return string
	 */
	public function override_title( $title ) {
		if ( empty( GeoScale_Router::$current_route_data ) || empty( GeoScale_Router::$current_route_data->payload ) ) {
			return $title;
		}

		$payload = GeoScale_Router::$current_route_data->payload;
		
		if ( ! empty( $payload['seo_title'] ) ) {
			return sanitize_text_field( $payload['seo_title'] );
		}

		return $title;
	}

	/**
	 * Override the meta description.
	 * Note: Expects the CSV to optionally contain a column named `seo_description`.
	 *
	 * @param string $description
	 * @return string
	 */
	public function override_description( $description ) {
		if ( empty( GeoScale_Router::$current_route_data ) || empty( GeoScale_Router::$current_route_data->payload ) ) {
			return $description;
		}

		$payload = GeoScale_Router::$current_route_data->payload;

		if ( ! empty( $payload['seo_description'] ) ) {
			return sanitize_text_field( $payload['seo_description'] );
		}

		return $description;
	}

	/**
	 * Register custom sitemap provider for WP GeoScale Routes.
	 */
	public function register_sitemap_provider() {
		if ( function_exists( 'wp_register_sitemap_provider' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'class-geoscale-sitemap-provider.php';
			wp_register_sitemap_provider( 'geoscale_routes', new GeoScale_Sitemap_Provider() );
		}
	}
}
