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
 * description — string[] paragraphs (or legacy string for unlisted suites)
 */
function _chic_suite_all_data(): array {
	return [

		/* ── Chavriou 2 ──────────────────────────────────────────────────── */

		'suite 1' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '30',
			'description' => [
				'Suite 1 is a modern studio in Athens city center, ideally located just steps from Ermou Street, one of the most popular shopping and pedestrian areas in the capital. This 30m² Athens accommodation is perfect for couples or solo travelers seeking a central and comfortable stay.',
				'The studio features a comfortable double bed, a cozy seating area with sofa, and a private balcony, offering the perfect space to relax after a day of exploring Athens. The interior combines natural tones with a modern and functional layout, creating a warm and practical environment.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and all essential amenities for a convenient stay in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping streets, Suite 1 is an excellent choice for travelers looking for a centrally located studio near Ermou Street and Syntagma Square with a private balcony and easy city access.',
			],
		],
		'suite 2' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'balcony',
			'size'        => '41',
			'description' => [
				'Suite 2 is a spacious one-bedroom apartment in Athens city center, ideally located in the heart of the capital. This 41m² Athens accommodation is perfect for families or small groups of up to 4 guests seeking extra comfort, space, and convenience in a central location.',
				'The apartment features a separate bedroom with a comfortable double bed and a living room with a sofa bed, offering privacy and flexibility for both short and longer stays. Its functional layout makes it an excellent choice for families visiting Athens.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and the added convenience of a washing machine, ideal for extended stays. A private balcony provides a relaxing outdoor space after a day exploring the city.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping areas, Suite 2 is a practical and comfortable base for travelers looking for a centrally located family apartment in Athens with extra space and home-like amenities.',
			],
		],
		'suite 3' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '23',
			'description' => [
				'Suite 3 is a comfortable studio in Athens city center, located in the heart of the capital. This 23m² Athens accommodation is ideal for couples or solo travelers seeking a practical and well-located stay in a central area of the city.',
				'The studio features a comfortable double bed, a small seating area with sofa, and a functional layout designed for easy and convenient living during your stay in Athens. Natural tones with olive accents create a warm and relaxing atmosphere, inspired by Mediterranean aesthetics.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and all essential amenities needed for a comfortable short or extended stay.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping streets, Suite 3 is an excellent choice for travelers looking for a centrally located and comfortable studio in Athens with easy access to everything the city has to offer.',
			],
		],
		'suite 4' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '24',
			'description' => [
				'Suite 4 is a modern studio in Athens city center, located in the heart of the capital. This 24m² Athens accommodation is ideal for couples or solo travelers seeking a comfortable and well-located stay with easy access to the city\'s main highlights.',
				'The studio features a comfortable double bed, a small seating area with sofa, and a private balcony, offering the perfect space to enjoy your morning coffee or relax after a day of sightseeing in Athens. The layout is designed for comfort and convenience in a compact urban space.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and all essential amenities for a pleasant stay in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping areas, Suite 4 is an excellent choice for travelers looking for a centrally located studio in Athens with a private balcony and modern comforts.',
			],
		],
		'suite 5' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '24',
			'description' => [
				'Suite 5 is a comfortable studio in Athens city center, located in the heart of the capital. This 24m² Athens accommodation is ideal for couples or solo travelers seeking a relaxed, practical, and centrally located stay.',
				'The studio features a comfortable double bed, a seating area with sofa, and a private balcony, offering a quiet outdoor space to unwind after a busy day exploring Athens. The functional layout ensures a convenient and comfortable living experience in a compact city setting.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and all essential amenities for both short and extended stays in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping streets, Suite 5 is an excellent choice for travelers looking for a centrally located studio in Athens with a balcony and easy access to everything the city offers.',
			],
		],
		'suite 6' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '26',
			'description' => [
				'Suite 6 is a studio with balcony in Athens city center, located in the heart of the capital. This 26m² Athens accommodation is ideal for couples or solo travelers seeking a comfortable, well-balanced, and centrally located stay.',
				'The studio features a comfortable double bed, a seating area with sofa, and a private balcony, offering a pleasant outdoor space to relax after a day exploring Athens. With a slightly more spacious layout than standard studios, it provides added comfort for both short and longer stays.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi and all essential amenities for a convenient stay in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping streets, Suite 6 is an excellent choice for travelers looking for a centrally located studio in Athens with a balcony, extra space, and modern comfort.',
			],
		],

		/* ── Thiseos 11 ──────────────────────────────────────────────────── */

		'avra suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'jacuzzi',
			'size'        => '52',
			'description' => [
				'Avra Suite is a spacious 52m² one-bedroom apartment in Athens city center, designed for families or small groups of up to four guests. Located near the city\'s most iconic landmarks, it offers easy access to the Acropolis, Monastiraki, and Ermou Street.',
				'The suite features a private bedroom, a separate living area with sofa bed, modern bathroom with Jacuzzi bathtub for added comfort after a day of sightseeing, a private balcony, fully equipped kitchen, high-speed WiFi, smart TV access and modern entertainment amenities make it ideal for both short city breaks and longer stays.',
				'With generous space and premium features, Avra Suite is a perfect choice for travelers seeking comfort in the heart of Athens.',
			],
		],
		'zakynthos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '28',
			'description' => [
				'Zakynthos Suite is a stylish studio apartment in Athens city center, designed for couples seeking a relaxing and convenient stay close to the historical center.',
				'This elegant 28m² suite features a comfortable double bed, a cozy seating area, and a private balcony where guests can enjoy their morning coffee or unwind after exploring the city. Island-inspired details create a warm and welcoming atmosphere, ideal for short stays or city breaks.',
				'Guests can also enjoy a fully equipped kitchen, a modern bathroom with shower, high-speed WiFi, and smart TV access, ensuring both comfort and practicality throughout their stay.',
				'Located near Athens\' most iconic attractions, shopping streets, and local dining spots, Zakynthos Suite offers the perfect balance of style, convenience, and central location.',
			],
		],
		'santorini suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'balcony',
			'size'        => '28',
			'description' => [
				'Santorini Suite is a stylish studio apartment in Athens historical center, ideal for couples seeking comfort and convenience in a prime location.',
				'This thoughtfully island-designed suite includes a cozy double bed, seating area, private balcony, a Cycladic bathroom style with shower and fully equipped kitchen. Guests can enjoy modern amenities such as smart TV access and high-speed WiFi, ensuring a seamless stay.',
				'Within walking distance of Athens\' major attractions, this studio offers the perfect base for discovering the city while enjoying a boutique-style atmosphere.',
			],
		],
		'kohili suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Jacuzzi',   // Jacuzzi fills the 4th slot; balcony is the 5th
			'highlight'   => 'balcony',
			'size'        => '37',
			'description' => [
				'Kohili Suite is a spacious premium apartment in Athens city center, designed for travelers who value comfort, style, and relaxation in a central location.',
				'This elegant 37m² suite features a comfortable double bed, a cozy seating area, and a private balcony, creating the perfect setting for a memorable city stay. Earthy tones and soft lighting enhance the calming atmosphere, while the private Jacuzzi bathtub offers an added touch of luxury after a day exploring Athens.',
				'Ideal for couples, the suite also includes a fully equipped kitchen, modern bathroom with Jacuzzi bathtub, high-speed WiFi, and smart TV access, ensuring a seamless and enjoyable experience.',
				'Located close to Athens\' historical landmarks, shopping areas, and vibrant dining spots, Kohili Suite combines premium comfort with the convenience of staying in the heart of the city.',
			],
		],
		'korali suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '20',
			'description' => [
				'Korali Suite is a stylish studio apartment in Athens city center, ideal for couples seeking a comfortable and well-located stay in the heart of the capital.',
				'This inviting 20m² suite features a cozy double bed, a small seating area with sofa, and warm interiors inspired by soft coral tones, creating a relaxing atmosphere after a day of exploring Athens.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, high-speed WiFi, and smart TV access, ensuring both comfort and convenience throughout their stay.',
				'Within walking distance of Athens\' main attractions, shopping streets, and local restaurants, Korali Suite offers a practical and welcoming base for short stays, city breaks, and holiday escapes.',
			],
		],
		'mykonos suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'terrace',
			'size'        => '35',
			'description' => [
				'Mykonos Suite is a spacious family suite in Athens city center, ideal for families, couples, or small groups of up to 4 guests seeking comfort and convenience in the heart of the capital.',
				'This 35m² suite features a comfortable double bed and a living room with a sofa bed, offering flexible accommodation for families or groups. A large private terrace provides the perfect outdoor space to relax and unwind after a day exploring Athens.',
				'Designed with subtle island-inspired elements, the suite combines a bright and relaxing atmosphere with modern functionality. Guests can enjoy a fully equipped kitchen, a contemporary Mykonian-style bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities for a comfortable stay.',
				'Located within walking distance of Athens\' main attractions, shopping areas, restaurants, and cultural sites, Mykonos Suite is an excellent choice for travelers looking for a centrally located apartment in Athens with extra space and a private terrace.',
			],
		],
		'paros suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'shower',
			'size'        => '36',
			'description' => [
				'Paros Suite is a spacious family suite in Athens city center, ideal for families or small groups of up to 4 guests looking for a comfortable and well-located stay in the heart of the capital.',
				'This bright 36m² suite features a separate bedroom with a double bed and a living area with a sofa bed, offering flexible sleeping arrangements for families or groups. Inspired by Cycladic design, the interiors combine minimal aesthetics, natural light, and a welcoming atmosphere.',
				'Guests can enjoy a fully equipped kitchen, a Cycladic bathroom style, smart TV access, high-speed WiFi, ensuring both comfort and convenience throughout their stay in Athens.',
				'Located in the historic center of Athens, within walking distance of major attractions, restaurants, shops, and cultural sites, Paros Suite offers a practical and stylish base for city breaks, family holidays, and short stays in Athens.',
			],
		],
		'ammos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'terrace',
			'size'        => '19',
			'description' => [
				'Ammos Suite is a bright and functional studio in Athens city center, ideally located in the historic center of the capital. Perfect for couples, this 19m² Athens accommodation offers a comfortable and well-connected base for exploring the city.',
				'The studio features a cozy double bed, a small seating area with sofa, and a private terrace, ideal for enjoying your morning coffee or relaxing after a day of sightseeing in Athens. Warm sandy tones throughout the interior create a calm and inviting atmosphere inspired by natural island aesthetics.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities for a comfortable short or extended stay in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping areas, Ammos Suite is an excellent choice for travelers looking for a central studio in Athens with a private terrace and easy access to the city\'s highlights.',
			],
		],
		'ermou suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'terrace',
			'size'        => '19',
			'description' => [
				'Ermou Suite is a modern studio in Athens city center, ideally located just steps away from Ermou Street, one of the most popular shopping streets in Athens. Perfect for couples or solo travelers seeking a central and convenient stay in the heart of the capital.',
				'This functional 19m² studio features a comfortable double bed, a cozy seating area, and a private terrace, offering a relaxing outdoor space after a day of exploring Athens. The contemporary design creates a clean and comfortable environment for both short and extended stays.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom, smart TV access, high-speed WiFi, and essential amenities for a comfortable stay. The studio is designed to provide convenience, privacy, and functionality in a compact urban space.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping areas, Ermou Suite is an excellent choice for travelers looking for a centrally located apartment near Ermou Street and Syntagma Square.',
			],
		],
		'pelagos suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'terrace',
			'size'        => '27',
			'description' => [
				'Pelagos Suite is a stylish studio in Athens city center, ideally located in the historic center of the capital. Perfect for couples or solo travelers, this 27m² Athens accommodation offers comfort, convenience, and a central base for exploring the city.',
				'The studio features a comfortable double bed, a cozy seating area with sofa, and a private terrace, ideal for enjoying your morning coffee or relaxing after a day of sightseeing in Athens. Soft blue tones inspired by the Aegean Sea create a calming and refreshing atmosphere throughout the space.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities for a comfortable stay in Athens.',
				'Located within walking distance of major attractions, restaurants, cafés, and shopping streets, Pelagos Suite is an excellent choice for travelers seeking a central studio in Athens with a private terrace and relaxing island-inspired design.',
			],
		],

		/* ── Thiseos 13 ──────────────────────────────────────────────────── */

		'ocean suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '30',
			'description' => [
				'Ocean Suite is a modern studio in Athens city center, located in the historic heart of the capital. Ideal for couples or solo travelers, this 30m² Athens accommodation offers a comfortable, stylish, and well-connected base for exploring the city.',
				'The studio features a comfortable double bed and a cozy seating area with sofa, designed in a clean and modern layout. Calming blue tones inspired by the sea create a fresh and relaxing atmosphere, perfect for unwinding after a day in Athens.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities needed for a convenient short or extended stay.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping streets, Ocean Suite is an excellent choice for travelers seeking a central studio in Athens with a modern design and easy access to the city\'s highlights.',
			],
		],
		'ginger suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '17',
			'description' => [
				'Ginger Suite is a compact studio in Athens city center, located in the historic heart of the capital. Ideal for couples or solo travelers, this 17m² Athens accommodation offers a practical, comfortable, and well-located stay for short city breaks.',
				'The studio features a comfortable double bed, a small seating area with sofa, and a functional layout designed for convenience and efficiency. Warm tones inspired by natural ginger shades create a cozy and inviting atmosphere, perfect for relaxing after exploring Athens.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities needed for a comfortable stay.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping streets, Ginger Suite is an excellent choice for travelers seeking an affordable and centrally located studio in Athens with everything needed for a pleasant stay.',
			],
		],
		'gray suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '32',
			'description' => [
				'Grey Suite is a spacious studio in Athens city center, located in the historic heart of the capital. Offering 32m² of living space, this Athens accommodation is ideal for couples or solo travelers seeking extra comfort and room during their stay.',
				'The studio features a comfortable double bed, a seating area with sofa, and a clean, contemporary design with neutral gray tones that create a calm and balanced atmosphere. Its larger layout provides added comfort compared to standard city studios.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities for a convenient short or extended stay in Athens.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping streets, Gray Suite is a perfect choice for travelers looking for a spacious and centrally located studio in Athens with modern comfort and functionality.',
			],
		],
		'sunshine suite' => [
			'capacity'    => 'Up to 2 guests',
			'sofa'        => 'Sofa',
			'highlight'   => 'shower',
			'size'        => '15',
			'description' => [
				'Sunshine Suite is a bright studio in Athens city center, located in the historic heart of the capital. This 15m² Athens accommodation is ideal for couples or solo travelers seeking a functional, comfortable, and centrally located stay.',
				'The studio features a comfortable double bed, a small seating area with sofa, and a bright interior with warm tones that create a welcoming and uplifting atmosphere, perfect for relaxing after exploring Athens.',
				'Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities needed for a convenient short or extended stay.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping areas, Sunshine Suite is a practical and well-positioned choice for travelers looking for a central studio in Athens with comfort and easy access to the city highlights.',
			],
		],
		'forest suite' => [
			'capacity'    => 'Up to 4 guests',
			'sofa'        => 'Sofa / Bed',
			'highlight'   => 'shower',
			'size'        => '35',
			'description' => [
				'Forest Suite is a spacious apartment in Athens city center, located in the historic heart of the capital. Ideal for families, couples, or small groups of up to 4 guests, this 35m² Athens accommodation offers comfort, space, and a central location.',
				'The apartment features a double bed and a sofa bed, providing flexible sleeping arrangements for families or groups. The larger layout offers extra space compared to standard studios, making it ideal for longer stays or guests who prefer more comfort.',
				'Soft green tones inspired by nature create a calm and welcoming atmosphere throughout the space. Guests can enjoy a fully equipped kitchen, a modern bathroom with shower, smart TV access, high-speed WiFi, and all essential amenities for a convenient stay in Athens.',
				'Located within walking distance of Athens\' main attractions, restaurants, cafés, and shopping streets, Forest Suite is an excellent choice for travelers seeking a spacious and centrally located apartment in Athens with a relaxing, nature-inspired design.',
			],
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
 * Returns the display capacity label for the current language.
 * ACF chic_suite_capacity_{en|el} → PHP data map → numeric MPHB fallback.
 */
function chic_suite_capacity_label( int $post_id ): string {
	$lang = chic_get_current_lang();

	if ( function_exists( 'get_field' ) ) {
		$val = (string) ( get_field( 'chic_suite_capacity_' . $lang, $post_id ) ?: '' );
		if ( 'el' === $lang && $val !== '' ) {
			$en = (string) ( get_field( 'chic_suite_capacity_en', $post_id ) ?: '' );
			if ( $val === $en ) {
				$val = '';
			}
		}
		if ( $val !== '' ) {
			return $val;
		}
	}

	$data = _chic_suite_data_for( $post_id );
	if ( $data && ! empty( $data['capacity'] ) ) {
		return t( $data['capacity'] );
	}

	return sprintf( t( 'Up to %d guests' ), chic_suite_capacity( $post_id ) );
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
	return t( 'Double Bed' );
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
	// Translate highlight labels at runtime.
	$translated_highlight_map = array_map( fn( $v ) => [ $v[0], t( $v[1] ) ], $highlight_map );

	$data = _chic_suite_data_for( $post_id );

	if ( $data ) {
		$sofa_icon  = ( 'jacuzzi' === strtolower( $data['sofa'] ) ) ? 'fas fa-hot-tub' : 'fas fa-couch';
		$hl         = $translated_highlight_map[ $data['highlight'] ] ?? $translated_highlight_map['shower'];

		return [
			[ 'icon' => 'fas fa-user-plus',                               'label' => chic_suite_capacity_label( $post_id ) ],
			[ 'icon' => 'fas fa-bed',                                     'label' => t( 'King Size Bed' ) ],
			[ 'icon' => 'fasth-trip travelpack-fork-plate-knife',         'label' => t( 'Equipped Kitchen' ) ],
			[ 'icon' => $sofa_icon,                                       'label' => t( $data['sofa'] ) ],
			[ 'icon' => $hl[0],                                           'label' => $hl[1] ],
			[ 'icon' => 'fas fa-home',                                    'label' => $data['size'] . 'm²' ],
		];
	}

	// MPHB fallback for suites not in the map
	$size = chic_suite_size( $post_id );
	$bed  = chic_suite_bed_type( $post_id );

	return [
		[ 'icon' => 'fas fa-user-plus',                               'label' => chic_suite_capacity_label( $post_id ) ],
		[ 'icon' => 'fas fa-bed',                                     'label' => t( $bed ) ],
		[ 'icon' => 'fasth-trip travelpack-fork-plate-knife',         'label' => t( 'Equipped Kitchen' ) ],
		[ 'icon' => 'fas fa-couch',                                   'label' => t( 'Sofa' ) ],
		[ 'icon' => 'fas fa-shower',                                  'label' => t( 'Shower' ) ],
		[ 'icon' => 'fas fa-home',                                    'label' => $size ? $size . 'm²' : '-' ],
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
 * Returns the description paragraphs for a suite from the per-suite data map.
 * Always returns an array of strings, even for suites with a legacy string description.
 *
 * @return string[]
 */
function chic_suite_description( int $post_id ): array {
	$data = _chic_suite_data_for( $post_id );
	$desc = $data['description'] ?? '';
	if ( is_string( $desc ) ) {
		$paras = $desc === '' ? [] : [ $desc ];
	} else {
		$paras = is_array( $desc ) ? array_values( array_filter( $desc, 'is_string' ) ) : [];
	}
	return array_map( 't', $paras );
}
