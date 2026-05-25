<?php
/**
 * Database management for the plugin.
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_DB {

	/**
	 * Custom table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'geoscale_routes';
	}

	/**
	 * Create or update the custom database table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		// Schema defining a Flat Index + JSON Payload architecture.
		// Note: dbDelta requires very specific formatting (e.g. two spaces after PRIMARY KEY).
		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			route_slug varchar(255) NOT NULL,
			template_post_id bigint(20) unsigned NOT NULL,
			dynamic_data longtext NOT NULL,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			UNIQUE KEY route_slug (route_slug),
			KEY template_post_id (template_post_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
