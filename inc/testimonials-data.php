<?php

/**
 * Testimonials page: curated reviews and mphb_room_type resolution.
 */

/**
 * @return string Lowercase single-spaced trim.
 */
function chic_testimonials_normalize_title( string $s ): string {
	return strtolower( preg_replace( '/\s+/', ' ', trim( $s ) ) );
}

/**
 * Values that must not be matched to a room post title.
 */
function chic_testimonials_is_aggregate_source( string $label ): bool {
	$n = chic_testimonials_normalize_title( $label );
	return in_array( $n, [ 'booking.com', 'facebook' ], true );
}

/**
 * Resolve a suite title to published mphb_room_type post ID (0 if unknown / aggregate).
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
 * Curated reviews (chiccentresuites.com/reviews-what-people-say/).
 *
 * @return array<int, array{author: string, suite_match: string, suite_label: string, suite_context: string, source_label: string, suite_fallback_line: string, review: string}>
 */
function chic_testimonials_curated_rows(): array {
	return [
		[
			'author'              => 'Eleutheria',
			'country_code'        => 'gr',
			'suite_match'         => 'Suite 3',
			'suite_label'         => 'Suite 3',
			'suite_context'       => 'January 2025',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Praised the modern, spotless facilities, high-quality bedding and amenities, helpful hosts, and strong sense of security. Mentioned the lively yet safe surrounding area and appreciated the hospitality.',
		],
		[
			'author'              => 'Mora Karen',
			'country_code'        => 'mx',
			'suite_match'         => 'Forest Suite',
			'suite_label'         => 'Forest Suite',
			'suite_context'       => 'October 2024',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Described the location, attention, cleanliness, and spacious room as excellent.',
		],
		[
			'author'              => 'Erica',
			'country_code'        => 'it',
			'suite_match'         => 'Korali Suite',
			'suite_label'         => 'Korali Suite',
			'suite_context'       => 'July 2021',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Loved the stylish apartment, modern technology features, excellent cleanliness, kitchen setup, and central location. Highlighted the owner\'s kindness and hospitality.',
		],
		[
			'author'              => 'Marihke',
			'country_code'        => 'nl',
			'suite_match'         => 'Forest Suite',
			'suite_label'         => 'Forest Suite',
			'suite_context'       => 'July 2021',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Mentioned the top location, tasteful furnishings, well-equipped apartment, and helpful owner.',
		],
		[
			'author'              => 'Alexander',
			'country_code'        => 'ru',
			'suite_match'         => 'Avra Suite',
			'suite_label'         => 'Avra Suite',
			'suite_context'       => 'July 2021',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Said the location was perfect for a city stay and appreciated the thoughtful details throughout the apartment.',
		],
		[
			'author'              => 'Davide',
			'country_code'        => 'it',
			'suite_match'         => 'Zakynthos Suite',
			'suite_label'         => 'Zakynthos Suite',
			'suite_context'       => 'June 2021',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Called the stay "great," praising the cleanliness, central Athens location, warm welcome, and useful local advice from the host.',
		],
		[
			'author'              => 'Em',
			'country_code'        => 'gb',
			'suite_match'         => 'Korali Suite',
			'suite_label'         => 'Korali Suite',
			'suite_context'       => 'June 2021',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Highlighted the clean and tidy room, excellent bed and bathroom, privacy, and accommodating owners who allowed late checkout.',
		],
		[
			'author'              => 'Constantinos',
			'country_code'        => 'cy',
			'suite_match'         => 'Santorini Suite',
			'suite_label'         => 'Santorini Suite',
			'suite_context'       => 'July 2020',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Mentioned the owner\'s friendliness and helpfulness, spotless condition, quiet environment despite the central location, and walking-distance access to attractions.',
		],
		[
			'author'              => 'Koula',
			'country_code'        => 'gr',
			'suite_match'         => '',
			'suite_label'         => '',
			'suite_context'       => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'review'              => 'Praised the amazing location, clean and modern rooms, comfortable bed, and proximity to shops and the city center.',
		],
		[
			'author'              => 'Aoi',
			'country_code'        => 'jp',
			'suite_match'         => '',
			'suite_label'         => '',
			'suite_context'       => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'review'              => 'Appreciated the excellent amenities, cleanliness, comfort, many electrical outlets, and the host\'s helpfulness during the stay.',
		],
		[
			'author'              => 'Stephanie',
			'country_code'        => 'fr',
			'suite_match'         => '',
			'suite_label'         => '',
			'suite_context'       => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'review'              => 'Highlighted the central location, attentive reception, and responsive service.',
		],
		[
			'author'              => 'Anonymous',
			'country_code'        => 'gb',
			'suite_match'         => '',
			'suite_label'         => '',
			'suite_context'       => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'review'              => 'Enjoyed the fully equipped apartment, private balcony area, espresso machine, jacuzzi, and overall modern condition of the property.',
		],
		[
			'author'              => 'Andros',
			'country_code'        => 'cy',
			'suite_match'         => 'Santorini Suite',
			'suite_label'         => 'Santorini Suite',
			'suite_context'       => 'December 2019',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Said it was the best apartment they had stayed in Athens, noting the luxury feel, comfort, strong Wi-Fi, cleanliness, and friendly staff.',
		],
		[
			'author'              => 'Guest',
			'suite_match'         => '',
			'suite_label'         => '',
			'suite_context'       => '',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'review'              => 'Strongly recommended the suites for luxury stays, business trips, couples, or families, mentioning the central location and hotel-style facilities at reasonable prices.',
		],
	];
}

/**
 * Curated rows with permalink and suite_linked (text-only cards; no images).
 *
 * @return array<int, array<string, mixed>>
 */
function chic_testimonials_enriched_rows(): array {
	$out = [];
	foreach ( chic_testimonials_curated_rows() as $r ) {
		$match     = (string) ( $r['suite_match'] ?? '' );
		$id        = $match !== '' ? chic_testimonials_resolve_room_id( $match ) : 0;
		$permalink = $id > 0 ? (string) ( get_permalink( $id ) ?: '' ) : '';
		$out[] = array_merge( $r, [
			'room_id'      => $id,
			'permalink'    => $permalink,
			'suite_linked' => $id > 0 && '' !== $permalink && '' !== ( $r['suite_label'] ?? '' ),
		] );
	}
	return $out;
}
