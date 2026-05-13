<?php

/**
 * Single accommodation template helpers.
 *
 * chic_suite_amenities() uses the per-suite data map first.
 * Other helpers (capacity, bed type, size) keep MPHB-driven fallbacks for
 * any suite not in the map.
 */

/* ── Per-suite master data map ───────────────────────────────────────────── */

/**
 * Returns the full data record for a suite by post title (case-insensitive).
 * Keys: capacity, sofa, highlight, size, description.
 *
 * sofa      — 'Sofa' | 'Sofa / Bed' | 'Jacuzzi' (Kohili only)
 * highlight — 'balcony' | 'jacuzzi' | 'terrace' | 'shower'
 * size      — integer m² as string
 */
function _chic_suite_all_data(): array {
	return [

		/* ── Chavriou 2 ──────────────────────────────────────────────────── */

		'suite 1' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Earth tones and olive green decor. Includes double bed, sofa, and balcony.',
		],
		'suite 2' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'shower',
			'size'        => '45',
			'description' => 'Spacious suite with double bed and sofa bed. Accommodates up to 4.',
		],
		'suite 3' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => 'Earth tones and olive green decor. Includes double bed and sofa.',
		],
		'suite 4' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Earth tones and olive green decor. Includes double bed and balcony.',
		],
		'suite 5' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Earth tones and olive green decor. Includes double bed and balcony.',
		],
		'suite 6' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => 'Earth tones and olive green decor. Includes double bed and sofa.',
		],

		/* ── Thiseos 11 ──────────────────────────────────────────────────── */

		'avra suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'jacuzzi',
			'size'        => '50',
			'description' => 'Bedroom with double bed, living room with sofa bed, and Jacuzzi bathtub.',
		],
		'zakynthos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Named after the island. Features double bed, sofa, and balcony.',
		],
		'santorini suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Features double bed, sofa, and balcony.',
		],
		'kohili suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Jacuzzi',   // Jacuzzi fills the 4th slot; balcony is the 5th
			'highlight'   => 'balcony',
			'size'        => '35',
			'description' => 'Earthy tones, ambient lighting, Jacuzzi, and balcony.',
		],
		'korali suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '35',
			'description' => 'Coral hues, double bed, sofa, and shower.',
		],
		'mykonos suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'jacuzzi',
			'size'        => '45',
			'description' => 'Modern design with Jacuzzi. Accommodates up to 4.',
		],
		'paros suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'balcony',
			'size'        => '45',
			'description' => 'Traditional/modern blend, sofa bed, and balcony.',
		],
		'ammos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '35',
			'description' => 'Sand-inspired atmosphere, double bed, and sofa.',
		],
		'ermou suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'terrace',
			'size'        => '30',
			'description' => 'Modern touches, terrace, and double bed.',
		],
		'pelagos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '35',
			'description' => 'Blue accents, double bed, and sofa.',
		],

		/* ── Thiseos 13 ──────────────────────────────────────────────────── */

		'ocean suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => 'Blue ocean theme, double bed, and sofa.',
		],
		'ginger suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => 'Cozy and stylish, double bed, and sofa.',
		],
		'gray suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => 'Minimalist design, double bed, and sofa.',
		],
		'sunshine suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '30',
			'description' => 'Bright and airy with a balcony.',
		],
		'forest suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'shower',
			'size'        => '45',
			'description' => 'Natural elements, bedroom plus living room. Accommodates up to 4.',
		],
	];
}

/**
 * Returns the data record for a specific post, or null if not in the map.
 */
function _chic_suite_data_for( int $post_id ): ?array {
	static $map = null;
	if ( null === $map ) {
		$map = _chic_suite_all_data();
	}
	$key = strtolower( trim( get_the_title( $post_id ) ) );
	return $map[ $key ] ?? null;
}

/* ── Public helpers ──────────────────────────────────────────────────────── */

/**
 * Returns max guest capacity (int) for a suite.
 * Reads mphb_room_type_category slug matching up-to-N-guests (same as homepage logic).
 */
function chic_suite_capacity( int $post_id ): int {
	$terms = get_the_terms( $post_id, 'mphb_room_type_category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( preg_match( '/^up-to-(\d+)-guests$/', $term->slug, $m ) ) {
				return (int) $m[1];
			}
		}
	}
	$adults   = (int) get_post_meta( $post_id, 'mphb_adults', true );
	$children = (int) get_post_meta( $post_id, 'mphb_children', true );
	$total    = $adults + $children;
	return $total > 0 ? $total : 2;
}

