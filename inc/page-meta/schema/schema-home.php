<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( 'home', [

	// Hero section
	'hero_aria'         => [ 'type' => 'text', 'label' => 'Hero Carousel Aria-Label', 'default' => 'Chic Centre Suites hero photos' ],
	'hero_heading'      => [ 'type' => 'text', 'label' => 'Hero Heading', 'default' => 'Stay in the Heart<br> of Athens' ],
	'hero_subhead'      => [ 'type' => 'textarea', 'label' => 'Hero Subheading', 'default' => 'Our suites, spread across three buildings on the same road, steps from Syntagma Square, Monastiraki, and the Ermou pedestrian street.' ],
	'hero_cta_label'    => [ 'type' => 'text', 'label' => 'Hero CTA Label', 'default' => 'Book Now' ],
	'hero_cta_url'      => [ 'type' => 'url', 'label' => 'Hero CTA URL', 'default' => 'https://direct-book.com/properties/chiccentresuitesathens' ],
	'hero_scroll_aria'  => [ 'type' => 'text', 'label' => 'Scroll Chevron Aria-Label', 'default' => 'Scroll to next section' ],

	// Intro section
	'intro_heading'     => [ 'type' => 'text', 'label' => 'Intro Heading', 'default' => 'Welcome to Athens' ],
	'intro_body'        => [ 'type' => 'textarea', 'label' => 'Intro Body', 'default' => 'Our fully equipped apartments are located in three buildings on the same road in the heart of Athens, just a 5-minute walk from Syntagma Square and Monastiraki, and only steps away from the iconic pedestrian street of Ermou, where you can explore the city&rsquo;s treasures, experience its culture and history, and, of course, enjoy shopping.' ],

	// Suite carousels
	'carousel_prev_aria' => [ 'type' => 'text', 'label' => 'Carousel Previous Aria-Label', 'default' => 'Previous suites' ],
	'carousel_next_aria' => [ 'type' => 'text', 'label' => 'Carousel Next Aria-Label', 'default' => 'Next suites' ],

] );
