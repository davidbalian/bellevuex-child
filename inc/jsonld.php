<?php
/**
 * JSON-LD structured data emitter for Chic Centre Suites.
 *
 * Disables AIOSEO's built-in schema; emits a single @graph on every
 * front-end request. Fully bilingual: canonical entities share the same
 * @id across EN and EL; WebPage and BreadcrumbList nodes are language-specific.
 */
defined( 'ABSPATH' ) || exit;

// Ensure data helpers are available regardless of which template is active.
require_once __DIR__ . '/testimonials-data.php';
require_once __DIR__ . '/explore-athens-data.php';

/* ── Disable AIOSEO schema ─────────────────────────────────────────────────── */

add_filter( 'aioseo_schema_disable',        '__return_true' );
add_filter( 'aioseo_schema_disable_graphs', '__return_true' );
add_filter( 'aioseo_schema_graphs',         fn() => [] );

/* ── Entry point ───────────────────────────────────────────────────────────── */

add_action( 'wp_head', 'chic_jsonld_emit', 20 );

function chic_jsonld_emit(): void {
	if ( is_admin() || is_feed() || is_preview() || is_404() ) {
		return;
	}
	$graph = chic_jsonld_build_graph();
	if ( empty( $graph ) ) {
		return;
	}
	$doc = [
		'@context' => 'https://schema.org',
		'@graph'   => array_values( array_filter( $graph ) ),
	];
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $doc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}

/* ── Graph builder ─────────────────────────────────────────────────────────── */

function chic_jsonld_build_graph(): array {
	$nodes = [];

	$nodes[] = chic_jsonld_node_organization();
	$nodes[] = chic_jsonld_node_website();
	$nodes[] = chic_jsonld_node_lodging_business();
	$nodes[] = chic_jsonld_node_webpage();

	$breadcrumb = chic_jsonld_node_breadcrumbs();
	if ( $breadcrumb ) {
		$nodes[] = $breadcrumb;
	}

	if ( is_front_page() ) {
		foreach ( chic_jsonld_nodes_for_buildings() as $node ) {
			$nodes[] = $node;
		}
	} elseif ( is_singular( 'mphb_room_type' ) ) {
		$post_id  = (int) get_the_ID();
		$nodes[]  = chic_jsonld_node_hotel_room( $post_id );
		$building = chic_suite_building( $post_id );
		if ( $building ) {
			$nodes[] = chic_jsonld_node_for_building( $building );
		}
		foreach ( chic_jsonld_nodes_reviews( 'suite', $post_id ) as $node ) {
			$nodes[] = $node;
		}
	} elseif ( is_page_template( 'page-testimonials.php' ) ) {
		foreach ( chic_jsonld_nodes_reviews( 'all' ) as $node ) {
			$nodes[] = $node;
		}
	} elseif ( is_page_template( 'page-explore-athens.php' ) ) {
		foreach ( chic_jsonld_nodes_tourist_attractions() as $node ) {
			$nodes[] = $node;
		}
	}

	return $nodes;
}

/* ── Small helpers ─────────────────────────────────────────────────────────── */

/** Canonical @id anchor — EN base, no /el/ prefix. */
function chic_jsonld_id( string $fragment ): string {
	return rtrim( home_url( '/' ), '/' ) . $fragment;
}

/** 'en-US' or 'el-GR' matching the <html lang> attribute (inc/i18n.php:118-128). */
function chic_jsonld_locale_tag(): string {
	return chic_get_current_lang() === 'el' ? 'el-GR' : 'en-US';
}

/** Current page URL — localized (EN or EL) based on the request. */
function chic_jsonld_current_url(): string {
	if ( is_front_page() ) {
		return chic_localized_url( '/' );
	}
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	return chic_localized_url( '/' );
}

/**
 * EN-canonical permalink for a post — strips any /el/ prefix so it's stable
 * regardless of which language the current request is in.
 */
