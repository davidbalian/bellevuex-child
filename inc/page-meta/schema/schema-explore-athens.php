<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( 'explore', [

	// Hero
	'hero_image'      => [
		'type'        => 'image',
		'label'       => 'Hero Image',
		'default_url' => 'https://davidb1553.sg-host.com/wp-content/uploads/7-chic-centre-suites-athens-nearby-attractions-acropolis.webp',
		'alt_default' => 'The Acropolis of Athens at sunset, overlooking the city',
	],
	'hero_heading'    => [ 'type' => 'text', 'label' => 'Hero Heading', 'default' => 'EXPLORE AND<br>EXPERIENCE ATHENS' ],
	'hero_subhead'    => [ 'type' => 'text', 'label' => 'Hero Subheading', 'default' => "Discover the timeless beauty of Greece's capital from the heart of the city" ],

	// Intro
	'intro_eyebrow'   => [ 'type' => 'text', 'label' => 'Intro Eyebrow', 'default' => 'Explore Athens / Athina' ],
	'intro_heading'   => [ 'type' => 'text', 'label' => 'Intro Heading', 'default' => 'A City Steeped in History, Culture, and Ancient Landmarks' ],
	'intro_body_1'    => [ 'type' => 'textarea', 'label' => 'Intro Paragraph 1', 'default' => 'Staying at Athens means immersing yourself in a destination where ancient history meets vibrant modern life. From iconic landmarks to hidden local gems, Athens offers an unforgettable experience for every traveler, and at Chic Centre Suites Athens, you are perfectly positioned to explore it all.' ],
	'intro_body_2'    => [ 'type' => 'textarea', 'label' => 'Intro Paragraph 2', 'default' => 'Located just steps from Ermou Street and within a short walk of Syntagma Square and Monastiraki, your stay places you at the very center of culture, history, shopping, and gastronomy.' ],

	// "Also worth exploring" section heading
	'more_heading'    => [ 'type' => 'text', 'label' => '"Also Worth Exploring" Heading', 'default' => 'Also Worth Exploring' ],

] );
