<?php
/**
 * Action Scheduler batch processing logic.
 *
 * @package WP_GeoScale
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Batch {

	public function init() {
		// Note: Action scheduler hooks pass the exact args array elements as separate parameters
		add_action( 'geoscale_process_csv_chunk', array( $this, 'process_chunk' ), 10, 5 );
	}

	/**
	 * Schedule the chunks for processing.
	 */
	public static function schedule_csv_processing( $file_path, $template_post_id, $total_rows, $task_id ) {
		$chunk_size = 500;
		$chunks = ceil( $total_rows / $chunk_size );

		for ( $i = 0; $i < $chunks; $i++ ) {
			$offset = $i * $chunk_size;
			as_enqueue_async_action(
				'geoscale_process_csv_chunk',
				array(
					'file_path'        => $file_path,
					'template_post_id' => $template_post_id,
					'offset'           => $offset,
					'limit'            => $chunk_size,
					'task_id'          => $task_id,
				),
				'geoscale_batch'
			);
		}
	}

	/**
	 * Process a specific chunk of the CSV.
	 */
	public function process_chunk( $file_path, $template_post_id, $offset, $limit, $task_id ) {
		if ( ! file_exists( $file_path ) ) {
			return;
		}

		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			global $wpdb;
			$table_name = GeoScale_DB::get_table_name();

			$headers = fgetcsv( $handle, 10000, ',' );
			if ( ! $headers ) {
				fclose( $handle );
				return;
			}
			$headers = array_map( 'trim', $headers );

			// Seek to the target offset (skip the header and up to the offset)
			$current_row = 0;
			while ( $current_row < $offset && fgetcsv( $handle, 10000, ',' ) !== false ) {
				$current_row++;
			}

			$processed = 0;
			while ( $processed < $limit && ( $data = fgetcsv( $handle, 10000, ',' ) ) !== false ) {
				if ( count( $headers ) !== count( $data ) ) {
					continue;
				}

				$row_data = array_combine( $headers, $data );
				$route_slug = sanitize_title( $row_data['route_slug'] );

				if ( empty( $route_slug ) ) {
					continue;
				}

				$dynamic_data = wp_json_encode( $row_data );

				$wpdb->replace(
					$table_name,
					array(
						'task_id'          => $task_id,
						'route_slug'       => $route_slug,
						'template_post_id' => $template_post_id,
						'dynamic_data'     => $dynamic_data,
						'is_active'        => 1,
					),
					array( '%d', '%s', '%d', '%s', '%d' )
				);
				
				$processed++;
			}

			$is_eof = feof( $handle );
			fclose( $handle );

			// Cleanup file on last chunk and mark task completed
			if ( $is_eof || $processed < $limit ) {
				@unlink( $file_path );
				$tasks_table = GeoScale_DB::get_tasks_table_name();
				$wpdb->update( $tasks_table, array( 'status' => 'completed' ), array( 'id' => $task_id ) );
			}
		}
	}
}
