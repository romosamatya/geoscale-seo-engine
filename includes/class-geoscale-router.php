<?php
/**
 * The Virtual Routing Engine
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Router {

	/**
	 * Holds the decoded JSON payload for the current virtual route.
	 *
	 * @var array|null
	 */
	public static $current_route_data = null;

	/**
	 * Initialize routing hooks.
	 */
	public function init() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'intercept_request' ) );
		add_filter( 'template_include', array( $this, 'load_master_template' ) );
		add_action( 'save_post', array( $this, 'purge_cache_on_save' ) );
	}

	/**
	 * Register custom rewrite rule.
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule(
			'^locations/([^/]+)/?$',
			'index.php?geoscale_route=$matches[1]',
			'top'
		);
	}

	/**
	 * Register custom query variable.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'geoscale_route';
		return $vars;
	}

	/**
	 * Perform highly optimized lookup and handle HTTP headers.
	 */
	public function intercept_request() {
		$route_slug = get_query_var( 'geoscale_route' );

		if ( empty( $route_slug ) ) {
			return;
		}

		$route_data = $this->get_route_data( $route_slug );

		if ( ! $route_data ) {
			// If not found, force WordPress 404.
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			return;
		}

		// Found! Store payload in memory for shortcodes.
		self::$current_route_data = $route_data;

		// The virtual pages must return a 200 OK HTTP status code.
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
	}

	/**
	 * Retrieve route data from object cache or database.
	 *
	 * @param string $route_slug
	 * @return object|false
	 */
	private function get_route_data( $route_slug ) {
		$cache_key   = 'geoscale_route_' . md5( $route_slug );
		$cache_group = 'geoscale';
		
		$route_data = wp_cache_get( $cache_key, $cache_group );

		if ( false === $route_data ) {
			global $wpdb;
			$table_name = GeoScale_DB::get_table_name();

			// Highly optimized lookup against UNIQUE KEY index
			$safe_table = esc_sql( $table_name );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare(
				"SELECT template_post_id, dynamic_data FROM `{$safe_table}` WHERE route_slug = %s AND is_active = 1 LIMIT 1",
				$route_slug
			);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			$route_data = $wpdb->get_row( $sql );

			if ( ! $route_data ) {
				// Cache 'not_found' to bypass MySQL on repeat 404s
				wp_cache_set( $cache_key, 'not_found', $cache_group, 3600 );
			} else {
				// Decode the JSON payload immediately for memory
				$route_data->payload = json_decode( $route_data->dynamic_data, true );
				wp_cache_set( $cache_key, $route_data, $cache_group, 12 * HOUR_IN_SECONDS );
			}
		}

		if ( 'not_found' === $route_data ) {
			return false;
		}

		return $route_data;
	}

	/**
	 * Swap the template to the master template.
	 */
	public function load_master_template( $template ) {
		$route_slug = get_query_var( 'geoscale_route' );

		if ( empty( $route_slug ) || ! self::$current_route_data ) {
			return $template;
		}

		$template_post_id = self::$current_route_data->template_post_id;
		$post = get_post( $template_post_id );

		if ( $post ) {
			// Setup global post data so the template functions correctly
			global $wp_query;
			$wp_query->queried_object = $post;
			$wp_query->queried_object_id = $post->ID;
			$wp_query->post = $post;
			$wp_query->posts = array( $post );
			$wp_query->post_count = 1;
			$wp_query->is_404 = false;
			$wp_query->is_page = ( 'page' === $post->post_type );
			$wp_query->is_single = ( 'post' === $post->post_type );
			$wp_query->is_singular = true;

			// Check for custom assigned template
			$custom_template = get_page_template_slug( $post->ID );
			if ( $custom_template ) {
				$located = locate_template( $custom_template );
				if ( ! empty( $located ) ) {
					return $located;
				}
			}

			// Fallback to standard hierarchy
			if ( 'page' === $post->post_type ) {
				$located = get_query_template( 'page' );
				if ( $located ) {
					return $located;
				}
			} else {
				$located = get_query_template( 'single' );
				if ( $located ) {
					return $located;
				}
			}
		}
		
		return $template;
	}

	/**
	 * Purge object cache for virtual routes when their master template is updated.
	 */
	public function purge_cache_on_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		global $wpdb;
		$table_name = GeoScale_DB::get_table_name();

		$safe_table = esc_sql( $table_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$routes = $wpdb->get_col( $wpdb->prepare(
			"SELECT route_slug FROM `{$safe_table}` WHERE template_post_id = %d",
			$post_id
		) );

		if ( ! empty( $routes ) ) {
			foreach ( $routes as $slug ) {
				$cache_key = 'geoscale_route_' . md5( $slug );
				wp_cache_delete( $cache_key, 'geoscale' );
			}
		}
	}
}
