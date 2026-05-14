<?php
/**
 * One-off AIOSEO content updater for Chic Centre Suites.
 *
 * Applies SEO titles + meta descriptions from new-content.md to every
 * mphb_room_type post and the home page.
 *
 * Usage:
 *   - Browser: triggered via inc/seo-updater-trigger.php (admin_init hook).
 *   - WP-CLI:  wp eval-file wp-content/themes/bellevuex-child/tools/apply-seo-content.php
 *
 * Pass &dry_run=1 (browser) or define DRY_RUN before including (CLI) to
 * preview changes without writing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Standalone CLI execution — bootstrap WordPress.
	$root = dirname( __DIR__, 4 ); // theme → themes → wp-content → ABSPATH
	if ( ! file_exists( $root . '/wp-load.php' ) ) {
		die( "Cannot locate wp-load.php. Run via WP-CLI or the admin trigger.\n" );
	}
	require_once $root . '/wp-load.php';
}

if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	wp_die( 'Unauthorized.' );
}

$dry_run = ( defined( 'WP_CLI' ) && WP_CLI )
	? in_array( '--dry-run', $_SERVER['argv'] ?? [], true )
	: ! empty( $_GET['dry_run'] );

/* ── SEO content map ─────────────────────────────────────────────────────── */

// Keys match lowercased WP post titles for mphb_room_type posts.
// 'home' is a special key handled separately via page template lookup.

