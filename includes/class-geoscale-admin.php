<?php
/**
 * Admin functionality for the plugin.
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Admin {

	/**
	 * Initialize admin hooks.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'process_csv_upload' ) );
	}

	/**
	 * Register the administration menu.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			'WP GeoScale',
			'GeoScale',
			'manage_options',
			'wp-geoscale',
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-admin-site-alt3',
			80
		);
	}

	/**
	 * Render the settings page.
	 */
	public function display_plugin_setup_page() {
		require_once WP_GEOSCALE_PLUGIN_DIR . 'views/admin-page-upload.php';
	}

	/**
	 * Process the CSV upload.
	 */
	public function process_csv_upload() {
		if ( ! isset( $_POST['geoscale_csv_upload'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized user' );
		}

		check_admin_referer( 'geoscale_csv_upload_action', 'geoscale_csv_upload_nonce' );

		if ( empty( $_FILES['geoscale_csv']['tmp_name'] ) ) {
			add_settings_error( 'geoscale_messages', 'geoscale_message', 'Please select a file to upload.', 'error' );
			return;
		}

		$template_post_id = isset( $_POST['template_post_id'] ) ? absint( $_POST['template_post_id'] ) : 0;
		if ( ! $template_post_id ) {
			add_settings_error( 'geoscale_messages', 'geoscale_message', 'Please select a master template.', 'error' );
			return;
		}

		$file = $_FILES['geoscale_csv']['tmp_name'];
		
		// Attempt to open the uploaded file
		if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
			global $wpdb;
			$table_name = GeoScale_DB::get_table_name();
			
			$headers = fgetcsv( $handle, 10000, ',' );
			if ( ! $headers ) {
				add_settings_error( 'geoscale_messages', 'geoscale_message', 'Invalid CSV format.', 'error' );
				return;
			}

			// Clean headers
			$headers = array_map( 'trim', $headers );

			// Check for route_slug column
			$slug_index = array_search( 'route_slug', $headers );
			if ( $slug_index === false ) {
				add_settings_error( 'geoscale_messages', 'geoscale_message', 'CSV must contain a column named "route_slug".', 'error' );
				return;
			}

			$success_count = 0;
			
			// Process each row
			while ( ( $data = fgetcsv( $handle, 10000, ',' ) ) !== false ) {
				if ( count( $headers ) !== count( $data ) ) {
					continue; // Skip malformed rows
				}

				$row_data = array_combine( $headers, $data );
				$route_slug = sanitize_title( $row_data['route_slug'] );
				
				if ( empty( $route_slug ) ) {
					continue;
				}

				// The rest of the row data becomes dynamic_data
				$dynamic_data = wp_json_encode( $row_data );

				// Insert or update based on route_slug
				$wpdb->replace(
					$table_name,
					array(
						'route_slug'       => $route_slug,
						'template_post_id' => $template_post_id,
						'dynamic_data'     => $dynamic_data,
						'is_active'        => 1,
					),
					array(
						'%s',
						'%d',
						'%s',
						'%d'
					)
				);
				$success_count++;
			}
			fclose( $handle );
			
			add_settings_error( 'geoscale_messages', 'geoscale_message', sprintf( 'Successfully imported %d routes.', $success_count ), 'updated' );
		}
	}
}
