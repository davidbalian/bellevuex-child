<?php
/**
 * Google Analytics (gtag.js) — load-on-accept.
 *
 * Exposes the measurement ID to js/cookie-banner.js, which injects gtag
 * only after the user accepts cookies (or on return visits with prior accept).
 */
defined( 'ABSPATH' ) || exit;

const CHIC_GA_MEASUREMENT_ID = 'G-X78QTLBHND';

add_action( 'wp_enqueue_scripts', 'chic_analytics_localize', 20 );

function chic_analytics_localize(): void {
	if ( is_admin() || is_feed() || is_preview() ) {
		return;
	}

	wp_localize_script(
		'chic-cookie-banner',
		'chicAnalytics',
		[ 'id' => CHIC_GA_MEASUREMENT_ID ]
	);
}
