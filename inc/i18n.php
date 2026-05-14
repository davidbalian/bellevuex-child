<?php
/**
 * Chic Centre Suites — custom i18n layer.
 *
 * Path-prefix model: English (default) has no prefix; Greek lives under /el/.
 * Uses WordPress query_vars + add_rewrite_rule; no Polylang/WPML dependency.
 */
defined( 'ABSPATH' ) || exit;

/* ── Constants ───────────────────────────────────────────────────────────── */

define( 'CHIC_DEFAULT_LANG',    'en' );
define( 'CHIC_SUPPORTED_LANGS', [ 'en', 'el' ] );
define( 'CHIC_I18N_VERSION',    '1' );

/* ── Language detection ──────────────────────────────────────────────────── */

function chic_get_current_lang(): string {
	static $lang = null;
	if ( null !== $lang ) {
		return $lang;
	}
	$qv = (string) get_query_var( 'lang', '' );
	if ( $qv && in_array( $qv, CHIC_SUPPORTED_LANGS, true ) ) {
		$lang = $qv;
		return $lang;
	}
	$path     = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	$segments = explode( '/', $path );
	if ( ! empty( $segments[0] ) && in_array( $segments[0], [ 'el' ], true ) ) {
		$lang = $segments[0];
		return $lang;
	}
	$lang = CHIC_DEFAULT_LANG;
	return $lang;
}

/* ── Rewrite rules ───────────────────────────────────────────────────────── */

add_action( 'init', function () {
	add_rewrite_rule( '^el/?$',          'index.php?lang=el',                          'top' );
	add_rewrite_rule( '^el/(.+?)/?$',    'index.php?lang=el&pagename=$matches[1]',     'top' );
}, 1 );

add_filter( 'query_vars', function ( array $vars ): array {
	$vars[] = 'lang';
	return $vars;
} );

// Force the static front page when /el/ is requested without a pagename.
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! $q->is_main_query() ) return;
	if ( $q->get( 'lang' ) === 'el' && '' === (string) $q->get( 'pagename' ) ) {
		$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
	}
} );

// Flush rewrite rules on theme activation.
add_action( 'after_switch_theme', function () {
	flush_rewrite_rules();
} );

// Flush when our version bumps.
add_action( 'init', function () {
	if ( get_option( 'chic_i18n_version' ) !== CHIC_I18N_VERSION ) {
		flush_rewrite_rules();
		update_option( 'chic_i18n_version', CHIC_I18N_VERSION );
	}
}, 99 );

/* ── Canonical redirect guard ────────────────────────────────────────────── */

