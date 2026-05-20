<?php
defined( 'ABSPATH' ) || exit;

// Header schema: logo + menu items.
// Menu items are a Repeater (chic_hdr_menu_items) — not schema-defined,
// read directly via ACF.
Chic_Page_Meta_Schema::register( 'hdr', [

	'logo' => [
		'type'        => 'image',
		'label'       => 'Header Logo',
		'default_url' => chic_upload_url( 'chic-centre-suites-logo-no-text.png' ),
		'alt_default' => '',
	],

] );
