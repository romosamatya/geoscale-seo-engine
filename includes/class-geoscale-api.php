<?php
/**
 * REST API Endpoints for React SPA.
 *
 * @package WP_GeoScale
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_API {

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$namespace = 'geoscale/v1';

		register_rest_route( $namespace, '/upload', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'handle_upload' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );

		register_rest_route( $namespace, '/status', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'handle_status' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );

		register_rest_route( $namespace, '/schema', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_schema' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_schema' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			),
		) );

		// V3 Routes for Tasks Management
		register_rest_route( $namespace, '/tasks', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_tasks' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );

		register_rest_route( $namespace, '/tasks/(?P<id>\d+)/routes', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_task_routes' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );

		register_rest_route( $namespace, '/tasks/bulk-action', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bulk_action' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );
	}

	public function permissions_check() {
		return current_user_can( 'manage_options' );
	}

	public function handle_upload( WP_REST_Request $request ) {
		$files = $request->get_file_params();
		$template_post_id = $request->get_param( 'template_post_id' );

		if ( empty( $files['csv_file'] ) || empty( $template_post_id ) ) {
			return new WP_Error( 'missing_params', 'Missing CSV file or template ID.', array( 'status' => 400 ) );
		}

		$file = $files['csv_file'];

		// Secure upload directory
		$upload_dir = wp_upload_dir();
		$geoscale_dir = $upload_dir['basedir'] . '/geoscale_temp';
		if ( ! file_exists( $geoscale_dir ) ) {
			wp_mkdir_p( $geoscale_dir );
			file_put_contents( $geoscale_dir . '/.htaccess', "deny from all\n" );
			file_put_contents( $geoscale_dir . '/index.php', "<?php\n// Silence is golden.\n" );
		}

		$file_path = $geoscale_dir . '/' . sanitize_file_name( time() . '_' . $file['name'] );
		move_uploaded_file( $file['tmp_name'], $file_path );

		// Parse total rows
		$total_rows = 0;
		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			$headers = fgetcsv( $handle, 10000, ',' ); // Header row
			if ( $headers ) {
				$slug_index = array_search( 'route_slug', array_map( 'trim', $headers ) );
				if ( $slug_index === false ) {
					fclose( $handle );
					unlink( $file_path );
					return new WP_Error( 'invalid_csv', 'CSV must contain route_slug column.', array( 'status' => 400 ) );
				}
				while ( fgetcsv( $handle, 10000, ',' ) !== false ) {
					$total_rows++;
				}
			}
			fclose( $handle );
		}

		if ( $total_rows > 0 ) {
			global $wpdb;
			$tasks_table = GeoScale_DB::get_tasks_table_name();
			$wpdb->insert(
				$tasks_table,
				array(
					'task_name'        => sanitize_file_name( $file['name'] ),
					'template_post_id' => absint( $template_post_id ),
					'row_count'        => $total_rows,
					'status'           => 'processing',
				)
			);
			$task_id = $wpdb->insert_id;

			GeoScale_Batch::schedule_csv_processing( $file_path, absint( $template_post_id ), $total_rows, $task_id );
		}

		return rest_ensure_response( array(
			'success'    => true,
			'message'    => 'Upload successful. Processing started in background.',
			'total_rows' => $total_rows,
		) );
	}

	public function handle_status() {
		if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
			return rest_ensure_response( array( 'pending' => 0, 'in_progress' => 0, 'active_jobs' => 0 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'actionscheduler_actions';
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) {
			return rest_ensure_response( array( 'pending' => 0, 'in_progress' => 0, 'active_jobs' => 0 ) );
		}
		
		$pending = $wpdb->get_var( "SELECT COUNT(action_id) FROM {$table} WHERE hook = 'geoscale_process_csv_chunk' AND status = 'pending'" );
		$in_progress = $wpdb->get_var( "SELECT COUNT(action_id) FROM {$table} WHERE hook = 'geoscale_process_csv_chunk' AND status = 'in-progress'" );

		return rest_ensure_response( array(
			'pending'     => (int) $pending,
			'in_progress' => (int) $in_progress,
			'active_jobs' => (int) $pending + (int) $in_progress,
		) );
	}

	public function get_schema() {
		return rest_ensure_response( array(
			'schema' => get_option( 'geoscale_schema_template', '' )
		) );
	}

	public function save_schema( WP_REST_Request $request ) {
		$schema = $request->get_param( 'schema' );
		update_option( 'geoscale_schema_template', $schema );
		return rest_ensure_response( array( 'success' => true, 'message' => 'Schema saved successfully.' ) );
	}

	// --- V3 Task Endpoints ---

	public function get_tasks() {
		global $wpdb;
		$tasks_table = GeoScale_DB::get_tasks_table_name();

		if ( $wpdb->get_var("SHOW TABLES LIKE '$tasks_table'") != $tasks_table ) {
			return rest_ensure_response( array() );
		}

		$tasks = $wpdb->get_results( "SELECT * FROM {$tasks_table} ORDER BY created_at DESC" );
		return rest_ensure_response( $tasks );
	}

	public function get_task_routes( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = GeoScale_DB::get_table_name();
		
		$task_id = $request->get_param( 'id' );
		$page    = max( 1, absint( $request->get_param( 'page' ) ) );
		$per_page = 50;
		$search  = sanitize_text_field( $request->get_param( 'search' ) );

		$offset = ( $page - 1 ) * $per_page;
		$where = $wpdb->prepare( "WHERE task_id = %d", $task_id );

		if ( ! empty( $search ) ) {
			$where .= $wpdb->prepare( " AND route_slug LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$total = $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} {$where}" );
		$routes = $wpdb->get_results( "SELECT id, route_slug, is_active, dynamic_data FROM {$table_name} {$where} ORDER BY id ASC LIMIT {$per_page} OFFSET {$offset}" );

		// Decode dynamic_data for the frontend preview
		foreach ( $routes as &$route ) {
			$route->dynamic_data = json_decode( $route->dynamic_data );
		}

		return rest_ensure_response( array(
			'routes' => $routes,
			'total'  => (int) $total,
			'pages'  => ceil( $total / $per_page ),
		) );
	}

	public function bulk_action( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = GeoScale_DB::get_table_name();
		$tasks_table = GeoScale_DB::get_tasks_table_name();

		$task_id = absint( $request->get_param( 'task_id' ) );
		$action  = sanitize_text_field( $request->get_param( 'action' ) );

		if ( ! $task_id || ! in_array( $action, array( 'activate', 'deactivate', 'delete' ) ) ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		if ( 'delete' === $action ) {
			$wpdb->delete( $table_name, array( 'task_id' => $task_id ) );
			$wpdb->delete( $tasks_table, array( 'id' => $task_id ) );
			$message = 'Campaign and all associated routes deleted successfully.';
		} else {
			$is_active = ( 'activate' === $action ) ? 1 : 0;
			$wpdb->update( $table_name, array( 'is_active' => $is_active ), array( 'task_id' => $task_id ) );
			$message = "All routes successfully " . ( 'activate' === $action ? 'activated' : 'deactivated' ) . ".";
		}

		return rest_ensure_response( array( 'success' => true, 'message' => $message ) );
	}
}
