<?php
defined( 'ABSPATH' ) || exit;

/**
 * One-time Greek (_el) backfill for ACF fields seeded under v1 (t() in wp-admin).
 */
class Chic_Acf_Seeder_Backfill {

	/**
	 * Fills empty or English-duplicated _el fields using translations/el.php.
	 */
	public static function run( int $seed_version ): void {
		if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) || ! function_exists( 't_el' ) ) {
			return;
		}

		$updated = 0;
		$updated += self::backfill_option_bilingual_scalars();
		$updated += self::backfill_repeater( 'chic_ss_buildings', 'option' );
		$updated += self::backfill_repeater( 'chic_ss_facilities', 'option' );
		$updated += self::backfill_repeater( 'chic_ss_hygiene', 'option' );
		$updated += self::backfill_repeater( 'chic_ss_awards', 'option' );

		$home_id = Chic_Acf_Seeder::find_page_by_template( 'page-home.php' );
		if ( ! $home_id ) {
			$home_id = (int) get_option( 'page_on_front' );
		}
		if ( $home_id ) {
			$updated += self::backfill_page_bilingual_scalars( $home_id, 'home' );
		}

		$explore_id = Chic_Acf_Seeder::find_page_by_template( 'page-explore-athens.php' );
		if ( $explore_id ) {
			$updated += self::backfill_page_bilingual_scalars( $explore_id, 'explore' );
			$updated += self::backfill_repeater( 'chic_ea_attractions', $explore_id );
			$updated += self::backfill_repeater( 'chic_ea_more_items', $explore_id );
		}

		$tsm_id = Chic_Acf_Seeder::find_page_by_template( 'page-testimonials.php' );
		if ( $tsm_id ) {
			$updated += self::backfill_page_bilingual_scalars( $tsm_id, 'testimonials' );
			$updated += self::backfill_repeater( 'chic_tsm_reviews', $tsm_id );
		}

		if ( function_exists( 'chic_header_config_id' ) ) {
			$hdr_id = chic_header_config_id();
			if ( $hdr_id ) {
				$updated += self::backfill_bilingual_pair( 'chic_hdr_logo_alt', $hdr_id );
				$updated += self::backfill_repeater( 'chic_hdr_menu_items', $hdr_id );
			}
		}

		$updated += self::backfill_suite_posts();

		if ( $seed_version >= 3 ) {
			$updated += self::backfill_suite_features();
		}

		update_option(
			'chic_acf_seed_backfill_log',
			[
				'version'   => $seed_version,
				'updated'   => $updated,
				'timestamp' => gmdate( 'c' ),
			],
			false
		);
	}

	/** @return int */
	private static function backfill_bilingual_pair( string $base_name, $post_id ): int {
		$en_field = ( strlen( $base_name ) >= 3 && substr( $base_name, -3 ) === '_en' )
			? $base_name
			: $base_name . '_en';
		$el_field = preg_replace( '/_en$/', '_el', $en_field );
		if ( $el_field === $en_field ) {
			$el_field = $base_name . '_el';
		}

		$en = get_field( $en_field, $post_id );
		if ( $en === null || $en === '' || $en === false ) {
			return 0;
		}
		$en = (string) $en;
		$el = (string) ( get_field( $el_field, $post_id ) ?? '' );

		if ( $el !== '' && $el !== $en ) {
			return 0;
		}

		$new_el = t_el( $en );
		if ( $new_el === $en || $new_el === '' ) {
			return 0;
		}

		update_field( $el_field, $new_el, $post_id );
		return 1;
	}

	/** @return int */
	private static function backfill_repeater( string $field_name, $post_id ): int {
		$rows = get_field( $field_name, $post_id );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return 0;
		}

		$count = 0;
		$dirty = false;
		foreach ( $rows as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			foreach ( array_keys( $row ) as $key ) {
				if ( ! preg_match( '/^(.+)_el$/', $key, $m ) ) {
					continue;
				}
				$en_key = $m[1] . '_en';
				if ( ! array_key_exists( $en_key, $row ) ) {
					continue;
				}
				$en = (string) ( $row[ $en_key ] ?? '' );
				$el = (string) ( $row[ $key ] ?? '' );
				if ( $en === '' || ( $el !== '' && $el !== $en ) ) {
					continue;
				}
				$new_el = t_el( $en );
				if ( $new_el === $en || $new_el === '' ) {
					continue;
				}
				$rows[ $i ][ $key ] = $new_el;
				$dirty = true;
				++$count;
			}
		}

		if ( $dirty ) {
			update_field( $field_name, $rows, $post_id );
		}
		return $count;
	}

	/** @return int */
	private static function backfill_option_bilingual_scalars(): int {
		$pairs = [
			'chic_ss_book_now', 'chic_ss_view', 'chic_ss_photos', 'chic_ss_suite_description',
			'chic_ss_suite_facilities', 'chic_ss_prev_photo_aria', 'chic_ss_next_photo_aria',
			'chic_ss_contact_info', 'chic_ss_useful_links', 'chic_ss_awards_heading', 'chic_ss_follow_us',
			'chic_footer_privacy_label', 'chic_footer_cookie_label', 'chic_footer_terms_label',
			'chic_footer_all_rights', 'chic_footer_developed_by',
			'chic_404_aria', 'chic_404_error_label', 'chic_404_heading', 'chic_404_body', 'chic_404_return_label',
		];
		$n = 0;
		foreach ( $pairs as $base ) {
			$n += self::backfill_bilingual_pair( $base, 'option' );
		}
		return $n;
	}

	/** @return int */
	private static function backfill_page_bilingual_scalars( int $post_id, string $context ): int {
		$keys = [
			'home'         => [ 'hero_aria', 'hero_heading', 'hero_subhead', 'hero_cta_label', 'hero_scroll_aria', 'intro_heading', 'intro_body', 'carousel_prev_aria', 'carousel_next_aria' ],
			'explore'      => [ 'hero_image_alt', 'hero_heading', 'hero_subhead', 'intro_eyebrow', 'intro_heading', 'intro_body_1', 'intro_body_2', 'more_heading' ],
			'testimonials' => [ 'hero_image_alt', 'hero_heading', 'section_aria' ],
		];
		$n = 0;
		foreach ( $keys[ $context ] ?? [] as $key ) {
			$n += self::backfill_bilingual_pair( 'chic_' . $context . '_' . $key, $post_id );
		}
		return $n;
	}

	/** @return int */
	private static function backfill_suite_posts(): int {
		$posts = get_posts( [
			'post_type'      => 'mphb_room_type',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'no_found_rows'  => true,
		] );
		$n        = 0;
		$all_data = function_exists( '_chic_suite_all_data' ) ? _chic_suite_all_data() : [];

		foreach ( $posts as $post ) {
			$n += self::backfill_suite_description( $post, $all_data );
			$n += self::backfill_bilingual_pair( 'chic_suite_capacity', $post->ID );
		}
		return $n;
	}

	/** @return int */
	private static function backfill_suite_features(): int {
		if ( ! function_exists( '_chic_suite_legacy_features_from_acf' ) ) {
			require_once dirname( __DIR__ ) . '/suite-data.php';
		}

		$posts = get_posts( [
			'post_type'      => 'mphb_room_type',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'no_found_rows'  => true,
		] );

		$all_data = function_exists( '_chic_suite_all_data' ) ? _chic_suite_all_data() : [];
		$count    = 0;

		foreach ( $posts as $post ) {
			$current = get_field( 'chic_suite_features', $post->ID );
			if ( is_array( $current ) && ! empty( $current ) ) {
				continue;
			}

			$features = _chic_suite_legacy_features_from_acf( $post->ID );
			if ( empty( $features ) ) {
				$key  = strtolower( trim( $post->post_title ) );
				$data = $all_data[ $key ] ?? null;
				if ( $data ) {
					$features = _chic_suite_map_to_features( $data );
				}
			}

			if ( empty( $features ) ) {
				continue;
			}

			update_field( 'chic_suite_features', $features, $post->ID );
			++$count;
		}

		return $count;
	}

	/** @return int */
	private static function backfill_suite_description( WP_Post $post, array $all_data ): int {
		$en = (string) ( get_field( 'chic_suite_description_en', $post->ID ) ?? '' );
		$el = (string) ( get_field( 'chic_suite_description_el', $post->ID ) ?? '' );
		if ( $en === '' ) {
			return 0;
		}
		if ( $el !== '' && $el !== $en ) {
			return 0;
		}

		$key  = strtolower( trim( $post->post_title ) );
		$data = $all_data[ $key ] ?? null;
		if ( ! $data ) {
			return self::backfill_bilingual_pair( 'chic_suite_description', $post->ID );
		}

		$paras   = is_array( $data['description'] ) ? array_filter( $data['description'] ) : [ $data['description'] ];
		$html_el = '<p>' . implode( "</p>\n<p>", array_map( 't_el', $paras ) ) . '</p>';
		if ( $html_el === $en ) {
			return 0;
		}

		update_field( 'chic_suite_description_el', $html_el, $post->ID );
		return 1;
	}
}
