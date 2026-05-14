<?php
/**
 * Performance: dequeue unused plugin + parent-theme assets on custom-template pages,
 * strip always-wasteful globals, and inject a hero LCP preload on the homepage.
 *
 * "Custom template pages" = page-home.php, page-explore-athens.php,
 * page-testimonials.php, and any single mphb_room_type post. These templates
 * are 100% hand-rolled — they carry zero Elementor, WooCommerce, EA, social-icons,
 * or parent-theme markup, so those assets are dead weight.
 */

// ── Global always-safe removals ───────────────────────────────────────────────

add_action( 'init', function () {
	// WP emoji: detection script + inline emoji styles (~5 KB, zero value).
	remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles',     'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles',  'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss',  'wp_staticize_emoji' );
	remove_filter( 'wp_mail',           'wp_staticize_emoji_for_email' );
} );

add_action( 'wp_enqueue_scripts', function () {
	// Dashicons: only needed when the admin bar is visible.
	if ( ! is_admin_bar_showing() ) {
		wp_dequeue_style( 'dashicons' );
	}

	// jquery-migrate: move to footer (don't remove — MPHB/WooCommerce rely on it).
	global $wp_scripts;
	if ( isset( $wp_scripts->registered['jquery-migrate'] ) ) {
		$wp_scripts->add_data( 'jquery-migrate', 'group', 1 );
	}
}, 9999 );


// ── Custom-template targeted dequeues ─────────────────────────────────────────

// WooCommerce outputs its PhotoSwipe modal HTML to wp_footer on every page.
// On our custom templates we dequeue the photoswipe CSS, leaving raw HTML visible.
// Remove the footer action early (before wp_footer fires) on those pages.
add_action( 'template_redirect', function () {
	if ( ! chic_is_custom_template() ) {
		return;
	}
	remove_action( 'wp_footer', 'woocommerce_photoswipe' );
	// Also covers WooCommerce 8+ which may register it under a different priority.
	for ( $p = 1; $p <= 99; $p++ ) {
		remove_action( 'wp_footer', 'woocommerce_photoswipe', $p );
	}
} );

/**
 * Returns true on our fully custom-PHP page templates and single suite pages.
 * Elementor, WooCommerce, EA, th-widget-pack, social-icons-widget, and the
 * parent theme's generic CSS/JS are not used on any of these pages.
 */