function chic_jsonld_en_permalink( int $post_id = 0 ): string {
	if ( is_front_page() && $post_id === 0 ) {
		return home_url( '/' );
	}
	$url = $post_id > 0 ? (string) get_permalink( $post_id ) : (string) get_permalink();
	$rel = wp_make_link_relative( $url );
	$rel = (string) preg_replace( '#^/el(/|$)#', '/', $rel );
	return home_url( $rel );
}

/** Page title for the current request (EL meta → AIOSEO EN meta → WP title). */
function chic_jsonld_current_page_title(): string {
	if ( is_singular() ) {
		$id = (int) get_the_ID();
		if ( chic_get_current_lang() === 'el' ) {
			$v = (string) get_post_meta( $id, '_chic_aioseo_title_el', true );
			if ( '' !== $v ) {
				return wp_strip_all_tags( $v );
			}
		}
		$v = (string) get_post_meta( $id, '_aioseo_title', true );
		if ( '' !== $v ) {
			return wp_strip_all_tags( $v );
		}
		return wp_strip_all_tags( (string) get_the_title() );
	}
	return wp_strip_all_tags( get_bloginfo( 'name' ) );
}

/** Page description for the current request. */
function chic_jsonld_current_page_description(): string {
	if ( is_singular() ) {
		$id = (int) get_the_ID();
		if ( chic_get_current_lang() === 'el' ) {
			$v = (string) get_post_meta( $id, '_chic_aioseo_description_el', true );
			if ( '' !== $v ) {
				return wp_strip_all_tags( $v );
			}
		}
		$v = (string) get_post_meta( $id, '_aioseo_description', true );
		if ( '' !== $v ) {
			return wp_strip_all_tags( $v );
		}
		$e = wp_strip_all_tags( (string) get_the_excerpt() );
		return '' !== $e ? $e : wp_strip_all_tags( get_bloginfo( 'description' ) );
	}
	return wp_strip_all_tags( get_bloginfo( 'description' ) );
}

/**
 * Hotel-level description — tries the home page's Greek AIOSEO meta on EL requests
 * so the LodgingBusiness entity carries language-appropriate copy.
 */
function chic_jsonld_hotel_description(): string {
	$en = 'Chic Centre Suites is a collection of modern serviced apartments in the heart of Athens, spread across three buildings in the historical city centre. Perfectly located within walking distance of the Acropolis, Monastiraki Square, and Ermou shopping street, each suite features fully equipped kitchens, high-speed WiFi, smart TVs with streaming access, and contemporary interiors, ideal for couples, families, solo travellers, and small groups.';
	if ( chic_get_current_lang() === 'el' ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id > 0 ) {
			$el = (string) get_post_meta( $front_id, '_chic_aioseo_description_el', true );
			if ( '' !== $el ) {
				return wp_strip_all_tags( $el );
			}
		}
	}
	return $en;
}

/** Parse a "Month YYYY" label into "YYYY-MM-DD" or '' if blank/unparseable. */
function chic_jsonld_parse_date_label( string $label ): string {
	if ( '' === trim( $label ) ) {
		return '';
	}
	$ts = strtotime( '1 ' . $label );
	return $ts ? gmdate( 'Y-m-d', $ts ) : '';
}

/** Logo URL — resolves to the current domain (same image path as header-nav.php). */
function chic_jsonld_logo_url(): string {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) || empty( $upload['baseurl'] ) ) {
		return home_url( '/wp-content/uploads/chic-centre-suites-logo-no-text.png' );
	}
	return trailingslashit( $upload['baseurl'] ) . 'chic-centre-suites-logo-no-text.png';
}

/* ── Shared facilities list ────────────────────────────────────────────────── */

function chic_jsonld_suite_facilities(): array {
	static $list = null;
	if ( null === $list ) {
		$keys = [
			'Free Wi-Fi',
			'Fully Equipped Kitchen included Nespresso coffee machine',
			'Towels, bath robes, slippers',
			'USB-A Sockets',
			'Hangers',
			'Easy keyless secure access',
			'Elevator in building',
			'Safe box',
			'Air Conditioning',
			'Iron with iron board',
			'Hair dryer',
			'Welcome consumables upon arrival',
			'High Quality Mattress & Pillows',
			'Smoke detector & extinguisher',
			'Ambient lighting',
			'First Aid Kit',
			'Linen',
			'Cosmetics',
			'Soap, shampoo, shower gel',
		];
		$list = $keys;
	}
	return $list;
}