$seo_map = [

	/* Home page */
	'home' => [
		'seo_title'       => 'Serviced Apartments in Athens City Center | Chic Center Suites Athens',
		'seo_description' => 'Stay in stylish serviced apartments in Athens city center, near Acropolis, Monastiraki, and Ermou Street. Ideal for short stays, holidays, and business travel.',
	],

	/* Chavriou 2 */
	'suite 1' => [
		'seo_title'       => 'SUITE 1 | Suite with Balcony',
		'seo_description' => 'Modern 30m² studio near Ermou Street in Athens city center with balcony, kitchen, smart TV, and amenities. Ideal for 2 guests near top attractions.',
	],
	'suite 2' => [
		'seo_title'       => 'SUITE 2 | One-Bedroom Suite with Balcony',
		'seo_description' => 'Spacious 41m² one-bedroom apartment in Athens city center with balcony, kitchen, washing machine, and amenities. Ideal for up to 4 guests.',
	],
	'suite 3' => [
		'seo_title'       => 'SUITE 3 | Suite without Balcony',
		'seo_description' => 'Comfortable 23m² studio in Athens city center with kitchen, smart TV access, high-speed WiFi and modern amenities. Ideal for couples or solo travelers near top attractions.',
	],
	'suite 4' => [
		'seo_title'       => 'SUITE 4 | Suite with Balcony',
		'seo_description' => 'Modern 24m² studio in Athens city center with balcony, kitchen, smart TV access, high-speed WiFi and amenities. Ideal for couples or solo travelers near top attractions.',
	],
	'suite 5' => [
		'seo_title'       => 'SUITE 5 | Suite with Balcony',
		'seo_description' => 'Comfortable 24m² studio in Athens city center with balcony, kitchen, smart TV access, high-speed WiFi and modern amenities. Ideal for couples or solo travelers.',
	],
	'suite 6' => [
		'seo_title'       => 'SUITE 6 | Suite with Balcony',
		'seo_description' => 'Spacious 26m² studio in Athens city center with balcony, kitchen, smart TV access, high-speed WiFi and modern amenities. Ideal for couples or solo travelers near top attractions.',
	],

	/* Thiseos 11 */
	'avra suite' => [
		'seo_title'       => 'AVRA SUITE | One-Bedroom Suite with Balcony',
		'seo_description' => 'Spacious 52m² one-bedroom suite in Athens city center with Jacuzzi and balcony. Ideal for families or small groups, close to Acropolis and Monastiraki.',
	],
	'zakynthos suite' => [
		'seo_title'       => 'ZAKYNTHOS SUITE | Suite with Balcony',
		'seo_description' => 'Stylish 28m² studio apartment in Athens city center with private balcony, kitchen, and modern amenities. Ideal for couples near major attractions.',
	],
	'santorini suite' => [
		'seo_title'       => 'SANTORINI SUITE | Suite with Balcony',
		'seo_description' => 'Stylish 28m² studio suite in Athens historical center with balcony, kitchen, and modern amenities. Perfect for couples near Acropolis.',
	],
	'kohili suite' => [
		'seo_title'       => 'KOHILI SUITE | Suite with Balcony',
		'seo_description' => 'Spacious 37m² premium suite in Athens city center with Jacuzzi, private balcony, kitchen, and modern amenities. Ideal for couples seeking comfort and relaxation.',
	],
	'korali suite' => [
		'seo_title'       => 'KORALI SUITE | Suite without Balcony',
		'seo_description' => 'Stylish 20m² studio suite in Athens city center with kitchen, modern amenities, and cozy design. Ideal for couples near major attractions.',
	],
	'mykonos suite' => [
		'seo_title'       => 'MYKONOS SUITE | Suite with Terrace',
		'seo_description' => 'Spacious 35m² family suite in Athens city center with private terrace, kitchen, smart TV, and modern amenities. Ideal for up to 4 guests near top attractions.',
	],
	'paros suite' => [
		'seo_title'       => 'PAROS SUITE | One-Bedroom Suite without Balcony',
		'seo_description' => 'Spacious 36m² family suite in Athens city center with kitchen, modern amenities, and Cycladic design. Ideal for up to 4 guests near top attractions.',
	],
	'ammos suite' => [
		'seo_title'       => 'AMMOS SUITE | Suite with Terrace',
		'seo_description' => 'Bright 19m² studio in Athens city center with private terrace, kitchen, smart TV, and modern amenities. Ideal for couples seeking comfort and central location.',
	],
	'ermou suite' => [
		'seo_title'       => 'ERMOU SUITE | Suite with Terrace',
		'seo_description' => 'Modern 19m² studio near Ermou Street in Athens city center with terrace, kitchen, smart TV, and amenities. Ideal for couples or solo travelers.',
	],
	'pelagos suite' => [
		'seo_title'       => 'PELAGOS SUITE | Suite with Terrace',
		'seo_description' => 'Stylish 27m² studio in Athens city center with private terrace, kitchen, smart TV access, high-speed WiFi, and modern amenities. Ideal for 2 guests near top attractions.',
	],

	/* Thiseos 13 */
	'ocean suite' => [
		'seo_title'       => 'OCEAN SUITE | Suite without Balcony',
		'seo_description' => 'Modern 30m² studio in Athens city center with kitchen, smart TV, and sea-inspired design. Ideal for couples or solo travelers near top attractions.',
	],
	'ginger suite' => [
		'seo_title'       => 'GINGER SUITE | Suite without Balcony',
		'seo_description' => 'Compact 17m² studio in Athens city center with kitchen, smart TV access, high-speed WiFi, and modern amenities. Ideal for couples or solo travelers near main attractions.',
	],
	'gray suite' => [
		'seo_title'       => 'GREY SUITE | Suite without Balcony',
		'seo_description' => 'Spacious 32m² studio in Athens city center with kitchen, smart TV access, high-speed WiFi, and modern design. Ideal for couples or solo travelers near top attractions.',
	],
	'sunshine suite' => [
		'seo_title'       => 'SUNSHINE SUITE | Suite without Balcony',
		'seo_description' => 'Bright 15m² studio in Athens city center with kitchen, smart TV access, high-speed WiFi, and modern amenities. Ideal for couples or solo travelers near main attractions.',
	],
	'forest suite' => [
		'seo_title'       => 'FOREST SUITE | Suite without Balcony',
		'seo_description' => 'Spacious 35m² apartment in Athens city center for up to 4 guests with kitchen, smart TV access, high-speed WiFi and modern amenities. Ideal for families and groups.',
	],
];