/**
 * Returns the suite size string from MPHB post meta (mphb_size).
 */
function chic_suite_size( int $post_id ): string {
	$size = get_post_meta( $post_id, 'mphb_size', true );
	return $size ? (string) $size : '';
}

/**
 * Returns the first bed type term name for the suite.
 */
function chic_suite_bed_type( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'mphb_room_type_bed_type' );
	if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		return $terms[0]->name;
	}
	return 'Double Bed';
}

/**
 * Returns the 6 amenity rows for the amenities strip.
 * Driven by the per-suite data map; falls back to MPHB meta lookups.
 * Each row: [ 'icon' => string, 'label' => string ]
 */
function chic_suite_amenities( int $post_id ): array {
	static $highlight_map = [
		'jacuzzi' => [ 'fas fa-hot-tub',                                       'Jacuzzi' ],
		'balcony' => [ 'th-linea icon th-linea icon-arrows-circle-check',      'Balcony' ],
		'terrace' => [ 'th-linea icon th-linea icon-arrows-circle-check',      'Terrace' ],
		'shower'  => [ 'fas fa-shower',                                        'Shower'  ],
	];

	$data = _chic_suite_data_for( $post_id );

	if ( $data ) {
		$sofa_icon  = ( 'jacuzzi' === strtolower( $data['sofa'] ) ) ? 'fas fa-hot-tub' : 'fas fa-couch';
		$hl         = $highlight_map[ $data['highlight'] ] ?? $highlight_map['shower'];

		return [
			[ 'icon' => 'fas fa-user-plus',                               'label' => $data['capacity'] ],
			[ 'icon' => 'fas fa-bed',                                     'label' => 'King Size Bed' ],
			[ 'icon' => 'fasth-trip travelpack-fork-plate-knife',         'label' => 'Equipped Kitchen' ],
			[ 'icon' => $sofa_icon,                                       'label' => $data['sofa'] ],
			[ 'icon' => $hl[0],                                           'label' => $hl[1] ],
			[ 'icon' => 'fas fa-home',                                    'label' => $data['size'] . 'm²' ],
		];
	}

	// MPHB fallback for suites not in the map
	$capacity = chic_suite_capacity( $post_id );
	$size     = chic_suite_size( $post_id );
	$bed      = chic_suite_bed_type( $post_id );

	return [
		[ 'icon' => 'fas fa-user-plus',                               'label' => 'Up to ' . $capacity . ' guests' ],
		[ 'icon' => 'fas fa-bed',                                     'label' => $bed ],
		[ 'icon' => 'fasth-trip travelpack-fork-plate-knife',         'label' => 'Equipped Kitchen' ],
		[ 'icon' => 'fas fa-couch',                                   'label' => 'Sofa' ],
		[ 'icon' => 'fas fa-shower',                                  'label' => 'Shower' ],
		[ 'icon' => 'fas fa-home',                                    'label' => $size ? $size . 'm²' : '—' ],
	];
}

/**
 * Returns gallery attachment IDs from the mphb_gallery CSV meta.
 *
 * @return int[]
 */
function chic_suite_gallery_ids( int $post_id ): array {
	$raw = get_post_meta( $post_id, 'mphb_gallery', true );
	if ( empty( $raw ) ) {
		return [];
	}
	return array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
}

/**
 * Returns the building config array for the suite (from chic_home_buildings()).
 * Matched by mphb_room_type_category term slug. Returns null if not found.
 */
function chic_suite_building( int $post_id ): ?array {
	$buildings    = chic_home_buildings();
	$by_term_slug = array_column( $buildings, null, 'term' );
	$terms        = get_the_terms( $post_id, 'mphb_room_type_category' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	foreach ( $terms as $term ) {
		if ( isset( $by_term_slug[ $term->slug ] ) ) {
			return $by_term_slug[ $term->slug ];
		}
	}
	return null;
}

/**
 * Returns the description for a suite from the per-suite data map.
 */
function chic_suite_description( int $post_id ): string {
	$data = _chic_suite_data_for( $post_id );
	return $data['description'] ?? '';
}
