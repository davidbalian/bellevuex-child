<?php

/**
 * Single accommodation template helpers.
 */

/**
 * Returns max guest capacity for a suite.
 * Prefers mphb_room_type_category slug matching up-to-N-guests (same logic as homepage).
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
 * Each row: [ 'icon' => string, 'label' => string ]
 */
function chic_suite_amenities( int $post_id ): array {
	$capacity = chic_suite_capacity( $post_id );
	$size     = chic_suite_size( $post_id );
	$bed      = chic_suite_bed_type( $post_id );

	return [
		[ 'icon' => 'fas fa-user-plus',                                  'label' => 'Up to ' . $capacity . ' guests' ],
		[ 'icon' => 'fas fa-bed',                                        'label' => $bed ],
		[ 'icon' => 'fasth-trip travelpack-fork-plate-knife',            'label' => 'Equipped Kitchen' ],
		[ 'icon' => 'fas fa-couch',                                      'label' => 'Sofa' ],
		[ 'icon' => 'th-linea icon th-linea icon-arrows-circle-check',  'label' => 'Balcony' ],
		[ 'icon' => 'fas fa-home',                                       'label' => $size ? $size . 'm²' : '—' ],
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
 * Returns the description string for a suite, keyed by post title (case-insensitive).
 */
function chic_suite_description( int $post_id ): string {
	static $map = null;
	if ( null === $map ) {
		$map = _chic_suite_description_map();
	}
	$key = strtolower( trim( get_the_title( $post_id ) ) );
	return $map[ $key ] ?? '';
}

function _chic_suite_description_map(): array {
	return [
		// Chavriou 2
		'suite 1'         => 'This suite decorated with earth tones and olive green as the dominant color includes a double bed, sofa, large TV with Netflix subscription, equipped kitchen, bathroom with shower and balcony. It can accommodate up to 2 people.',
		'suite 2'         => 'This spacious suite is decorated with earth tones and olive green as the dominant color. It features a comfortable double bed in the bedroom and a sofa/bed in the living room. Includes a large TV with Netflix subscription, fully equipped kitchen, and bathroom with shower. It can accommodate up to 4 people.',
		'suite 3'         => 'This suite decorated with earth tones and olive green as the dominant color includes a comfortable double bed, sofa, large TV with Netflix subscription, equipped kitchen, and bathroom with shower. It can accommodate up to 2 people.',
		'suite 4'         => 'This suite decorated with earth tones and olive green as the dominant color includes a double bed, sofa, large TV with Netflix subscription, equipped kitchen, bathroom with shower and balcony. It can accommodate up to 2 people.',
		'suite 5'         => 'This suite decorated with earth tones and olive green as the dominant color includes a comfortable double bed, sofa, large TV with Netflix subscription, equipped kitchen, bathroom with shower and balcony. It can accommodate up to 2 guests.',
		'suite 6'         => 'This suite decorated with earth tones and olive green as the dominant color includes a comfortable double bed, sofa, large TV with Netflix subscription, equipped kitchen, and bathroom with shower. It can accommodate up to 2 people.',
		// Thiseos 11
		'avra suite'      => 'Feel the warm atmosphere and positive energy radiating from this beautiful suite. It consists of a bedroom with a double bed and big TV, a living room with a comfortable sofa/bed, TV with Netflix subscription, fully equipped kitchen, bathroom with a Jacuzzi bathtub, and a balcony. Accommodating up to 4 people.',
		'zakynthos suite' => 'This suite is named after one of the most beautiful islands in Greece. It features a double bed, a sofa, a large TV with Netflix subscription, a fully equipped kitchen, a bathroom with a shower, and a balcony. This suite can accommodate up to 2 people.',
		'santorini suite' => 'Every detail is designed to offer you an unforgettable experience. Features a double bed, a comfortable sofa, a large TV with Netflix subscription, a fully equipped kitchen, a bathroom with a shower, and a balcony. This suite can accommodate up to 2 people.',
		'kohili suite'    => 'Treat yourself to moments of relaxation in this stunning suite. Decorated with earthy tones and ambient lighting, it is a haven of tranquillity where you can enjoy the Jacuzzi and your coffee on the balcony. It can accommodate up to 2 people.',
		'korali suite'    => 'This suite is perfect for couples, offering both comfort and luxury for your vacation. Adorned in coral hues, it features a double bed, a sofa, a large TV with Netflix, a fully equipped kitchen, and a shower. The suite accommodates up to 2 guests.',
		'mykonos suite'   => 'Experience luxury in our Mykonos Suite, featuring modern design and high-end amenities. It includes a double bed, a sofa/bed, a fully equipped kitchen, and a bathroom with a Jacuzzi. Accommodates up to 4 people.',
		'paros suite'     => 'This suite offers a blend of traditional and modern elements. It features a spacious living area with a sofa/bed, a bedroom with a double bed, a fully equipped kitchen, and a balcony. Accommodates up to 4 guests.',
		'ammos suite'     => 'Inspired by the sandy beaches of Greece, this suite offers a relaxing atmosphere. It includes a double bed, a sofa, a large TV with Netflix, and a fully equipped kitchen. Ideal for up to 2 guests.',
		'ermou suite'     => 'This suite, adorned with modern touches, is ideal for unwinding after a full day in the center of Athens. It features a double bed, a sofa, a fully equipped kitchen, a large TV with Netflix subscription, a modern bathroom with shower, and a lovely terrace. It can accommodate up to 2 people.',
		'pelagos suite'   => 'Named after the Greek sea, this suite features blue accents and a refreshing design. It includes a double bed, sofa, equipped kitchen, and a bathroom with shower. Accommodates up to 2 guests.',
		// Thiseos 13
		'ocean suite'     => 'This suite decoration is based on the blue ocean and has winning first impressions from the moment you walk in. Includes a double bed, sofa, big TV with Netflix, fully equipped kitchen and bathroom with shower. It can accommodate up to 2 people.',
		'ginger suite'    => 'A cozy and stylish suite featuring warm tones. It includes a double bed, a sofa, a large TV with Netflix, a fully equipped kitchen, and a modern bathroom. Accommodates up to 2 guests.',
		'gray suite'      => 'Modern and minimalist, this suite offers a sleek design with all the comforts of home. It features a double bed, sofa, fully equipped kitchen, and a large TV. Accommodates up to 2 guests.',
		'sunshine suite'  => 'Bright and airy, the Sunshine Suite is designed to make you feel at home. It includes a double bed, sofa, fully equipped kitchen, and a balcony to enjoy the Athens sun. Accommodates up to 2 guests.',
		'forest suite'    => 'A spacious suite decorated with natural elements. It features a bedroom with a double bed and a living room with a sofa/bed, making it ideal for up to 4 guests. Includes a kitchen and large TV.',
	];
}