add_filter( 'redirect_canonical', function ( $redirect_url, $requested_url ) {
	$path = trim( (string) parse_url( $requested_url ?? $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	if ( strncmp( $path, 'el/', 3 ) === 0 || $path === 'el' ) {
		return false;
	}
	return $redirect_url;
}, 10, 2 );

/* ── HTML lang attribute ─────────────────────────────────────────────────── */

add_filter( 'language_attributes', function ( string $output ): string {
	$map = [
		'en' => 'en-US',
		'el' => 'el-GR',
	];
	$lang = chic_get_current_lang();
	if ( isset( $map[ $lang ] ) ) {
		return 'lang="' . esc_attr( $map[ $lang ] ) . '"';
	}
	return $output;
} );

/* ── Translation helpers ─────────────────────────────────────────────────── */

function chic_get_translations( string $lang ): array {
	static $cache = [];
	if ( isset( $cache[ $lang ] ) ) {
		return $cache[ $lang ];
	}
	if ( $lang === CHIC_DEFAULT_LANG ) {
		$cache[ $lang ] = [];
		return $cache[ $lang ];
	}
	$file = get_stylesheet_directory() . '/translations/' . $lang . '.php';
	$cache[ $lang ] = file_exists( $file ) ? include $file : [];
	return $cache[ $lang ];
}

function t( string $key ): string {
	$lang = chic_get_current_lang();
	if ( $lang === CHIC_DEFAULT_LANG ) {
		return $key;
	}
	$map = chic_get_translations( $lang );
	return $map[ $key ] ?? $key;
}

function te( string $key ): void {
	echo esc_html( t( $key ) );
}

function ta( string $key ): string {
	return esc_attr( t( $key ) );
}

function t_html( string $key ): string {
	return t( $key );
}

/* ── Suite title translation helper ─────────────────────────────────────── */

function chic_translate_suite_title( string $english_title ): string {
	if ( chic_get_current_lang() === CHIC_DEFAULT_LANG ) {
		return $english_title;
	}
	// "Forest Suite" → "Σουίτα Forest"
	if ( preg_match( '/^(.+?)\s+Suite$/i', $english_title, $m ) ) {
		return t( 'Suite' ) . ' ' . $m[1];
	}
	// "Suite 3" → "Σουίτα 3"
	if ( preg_match( '/^Suite\s+(.+)$/i', $english_title, $m ) ) {
		return t( 'Suite' ) . ' ' . $m[1];
	}
	return $english_title;
}

/* ── URL builders ────────────────────────────────────────────────────────── */

function chic_localized_url( string $path = '/', ?string $lang = null ): string {
	$lang = $lang ?? chic_get_current_lang();
	$path = '/' . ltrim( $path, '/' );
	if ( $lang === CHIC_DEFAULT_LANG ) {
		return home_url( $path );
	}
	return home_url( '/' . $lang . $path );
}

function chic_lang_switch_url( string $target_lang ): string {
	$raw_path  = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	$clean     = trim( $raw_path, '/' );
	// Strip existing language prefix (el) from the path.
	foreach ( CHIC_SUPPORTED_LANGS as $code ) {
		if ( $code === CHIC_DEFAULT_LANG ) continue;
		if ( strncmp( $clean, $code . '/', strlen( $code ) + 1 ) === 0 ) {
			$clean = substr( $clean, strlen( $code ) + 1 );
			break;
		}
		if ( $clean === $code ) {
			$clean = '';
			break;
		}
	}
	if ( $target_lang === CHIC_DEFAULT_LANG ) {
		return home_url( '/' . ( $clean ? $clean . '/' : '' ) );
	}
	return home_url( '/' . $target_lang . '/' . ( $clean ? $clean . '/' : '' ) );
}

/* ── Browser-language redirect (first visit) ─────────────────────────────── */

add_action( 'template_redirect', function () {
	$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	if ( $path !== '' ) return;
	if ( ! empty( $_COOKIE['mci_lang'] ) ) return;

	// Skip known crawlers.
	$ua = strtolower( $_SERVER['HTTP_USER_AGENT'] ?? '' );
	if ( preg_match( '/bot|crawl|spider|slurp|bing|yandex|baidu|duckduckbot|facebookexternalhit|twitterbot|googlebot/', $ua ) ) {
		return;
	}

	$accept  = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
	$matched = null;
	// Parse quality values.
	$parts = explode( ',', $accept );
	$langs_q = [];
	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( preg_match( '/^([a-zA-Z]{1,8}(?:-[a-zA-Z0-9]{1,8})?)\s*(?:;\s*q=([\d.]+))?$/', $part, $m ) ) {
			$code = strtolower( substr( $m[1], 0, 2 ) );
			$q    = isset( $m[2] ) ? (float) $m[2] : 1.0;
			if ( ! isset( $langs_q[ $code ] ) || $langs_q[ $code ] < $q ) {
				$langs_q[ $code ] = $q;
			}
		}
	}
	arsort( $langs_q );
	foreach ( array_keys( $langs_q ) as $code ) {
		if ( $code === 'el' ) { $matched = 'el'; break; }
	}

	if ( $matched ) {
		setcookie( 'mci_lang', $matched, [
			'expires'  => time() + 60 * 60 * 24 * 365,
			'path'     => '/',
			'samesite' => 'Lax',
		] );
		wp_safe_redirect( home_url( '/' . $matched . '/' ), 302 );
		exit;
	}

	setcookie( 'mci_lang', 'en', [
		'expires'  => time() + 60 * 60 * 24 * 365,
		'path'     => '/',
		'samesite' => 'Lax',
	] );
}, 5 );

// Sync cookie when user manually navigates to a prefixed URL.
add_action( 'template_redirect', function () {
	$lang = chic_get_current_lang();
	if ( $lang !== CHIC_DEFAULT_LANG && empty( $_COOKIE['mci_lang'] ) ) {
		setcookie( 'mci_lang', $lang, [
			'expires'  => time() + 60 * 60 * 24 * 365,
			'path'     => '/',
			'samesite' => 'Lax',
		] );
	}
}, 9 );

/* ── SEO: hreflang + AIOSEO filters ─────────────────────────────────────── */

// Emit hreflang alternates early in wp_head.
add_action( 'wp_head', function () {
	if ( is_admin() ) return;

	$raw_path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	$clean    = trim( $raw_path, '/' );
	foreach ( CHIC_SUPPORTED_LANGS as $code ) {
		if ( $code === CHIC_DEFAULT_LANG ) continue;
		if ( strncmp( $clean, $code . '/', strlen( $code ) + 1 ) === 0 ) {
			$clean = substr( $clean, strlen( $code ) + 1 );
			break;
		}
		if ( $clean === $code ) {
			$clean = '';
			break;
		}
	}
	$base_path = $clean ? '/' . $clean . '/' : '/';

	$en_url      = home_url( $base_path );
	$el_url      = home_url( '/el' . $base_path );
	$default_url = $en_url;

	echo '<link rel="alternate" hreflang="en" href="' . esc_url( $en_url ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="el" href="' . esc_url( $el_url ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $default_url ) . '">' . "\n";
}, 1 );

// Swap AIOSEO title/description to Greek when on /el/ routes.
add_filter( 'aioseo_title', function ( $title ) {
	if ( chic_get_current_lang() === 'el' && is_singular() ) {
		$greek = get_post_meta( get_the_ID(), '_chic_aioseo_title_el', true );
		if ( $greek ) return $greek;
	}
	return $title;
} );

add_filter( 'aioseo_description', function ( $desc ) {
	if ( chic_get_current_lang() === 'el' && is_singular() ) {
		$greek = get_post_meta( get_the_ID(), '_chic_aioseo_description_el', true );
		if ( $greek ) return $greek;
	}
	return $desc;
} );

// Force prefixed canonical URL for Greek pages.
add_filter( 'aioseo_canonical_url', function ( $url ) {
	if ( chic_get_current_lang() === 'el' ) {
		$path = (string) parse_url( $url, PHP_URL_PATH );
		return chic_localized_url( $path ?: '/' );
	}
	return $url;
} );

// Set OG locale for Greek pages.
add_filter( 'aioseo_facebook_locale', function ( $locale ) {
	if ( chic_get_current_lang() === 'el' ) {
		return 'el_GR';
	}
	return $locale;
} );
