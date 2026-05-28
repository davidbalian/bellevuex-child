<?php
defined( 'ABSPATH' ) || exit;

// Per-suite schema: these are per-post defaults.
// Actual defaults come from _chic_suite_all_data() and are seeded into each post.
Chic_Page_Meta_Schema::register( 'suite', [

	'description'    => [ 'type' => 'wysiwyg', 'label' => 'Suite Description', 'default' => '' ],
	'capacity'       => [ 'type' => 'text',    'label' => 'Capacity Label',       'default' => 'Up to 2 guests' ],
	'features'       => [ 'type' => 'array',   'label' => 'Suite Features',       'default' => [] ],
	'size'           => [ 'type' => 'number',  'label' => 'Size (m²)',            'default' => '' ],
	'booking_url'    => [ 'type' => 'url',     'label' => 'Booking URL Override', 'default' => '' ],

] );
