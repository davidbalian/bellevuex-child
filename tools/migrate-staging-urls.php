<?php
/**
 * Replace staging host URLs in the WordPress database after domain migration.
 *
 * Usage (from WordPress root):
 *   wp eval-file wp-content/themes/<child-theme>/tools/migrate-staging-urls.php
 *
 * Dry run (preview counts only):
 *   wp eval 'define("CHIC_MIGRATE_DRY_RUN", true);' && wp eval-file wp-content/themes/<child-theme>/tools/migrate-staging-urls.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via WP-CLI from the WordPress root.\n" );
	exit( 1 );
}

if ( ! class_exists( 'WP_CLI' ) ) {
	fwrite( STDERR, "WP-CLI is required.\n" );
	exit( 1 );
}

$from_host = 'davidb1553.sg-host.com';
$to_host   = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
$dry_run   = defined( 'CHIC_MIGRATE_DRY_RUN' ) && CHIC_MIGRATE_DRY_RUN;

if ( '' === $from_host || '' === $to_host ) {
	WP_CLI::error( 'Both --from and --to hosts must be non-empty.' );
}

$from = 'https://' . $from_host;
$to   = 'https://' . $to_host;

global $wpdb;

$tables = [
	$wpdb->posts,
	$wpdb->postmeta,
	$wpdb->options,
	$wpdb->comments,
	$wpdb->commentmeta,
	$wpdb->termmeta,
];

WP_CLI::log( "Staging URL migration: {$from} → {$to}" );
WP_CLI::log( $dry_run ? 'DRY RUN — no rows will be updated.' : 'Live run — updating database rows.' );

$total = 0;

foreach ( $tables as $table ) {
	$columns = $wpdb->get_col( "DESCRIBE `{$table}`", 0 );
	if ( ! $columns ) {
		continue;
	}

	foreach ( $columns as $column ) {
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE %s",
				'%' . $wpdb->esc_like( $from_host ) . '%'
			)
		);

		if ( $count < 1 ) {
			continue;
		}

		WP_CLI::log( "  {$table}.{$column}: {$count} row(s)" );
		$total += $count;

		if ( $dry_run ) {
			continue;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, %s, %s) WHERE `{$column}` LIKE %s",
				$from,
				$to,
				'%' . $wpdb->esc_like( $from_host ) . '%'
			)
		);

		// Also replace http:// variant if present.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, %s, %s) WHERE `{$column}` LIKE %s",
				'http://' . $from_host,
				'https://' . $to_host,
				'%' . $wpdb->esc_like( $from_host ) . '%'
			)
		);
	}
}

if ( $dry_run ) {
	WP_CLI::success( "Dry run complete — {$total} row(s) would be updated." );
} else {
	WP_CLI::success( "Migration complete — {$total} row(s) updated." );
}
