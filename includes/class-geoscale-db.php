<?php
/**
 * Database management for the plugin.
 *
 * @package WP_GeoScale
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_DB {

	/**
	 * Custom tables names.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'geoscale_routes';
	}

	public static function get_tasks_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'geoscale_tasks';
	}

	/**
	 * Create or update the custom database tables.
	 */
	public static function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// 1. Tasks Table
		$tasks_table = self::get_tasks_table_name();
		$sql_tasks = "CREATE TABLE $tasks_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			task_name varchar(255) NOT NULL,
			template_post_id bigint(20) unsigned NOT NULL,
			row_count int(11) NOT NULL DEFAULT 0,
			status varchar(50) NOT NULL DEFAULT 'processing',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql_tasks );

		// 2. Routes Table
		$routes_table = self::get_table_name();
		$sql_routes = "CREATE TABLE $routes_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			task_id bigint(20) unsigned NOT NULL,
			route_slug varchar(255) NOT NULL,
			template_post_id bigint(20) unsigned NOT NULL,
			dynamic_data longtext NOT NULL,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			UNIQUE KEY route_slug (route_slug),
			KEY template_post_id (template_post_id),
			KEY task_id (task_id)
		) $charset_collate;";
		dbDelta( $sql_routes );
	}
}
