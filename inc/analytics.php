<?php
/**
 * Google Analytics (gtag.js) with Consent Mode defaults.
 *
 * Loads gtag in the head with analytics_storage denied until the cookie
 * banner grants consent via js/cookie-banner.js.
 */
defined( 'ABSPATH' ) || exit;

const CHIC_GA_MEASUREMENT_ID = 'G-X78QTLBHND';

add_action( 'wp_enqueue_scripts', 'chic_analytics_enqueue', 5 );

function chic_analytics_enqueue(): void {
	if ( is_admin() || is_feed() || is_preview() ) {
		return;
	}

	$id     = CHIC_GA_MEASUREMENT_ID;
	$handle = 'chic-gtag';
	$url    = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $id );

	wp_register_script( $handle, $url, [], null, false );
	wp_script_add_data( $handle, 'async', true );

	wp_add_inline_script(
		$handle,
		"window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
		. "gtag('consent','default',{analytics_storage:'denied',ad_storage:'denied',wait_for_update:500});"
		. "gtag('js',new Date());"
		. "gtag('config','" . esc_js( $id ) . "');",
		'before'
	);

	wp_enqueue_script( $handle );
}
