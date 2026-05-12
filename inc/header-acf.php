<?php
defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'chic_header_register_options_page' );
function chic_header_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) return;
	acf_add_options_page( [
		'page_title' => 'Header Settings',
		'menu_title' => 'Header',
		'menu_slug'  => 'header-settings',
		'capability' => 'manage_options',
		'icon_url'   => 'dashicons-menu-alt',
		'redirect'   => false,
		'position'   => 4,
	] );
}

add_action( 'acf/init', 'chic_header_register_field_group' );
function chic_header_register_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$suite_link_choices = [
		'url'         => 'Custom URL',
		'page'        => 'Page',
		'placeholder' => 'Placeholder (#)',
	];

	$suite_fields = [
		[
			'key'      => 'field_chic_suite_label',
			'label'    => 'Suite Name',
			'name'     => 'label',
			'type'     => 'text',
			'required' => 1,
		],
		[
			'key'           => 'field_chic_suite_link_type',
			'label'         => 'Link Type',
			'name'          => 'link_type',
			'type'          => 'select',
			'choices'       => $suite_link_choices,
			'default_value' => 'url',
			'return_format' => 'value',
		],
		[
			'key'               => 'field_chic_suite_url',
			'label'             => 'URL',
			'name'              => 'url',
			'type'              => 'url',
			'conditional_logic' => [ [ [ 'field' => 'field_chic_suite_link_type', 'operator' => '==', 'value' => 'url' ] ] ],
		],
		[
			'key'               => 'field_chic_suite_page',
			'label'             => 'Page',
			'name'              => 'page',
			'type'              => 'page_link',
			'conditional_logic' => [ [ [ 'field' => 'field_chic_suite_link_type', 'operator' => '==', 'value' => 'page' ] ] ],
		],
		[
			'key'           => 'field_chic_suite_image',
			'label'         => 'Preview Image',
			'name'          => 'image',
			'type'          => 'image',
			'return_format' => 'url',
			'preview_size'  => 'medium',
		],
	];

	$building_fields = [
		[
			'key'      => 'field_chic_building_label',
			'label'    => 'Building Name',
			'name'     => 'building_label',
			'type'     => 'text',
			'required' => 1,
		],
		[
			'key'          => 'field_chic_building_suites',
			'label'        => 'Suites',
			'name'         => 'suites',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Suite',
			'sub_fields'   => $suite_fields,
		],
	];

	$item_link_choices = [
		'url'         => 'Custom URL',
		'page'        => 'Page',
		'placeholder' => 'Placeholder (#)',
	];

	$item_fields = [
		[
			'key'      => 'field_chic_item_label',
			'label'    => 'Label',
			'name'     => 'label',
			'type'     => 'text',
			'required' => 1,
		],
		[
			'key'           => 'field_chic_item_link_type',
			'label'         => 'Link Type',
			'name'          => 'link_type',
			'type'          => 'select',
			'choices'       => $item_link_choices,
			'default_value' => 'url',
			'return_format' => 'value',
		],
		[
			'key'               => 'field_chic_item_url',
			'label'             => 'URL',
			'name'              => 'url',
			'type'              => 'url',
			'conditional_logic' => [ [ [ 'field' => 'field_chic_item_link_type', 'operator' => '==', 'value' => 'url' ] ] ],
		],
		[
			'key'               => 'field_chic_item_page',
			'label'             => 'Page',
			'name'              => 'page',
			'type'              => 'page_link',
			'conditional_logic' => [ [ [ 'field' => 'field_chic_item_link_type', 'operator' => '==', 'value' => 'page' ] ] ],
		],
		[
			'key'           => 'field_chic_item_is_mega',
			'label'         => 'Mega Panel?',
			'name'          => 'is_mega',
			'type'          => 'true_false',
			'default_value' => 0,
			'ui'            => 1,
		],
		[
			'key'               => 'field_chic_item_mega_groups',
			'label'             => 'Building Groups',
			'name'              => 'mega_groups',
			'type'              => 'repeater',
			'layout'            => 'block',
			'button_label'      => 'Add Building',
			'conditional_logic' => [ [ [ 'field' => 'field_chic_item_is_mega', 'operator' => '==', 'value' => '1' ] ] ],
			'sub_fields'        => $building_fields,
		],
	];

	acf_add_local_field_group( [
		'key'      => 'group_chic_header',
		'title'    => 'Header Menu',
		'fields'   => [
			[
				'key'          => 'field_chic_menu_items',
				'label'        => 'Menu Items',
				'name'         => 'menu_items',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Add Menu Item',
				'sub_fields'   => $item_fields,
			],
		],
		'location' => [ [ [
			'param'    => 'options_page',
			'operator' => '==',
			'value'    => 'header-settings',
		] ] ],
		'active'   => true,
	] );
}
