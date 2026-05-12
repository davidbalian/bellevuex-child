<?php
defined( 'ABSPATH' ) || exit;

// ── Hidden CPT to hold header settings (works with ACF Free) ─────────────────

add_action( 'init', 'chic_header_register_cpt' );
function chic_header_register_cpt() {
	register_post_type( 'chic_header_cfg', [
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => false,
		'supports'     => [ 'title' ],
		'labels'       => [
			'name'          => 'Header Config',
			'singular_name' => 'Header Config',
			'edit_item'     => 'Header Settings',
		],
	] );
}

function chic_header_config_id(): int {
	static $id = null;
	if ( null !== $id ) return $id;

	$posts = get_posts( [
		'post_type'      => 'chic_header_cfg',
		'numberposts'    => 1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	] );

	if ( ! empty( $posts ) ) {
		$id = (int) $posts[0];
	} else {
		$id = (int) wp_insert_post( [
			'post_type'   => 'chic_header_cfg',
			'post_title'  => 'Header Settings',
			'post_status' => 'publish',
		] );
	}

	return $id;
}

// ── Admin sidebar: top-level "Header" item that redirects to the CPT edit page ─

add_action( 'admin_menu', 'chic_header_admin_menu' );
function chic_header_admin_menu() {
	add_menu_page(
		'Header Settings',
		'Header',
		'manage_options',
		'chic-header-settings',
		'__return_false',
		'dashicons-menu-alt',
		4
	);
}

add_action( 'admin_init', 'chic_header_admin_redirect' );
function chic_header_admin_redirect() {
	if ( ! isset( $_GET['page'] ) || 'chic-header-settings' !== $_GET['page'] ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	$post_id = chic_header_config_id();
	wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
	exit;
}

// Keep "Header" highlighted in the sidebar when editing the config post.
add_filter( 'parent_file', 'chic_header_highlight_menu' );
function chic_header_highlight_menu( string $parent_file ): string {
	global $post;
	if ( $post && 'chic_header_cfg' === $post->post_type ) {
		return 'chic-header-settings';
	}
	return $parent_file;
}

// ── ACF field group (attached to the CPT, not an options page) ───────────────

add_action( 'acf/init', 'chic_header_register_field_group' );
function chic_header_register_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$link_choices = [
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
			'choices'       => $link_choices,
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
			'choices'       => $link_choices,
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
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'chic_header_cfg',
		] ] ],
		'active'   => true,
	] );
}
