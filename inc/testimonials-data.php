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
 * Flag asset in uploads root: …/uploads/flag-{code}.png (e.g. flag-cy.png).
 */
function chic_testimonials_flag_url( string $country_code ): string {
	$code = preg_replace( '/[^a-z]/', '', strtolower( $country_code ) );
	if ( '' === $code ) {
		return '';
	}
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) || empty( $upload['baseurl'] ) ) {
		return '';
	}
	return trailingslashit( $upload['baseurl'] ) . 'flag-' . $code . '.png';
}

/**
 * Curated reviews (chiccentresuites.com/reviews-what-people-say/).
 *
 * @return array<int, array{author: string, country_code: string, suite_match: string, suite_label: string, source_label: string, suite_fallback_line: string, date_label: string, review: string}>
 */
function chic_testimonials_curated_rows(): array {
	return [
		[
			'author'              => 'Eleutheria',
			'country_code'        => 'gr',
			'suite_match'         => 'Suite 3',
			'suite_label'         => 'Suite 3',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'January 2025',
			'review'              => 'The facilities are modern, impeccably clean, and of exceptional quality. From the bathrobes and towels to the sheets, pillows, and bed, every detail is carefully curated to ensure maximum comfort, making the stay even more restful. Our hosts were helpful and willing to assist us with anything we needed. It is truly rare to find a place that is so well-maintained and complemented by such outstanding hospitality. Additionally, the location is very secure, with three security codes required for access to the room. On the ground floor, there is a bistro, and the street outside has regular traffic, so there are always people around, which adds to the sense of safety.',
		],
		[
			'author'              => 'Mora Karen',
			'country_code'        => 'mx',
			'suite_match'         => 'Forest Suite',
			'suite_label'         => 'Forest Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'October 2024',
			'review'              => 'Excellent location and attention and clean large room everything was excellent',
		],
		[
			'author'              => 'Erica',
			'country_code'        => 'it',
			'suite_match'         => 'Korali Suite',
			'suite_label'         => 'Korali Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'July 2021',
			'review'              => 'The apartment is very beautiful, furnished with great style and care, equipped with every comfort and with every kind of technology (for example: digital code to access the room, maxi TV, usb refills on the wall...). Cleaning is excellent, new dishes are present in the kitchen; also provided Nespresso coffee machine and breakfast supplies. It is located in a central area, with all kinds of services (bars, restaurants, shops). No need to take any transportation to visit the main historical sites. The owner is a welcoming and very kind person... he accompanied us for a small tour around the property. Such hospitality is really rare. We would recommend everyone to stay at one of these apartments.',
		],
		[
			'author'              => 'Marihke',
			'country_code'        => 'nl',
			'suite_match'         => 'Forest Suite',
			'suite_label'         => 'Forest Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'July 2021',
			'review'              => 'Top location, the nicely furnished apartment is very well equipped, very helpful owner.',
		],
		[
			'author'              => 'Alexander',
			'country_code'        => 'ru',
			'suite_match'         => 'Avra Suite',
			'suite_label'         => 'Avra Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'July 2021',
			'review'              => 'Great location for a short stay in the city. Everything is thought out to the smallest detail. Highest mark!',
		],
		[
			'author'              => 'Davide',
			'country_code'        => 'it',
			'suite_match'         => 'Zakynthos Suite',
			'suite_label'         => 'Zakynthos Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'June 2021',
			'review'              => 'Just one word: GREAT!!! Clean facility and in the center of Athens nothing is missing. Jorgos number one, welcomed us warmly, gave us lots of advice of all kinds.. making us do a quick and short city tour.',
		],
		[
			'author'              => 'Em',
			'country_code'        => 'gb',
			'suite_match'         => 'Korali Suite',
			'suite_label'         => 'Korali Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'June 2021',
			'review'              => 'The room itself was very clean and tidy, great bed and bathroom! You also get a lot of privacy. Owners very accommodating, extended our stay for some hours after our request for late check out.',
		],
		[
			'author'              => 'Constantinos',
			'country_code'        => 'cy',
			'suite_match'         => 'Santorini Suite',
			'suite_label'         => 'Santorini Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'July 2020',
			'review'              => 'For starters let me just say that the owner was super friendly and helpful. Unbelievable clean, everything was new and spotless. Even though the apartment is located Centre of all attractions where it\'s super crowded, we couldn\'t hear anything. Located-as mentioned- in the center of everything, in a walking distance. We loved Chic Centre Suites and we have already bookmarked it on our favorites.',
		],
		[
			'author'              => 'Koula',
			'country_code'        => 'gr',
			'suite_match'         => '',
			'suite_label'         => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'date_label'          => '',
			'review'              => 'The Location was amazing in the heart of center and shops. rooms were very clean and new. Comfortable room excellent bed.',
		],
		[
			'author'              => 'Aoi',
			'country_code'        => 'jp',
			'suite_match'         => '',
			'suite_label'         => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'date_label'          => '',
			'review'              => 'Amenity is very good ・There are many outlets ・Comfortable ・Rooms are very clean ・Close to the center ・Equipped with excellent facilities It was a stay with people, but he helped me in various ways. If I have a chance to visit Athens again, I would definitely like to use it again.',
		],
		[
			'author'              => 'Stephanie',
			'country_code'        => 'fr',
			'suite_match'         => '',
			'suite_label'         => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'date_label'          => '',
			'review'              => 'Quality of services and very central location that allows you to enjoy Athens. The reception is very attentive and listens to our requests.',
		],
		[
			'author'              => 'Anonymous',
			'country_code'        => 'gb',
			'suite_match'         => '',
			'suite_label'         => '',
			'source_label'        => 'Review from Booking.com',
			'suite_fallback_line' => '',
			'date_label'          => '',
			'review'              => 'Excellent location, apartment fully equipped and everything brand new.Pleasant surprise the private balcony area in which smokers can use.Espresso machine and coffee tablets available as well as jacuzzi.Strongly recommended.',
		],
		[
			'author'              => 'Andros',
			'country_code'        => 'cy',
			'suite_match'         => 'Santorini Suite',
			'suite_label'         => 'Santorini Suite',
			'source_label'        => '',
			'suite_fallback_line' => '',
			'date_label'          => 'December 2019',
			'review'              => 'Came to Athens many times, stayed in several places. This apartment were the best place so far. Just 1min walking distance from Ermou. The apartment is such a luxury with good facilities. The room was very clean and the Wifi signal was good too. Mattress was unbelievable ! So comfortable . Staff was very friendly . Value for money ! Looking forward to visit your apartment in March too!!!',
		],
		[
			'author'              => 'Christiana',
			'country_code'        => 'gr',
			'suite_match'         => '',
			'suite_label'         => '',
			'source_label'        => 'Review from Facebook',
			'suite_fallback_line' => '',
			'date_label'          => '',
			'review'              => 'Strongly recommended for a luxury and enjoyable stay in Athens. Perfect location in the city centre and fully equipped to accommodate family or couples. Ideal for business trips as well. Wise choice as you enjoy the facilities of a 5 star hotel with a very reasonable and attractive price. Looking forward to repeat our trip to Athens soon and stay at Chic Centre Suites again.',
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
