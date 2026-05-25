<?php
/**
 * Pro feature: Spintax parser.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class GeoScale_Spintax {

	public static function parse( $text ) {
		return preg_replace_callback( '/\{(((?>[^\{\}]+)|(?R))*)\}/x', array( __CLASS__, 'replace' ), $text );
	}

	private static function replace( $matches ) {
		$text = self::parse( $matches[1] );
		$parts = explode( '|', $text );
		return $parts[ array_rand( $parts ) ];
	}
}
