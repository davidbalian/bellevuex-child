<?php
/**
 * Temporary admin trigger for the SEO content updater.
 *
 * Exposes the updater at a nonce-protected admin URL while you are logged in
 * as an administrator. Remove this file (and the require_once in functions.php)
 * after you have confirmed a successful run.
 */

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$dry_url  = wp_nonce_url( admin_url( '?chic_apply_seo=1&dry_run=1' ), 'chic_apply_seo' );
	$live_url = wp_nonce_url( admin_url( '?chic_apply_seo=1' ), 'chic_apply_seo' );
	echo '<div class="notice notice-info">';
	echo '<p><strong>Chic SEO Updater:</strong> ';
	echo '<a href="' . esc_url( $dry_url ) . '">Dry run (preview)</a> &nbsp;|&nbsp; ';
	echo '<a href="' . esc_url( $live_url ) . '">Live run (write)</a>';
	echo ' — Remove <code>inc/seo-updater-trigger.php</code> from <code>functions.php</code> once done.</p>';
	echo '</div>';
} );

add_action( 'admin_init', function () {
	if ( empty( $_GET['chic_apply_seo'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'chic_apply_seo' ) ) {
		wp_die( 'Invalid nonce. Go back and use the links from the admin notice.' );
	}

	require_once get_stylesheet_directory() . '/tools/apply-seo-content.php';
	exit;
} );
