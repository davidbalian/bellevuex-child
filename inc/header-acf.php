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

// ── ACF Free field groups can be added here in the future ────────────────────
// Attach single-value fields (text, image, true/false, etc.) to post_type=chic_header_cfg.
// The menu repeater is handled by the custom meta box in inc/header-admin-ui.php.
