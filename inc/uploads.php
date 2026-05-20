<?php
defined( 'ABSPATH' ) || exit;

/**
 * Resolve a media filename under wp-content/uploads for the current site domain.
 */
function chic_upload_url( string $filename ): string {
	$filename = ltrim( $filename, '/' );
	$upload   = wp_upload_dir();

	if ( ! empty( $upload['error'] ) || empty( $upload['baseurl'] ) ) {
		return home_url( '/wp-content/uploads/' . $filename );
	}

	return trailingslashit( $upload['baseurl'] ) . $filename;
}
