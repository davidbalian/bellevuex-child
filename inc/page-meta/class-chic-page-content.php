<?php
defined( 'ABSPATH' ) || exit;

/**
 * ACF content resolver.
 *
 * All templates use these static helpers instead of bare te() / t() calls.
 * Resolution order:  ACF saved value  →  schema default  →  t() for Greek.
 *
 * Field naming convention
 * ──────────────────────
 * Bilingual text  :  chic_{ctx}_{key}_en   /   chic_{ctx}_{key}_el
 * Image           :  chic_{ctx}_{key}               (ACF Image field)
 * Image alt EN/EL :  chic_{ctx}_{key}_alt_en  /  chic_{ctx}_{key}_alt_el
 * Single URL      :  chic_{ctx}_{key}               (ACF URL or Text field)
 * Options page    :  same names, $post_id = 'option'
 */
class Chic_Page_Content {

	// ── Text ──────────────────────────────────────────────────────────────────

	/**
	 * Returns frontend text in the current language.
	 * Falls back: ACF → schema default → t() for Greek.
	 */
	public static function get_text( $post_id, string $context, string $key ): string {
		$lang = chic_get_current_lang();

		if ( function_exists( 'get_field' ) ) {
			$val = get_field( 'chic_' . $context . '_' . $key . '_' . $lang, $post_id );
			if ( $val !== null && $val !== '' && $val !== false ) {
				return (string) $val;
			}
		}

		$default = Chic_Page_Meta_Schema::get_default( $context, $key );
		if ( 'el' === $lang ) {
			return t( $default );
		}
		return $default;
	}

	/** Uppercase variant: strips Greek tonos when lang === 'el'. */
	public static function get_text_uc( $post_id, string $context, string $key ): string {
		$text = self::get_text( $post_id, $context, $key );
		if ( 'el' === chic_get_current_lang() ) {
			return chic_el_strip_monotonic_tonos( $text );
		}
		return $text;
	}

	/** Returns raw HTML (caller escapes). Suitable for fields containing &lt;br&gt; etc. */
	public static function get_text_html( $post_id, string $context, string $key ): string {
		return self::get_text( $post_id, $context, $key );
	}

	/** Uppercase raw HTML. */
	public static function get_text_uc_html( $post_id, string $context, string $key ): string {
		$text = self::get_text_html( $post_id, $context, $key );
		if ( 'el' === chic_get_current_lang() ) {
			return chic_el_strip_monotonic_tonos( $text );
		}
		return $text;
	}

	/** Attribute-escaped text (for use inside HTML attributes). */
	public static function get_attr( $post_id, string $context, string $key ): string {
		return esc_attr( self::get_text( $post_id, $context, $key ) );
	}

	/** Attribute-escaped uppercase text. */
	public static function get_attr_uc( $post_id, string $context, string $key ): string {
		return esc_attr( self::get_text_uc( $post_id, $context, $key ) );
	}

	// ── Image ─────────────────────────────────────────────────────────────────

	/**
	 * Returns attachment ID for an image field, or 0 if not set.
	 */
	public static function get_image_id( $post_id, string $context, string $key ): int {
		if ( function_exists( 'get_field' ) ) {
			$img = get_field( 'chic_' . $context . '_' . $key, $post_id );
			if ( ! empty( $img ) ) {
				if ( is_array( $img ) && isset( $img['ID'] ) ) {
					return (int) $img['ID'];
				}
				if ( is_numeric( $img ) ) {
					return (int) $img;
				}
			}
		}
		return 0;
	}

	/**
	 * Returns image URL at the requested size.
	 * Falls back to schema default_url if ACF returns nothing.
	 */
	public static function get_image_url( $post_id, string $context, string $key, string $size = 'full' ): string {
		if ( function_exists( 'get_field' ) ) {
			$img = get_field( 'chic_' . $context . '_' . $key, $post_id );
			if ( ! empty( $img ) ) {
				$url = self::acf_image_url( $img, $size );
				if ( $url !== '' ) return $url;
			}
		}
		return Chic_Page_Meta_Schema::get_default_url( $context, $key );
	}

	/**
	 * Returns the localized alt text for an image field.
	 * Companion fields: chic_{ctx}_{key}_alt_en / _el.
	 * Falls back to schema alt_default → t() for Greek.
	 */
	public static function get_image_alt( $post_id, string $context, string $key ): string {
		$lang = chic_get_current_lang();

		if ( function_exists( 'get_field' ) ) {
			$alt = get_field( 'chic_' . $context . '_' . $key . '_alt_' . $lang, $post_id );
			if ( $alt !== null && $alt !== '' ) {
				return (string) $alt;
			}
		}

		$default_alt = Chic_Page_Meta_Schema::get_alt_default( $context, $key );
		if ( 'el' === $lang ) {
			return t( $default_alt );
		}
		return $default_alt;
	}