function chic_is_custom_template(): bool {
	return is_page_template( [
		'page-home.php',
		'page-explore-athens.php',
		'page-testimonials.php',
	] ) || is_singular( 'mphb_room_type' );
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! chic_is_custom_template() ) {
		return;
	}

	// ── Elementor ─────────────────────────────────────────────────────────────
	$elementor_scripts = [
		'elementor-frontend',
		'elementor-common',
		'elementor-app-loader',
		'elementor-web-cli',
		'elementor-dev-tools',
		'elementor-frontend-modules',
		'elementor-webpack-runtime',
		'elementor-pro-frontend',
		'pro-elements-frontend',
		'font-awesome-4-shim',   // both JS and CSS handle with this slug
		'elementor-dialog',
		'backbone.marionette',
		'backbone.radio',
	];
	foreach ( $elementor_scripts as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	$elementor_styles = [
		'elementor-icons',
		'elementor-icons-shared-0',
		'elementor-common',
		'elementor-theme-light',
		'elementor-frontend',
		'elementor-pro',
		'e-widget-divider',
		'e-widget-spacer',
		'e-widget-menu-anchor',
		'e-widget-social-icons',
		'e-widget-icon-list',
		'e-widget-blockquote',
		'font-awesome-5-all',
		'font-awesome-5-solid',
		'font-awesome-5-regular',
		'font-awesome-5-brands',
		'font-awesome-4-shim',
		'font-awesome',        // FA 4.7 min
		'elementor-icons-fa-brands',
		'elementor-icons-fa-solid',
		'elementor-icons-fa-regular',
	];
	foreach ( $elementor_styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	// Elementor auto-generated post CSS (e.g. elementor-post-47, elementor-post-5048).
	// Iterate registered styles and drop anything whose src path matches.
	global $wp_styles;
	foreach ( array_keys( $wp_styles->registered ) as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src ?? '';
		if (
			( strpos( $handle, 'elementor' ) !== false || strpos( $handle, 'e-' ) === 0 )
			&& strpos( $src, '/themes/bellevuex-child/' ) === false // never strip our own
		) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	// ── Essential Addons for Elementor Lite ───────────────────────────────────
	foreach ( [ 'eael-general', 'essential-addons-elementor' ] as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	// ── th-widget-pack (Themo HFE) ────────────────────────────────────────────
	$themo_handles = [
		'themo-foot',
		'th-widget-pack-icons',
		'th-widget-pack-frontend',
		'header-footer-elementor',
		'hfe-widget-css',
		'hfe-css',
	];
	foreach ( $themo_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
	// Sweep any remaining th-widget-pack assets by src.
	global $wp_scripts;
	foreach ( array_keys( $wp_scripts->registered ) as $handle ) {
		$src = $wp_scripts->registered[ $handle ]->src ?? '';
		if ( strpos( $src, '/plugins/th-widget-pack/' ) !== false ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}
	foreach ( array_keys( $wp_styles->registered ) as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src ?? '';
		if ( strpos( $src, '/plugins/th-widget-pack/' ) !== false ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	// ── Social Icons Widget by WPZOOM (~367 KB, 15 requests) ─────────────────
	$wpzoom_handles = [
		'wpzoom-social-icons-block-style',
		'wpzoom-social-icons-styles',
		'wpzoom-socicon',
		'academicons',
		'genericons',
		'social-icons-widget-frontend',
		'font-awesome-3',
		'font-awesome-5',
		'wpzoom-fa3',
		'wpzoom-fa5',
	];
	foreach ( $wpzoom_handles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
	// Sweep by src for any handles we missed.
	foreach ( array_keys( $wp_styles->registered ) as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src ?? '';
		if ( strpos( $src, '/plugins/social-icons-widget-by-wpzoom/' ) !== false ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
	foreach ( array_keys( $wp_scripts->registered ) as $handle ) {
		$src = $wp_scripts->registered[ $handle ]->src ?? '';
		if ( strpos( $src, '/plugins/social-icons-widget-by-wpzoom/' ) !== false ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	// ── WooCommerce frontend (not a WooCommerce page) ─────────────────────────
	$woo_styles = [
		'wc-blocks-style',
		'woocommerce-layout',
		'woocommerce-smallscreen',
		'woocommerce-general',
		'wc-photoswipe',
		'photoswipe',
		'photoswipe-ui-default',
	];
	foreach ( $woo_styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	$woo_scripts = [
		'woocommerce',
		'wc-cart-fragments',
		'wc-add-to-cart',
		'wc-add-to-cart-variation',
		'wc-single-product',
		'wc-order-attribution',
		'sourcebuster-js',
		'jquery-blockui',
		'js-cookie',
		'flexslider',
		'photoswipe',
		'photoswipe-ui-default',
		'prettyPhoto',
		'prettyPhoto-init',
		'wc-addons-integration',
	];
	foreach ( $woo_scripts as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	// ── Cardlink payment gateway (only needed on checkout) ────────────────────
	wp_dequeue_style( 'cardlink-payment-gateway-public' );
	wp_deregister_style( 'cardlink-payment-gateway-public' );
	wp_dequeue_script( 'cardlink-payment-gateway-public' );
	wp_deregister_script( 'cardlink-payment-gateway-public' );

	// ── AIOSEO: admin-bar Vue/app assets leak to frontend for logged-out users ─
	// Keep them only when admin bar is visible (logged-in admins).
	if ( ! is_admin_bar_showing() ) {
		$aioseo_styles = [
			'aioseo-toc-global',
			'aioseo-admin-bar-noindex-warning',
			'aioseo-vendor-vue-ui',
		];
		foreach ( $aioseo_styles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}

		$aioseo_scripts = [
			'aioseo',
			'aioseo-vendors',
			'aioseo-vue-ui',
			'aioseo-app-core',
			'aioseo-lodash',
			'aioseo-vendors-other',
			'aioseo-admin-bar-noindex-warning',
			'aioseo-index',
		];
		foreach ( $aioseo_scripts as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	// Sweep all remaining AIOSEO assets by src (catches version-hashed filenames).
	if ( ! is_admin_bar_showing() ) {
		foreach ( array_keys( $wp_styles->registered ) as $handle ) {
			$src = $wp_styles->registered[ $handle ]->src ?? '';
			if ( strpos( $src, '/plugins/all-in-one-seo-pack/' ) !== false ) {
				wp_dequeue_style( $handle );
				wp_deregister_style( $handle );
			}
		}
		foreach ( array_keys( $wp_scripts->registered ) as $handle ) {
			$src = $wp_scripts->registered[ $handle ]->src ?? '';
			if ( strpos( $src, '/plugins/all-in-one-seo-pack/' ) !== false ) {
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	// ── Parent theme bellevuex: app.css, hotel-booking, vendor_footer, main, nicescroll ──
	// Our custom templates render none of the parent theme markup.
	// Match by src to avoid hard-coding handles we can't verify locally.
	foreach ( array_keys( $wp_styles->registered ) as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src ?? '';
		// Parent theme only — never strip child theme assets.
		if (
			strpos( $src, '/themes/bellevuex/' ) !== false &&
			strpos( $src, '/themes/bellevuex-child/' ) === false
		) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
	foreach ( array_keys( $wp_scripts->registered ) as $handle ) {
		$src = $wp_scripts->registered[ $handle ]->src ?? '';
		if (
			strpos( $src, '/themes/bellevuex/' ) !== false &&
			strpos( $src, '/themes/bellevuex-child/' ) === false
		) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	// ── Parent theme Google Fonts (Roboto, Roboto Slab, Open Sans) ───────────
	// We load Manrope ourselves; the parent theme's fonts are unused.
	foreach ( array_keys( $wp_styles->registered ) as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src ?? '';
		if (
			strpos( $src, 'fonts.googleapis.com' ) !== false &&
			strpos( $src, 'Manrope' ) === false // keep our own font
		) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

}, 9999 );


// ── Hero LCP preload ──────────────────────────────────────────────────────────

add_action( 'wp_head', function () {
	if ( ! is_page_template( 'page-home.php' ) ) {
		return;
	}

	global $post;
	if ( ! $post ) {
		return;
	}

	require_once get_stylesheet_directory() . '/inc/home-data.php';
	$slides = chic_home_hero_slides( $post->ID );

	if ( empty( $slides ) ) {
		return;
	}

	$first    = $slides[0];
	$desktop  = esc_url( $first['desktop'] );
	$mobile   = esc_url( $first['mobile'] );

	// Preload the desktop hero for all viewports; mobile will be handled by <picture>.
	// fetchpriority=high tells the browser to download this before layout paint.
	echo '<link rel="preload" as="image" href="' . $desktop . '" fetchpriority="high">' . "\n";

	// If a distinct mobile src exists, also preload it for narrow viewports.
	if ( $mobile && $mobile !== $desktop ) {
		echo '<link rel="preload" as="image" href="' . $mobile . '" media="(max-width: 768px)">' . "\n";
	}
}, 2 ); // priority 2 = very early in <head>, before plugin stylesheets