/* ── Schema nodes ──────────────────────────────────────────────────────────── */

function chic_jsonld_node_organization(): array {
	$logo = chic_jsonld_logo_url();
	return [
		'@type'      => 'Organization',
		'@id'        => chic_jsonld_id( '#organization' ),
		'name'       => 'P. Yiatros I.K.E',
		'legalName'  => 'P. Yiatros Monoprosopi I.K.E',
		'identifier' => [
			'@type'      => 'PropertyValue',
			'propertyID' => 'GEMI',
			'value'      => '150824901000',
		],
		'url'  => home_url( '/' ),
		'logo' => [
			'@type'      => 'ImageObject',
			'@id'        => chic_jsonld_id( '#logo' ),
			'url'        => $logo,
			'contentUrl' => $logo,
			'caption'    => 'Chic Centre Suites',
		],
	];
}

function chic_jsonld_node_website(): array {
	return [
		'@type'     => 'WebSite',
		'@id'       => chic_jsonld_id( '#website' ),
		'url'       => home_url( '/' ),
		'name'      => 'Chic Centre Suites',
		'publisher' => [ '@id' => chic_jsonld_id( '#organization' ) ],
	];
}

function chic_jsonld_node_lodging_business(): array {
	$logo      = chic_jsonld_logo_url();
	$buildings = chic_home_buildings();

	$location_refs = array_map(
		fn( $b ) => [ '@id' => chic_jsonld_id( '#place-' . sanitize_title( $b['short_label'] ) ) ],
		$buildings
	);

	$amenity_features = array_map(
		fn( $f ) => [
			'@type' => 'LocationFeatureSpecification',
			'name'  => t( $f ),
			'value' => true,
		],
		chic_jsonld_suite_facilities()
	);
	// Add 55" Smart TV separately (no t() key for it in the template)
	$amenity_features[] = [
		'@type' => 'LocationFeatureSpecification',
		'name'  => '55" Smart TV with Netflix',
		'value' => true,
	];

	return [
		'@type'              => [ 'LodgingBusiness', 'Hotel' ],
		'@id'                => chic_jsonld_id( '#hotel' ),
		'name'               => 'Chic Centre Suites',
		'url'                => home_url( '/' ),
		'image'              => $logo,
		'logo'               => [ '@id' => chic_jsonld_id( '#logo' ) ],
		'description'        => chic_jsonld_hotel_description(),
		'brand'              => [ '@id' => chic_jsonld_id( '#organization' ) ],
		'parentOrganization' => [ '@id' => chic_jsonld_id( '#organization' ) ],
		'address'            => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => '11 Thiseos',
			'addressLocality' => 'Athens',
			'postalCode'      => '10562',
			'addressCountry'  => 'GR',
		],
		'location'           => $location_refs,
		'telephone'          => '+30 6982102221',
		'email'              => 'contact@chiccentresuites.com',
		'checkinTime'        => 'T14:00',
		'checkoutTime'       => 'T11:00',
		'numberOfRooms'      => 22,
		'priceRange'         => '€€',
		'currenciesAccepted' => 'EUR',
		'paymentAccepted'    => 'Cash, Credit Card',
		'amenityFeature'     => $amenity_features,
		'potentialAction'    => [
			'@type'  => 'ReserveAction',
			'target' => [
				'@type'          => 'EntryPoint',
				'urlTemplate'    => 'https://direct-book.com/properties/chiccentresuitesathens',
				'actionPlatform' => [
					'http://schema.org/DesktopWebPlatform',
					'http://schema.org/MobileWebPlatform',
				],
			],
			'result' => [
				'@type' => 'LodgingReservation',
				'name'  => 'Reservation',
			],
		],
		'isPartOf' => [ '@id' => chic_jsonld_id( '#website' ) ],
	];
}

