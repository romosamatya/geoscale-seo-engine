<?php
/**
 * Core WordPress Sitemap Provider for GeoScale Routes.
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'WP_Sitemaps_Provider' ) ) {
	return; // Sitemaps API introduced in WP 5.5
}

class GeoScale_Sitemap_Provider extends WP_Sitemaps_Provider {

	public function __construct() {
		$this->name        = 'geoscale';
		$this->object_type = 'geoscale_route';
	}

	/**
	 * Gets a URL list for a sitemap.
	 *
	 * @param int    $page_num Page of results.
	 * @param string $object_subtype Optional. Object subtype name. Default empty.
	 * @return array[] Array of URL information for a sitemap.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		global $wpdb;
		$table_name = GeoScale_DB::get_table_name();
		
		$limit  = wp_sitemaps_get_max_urls( $this->object_type );
		$offset = ( $page_num - 1 ) * $limit;

		$routes = $wpdb->get_results( $wpdb->prepare(
			"SELECT route_slug FROM {$table_name} WHERE is_active = 1 LIMIT %d OFFSET %d",
			$limit,
			$offset
		) );

		$url_list = array();

		foreach ( $routes as $route ) {
			$url_list[] = array(
				'loc' => home_url( '/locations/' . $route->route_slug . '/' ),
			);
		}

		return $url_list;
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @param string $object_subtype Optional. Object subtype. Default empty.
	 * @return int Total number of pages.
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		global $wpdb;
		$table_name = GeoScale_DB::get_table_name();

		$total = $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE is_active = 1" );

		return (int) ceil( $total / wp_sitemaps_get_max_urls( $this->object_type ) );
	}
}
