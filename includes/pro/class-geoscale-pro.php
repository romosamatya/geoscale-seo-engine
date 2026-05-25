<?php
/**
 * Pro feature bootstrapper.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'class-geoscale-batch.php';
require_once plugin_dir_path( __FILE__ ) . 'class-geoscale-seo.php';
require_once plugin_dir_path( __FILE__ ) . 'class-geoscale-spintax.php';

class GeoScale_Pro {
	public function init() {
		$batch = new GeoScale_Batch();
		$batch->init();

		$seo = new GeoScale_SEO();
		$seo->init();
	}
}
