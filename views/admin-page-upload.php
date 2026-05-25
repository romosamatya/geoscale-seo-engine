<?php
/**
 * Provide an admin area view for the plugin
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Fetch all pages to act as templates
$templates = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );
?>
	<div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
		<h2>Upload Data CSV</h2>
		<p>Upload a CSV file to generate virtual routes. The CSV <strong>must</strong> contain a column named <code>route_slug</code> which will be used for the URL (e.g., <code>new-york-city</code>).</p>
		
		<form method="post" enctype="multipart/form-data" action="">
			<?php wp_nonce_field( 'geoscale_csv_upload_action', 'geoscale_csv_upload_nonce' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row"><label for="template_post_id">Master Template Page</label></th>
					<td>
						<select name="template_post_id" id="template_post_id" required>
							<option value="">-- Select a Master Template --</option>
							<?php foreach ( $templates as $template ) : ?>
								<option value="<?php echo esc_attr( $template->ID ); ?>">
									<?php echo esc_html( $template->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Select the WordPress page that will serve as the template for these virtual routes.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="geoscale_csv">CSV File</label></th>
					<td>
						<input type="file" name="geoscale_csv" id="geoscale_csv" accept=".csv" required />
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<input type="submit" name="geoscale_csv_upload" id="submit" class="button button-primary" value="Upload and Process CSV">
			</p>
		</form>
	</div>
