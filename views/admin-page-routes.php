<?php
/**
 * Provide an admin area view for the generated routes.
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

global $wpdb;
$table_name = GeoScale_DB::get_table_name();
$safe_table = esc_sql( $table_name );

// Pagination setup
$per_page = 50;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$offset = ( $paged - 1 ) * $per_page;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM `{$safe_table}`" );
$total_pages = ceil( $total_items / $per_page );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
$routes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$safe_table}` ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ) );

?>
<div class="card" style="max-width: 100%; padding: 20px; margin-top: 20px;">
	<h2>Generated Virtual Routes</h2>
	<p>These are all the active routes generated from your CSV data. Click a URL to view the live page.</p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width: 50px;">ID</th>
				<th>Live URL</th>
				<th>Master Template</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $routes ) ) : ?>
				<tr>
					<td colspan="4">No routes found. Upload a CSV to get started.</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $routes as $route ) : 
					$url = home_url( '/locations/' . $route->route_slug . '/' );
					$template_title = get_the_title( $route->template_post_id );
				?>
					<tr>
						<td><?php echo esc_html( $route->id ); ?></td>
						<td><a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo esc_html( $url ); ?></a></td>
						<td>
							<?php if ( $template_title ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $route->template_post_id ) ); ?>"><?php echo esc_html( $template_title ); ?></a>
							<?php else : ?>
								<span style="color:red;">Template Missing</span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $route->is_active ? '<span style="color:green; font-weight:bold;">Active</span>' : '<span style="color:gray;">Inactive</span>'; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="pagination-links">
					<?php 
					echo wp_kses_post( paginate_links( array(
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'base' => add_query_arg( 'paged', '%#%' ),
						'format' => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total' => $total_pages,
						'current' => $paged,
					) ) );
					?>
				</span>
			</div>
		</div>
	<?php endif; ?>
</div>
