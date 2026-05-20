<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( 'testimonials', [

	// Hero
	'hero_image'   => [
		'type'        => 'image',
		'label'       => 'Hero Image',
		'default_url' => 'https://davidb1553.sg-host.com/wp-content/uploads/11-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp',
		'alt_default' => 'Syntagma Square with the Hellenic Parliament building and fountain',
	],
	'hero_heading' => [ 'type' => 'text', 'label' => 'Page Heading', 'default' => 'Latest Reviews' ],
	'section_aria' => [ 'type' => 'text', 'label' => 'Reviews Section Aria-Label', 'default' => 'Guest reviews' ],

] );
