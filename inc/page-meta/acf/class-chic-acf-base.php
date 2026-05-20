<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared helpers for building ACF field definitions.
 *
 * Subclasses call these static methods to avoid boilerplate.
 */
abstract class Chic_Acf_Base {

	/**
	 * Two text fields for a bilingual string (EN + EL), side by side (50% each).
	 *
	 * @param string $name  Full field name WITHOUT _en/_el suffix (e.g. 'chic_home_hero_heading').
	 * @param string $label Human label WITHOUT language suffix (e.g. 'Hero Heading').
	 * @param string $type  ACF type: text|textarea|wysiwyg.
	 * @param array  $extra Extra field properties merged into both sub-fields.
	 * @return array[] Two field definitions.
	 */
	protected static function bilingual( string $name, string $label, string $type = 'text', array $extra = [] ): array {
		$base = [ 'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ] ];
		return [
			array_merge( $base, $extra, [
				'key'   => 'field_' . $name . '_en',
				'name'  => $name . '_en',
				'label' => $label . ' — English',
				'type'  => $type,
			] ),
			array_merge( $base, $extra, [
				'key'   => 'field_' . $name . '_el',
				'name'  => $name . '_el',
				'label' => $label . ' — Ελληνικά',
				'type'  => $type,
			] ),
		];
	}

	/**
	 * An image field + alt-text EN/EL pair.
	 *
	 * @param string $name  Full field name without suffix (e.g. 'chic_explore_hero_image').
	 * @param string $label Human label (e.g. 'Hero Image').
	 * @return array[] Three field definitions: image, alt_en, alt_el.
	 */
	protected static function image_with_alt( string $name, string $label ): array {
		return [
			[
				'key'           => 'field_' . $name,
				'name'          => $name,
				'label'         => $label,
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
			],
			[
				'key'     => 'field_' . $name . '_alt_en',
				'name'    => $name . '_alt_en',
				'label'   => $label . ' Alt Text — English',
				'type'    => 'text',
				'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
			],
			[
				'key'     => 'field_' . $name . '_alt_el',
				'name'    => $name . '_alt_el',
				'label'   => $label . ' Alt Text — Ελληνικά',
				'type'    => 'text',
				'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
			],
		];
	}

	/**
	 * A visual separator tab field.
	 *
	 * @param string $label   Tab label shown in admin.
	 * @param string $key_suffix  Unique slug appended to 'field_tab_' to build the key.
	 */
	protected static function tab( string $label, string $key_suffix ): array {
		return [ [
			'key'       => 'field_tab_' . $key_suffix,
			'label'     => $label,
			'type'      => 'tab',
			'placement' => 'top',
			'endpoint'  => 0,
		] ];
	}
}
