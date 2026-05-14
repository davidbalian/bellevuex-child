<?php
/**
 * Trashes mphb_room_type posts whose titles contain Greek characters.
 * These are orphaned Polylang/WPML translation duplicates.
 *
 * Usage: triggered via inc/seo-updater-trigger.php (admin_init hook).
 * Pass &dry_run=1 to preview without trashing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( "Run via the admin trigger or WP-CLI.\n" );
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Unauthorized.' );
}

$dry_run = ! empty( $_GET['dry_run'] );

$suite_posts = get_posts( [
	'post_type'      => 'mphb_room_type',
	'post_status'    => [ 'publish', 'draft', 'private' ],
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

$log    = [];
$count  = 0;

foreach ( $suite_posts as $pid ) {
	$title = get_the_title( $pid );

	// Detect any Greek Unicode character (U+0370–U+03FF).
	if ( ! preg_match( '/[\x{0370}-\x{03FF}]/u', $title ) ) {
		continue;
	}

	if ( ! $dry_run ) {
		wp_trash_post( $pid );
	}

	$log[] = ( $dry_run ? 'DRY   ' : 'TRASH ' ) . '"' . $title . '" (ID ' . $pid . ')';
	$count++;
}

if ( empty( $log ) ) {
	$log[] = 'No Greek-titled mphb_room_type posts found.';
}

$mode   = $dry_run ? 'DRY RUN (nothing trashed)' : 'LIVE RUN';
$header = "[Trash Greek Suites — $mode] " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
$header .= str_repeat( '-', 60 ) . "\n";
$footer = str_repeat( '-', 60 ) . "\n";
$footer .= ( $dry_run ? 'Would trash: ' : 'Trashed: ' ) . $count . ' post(s).' . "\n";

$output = $header . implode( "\n", $log ) . "\n" . $footer;

echo '<pre style="font-family:monospace;font-size:13px;line-height:1.6;padding:2rem;background:#f4f4f4;color:#222;">';
echo esc_html( $output );
echo '</pre>';
