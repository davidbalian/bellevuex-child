<?php
/**
 * Temporary admin triggers for one-off site tools.
 * Remove this file (and the require_once in functions.php) once all runs are confirmed.
 */

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$seo_dry  = wp_nonce_url( admin_url( '?chic_apply_seo=1&dry_run=1' ), 'chic_apply_seo' );
	$seo_live = wp_nonce_url( admin_url( '?chic_apply_seo=1' ), 'chic_apply_seo' );

	$greek_dry  = wp_nonce_url( admin_url( '?chic_trash_greek=1&dry_run=1' ), 'chic_trash_greek' );
	$greek_live = wp_nonce_url( admin_url( '?chic_trash_greek=1' ), 'chic_trash_greek' );

	echo '<div class="notice notice-info"><p>';
	echo '<strong>Chic SEO Updater:</strong> ';
	echo '<a href="' . esc_url( $seo_dry ) . '">Dry run</a> &nbsp;|&nbsp; ';
	echo '<a href="' . esc_url( $seo_live ) . '">Live run</a>';
	echo ' &emsp; <strong>Trash Greek duplicates:</strong> ';
	echo '<a href="' . esc_url( $greek_dry ) . '">Dry run</a> &nbsp;|&nbsp; ';
	echo '<a href="' . esc_url( $greek_live ) . '">Live run</a>';
	echo ' — Remove <code>inc/seo-updater-trigger.php</code> from <code>functions.php</code> once done.</p></div>';
} );

add_action( 'admin_init', function () {
	$stylesheet_dir = get_stylesheet_directory();

	if ( ! empty( $_GET['chic_apply_seo'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized.' );
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'chic_apply_seo' ) ) {
			wp_die( 'Invalid nonce. Use the links in the admin notice.' );
		}
		require_once $stylesheet_dir . '/tools/apply-seo-content.php';
		exit;
	}

	if ( ! empty( $_GET['chic_trash_greek'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized.' );
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'chic_trash_greek' ) ) {
			wp_die( 'Invalid nonce. Use the links in the admin notice.' );
		}
		require_once $stylesheet_dir . '/tools/trash-greek-suites.php';
		exit;
	}
} );
