<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( 'testimonials', [

	// Hero
	'hero_image'   => [
		'type'        => 'image',
		'label'       => 'Hero Image',
		'default_url' => chic_upload_url( '11-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp' ),
		'alt_default' => 'Syntagma Square with the Hellenic Parliament building and fountain',
	],
	'hero_heading' => [ 'type' => 'text', 'label' => 'Page Heading', 'default' => 'Latest Reviews' ],
	'section_aria' => [ 'type' => 'text', 'label' => 'Reviews Section Aria-Label', 'default' => 'Guest reviews' ],

] );
