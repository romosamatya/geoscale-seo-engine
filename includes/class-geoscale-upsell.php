<?php
/**
 * Pro Upsell Page
 *
 * @package WP_GeoScale
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Upsell {
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_upsell_page' ), 99 );
	}

	public function add_upsell_page() {
		add_submenu_page(
			'wp-geoscale',
			'Upgrade to Pro',
			'Upgrade to Pro <span class="dashicons dashicons-star-filled" style="color: #f56e28; font-size: 14px; margin-top: 3px;"></span>',
			'manage_options',
			'wp-geoscale-pricing',
			array( $this, 'render_pricing_page' )
		);
	}

	public function render_pricing_page() {
		?>
		<div class="wrap">
			<h1>WP GeoScale Pro</h1>
			<p>Unlock the full programmatic SEO capabilities with WP GeoScale Pro.</p>
			
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; max-width: 600px; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2>Pro Features</h2>
				<ul style="list-style-type: disc; padding-left: 20px;">
					<li><strong>Unlimited CSV Rows:</strong> Process campaigns of any size without the 100-row limit.</li>
					<li><strong>Bulk Actions:</strong> Activate, deactivate, or delete entire campaigns instantly.</li>
					<li><strong>Advanced Schema:</strong> Inject dynamic, location-based JSON-LD schema.</li>
					<li><strong>Priority Support:</strong> Get direct assistance for your programmatic SEO campaigns.</li>
				</ul>
				
				<p style="margin-top: 30px;">
					<a href="https://geoscale-seo.netlify.app/" target="_blank" class="button button-primary button-hero" style="background: #f56e28; border-color: #d55819;">View Plan Details & Upgrade</a>
				</p>
			</div>
		</div>
		<?php
	}
}
