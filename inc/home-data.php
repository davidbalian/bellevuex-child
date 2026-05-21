<?php

/**
 * Building configuration and suite data helper for the Home page template.
 */

/**
 * True when the Home page template should run (English front page or /el/).
 *
 * is_page_template( 'page-home.php' ) fails on the default-language front page
 * because WP sets is_front_page without is_page; Greek /el/ injects pagename so
 * is_page_template works there only.
 */
function chic_is_home_page(): bool {
	if ( is_page_template( 'page-home.php' ) ) {
		return true;
	}
	if ( ! is_front_page() || 'page' !== get_option( 'show_on_front' ) ) {
		return false;
	}
	$front_id = (int) get_option( 'page_on_front' );
	return $front_id > 0 && 'page-home.php' === get_page_template_slug( $front_id );
}

function chic_home_buildings(): array {
	return [
		[
			'term'           => 'thiseos-11',
			'short_label'    => 'Thiseos 11',
			'label'          => '11 Thiseos, Floor 1, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/MvpXTE7YqntLY3rC6',
			'building_image' => chic_upload_url( '2-chic-centre-suites-athens-thisseos-11-common-seating-area-athens.webp' ),
			'suite_order'    => [
				'Avra Suite',
				'Zakynthos Suite',
				'Santorini Suite',
				'Kohili Suite',
				'Korali Suite',
				'Paros Suite',
				'Mykonos Suite',
				'Ammos Suite',
				'Ermou Suite',
				'Pelagos Suite',
			],
		],
		[
			'term'           => 'thiseos13',
			'short_label'    => 'Thiseos 13',
			'label'          => '13 Thiseos, Floor 4, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/CGVBd7sJzNA59DeZ6',
			'building_image' => chic_upload_url( '1-chic-centre-suites-athens-thisseos-13-corridor-athens.webp' ),
			'suite_order'    => [
				'Ocean Suite',
				'Ginger Suite',
				'Gray Suite',
				'Sunshine Suite',
				'Forest Suite',
			],
		],
		[
			'term'           => 'chavriou2',
			'short_label'    => 'Chavriou 2',
			'label'          => '2 Chavriou, Floor 2, 10562 Athens',
			'maps'           => 'https://maps.app.goo.gl/wS26rSd7fWL7XgAb7',
			'building_image' => chic_upload_url( '1-chic-centre-suites-athens-chavriou-2-reception-desk-athens.webp' ),
			'suite_order'    => [
				'Suite 1',
				'Suite 2',
				'Suite 3',
				'Suite 4',
				'Suite 5',
				'Suite 6',
			],
		],
	];
}

/**
 * Building list for templates: ACF Site Settings when seeded, merged with PHP defaults.
 *
 * Keeps suite_order and term slugs from chic_home_buildings(); labels, maps, and images
 * come from chic_ss_buildings when rows exist.
 *
 * @return array[] Same shape as chic_home_buildings().
 */
function chic_site_buildings(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$defaults = chic_home_buildings();
	$by_term  = [];
	foreach ( $defaults as $b ) {
		$by_term[ $b['term'] ] = $b;
	}

	if ( ! class_exists( 'Chic_Page_Content' ) ) {
		$cache = $defaults;
		return $cache;
	}

	$acf_rows = Chic_Page_Content::get_repeater( 'option', 'chic_ss_buildings' );
	if ( empty( $acf_rows ) ) {
		$cache = $defaults;
		return $cache;
	}

	$merged = [];
	foreach ( $acf_rows as $row ) {
		$term = (string) ( $row['term_slug'] ?? '' );
		if ( '' === $term || ! isset( $by_term[ $term ] ) ) {
			continue;
		}
		$base = $by_term[ $term ];

		$image = (string) ( $row['building_image'] ?? '' );
		if ( '' === $image && ! empty( $row['building_image_id'] ) ) {
			$url = wp_get_attachment_image_url( (int) $row['building_image_id'], 'full' );
			if ( $url ) {
				$image = $url;
			}
		}
		if ( '' === $image ) {
			$image = $base['building_image'];
		}

		$short = (string) ( $row['short_label'] ?? '' );
		$addr  = (string) ( $row['address'] ?? '' );
		if ( '' === $short ) {
			$short = 'el' === chic_get_current_lang() ? t_el( $base['short_label'] ) : $base['short_label'];
		}
		if ( '' === $addr ) {
			$addr = 'el' === chic_get_current_lang() ? t_el( $base['label'] ) : $base['label'];
		}

		$merged[] = [
			'term'           => $term,
			'short_label'    => $short,
			'label'          => $addr,
			'maps'           => (string) ( $row['maps_url'] ?? $base['maps'] ),
			'building_image' => $image,
			'suite_order'    => $base['suite_order'],
		];
	}

	$cache = ! empty( $merged ) ? $merged : $defaults;
	return $cache;
}

/**
 * Returns a building config by mphb_room_type_category slug, or null.
 */
function chic_home_building_by_term( string $term_slug ): ?array {
	foreach ( chic_site_buildings() as $building ) {
		if ( $building['term'] === $term_slug ) {
			return $building;
		}
	}
	return null;
}

