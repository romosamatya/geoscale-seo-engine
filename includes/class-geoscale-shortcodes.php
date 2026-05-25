<?php
/**
 * Shortcode engine for dynamic content.
 *
 * @package WP_GeoScale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Shortcodes {

	/**
	 * Initialize shortcode hooks.
	 */
	public function init() {
		add_shortcode( 'geoscale', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the [geoscale] shortcode.
	 * Example usage: [geoscale field="city_name" default="Anywhere"]
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'field'   => '',
			'default' => '',
		), $atts, 'geoscale' );

		if ( empty( $atts['field'] ) ) {
			return '';
		}

		// Ensure we are on a virtual route and the payload exists in memory
		if ( ! empty( GeoScale_Router::$current_route_data ) && ! empty( GeoScale_Router::$current_route_data->payload ) ) {
			$payload = GeoScale_Router::$current_route_data->payload;

			if ( isset( $payload[ $atts['field'] ] ) ) {
				$text = wp_kses_post( $payload[ $atts['field'] ] );
				return $this->parse_spintax( $text );
			}
		}

		return $this->parse_spintax( wp_kses_post( $atts['default'] ) );
	}

	/**
	 * Parse Spintax formatted text like {Fast|Reliable|Expert} Plumbers.
	 */
	private function parse_spintax( $text ) {
		return preg_replace_callback( '/\{(((?>[^\{\}]+)|(?R))*)\}/x', array( $this, 'spintax_replace' ), $text );
	}

	private function spintax_replace( $matches ) {
		$text = $this->parse_spintax( $matches[1] );
		$parts = explode( '|', $text );
		return $parts[ array_rand( $parts ) ];
	}
}
