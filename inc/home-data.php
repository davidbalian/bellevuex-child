<?php

/**
 * Building configuration and suite data helper for the Home page template.
 */

function chic_home_buildings(): array {
	return [
		[
			'term'           => 'thiseos-11',
			'short_label'    => 'Thiseos 11',
			'label'          => '11 Thiseos, Floor 1, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/hCFxRXMY6xKPDG3T7',
			'building_image' => 'https://davidb1553.sg-host.com/wp-content/uploads/2-chic-centre-suites-athens-thisseos-11-common-seating-area-athens.webp',
		],
		[
			'term'           => 'thiseos13',
			'short_label'    => 'Thiseos 13',
			'label'          => '13 Thiseos, Floor 4, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/YbpkkEj8YbHoQoV4A',
			'building_image' => 'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-thisseos-13-corridor-athens.webp',
		],
		[
			'term'           => 'chavriou2',
			'short_label'    => 'Chavriou 2',
			'label'          => '2 Chavriou, Floor 2, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/kmVN1pyNxdU6rt8m8',
			'building_image' => 'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-chavriou-2-reception-desk-athens.webp',
		],
	];
}

/**
 * Returns simplified card data for all suites in a given building taxonomy term.
 *
 * @param string $term_slug   mphb_room_type_category slug (e.g. 'thiseos-11').
 * @param string $image_size  WP image size for thumb_url. Default 'medium_large'.
 * @return array[]  Each item: { id, title, permalink, thumb_url, capacity_label }
 */
function chic_home_get_suites( string $term_slug, string $image_size = 'medium_large' ): array {
	$query = new WP_Query( [
		'post_type'      => 'mphb_room_type',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'tax_query'      => [ [
			'taxonomy' => 'mphb_room_type_category',
			'field'    => 'slug',
			'terms'    => $term_slug,
		] ],
		'orderby' => 'menu_order title',
		'order'   => 'ASC',
	] );

	$cards = [];

	foreach ( $query->posts as $post ) {
		$terms    = get_the_terms( $post->ID, 'mphb_room_type_category' );
		$capacity = '';

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( preg_match( '/^up-to-(\d+)-guests$/', $term->slug, $m ) ) {
					$capacity = 'Hosting up to ' . $m[1] . ' guests';
					break;
				}
			}
		}

		$cards[] = [
			'id'              => $post->ID,
			'title'           => get_the_title( $post ),
			'permalink'       => get_permalink( $post ),
			'thumb_url'       => get_the_post_thumbnail_url( $post->ID, $image_size ) ?: '',
			'capacity_label'  => $capacity,
		];
	}

	return $cards;
}

/**
 * Returns hero slide data for the home page.
 * Reads attachment IDs stored in _chic_home_hero_slides post meta (JSON array).
 * Falls back to the page featured image so the slider always has at least one slide.
 *
 * @param int $post_id  The Home page post ID.
 * @return array[]  Each item: { id, alt, desktop, mobile, fetchpriority }
 */
function chic_home_hero_slides( int $post_id ): array {
	$raw = get_post_meta( $post_id, '_chic_home_hero_slides', true );
	$ids = is_string( $raw ) ? json_decode( $raw, true ) : null;

	if ( empty( $ids ) || ! is_array( $ids ) ) {
		return [
			[
				'id'            => 0,
				'alt'           => 'Chic Centre Suites Athens suite interior',
				'desktop'       => 'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-suite-no-balcony-ginger-main-room-athens.webp',
				'mobile'        => 'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-suite-no-balcony-ginger-main-room-athens.webp',
				'fetchpriority' => 'high',
			],
			[
				'id'            => 0,
				'alt'           => 'Chic Centre Suites Athens nearby attractions',
				'desktop'       => 'https://davidb1553.sg-host.com/wp-content/uploads/10-chic-centre-suites-athens-nearby-attractions.webp',
				'mobile'        => 'https://davidb1553.sg-host.com/wp-content/uploads/10-chic-centre-suites-athens-nearby-attractions.webp',
				'fetchpriority' => 'auto',
			],
			[
				'id'            => 0,
				'alt'           => 'Chic Centre Suites Athens deluxe suite kitchen',
				'desktop'       => 'https://davidb1553.sg-host.com/wp-content/uploads/3-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
				'mobile'        => 'https://davidb1553.sg-host.com/wp-content/uploads/3-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
				'fetchpriority' => 'low',
			],
		];
	}

	$slides = [];
	foreach ( array_values( $ids ) as $index => $id ) {
		$id = (int) $id;
		if ( $id <= 0 ) continue;
		$desktop = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $desktop ) continue;
		$slides[] = [
			'id'            => $id,
			'alt'           => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'desktop'       => $desktop,
			'mobile'        => wp_get_attachment_image_url( $id, 'medium_large' ) ?: $desktop,
			'fetchpriority' => 0 === $index ? 'high' : ( 1 === $index ? 'auto' : 'low' ),
		];
	}

	return $slides;
}