/**
 * Sorts suite card arrays to match an explicit title order.
 * Unknown suites fall to the end, then alphabetical by title.
 *
 * @param array[] $cards  Each item must include a 'title' key.
 * @param string[] $order MPHB post titles in display order.
 * @return array[]
 */
function chic_home_sort_suites( array $cards, array $order ): array {
	$rank = [];
	foreach ( array_values( $order ) as $index => $title ) {
		$rank[ strtolower( trim( $title ) ) ] = $index;
	}

	usort( $cards, static function ( array $a, array $b ) use ( $rank ): int {
		$ra = $rank[ strtolower( trim( $a['title'] ) ) ] ?? PHP_INT_MAX;
		$rb = $rank[ strtolower( trim( $b['title'] ) ) ] ?? PHP_INT_MAX;
		if ( $ra !== $rb ) {
			return $ra <=> $rb;
		}
		return strcasecmp( $a['title'], $b['title'] );
	} );

	return $cards;
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
		$capacity = function_exists( 'chic_suite_capacity_label' )
			? chic_suite_capacity_label( $post->ID )
			: '';

		$cards[] = [
			'id'              => $post->ID,
			'title'           => get_the_title( $post ),
			'permalink'       => get_permalink( $post ),
			'thumb_url'       => get_the_post_thumbnail_url( $post->ID, $image_size ) ?: '',
			'capacity_label'  => $capacity,
		];
	}

	$building = chic_home_building_by_term( $term_slug );
	if ( ! empty( $building['suite_order'] ) ) {
		$cards = chic_home_sort_suites( $cards, $building['suite_order'] );
	}

	return $cards;
}

/**
 * Returns all suite cards across buildings in display order.
 *
 * @param string $image_size  WP image size for thumb_url. Default 'medium_large'.
 * @return array[]  Each item: { id, title, permalink, thumb_url, capacity_label }
 */
function chic_home_get_all_suites_ordered( string $image_size = 'medium_large' ): array {
	$all = [];
	foreach ( chic_site_buildings() as $building ) {
		$all = array_merge( $all, chic_home_get_suites( $building['term'], $image_size ) );
	}
	return $all;
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
				'alt'           => 'Chic Centre Suites Athens Mykonos suite main room',
				'desktop'       => chic_upload_url( '1-chic-centre-suites-athens-suite-terrace-mykonos-main-room-athens.webp' ),
				'mobile'        => chic_upload_url( '1-chic-centre-suites-athens-suite-terrace-mykonos-main-room-athens.webp' ),
				'fetchpriority' => 'high',
				'srcset'        => '',
				'mobile_srcset' => '',
			],
			[
				'id'            => 0,
				'alt'           => 'Chic Centre Suites Athens nearby attractions',
				'desktop'       => chic_upload_url( '10-chic-centre-suites-athens-nearby-attractions.avif' ),
				'mobile'        => chic_upload_url( '10-chic-centre-suites-athens-nearby-attractions.avif' ),
				'fetchpriority' => 'auto',
				'srcset'        => '',
				'mobile_srcset' => '',
			],
			[
				'id'            => 0,
				'alt'           => 'Chic Centre Suites Athens nearby restaurants',
				'desktop'       => chic_upload_url( '5-chic-centre-suites-athens-nearby-restaurants.avif' ),
				'mobile'        => chic_upload_url( '5-chic-centre-suites-athens-nearby-restaurants.avif' ),
				'fetchpriority' => 'low',
				'srcset'        => '',
				'mobile_srcset' => '',
			],
		];
	}

	$slides = [];
	foreach ( array_values( $ids ) as $index => $id ) {
		$id = (int) $id;
		if ( $id <= 0 ) continue;
		$desktop = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $desktop ) continue;
		$mobile = wp_get_attachment_image_url( $id, 'medium_large' ) ?: $desktop;
		$slides[] = [
			'id'            => $id,
			'alt'           => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'desktop'       => $desktop,
			'mobile'        => $mobile,
			'fetchpriority' => 0 === $index ? 'high' : ( 1 === $index ? 'auto' : 'low' ),
			'srcset'        => wp_get_attachment_image_srcset( $id, 'full' ) ?: '',
			'mobile_srcset' => wp_get_attachment_image_srcset( $id, 'medium_large' ) ?: '',
		];
	}

	chic_home_hero_apply_avif_slides( $slides );

	return $slides;
}

/**
 * Hero slides 2–3 use AVIF uploads (smaller than legacy WebP attachments).
 *
 * @param array[] $slides  Slide rows from chic_home_hero_slides().
 */
function chic_home_hero_apply_avif_slides( array &$slides ): void {
	$overrides = [
		1 => '10-chic-centre-suites-athens-nearby-attractions.avif',
		2 => '5-chic-centre-suites-athens-nearby-restaurants.avif',
	];

	foreach ( $overrides as $index => $filename ) {
		if ( ! isset( $slides[ $index ] ) ) {
			continue;
		}
		$url = chic_upload_url( $filename );
		$slides[ $index ]['desktop']       = $url;
		$slides[ $index ]['mobile']        = $url;
		$slides[ $index ]['srcset']        = '';
		$slides[ $index ]['mobile_srcset'] = '';
	}
}