function chic_jsonld_node_for_building( array $building ): array {
	$id_slug = sanitize_title( $building['short_label'] );

	// Use translated label so Greek addresses appear in Greek on EL pages.
	// en: "11 Thiseos, Floor 1, 10562 Athens" → el: "Θησέως 11, 1ος Όροφος, 10562 Αθήνα"
	$parts  = array_map( 'trim', explode( ',', t( $building['label'] ) ) );
	$street = isset( $parts[1] ) ? $parts[0] . ', ' . $parts[1] : ( $parts[0] ?? '' );
	$city_raw = $parts[2] ?? '10562 Athens';
	if ( preg_match( '/^(\d+)\s+(.+)$/', trim( $city_raw ), $m ) ) {
		$postal   = $m[1];
		$locality = $m[2];
	} else {
		$postal   = '10562';
		$locality = 'Athens';
	}

	$node = [
		'@type'            => [ 'Hotel', 'LodgingBusiness' ],
		'@id'              => chic_jsonld_id( '#place-' . $id_slug ),
		'name'             => t( $building['short_label'] ),
		'hasMap'           => $building['maps'],
		'address'          => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street,
			'addressLocality' => $locality,
			'postalCode'      => $postal,
			'addressCountry'  => 'GR',
		],
		'containedInPlace' => [ '@id' => chic_jsonld_id( '#hotel' ) ],
	];

	if ( ! empty( $building['building_image'] ) ) {
		$node['image'] = $building['building_image'];
	}

	return $node;
}

function chic_jsonld_nodes_for_buildings(): array {
	return array_map( 'chic_jsonld_node_for_building', chic_home_buildings() );
}

function chic_jsonld_node_webpage(): array {
	$url      = chic_jsonld_current_url();
	$lang_tag = chic_jsonld_locale_tag();
	$name     = chic_jsonld_current_page_title();
	$desc     = chic_jsonld_current_page_description();
	$logo     = chic_jsonld_logo_url();

	$tpl = is_singular() ? (string) get_page_template_slug( (int) get_the_ID() ) : '';
	if ( is_singular( 'mphb_room_type' ) ) {
		$type = 'ItemPage';
	} elseif ( in_array( $tpl, [ 'page-explore-athens.php', 'page-testimonials.php' ], true ) ) {
		$type = 'CollectionPage';
	} else {
		$type = 'WebPage';
	}

	$img = '';
	if ( is_singular() ) {
		$img = (string) get_the_post_thumbnail_url( (int) get_the_ID(), 'full' );
	}
	if ( '' === $img ) {
		$img = $logo;
	}

	$node = [
		'@type'      => $type,
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => $name,
		'inLanguage' => $lang_tag,
		'isPartOf'   => [ '@id' => chic_jsonld_id( '#website' ) ],
		'about'      => [ '@id' => chic_jsonld_id( '#hotel' ) ],
	];

	if ( '' !== $desc ) {
		$node['description'] = $desc;
	}
	if ( '' !== $img ) {
		$node['primaryImageOfPage'] = [ '@type' => 'ImageObject', 'url' => $img ];
	}

	if ( is_singular() ) {
		$p = get_post();
		if ( $p instanceof WP_Post ) {
			$pub = get_the_date( 'c', $p );
			$mod = get_the_modified_date( 'c', $p );
			if ( $pub ) {
				$node['datePublished'] = $pub;
			}
			if ( $mod ) {
				$node['dateModified'] = $mod;
			}
		}
	}

	// Reference the BreadcrumbList node if there will be one
	if ( ! is_front_page() ) {
		$node['breadcrumb'] = [ '@id' => $url . '#breadcrumb' ];
	}

	return $node;
}

