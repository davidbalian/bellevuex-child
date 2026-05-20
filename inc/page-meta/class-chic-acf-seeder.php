<?php
defined( 'ABSPATH' ) || exit;

/**
 * One-time seeder that pre-populates all ACF fields from the current
 * hardcoded PHP defaults so the client sees the live content on day 1.
 *
 * Version gate: stored in wp_options as 'chic_acf_seed_version'.
 * Bump SEED_VERSION to trigger a re-seed of any NEWLY added fields
 * (never overwrites a key that already holds a value).
 */
class Chic_Acf_Seeder {

	const SEED_VERSION = 2;

	public static function maybe_run(): void {
		if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) return;
		$current = (int) get_option( 'chic_acf_seed_version', 0 );
		if ( $current >= self::SEED_VERSION ) {
			return;
		}
		if ( $current < 1 ) {
			self::run();
		}
		if ( $current < self::SEED_VERSION ) {
			require_once __DIR__ . '/class-chic-acf-seeder-backfill.php';
			Chic_Acf_Seeder_Backfill::run( self::SEED_VERSION );
		}
		update_option( 'chic_acf_seed_version', self::SEED_VERSION, false );
	}

	// ── Orchestrator ─────────────────────────────────────────────────────────

	private static function run(): void {
		self::seed_site_settings();
		self::seed_home();
		self::seed_explore_athens();
		self::seed_testimonials();
		self::seed_suites();
		self::seed_header();
	}

	// ── Low-level helpers ─────────────────────────────────────────────────────

	/** Seed a single ACF field only if it has no existing value. */
	private static function seed_field( string $field_name, $value, $post_id ): void {
		if ( $value === null || $value === '' || $value === false ) return;
		$current = get_field( $field_name, $post_id );
		if ( ! empty( $current ) ) return;
		update_field( $field_name, $value, $post_id );
	}

	/**
	 * Seed paired _en / _el text fields.
	 * EL uses t_el() so Greek is written in wp-admin (t() would stay English there).
	 */
	private static function seed_bilingual( string $base_name, string $en, $post_id, string $el = '' ): void {
		if ( '' === $el && function_exists( 't_el' ) ) {
			$translated = t_el( $en );
			$el         = ( $translated !== $en ) ? $translated : '';
		}
		self::seed_field( $base_name . '_en', $en, $post_id );
		if ( '' !== $el ) {
			self::seed_field( $base_name . '_el', $el, $post_id );
		}
	}

	/** Seed an ACF Image field (stores attachment ID). */
	private static function seed_image( string $field_name, string $url, $post_id ): void {
		$current = get_field( $field_name, $post_id );
		if ( ! empty( $current ) ) return;
		if ( empty( $url ) ) return;
		$id = self::resolve_image_id( $url );
		if ( $id > 0 ) {
			update_field( $field_name, $id, $post_id );
		} else {
			self::log_missing_image( $url );
		}
	}

	/** Seed a Repeater only if it has no rows yet. */
	private static function seed_repeater( string $field_name, array $rows, $post_id ): void {
		if ( empty( $rows ) ) return;
		$current = get_field( $field_name, $post_id );
		if ( ! empty( $current ) ) return;
		update_field( $field_name, $rows, $post_id );
	}

	/** Resolve a URL to an attachment ID via WP helper + fallback filename query. */
	private static function resolve_image_id( string $url ): int {
		$id = (int) attachment_url_to_postid( $url );
		if ( $id > 0 ) return $id;

		global $wpdb;
		$filename = basename( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		if ( '' === $filename ) return 0;

		$id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			 AND guid LIKE %s
			 LIMIT 1",
			'%/' . $wpdb->esc_like( $filename )
		) );
		return $id;
	}

	private static function log_missing_image( string $url ): void {
		$missing = (array) get_option( 'chic_acf_seed_missing_images', [] );
		if ( ! in_array( $url, $missing, true ) ) {
			$missing[] = $url;
			update_option( 'chic_acf_seed_missing_images', $missing, false );
		}
	}

	/** Return the ID of the first page using a given page template. */
	public static function find_page_by_template( string $tpl ): int {
		$posts = get_posts( [
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $tpl,
		] );
		return ! empty( $posts ) ? (int) $posts[0]->ID : 0;
	}

	// ── Site Settings (options page) ──────────────────────────────────────────

	private static function seed_site_settings(): void {
		$pid = 'option';

		// Common labels
		self::seed_field(    'chic_ss_booking_url',            'https://direct-book.com/properties/chiccentresuitesathens', $pid );
		self::seed_bilingual( 'chic_ss_book_now',               'Book Now',              $pid );
		self::seed_bilingual( 'chic_ss_view',                   'View',                  $pid );
		self::seed_bilingual( 'chic_ss_photos',                 'Photos',                $pid );
		self::seed_bilingual( 'chic_ss_suite_description',      'Suite Description',     $pid );
		self::seed_bilingual( 'chic_ss_suite_facilities',       'Suite Facilities',      $pid );
		self::seed_bilingual( 'chic_ss_prev_photo_aria',        'Previous photo',        $pid );
		self::seed_bilingual( 'chic_ss_next_photo_aria',        'Next photo',            $pid );
		self::seed_bilingual( 'chic_ss_contact_info',           'Contact Info',          $pid );
		self::seed_bilingual( 'chic_ss_useful_links',           'Useful Links',          $pid );
		self::seed_bilingual( 'chic_ss_awards_heading',         'Awards',                $pid );
		self::seed_bilingual( 'chic_ss_follow_us',              'Follow Us',             $pid );

		self::seed_buildings( $pid );
		self::seed_facilities( $pid );
		self::seed_hygiene( $pid );
		self::seed_footer( $pid );
		self::seed_awards( $pid );
		self::seed_social( $pid );
		self::seed_404( $pid );
	}

	private static function seed_buildings( $pid ): void {
		$rows = [];
		foreach ( chic_home_buildings() as $b ) {
			$img_id = self::resolve_image_id( $b['building_image'] );
			if ( $img_id <= 0 && ! empty( $b['building_image'] ) ) {
				self::log_missing_image( $b['building_image'] );
			}
			$rows[] = [
				'term_slug'      => $b['term'],
				'short_label_en' => $b['short_label'],
				'short_label_el' => (string) t_el( $b['short_label'] ),
				'address_en'     => $b['label'],
				'address_el'     => (string) t_el( $b['label'] ),
				'maps_url'       => $b['maps'],
				'building_image' => $img_id > 0 ? $img_id : 0,
			];
		}
		self::seed_repeater( 'chic_ss_buildings', $rows, $pid );
	}

	private static function seed_facilities( $pid ): void {
		$items = [
			'Free Wi-Fi',
			'Fully Equipped Kitchen included Nespresso coffee machine',
			'Towels, bath robes, slippers',
			'USB-A Sockets',
			'Hangers',
			'Easy keyless secure access',
			'Elevator in building',
			'Safe box',
			'55″ Smart TV with Netflix',
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
		$rows = array_map( static fn( $f ) => [
			'label_en' => $f,
			'label_el' => (string) t_el( $f ),
		], $items );
		self::seed_repeater( 'chic_ss_facilities', $rows, $pid );
	}

	private static function seed_hygiene( $pid ): void {
		$notes = [
			[ 'Hygiene:',              'Fully cleaned and disinfected suite upon your arrival.' ],
			[ 'Room Cleaning Service:', 'Towels will be changed every 4 days.' ],
			[ '',                       'However, we also offer the following options for your convenience:' ],
			[ 'Room Cleaning:',         'Available upon request for an additional cost of €30. This service includes general cleaning of the room, fresh bed sheets, and towels.' ],
			[ 'Extra Towels:',          'Guests may request an additional set of fresh bath towels for an extra cost of €5 per set.' ],
		];
		$rows = array_map( static fn( $n ) => [
			'heading_en' => $n[0],
			'heading_el' => (string) t_el( $n[0] ),
			'body_en'    => $n[1],
			'body_el'    => (string) t_el( $n[1] ),
		], $notes );
		self::seed_repeater( 'chic_ss_hygiene', $rows, $pid );
	}

	private static function seed_footer( $pid ): void {
		self::seed_field( 'chic_footer_brand_name',  'P. Yiatros I.K.E',             $pid );
		self::seed_field( 'chic_footer_email',       'contact@chiccentresuites.com', $pid );
		self::seed_field( 'chic_footer_phone_1',     '+357 99674630',                $pid );
		self::seed_field( 'chic_footer_phone_1_href','tel:+35799674630',             $pid );
		self::seed_field( 'chic_footer_phone_2',     '+30 6982102221',               $pid );
		self::seed_field( 'chic_footer_phone_2_href','tel:+306982102221',            $pid );
		self::seed_field( 'chic_footer_address_1',   '11 Thiseos, 10562 Athens, Greece', $pid );
		self::seed_field( 'chic_footer_address_2',   '13 Thiseos, 10562 Athens, Greece', $pid );
		self::seed_field( 'chic_footer_address_3',   '2 Chavriou, 10562 Athens, Greece', $pid );
		self::seed_field( 'chic_footer_gemi',        '150824901000',                 $pid );

		self::seed_bilingual( 'chic_footer_privacy_label', 'Privacy Policy',    $pid );
		self::seed_bilingual( 'chic_footer_cookie_label',  'Cookie Policy',     $pid );
		self::seed_bilingual( 'chic_footer_terms_label',   'Terms & Conditions',$pid );
		self::seed_bilingual( 'chic_footer_all_rights',    'All rights reserved.', $pid );
		self::seed_bilingual( 'chic_footer_developed_by',  'Developed by',      $pid );
	}

	private static function seed_awards( $pid ): void {
		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['error'] ) ) return;
		$base = trailingslashit( $uploads['baseurl'] );
		$items = [
			[ 'booking-award-2026.avif',  'Booking.com Traveller Review Awards 2026 — 8.9 out of 10' ],
			[ 'kayak-award.avif',         'KAYAK Travel Awards Winner' ],
			[ 'hoterl-social-award.avif', 'Hotel.social Certificate of Excellence 2026 — 9.0 Review Score' ],
		];
		$rows = [];
		foreach ( $items as [ $file, $alt ] ) {
			$url    = $base . $file;
			$img_id = self::resolve_image_id( $url );
			if ( $img_id <= 0 ) self::log_missing_image( $url );
			$rows[] = [
				'image'  => $img_id > 0 ? $img_id : 0,
				'alt_en' => $alt,
				'alt_el' => (string) t_el( $alt ),
			];
		}
		self::seed_repeater( 'chic_ss_awards', $rows, $pid );
	}

	private static function seed_social( $pid ): void {
		$rows = [
			[ 'platform' => 'instagram', 'url' => '', 'aria_label_en' => 'Instagram', 'aria_label_el' => 'Instagram' ],
			[ 'platform' => 'facebook',  'url' => '', 'aria_label_en' => 'Facebook',  'aria_label_el' => 'Facebook'  ],
		];
		self::seed_repeater( 'chic_ss_social', $rows, $pid );
	}

	private static function seed_404( $pid ): void {
		self::seed_bilingual( 'chic_404_aria',         '404 error',                                                          $pid );
		self::seed_bilingual( 'chic_404_error_label',  '404 Error',                                                          $pid );
		self::seed_bilingual( 'chic_404_heading',      'Page Not Found',                                                      $pid );
		self::seed_bilingual( 'chic_404_body',         "The page you're looking for doesn't exist or may have been moved.",   $pid );
		self::seed_bilingual( 'chic_404_return_label', 'Return to Homepage',                                                  $pid );
	}

	// ── Home Page ─────────────────────────────────────────────────────────────

	private static function seed_home(): void {
		$pid = self::find_page_by_template( 'page-home.php' );
		if ( ! $pid ) $pid = (int) get_option( 'page_on_front' );
		if ( ! $pid ) return;

		self::seed_bilingual( 'chic_home_hero_aria',          'Chic Centre Suites hero photos', $pid );
		self::seed_bilingual( 'chic_home_hero_heading',       'Stay in the Heart<br> of Athens', $pid );
		self::seed_bilingual( 'chic_home_hero_subhead',       'Our suites, spread across three buildings on the same road, steps from Syntagma Square, Monastiraki, and the Ermou pedestrian street.', $pid );
		self::seed_bilingual( 'chic_home_hero_cta_label',     'Book Now', $pid );
		self::seed_field(     'chic_home_hero_cta_url',       'https://direct-book.com/properties/chiccentresuitesathens', $pid );
		self::seed_bilingual( 'chic_home_hero_scroll_aria',   'Scroll to next section', $pid );
		self::seed_bilingual( 'chic_home_intro_heading',      'Welcome to Athens', $pid );
		self::seed_bilingual( 'chic_home_intro_body',         'Our fully equipped apartments are located in three buildings on the same road in the heart of Athens, just a 5-minute walk from Syntagma Square and Monastiraki, and only steps away from the iconic pedestrian street of Ermou, where you can explore the city\'s treasures, experience its culture and history, and, of course, enjoy shopping.', $pid );
		self::seed_bilingual( 'chic_home_carousel_prev_aria', 'Previous suites', $pid );
		self::seed_bilingual( 'chic_home_carousel_next_aria', 'Next suites', $pid );
	}

	// ── Explore Athens ────────────────────────────────────────────────────────

	private static function seed_explore_athens(): void {
		require_once dirname( __DIR__, 2 ) . '/inc/explore-athens-data.php';

		$pid = self::find_page_by_template( 'page-explore-athens.php' );
		if ( ! $pid ) return;

		self::seed_image(     'chic_explore_hero_image',     'https://davidb1553.sg-host.com/wp-content/uploads/7-chic-centre-suites-athens-nearby-attractions-acropolis.webp', $pid );
		self::seed_bilingual( 'chic_explore_hero_image_alt', 'The Acropolis of Athens at sunset, overlooking the city', $pid );
		self::seed_bilingual( 'chic_explore_hero_heading',   'EXPLORE AND<br>EXPERIENCE ATHENS', $pid );
		self::seed_bilingual( 'chic_explore_hero_subhead',   "Discover the timeless beauty of Greece's capital from the heart of the city", $pid );
		self::seed_bilingual( 'chic_explore_intro_eyebrow',  'Explore Athens / Athina', $pid );
		self::seed_bilingual( 'chic_explore_intro_heading',  'A City Steeped in History, Culture, and Ancient Landmarks', $pid );
		self::seed_bilingual( 'chic_explore_intro_body_1',   'Staying at Athens means immersing yourself in a destination where ancient history meets vibrant modern life. From iconic landmarks to hidden local gems, Athens offers an unforgettable experience for every traveler, and at Chic Centre Suites Athens, you are perfectly positioned to explore it all.', $pid );
		self::seed_bilingual( 'chic_explore_intro_body_2',   'Located just steps from Ermou Street and within a short walk of Syntagma Square and Monastiraki, your stay places you at the very center of culture, history, shopping, and gastronomy.', $pid );
		self::seed_bilingual( 'chic_explore_more_heading',   'Also Worth Exploring', $pid );

		// Attractions repeater
		$att_rows = [];
		foreach ( chic_explore_athens_attractions() as $att ) {
			$img_id = self::resolve_image_id( $att['image'] );
			if ( $img_id <= 0 ) self::log_missing_image( $att['image'] );
			$att_rows[] = [
				'title_en' => $att['title'],
				'title_el' => (string) t_el( $att['title'] ),
				'body_en'  => $att['body'],
				'body_el'  => (string) t_el( $att['body'] ),
				'image'    => $img_id > 0 ? $img_id : 0,
				'alt_en'   => $att['alt'],
				'alt_el'   => (string) t_el( $att['alt'] ),
			];
		}
		self::seed_repeater( 'chic_ea_attractions', $att_rows, $pid );

		// "Also worth exploring" repeater
		$more_rows = [];
		foreach ( chic_explore_athens_more() as $item ) {
			$more_rows[] = [
				'title_en' => $item['title'],
				'title_el' => (string) t_el( $item['title'] ),
				'body_en'  => $item['body'],
				'body_el'  => (string) t_el( $item['body'] ),
			];
		}
		self::seed_repeater( 'chic_ea_more_items', $more_rows, $pid );
	}

	// ── Testimonials ──────────────────────────────────────────────────────────

	private static function seed_testimonials(): void {
		require_once dirname( __DIR__, 2 ) . '/inc/testimonials-data.php';

		$pid = self::find_page_by_template( 'page-testimonials.php' );
		if ( ! $pid ) return;

		self::seed_image(     'chic_tsm_hero_image',     'https://davidb1553.sg-host.com/wp-content/uploads/11-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp', $pid );
		self::seed_bilingual( 'chic_tsm_hero_image_alt', 'Syntagma Square with the Hellenic Parliament building and fountain', $pid );
		self::seed_bilingual( 'chic_tsm_hero_heading',   'Latest Reviews', $pid );
		self::seed_bilingual( 'chic_tsm_section_aria',   'Guest reviews',  $pid );

		$rows = [];
		foreach ( chic_testimonials_curated_rows() as $r ) {
			$match   = (string) ( $r['suite_match'] ?? '' );
			$room_id = $match !== '' ? chic_testimonials_resolve_room_id( $match ) : 0;
			$rows[]  = [
				'author'          => $r['author'],
				'country_code'    => $r['country_code'],
				'suite'           => $room_id > 0 ? $room_id : 0,
				'suite_label_en'  => $r['suite_label'],
				'suite_label_el'  => (string) t_el( $r['suite_label'] ),
				'source_label_en' => $r['source_label'],
				'source_label_el' => (string) t_el( $r['source_label'] ),
				'date_label_en'   => $r['date_label'],
				'date_label_el'   => (string) t_el( $r['date_label'] ),
				'review_en'       => $r['review'],
				'review_el'       => (string) t_el( $r['review'] ),
			];
		}
		self::seed_repeater( 'chic_tsm_reviews', $rows, $pid );
	}

	// ── Suites ────────────────────────────────────────────────────────────────

	private static function seed_suites(): void {
		$all_data = _chic_suite_all_data();

		$posts = get_posts( [
			'post_type'      => 'mphb_room_type',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		] );

		foreach ( $posts as $post ) {
			$key  = strtolower( trim( $post->post_title ) );
			$data = $all_data[ $key ] ?? null;
			if ( ! $data ) continue;
			$pid  = $post->ID;

			$paras    = is_array( $data['description'] ) ? array_filter( $data['description'] ) : [ $data['description'] ];
			$html_en  = '<p>' . implode( "</p>\n<p>", $paras ) . '</p>';
			$html_el  = '<p>' . implode( "</p>\n<p>", array_map( 't_el', $paras ) ) . '</p>';

			self::seed_field( 'chic_suite_description_en', $html_en, $pid );
			self::seed_field( 'chic_suite_description_el', $html_el, $pid );

			self::seed_bilingual( 'chic_suite_capacity', $data['capacity'], $pid );
			self::seed_bilingual( 'chic_suite_sofa',     $data['sofa'],     $pid );
			self::seed_field(     'chic_suite_highlight', $data['highlight'], $pid );
			self::seed_field(     'chic_suite_size',      (int) $data['size'], $pid );
		}
	}

	// ── Header ────────────────────────────────────────────────────────────────

	private static function seed_header(): void {
		if ( ! function_exists( 'chic_header_config_id' ) ) return;
		$pid = chic_header_config_id();
		if ( ! $pid ) return;

		self::seed_image(     'chic_hdr_logo',     'https://davidb1553.sg-host.com/wp-content/uploads/chic-centre-suites-logo-no-text.png', $pid );
		self::seed_bilingual( 'chic_hdr_logo_alt', 'Chic Centre Suites', $pid );

		$items = [];
		foreach ( chic_header_default_menu() as $item ) {
			$type      = $item['link_type'] ?? 'placeholder';
			$label_key = strtolower( trim( $item['label'] ?? '' ) );
			$url       = '';

			if ( 'url' === $type ) {
				switch ( $label_key ) {
					case 'home':           $url = home_url( '/' ); break;
					case 'explore athens': $url = home_url( '/explore-and-experience-athens/' ); break;
					case 'reviews':        $url = home_url( '/reviews-what-people-say/' ); break;
					case 'book now':       $url = 'https://direct-book.com/properties/chiccentresuitesathens'; break;
					default:               $url = $item['url'] ?? ''; break;
				}
			}

			$items[] = [
				'label_en'  => $item['label'],
				'label_el'  => (string) t_el( $item['label'] ),
				'link_type' => $type,
				'url'       => $url,
				'page_id'   => 0,
				'is_mega'   => ! empty( $item['is_mega'] ),
			];
		}
		self::seed_repeater( 'chic_hdr_menu_items', $items, $pid );
	}
}
