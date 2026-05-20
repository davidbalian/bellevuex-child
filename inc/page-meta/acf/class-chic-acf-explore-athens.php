<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Explore_Athens extends Chic_Acf_Base {

	public function __construct() {
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Hero', 'ea_hero' ),
			self::image_with_alt( 'chic_explore_hero_image', 'Hero Image' ),
			self::bilingual( 'chic_explore_hero_heading', 'Hero Heading' ),
			self::bilingual( 'chic_explore_hero_subhead', 'Hero Subheading' ),
			self::tab( 'Intro', 'ea_intro' ),
			self::bilingual( 'chic_explore_intro_eyebrow',  'Intro Eyebrow' ),
			self::bilingual( 'chic_explore_intro_heading',  'Intro Heading' ),
			self::bilingual( 'chic_explore_intro_body_1',   'Intro Paragraph 1', 'textarea' ),
			self::bilingual( 'chic_explore_intro_body_2',   'Intro Paragraph 2', 'textarea' ),
			self::tab( 'Attractions', 'ea_attractions' ),
			[ self::attractions_repeater() ],
			self::tab( 'Also Worth Exploring', 'ea_more' ),
			self::bilingual( 'chic_explore_more_heading', '"Also Worth Exploring" Heading' ),
			[ self::more_repeater() ]
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_explore_athens',
			'title'    => 'Explore Athens Content',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'page_template',
				'operator' => '==',
				'value'    => 'page-explore-athens.php',
			] ] ],
		] );
	}

	private static function attractions_repeater(): array {
		return [
			'key'          => 'field_chic_ea_attractions',
			'name'         => 'chic_ea_attractions',
			'label'        => 'Attractions',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Attraction',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_ea_att_title_en',
					'name'    => 'title_en',
					'label'   => 'Title — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_att_title_el',
					'name'    => 'title_el',
					'label'   => 'Title — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_att_body_en',
					'name'    => 'body_en',
					'label'   => 'Body — English',
					'type'    => 'textarea',
					'rows'    => 4,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_att_body_el',
					'name'    => 'body_el',
					'label'   => 'Body — Ελληνικά',
					'type'    => 'textarea',
					'rows'    => 4,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'           => 'field_chic_ea_att_image',
					'name'          => 'image',
					'label'         => 'Image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				],
				[
					'key'     => 'field_chic_ea_att_alt_en',
					'name'    => 'alt_en',
					'label'   => 'Image Alt — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_att_alt_el',
					'name'    => 'alt_el',
					'label'   => 'Image Alt — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		];
	}

	private static function more_repeater(): array {
		return [
			'key'          => 'field_chic_ea_more',
			'name'         => 'chic_ea_more_items',
			'label'        => 'Also Worth Exploring Items',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Item',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_ea_more_title_en',
					'name'    => 'title_en',
					'label'   => 'Title — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_more_title_el',
					'name'    => 'title_el',
					'label'   => 'Title — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_more_body_en',
					'name'    => 'body_en',
					'label'   => 'Body — English',
					'type'    => 'textarea',
					'rows'    => 3,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_ea_more_body_el',
					'name'    => 'body_el',
					'label'   => 'Body — Ελληνικά',
					'type'    => 'textarea',
					'rows'    => 3,
					'wrapper' => [ 'width' => '50', 'class' => '', 'id' => '' ],
				],
			],
		];
	}
}