function chic_jsonld_node_breadcrumbs(): ?array {
	if ( is_front_page() ) {
		return null;
	}

	$url       = chic_jsonld_current_url();
	$home_url  = chic_localized_url( '/' );
	$home_name = t( 'Home' );
	$items     = [];
	$pos       = 1;

	$add = function ( string $name, string $item_url ) use ( &$items, &$pos ): void {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => $name,
			'item'     => $item_url,
		];
		$pos++;
	};

	$add( $home_name, $home_url );

	if ( is_singular( 'mphb_room_type' ) ) {
		$post_id  = (int) get_the_ID();
		$building = chic_suite_building( $post_id );
		if ( $building ) {
			$building_name   = t( $building['short_label'] );
			$building_anchor = chic_localized_url( '/#' . sanitize_title( $building['short_label'] ) );
			$add( $building_name, $building_anchor );
		}
		$add( chic_translate_suite_title( (string) get_the_title() ), $url );
	} elseif ( is_singular() ) {
		$page_name = wp_strip_all_tags( (string) get_the_title() );
		if ( '' !== $page_name ) {
			$add( $page_name, $url );
		}
	}

	if ( count( $items ) < 2 ) {
		return null;
	}

	return [
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => $items,
	];
}

function chic_jsonld_node_hotel_room( int $post_id ): array {
	$en_url    = chic_jsonld_en_permalink( $post_id );
	$local_url = (string) get_permalink( $post_id );
	$title     = chic_translate_suite_title( (string) get_the_title( $post_id ) );
	$data      = _chic_suite_data_for( $post_id );
	$building  = chic_suite_building( $post_id );
	$capacity  = chic_suite_capacity( $post_id );
	$bed_type  = chic_suite_bed_type( $post_id );

	$size_m2 = ( $data && isset( $data['size'] ) && '' !== $data['size'] )
		? $data['size']
		: chic_suite_size( $post_id );

	// Description paragraphs — chic_suite_description() already applies t() per paragraph
	$desc = implode( ' ', chic_suite_description( $post_id ) );

	// Images: featured image + gallery
	$images = [];
	$thumb  = (string) get_the_post_thumbnail_url( $post_id, 'full' );
	if ( '' !== $thumb ) {
		$images[] = $thumb;
	}
	foreach ( chic_suite_gallery_ids( $post_id ) as $gid ) {
		$img_url = wp_get_attachment_image_url( (int) $gid, 'full' );
		if ( $img_url ) {
			$images[] = $img_url;
		}
	}

	// Amenity features: shared facilities + per-suite highlight + sofa
	$amenity_features = array_map(
		fn( $f ) => [ '@type' => 'LocationFeatureSpecification', 'name' => t( $f ), 'value' => true ],
		chic_jsonld_suite_facilities()
	);
	$amenity_features[] = [
		'@type' => 'LocationFeatureSpecification',
		'name'  => '55" Smart TV with Netflix',
		'value' => true,
	];

	if ( $data ) {
		static $highlight_labels = [
			'jacuzzi' => 'Jacuzzi',
			'balcony' => 'Balcony',
			'terrace' => 'Terrace',
			'shower'  => 'Shower',
		];
		$hl = $data['highlight'] ?? '';
		if ( isset( $highlight_labels[ $hl ] ) ) {
			$amenity_features[] = [
				'@type' => 'LocationFeatureSpecification',
				'name'  => t( $highlight_labels[ $hl ] ),
				'value' => true,
			];
		}
		$sofa = $data['sofa'] ?? '';
		if ( '' !== $sofa && 'Jacuzzi' !== $sofa ) {
			$amenity_features[] = [
				'@type' => 'LocationFeatureSpecification',
				'name'  => t( $sofa ),
				'value' => true,
			];
		}
	}

	$contained_in = $building
		? [ '@id' => chic_jsonld_id( '#place-' . sanitize_title( $building['short_label'] ) ) ]
		: [ '@id' => chic_jsonld_id( '#hotel' ) ];

	$node = [
		'@type'            => 'HotelRoom',
		'@id'              => $en_url . '#hotelroom',
		'name'             => $title,
		'url'              => $local_url,
		'isPartOf'         => [ '@id' => chic_jsonld_id( '#hotel' ) ],
		'containedInPlace' => $contained_in,
		'amenityFeature'   => $amenity_features,
		'occupancy'        => [
			'@type'    => 'QuantitativeValue',
			'maxValue' => $capacity,
			'unitText' => 'person',
		],
		'bed' => [
			'@type'     => 'BedDetails',
			'typeOfBed' => $bed_type,
		],
	];

	if ( '' !== $desc ) {
		$node['description'] = wp_strip_all_tags( $desc );
	}
	if ( ! empty( $images ) ) {
		$node['image'] = count( $images ) === 1 ? $images[0] : $images;
	}
	if ( '' !== $size_m2 ) {
		$node['floorSize'] = [
			'@type'    => 'QuantitativeValue',
			'value'    => (int) $size_m2,
			'unitCode' => 'MTK',
		];
	}

	return $node;
}

