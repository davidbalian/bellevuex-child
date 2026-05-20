<?php
defined( 'ABSPATH' ) || exit;

// Site-wide settings: buildings, facilities, hygiene notes, common labels.
// These are stored on the ACF Options page ('option' pseudo post-id).
// Repeater fields (ss_buildings, ss_facilities, ss_hygiene, ss_awards, ss_social)
// are not defined here as schema fields — they are read directly from ACF.
Chic_Page_Meta_Schema::register( 'ss', [

	// Default booking URL (used wherever Book Now links appear)
	'booking_url' => [ 'type' => 'url', 'label' => 'Default Booking URL', 'default' => 'https://direct-book.com/properties/chiccentresuitesathens' ],

	// Common UI labels
	'book_now'            => [ 'type' => 'text', 'label' => 'Book Now',            'default' => 'Book Now' ],
	'view'                => [ 'type' => 'text', 'label' => 'View (link)',         'default' => 'View' ],
	'photos'              => [ 'type' => 'text', 'label' => 'Photos (section)',    'default' => 'Photos' ],
	'suite_description'   => [ 'type' => 'text', 'label' => 'Suite Description (heading)', 'default' => 'Suite Description' ],
	'suite_facilities'    => [ 'type' => 'text', 'label' => 'Suite Facilities (heading)',   'default' => 'Suite Facilities' ],
	'prev_photo_aria'     => [ 'type' => 'text', 'label' => 'Previous Photo Aria', 'default' => 'Previous photo' ],
	'next_photo_aria'     => [ 'type' => 'text', 'label' => 'Next Photo Aria',     'default' => 'Next photo' ],
	'contact_info'        => [ 'type' => 'text', 'label' => 'Contact Info (heading)', 'default' => 'Contact Info' ],
	'useful_links'        => [ 'type' => 'text', 'label' => 'Useful Links (heading)', 'default' => 'Useful Links' ],
	'awards_heading'      => [ 'type' => 'text', 'label' => 'Awards (heading)',    'default' => 'Awards' ],
	'follow_us'           => [ 'type' => 'text', 'label' => 'Follow Us (heading)', 'default' => 'Follow Us' ],

] );
