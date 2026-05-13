<?php

/**
 * Testimonials page: CSV review rows and mphb_room_type resolution.
 */

require_once __DIR__ . '/home-data.php';

/**
 * @return string Lowercase single-spaced trim.
 */
function chic_testimonials_normalize_title( string $s ): string {
	return strtolower( preg_replace( '/\s+/', ' ', trim( $s ) ) );
}

/**
 * Suite/Source values that are not a specific accommodation title.
 */
function chic_testimonials_is_aggregate_source( string $label ): bool {
	$n = chic_testimonials_normalize_title( $label );
	return in_array( $n, [ 'booking.com', 'facebook' ], true );
}

/**
 * Resolve CSV suite label to published mphb_room_type post ID (0 if unknown / aggregate).
 */
function chic_testimonials_resolve_room_id( string $suite_or_source ): int {
	if ( chic_testimonials_is_aggregate_source( $suite_or_source ) ) {
		return 0;
	}
	$needle = chic_testimonials_normalize_title( $suite_or_source );
	if ( '' === $needle ) {
		return 0;
	}
	$posts = get_posts( [
		'post_type'      => 'mphb_room_type',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	] );
	foreach ( $posts as $p ) {
		if ( chic_testimonials_normalize_title( $p->post_title ) === $needle ) {
			return (int) $p->ID;
		}
	}
	return 0;
}

/**
 * @return string Fallback image when a review has no suite thumbnail.
 */
function chic_testimonials_fallback_card_image_url(): string {
	$buildings = chic_home_buildings();
	return ! empty( $buildings[0]['building_image'] ) ? (string) $buildings[0]['building_image'] : '';
}

/**
 * Raw CSV rows (associative), excluding header.
 *
 * @return array<int, array{reviewer: string, country: string, suite_source: string, date: string, review: string}>
 */
function chic_testimonials_load_csv_rows(): array {
	$path = get_stylesheet_directory() . '/chic_centre_suites_reviews.csv';
	if ( ! is_readable( $path ) ) {
		return [];
	}
	$fh = fopen( $path, 'r' );
	if ( false === $fh ) {
		return [];
	}
	$header = fgetcsv( $fh );
	if ( false === $header || empty( $header ) ) {
		fclose( $fh );
		return [];
	}
	$rows = [];
	while ( ( $data = fgetcsv( $fh ) ) !== false ) {
		if ( count( $data ) < 5 ) {
			continue;
		}
		$rows[] = [
			'reviewer'       => (string) $data[0],
			'country'        => (string) $data[1],
			'suite_source'   => (string) $data[2],
			'date'           => (string) $data[3],
			'review'         => (string) $data[4],
		];
	}
	fclose( $fh );
	return $rows;
}

/**
 * CSV rows with thumb_url, permalink, room_id, suite_linked.
 *
 * @return array<int, array<string, mixed>>
 */
function chic_testimonials_enriched_rows(): array {
	$fallback = chic_testimonials_fallback_card_image_url();
	$out      = [];
	foreach ( chic_testimonials_load_csv_rows() as $r ) {
		$id        = chic_testimonials_resolve_room_id( $r['suite_source'] );
		$thumb     = '';
		$permalink = '';
		if ( $id > 0 ) {
			$thumb     = (string) ( get_the_post_thumbnail_url( $id, 'large' ) ?: '' );
			$permalink = (string) ( get_permalink( $id ) ?: '' );
		}
		if ( '' === $thumb && '' !== $fallback ) {
			$thumb = $fallback;
		}
		$out[] = array_merge( $r, [
			'room_id'       => $id,
			'thumb_url'     => $thumb,
			'permalink'     => $permalink,
			'suite_linked'  => $id > 0 && '' !== $permalink,
		] );
	}
	return $out;
}