/**
 * @param string $context  'all' for testimonials page, 'suite' for a single suite
 * @param int    $post_id  Required when $context is 'suite'
 */
function chic_jsonld_nodes_reviews( string $context, int $post_id = 0 ): array {
	$rows    = chic_testimonials_curated_rows();
	$nodes   = [];
	$base_id = chic_jsonld_id( '/reviews-what-people-say/#review-' );

	foreach ( $rows as $i => $row ) {
		$suite_match = (string) ( $row['suite_match'] ?? '' );

		if ( 'suite' === $context ) {
			if ( '' === $suite_match ) {
				continue;
			}
			$matched_id = chic_testimonials_resolve_room_id( $suite_match );
			if ( $matched_id !== $post_id ) {
				continue;
			}
		}

		$author   = (string) ( $row['author'] ?? '' );
		$country  = strtoupper( (string) ( $row['country_code'] ?? '' ) );
		$date_iso = chic_jsonld_parse_date_label( (string) ( $row['date_label'] ?? '' ) );
		$body     = (string) ( $row['review'] ?? '' );
		$source   = (string) ( $row['source_label'] ?? '' );

		// Determine itemReviewed: specific HotelRoom or the hotel entity
		if ( '' !== $suite_match && ! chic_testimonials_is_aggregate_source( $suite_match ) ) {
			$room_id = chic_testimonials_resolve_room_id( $suite_match );
			$item    = $room_id > 0
				? [ '@id' => chic_jsonld_en_permalink( $room_id ) . '#hotelroom' ]
				: [ '@id' => chic_jsonld_id( '#hotel' ) ];
		} else {
			$item = [ '@id' => chic_jsonld_id( '#hotel' ) ];
		}

		$node = [
			'@type'        => 'Review',
			'@id'          => $base_id . $i,
			'author'       => [
				'@type'   => 'Person',
				'name'    => $author,
				'address' => '' !== $country ? [ '@type' => 'PostalAddress', 'addressCountry' => $country ] : null,
			],
			'reviewBody'   => $body,
			'inLanguage'   => 'en',
			'itemReviewed' => $item,
		];

		// Clean null address if no country
		if ( null === $node['author']['address'] ) {
			unset( $node['author']['address'] );
		}

		if ( '' !== $date_iso ) {
			$node['datePublished'] = $date_iso;
		}

		// Publisher from "Review from Booking.com" style labels
		if ( '' !== $source && preg_match( '/^Review from (.+)$/i', $source, $sm ) ) {
			$node['publisher'] = [ '@type' => 'Organization', 'name' => trim( $sm[1] ) ];
		}

		$nodes[] = $node;
	}

	return $nodes;
}

function chic_jsonld_nodes_tourist_attractions(): array {
	$attractions = chic_explore_athens_attractions();
	$en_url      = chic_jsonld_en_permalink();
	$nodes       = [];

	foreach ( $attractions as $attr ) {
		$slug  = sanitize_title( $attr['title'] );
		$node  = [
			'@type'            => 'TouristAttraction',
			'@id'              => $en_url . '#attraction-' . $slug,
			'name'             => $attr['title'],
			'description'      => wp_strip_all_tags( $attr['body'] ),
			'containedInPlace' => [
				'@type'          => 'City',
				'name'           => 'Athens',
				'addressCountry' => 'GR',
			],
		];
		if ( ! empty( $attr['image'] ) ) {
			$node['image'] = $attr['image'];
		}
		$nodes[] = $node;
	}

	return $nodes;
}