	// ── URL (single-language) ─────────────────────────────────────────────────

	/**
	 * Returns a URL field value (single-language: no _en/_el suffix).
	 */
	public static function get_url( $post_id, string $context, string $key ): string {
		if ( function_exists( 'get_field' ) ) {
			$val = get_field( 'chic_' . $context . '_' . $key, $post_id );
			if ( $val !== null && $val !== '' ) {
				return esc_url_raw( (string) $val );
			}
		}
		return Chic_Page_Meta_Schema::get_default( $context, $key );
	}

	// ── Repeater ─────────────────────────────────────────────────────────────

	/**
	 * Returns an ACF repeater as a normalized array of rows.
	 *
	 * Each row's bilingual sub-fields ({name}_en / {name}_el) are collapsed to
	 * just {name} in the current language.  Image sub-fields (arrays with 'url')
	 * are collapsed to a plain URL string.
	 *
	 * @param mixed $post_id  Post ID, or 'option' for options pages.
	 * @param string $field_name  Full ACF field name (e.g. 'chic_ss_buildings').
	 * @return array[]
	 */
	public static function get_repeater( $post_id, string $field_name ): array {
		if ( ! function_exists( 'get_field' ) ) {
			return [];
		}
		$rows = get_field( $field_name, $post_id );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return [];
		}
		$lang = chic_get_current_lang();
		$out  = [];
		foreach ( $rows as $raw ) {
			if ( ! is_array( $raw ) ) continue;
			$out[] = self::normalize_row( $raw, $lang );
		}
		return $out;
	}

	/**
	 * Collapse a repeater row: bilingual keys → single key, image arrays → URL.
	 *
	 * @internal
	 */
	public static function normalize_row( array $raw, string $lang ): array {
		$row = [];
		foreach ( $raw as $k => $v ) {
			if ( preg_match( '/^(.+)_(en|el)$/', $k, $m ) ) {
				// Bilingual sub-field — keep only the current language value.
				if ( $m[2] === $lang ) {
					$row[ $m[1] ] = ( is_array( $v ) && isset( $v['url'] ) ) ? $v['url'] : $v;
				}
				// Skip the other language.
			} elseif ( is_array( $v ) && isset( $v['url'] ) ) {
				// ACF Image return array → normalize to plain URL.
				$row[ $k ]           = $v['url'];
				$row[ $k . '_id' ]   = $v['ID'] ?? 0;
				$row[ $k . '_sizes' ] = $v['sizes'] ?? [];
			} else {
				$row[ $k ] = $v;
			}
		}
		return $row;
	}

	// ── Options page shorthand ────────────────────────────────────────────────

	/**
	 * Read a field from the ACF options page ('option' pseudo post-id).
	 * Returns raw ACF value.
	 */
	public static function get_options( string $field_name ) {
		if ( ! function_exists( 'get_field' ) ) return null;
		return get_field( $field_name, 'option' );
	}

	/**
	 * Read a single non-bilingual field directly (any post_id or 'option').
	 * Returns a string; falls back to $fallback if ACF is empty.
	 */
	public static function get_direct( $post_id, string $field_name, string $fallback = '' ): string {
		if ( function_exists( 'get_field' ) ) {
			$val = get_field( $field_name, $post_id );
			if ( $val !== null && $val !== '' && $val !== false ) {
				return (string) $val;
			}
		}
		return $fallback;
	}

	// ── Utilities ─────────────────────────────────────────────────────────────

	/**
	 * Normalize an ACF Image field return value to a URL string.
	 * ACF Image fields can return an array (return format = 'array') or an ID.
	 */
	public static function acf_image_url( $acf_image, string $size = 'full' ): string {
		if ( empty( $acf_image ) ) return '';
		if ( is_array( $acf_image ) ) {
			return $acf_image['sizes'][ $size ] ?? $acf_image['url'] ?? '';
		}
		if ( is_numeric( $acf_image ) ) {
			return (string) ( wp_get_attachment_image_url( (int) $acf_image, $size ) ?: '' );
		}
		if ( is_string( $acf_image ) ) {
			return $acf_image;
		}
		return '';
	}
}