/* ── Helpers ─────────────────────────────────────────────────────────────── */

$log   = [];
$wrote = 0;

function chic_seo_write( int $post_id, string $title, string $description, bool $dry_run, array &$log, int &$wrote ): void {
	$label = get_the_title( $post_id ) . ' (ID ' . $post_id . ')';

	if ( class_exists( '\AIOSEO\Plugin\Common\Models\Post' ) ) {
		// AIOSEO v4+
		$aioseo_post              = \AIOSEO\Plugin\Common\Models\Post::getPost( $post_id );
		$current_title            = $aioseo_post->title ?? '';
		$current_desc             = $aioseo_post->description ?? '';

		if ( $current_title === $title && $current_desc === $description ) {
			$log[] = "SKIP  $label — already up to date";
			return;
		}

		if ( ! $dry_run ) {
			$aioseo_post->title       = $title;
			$aioseo_post->description = $description;
			$aioseo_post->save();
		}

		$log[] = ( $dry_run ? 'DRY   ' : 'WROTE ' ) . $label;
		$log[] = "      title: $title";
		$log[] = "      desc:  $description";
		$wrote++;

	} else {
		// AIOSEO v3 fallback — postmeta keys
		$current_title = get_post_meta( $post_id, '_aioseo_title', true );
		$current_desc  = get_post_meta( $post_id, '_aioseo_description', true );

		if ( $current_title === $title && $current_desc === $description ) {
			$log[] = "SKIP  $label — already up to date (v3 meta)";
			return;
		}

		if ( ! $dry_run ) {
			update_post_meta( $post_id, '_aioseo_title', $title );
			update_post_meta( $post_id, '_aioseo_description', $description );
		}

		$log[] = ( $dry_run ? 'DRY   ' : 'WROTE ' ) . $label . ' [v3 postmeta fallback]';
		$log[] = "      title: $title";
		$log[] = "      desc:  $description";
		$wrote++;
	}
}

/* ── Home page ───────────────────────────────────────────────────────────── */

$home_pages = get_pages( [
	'meta_key'   => '_wp_page_template',
	'meta_value' => 'page-home.php',
	'number'     => 1,
] );

if ( $home_pages ) {
	$home_id = (int) $home_pages[0]->ID;
	chic_seo_write(
		$home_id,
		$seo_map['home']['seo_title'],
		$seo_map['home']['seo_description'],
		$dry_run,
		$log,
		$wrote
	);
} else {
	$log[] = 'WARN  Home page (page-home.php template) not found — skipped.';
}

/* ── Suite posts ─────────────────────────────────────────────────────────── */

$suite_posts = get_posts( [
	'post_type'      => 'mphb_room_type',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

foreach ( $suite_posts as $pid ) {
	$key = strtolower( trim( get_the_title( $pid ) ) );
	if ( ! isset( $seo_map[ $key ] ) ) {
		$log[] = 'WARN  "' . get_the_title( $pid ) . '" (ID ' . $pid . ') — no SEO entry in map, skipped.';
		continue;
	}
	chic_seo_write(
		$pid,
		$seo_map[ $key ]['seo_title'],
		$seo_map[ $key ]['seo_description'],
		$dry_run,
		$log,
		$wrote
	);
}

/* ── Output ──────────────────────────────────────────────────────────────── */

$mode   = $dry_run ? 'DRY RUN (nothing written)' : 'LIVE RUN';
$header = "[Chic SEO Updater — $mode] " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
$header .= str_repeat( '-', 70 ) . "\n";
$footer = str_repeat( '-', 70 ) . "\n";
$footer .= ( $dry_run ? 'Would write: ' : 'Wrote: ' ) . $wrote . ' post(s).' . "\n";

$output = $header . implode( "\n", $log ) . "\n" . $footer;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::log( $output );
} else {
	echo '<pre style="font-family:monospace;font-size:13px;line-height:1.6;padding:2rem;background:#f4f4f4;color:#222;">';
	echo esc_html( $output );
	echo '</pre>';
}
