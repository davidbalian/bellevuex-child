<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-chic-acf-base.php';

class Chic_Acf_Header extends Chic_Acf_Base {

	public function __construct() {
		$this->register();
	}

	private function register(): void {
		$fields = array_merge(
			self::tab( 'Logo', 'hdr_logo' ),
			[
				[
					'key'           => 'field_chic_hdr_logo',
					'name'          => 'chic_hdr_logo',
					'label'         => 'Header Logo',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
				],
			],
			self::bilingual( 'chic_hdr_logo_alt', 'Logo Alt Text' ),
			self::tab( 'Navigation', 'hdr_nav' ),
			[ $this->menu_items_repeater() ]
		);

		acf_add_local_field_group( [
			'key'      => 'group_chic_header',
			'title'    => 'Header Settings',
			'fields'   => $fields,
			'location' => [ [ [
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'chic_header_cfg',
			] ] ],
		] );
	}

	private function menu_items_repeater(): array {
		return [
			'key'          => 'field_chic_hdr_menu_items',
			'name'         => 'chic_hdr_menu_items',
			'label'        => 'Menu Items',
			'instructions' => 'Add top-level navigation items. For the "Suites / Buildings" mega-panel, set Mega Panel = Yes — the building tabs and suite links are populated automatically from MotoPress accommodation categories.',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Menu Item',
			'sub_fields'   => [
				[
					'key'     => 'field_chic_hdr_mi_label_en',
					'name'    => 'label_en',
					'label'   => 'Label — English',
					'type'    => 'text',
					'wrapper' => [ 'width' => '30', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_hdr_mi_label_el',
					'name'    => 'label_el',
					'label'   => 'Label — Ελληνικά',
					'type'    => 'text',
					'wrapper' => [ 'width' => '30', 'class' => '', 'id' => '' ],
				],
				[
					'key'          => 'field_chic_hdr_mi_link_type',
					'name'         => 'link_type',
					'label'        => 'Link Type',
					'type'         => 'radio',
					'choices'      => [
						'placeholder' => 'Placeholder (#)',
						'url'         => 'Custom URL',
						'page'        => 'Page',
					],
					'default_value' => 'placeholder',
					'layout'        => 'horizontal',
					'wrapper'       => [ 'width' => '40', 'class' => '', 'id' => '' ],
				],
				[
					'key'               => 'field_chic_hdr_mi_url',
					'name'              => 'url',
					'label'             => 'Custom URL',
					'type'              => 'text',
					'conditional_logic' => [ [ [
						'field'    => 'field_chic_hdr_mi_link_type',
						'operator' => '==',
						'value'    => 'url',
					] ] ],
					'wrapper' => [ 'width' => '70', 'class' => '', 'id' => '' ],
				],
				[
					'key'               => 'field_chic_hdr_mi_page',
					'name'              => 'page_id',
					'label'             => 'Page',
					'type'              => 'page_link',
					'post_type'         => [ 'page' ],
					'allow_null'        => 1,
					'conditional_logic' => [ [ [
						'field'    => 'field_chic_hdr_mi_link_type',
						'operator' => '==',
						'value'    => 'page',
					] ] ],
					'wrapper' => [ 'width' => '70', 'class' => '', 'id' => '' ],
				],
				[
					'key'     => 'field_chic_hdr_mi_is_mega',
					'name'    => 'is_mega',
					'label'   => 'Mega Panel?',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '30', 'class' => '', 'id' => '' ],
				],
			],
		];
	}
}
