<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( 'footer', [

	// Contact column
	'brand_name'   => [ 'type' => 'text', 'label' => 'Brand Name',       'default' => 'P. Yiatros I.K.E' ],
	'email'        => [ 'type' => 'text', 'label' => 'Contact Email',    'default' => 'contact@chiccentresuites.com' ],
	'phone_1'      => [ 'type' => 'text', 'label' => 'Phone 1 (display)', 'default' => '+357 99674630' ],
	'phone_1_href' => [ 'type' => 'text', 'label' => 'Phone 1 (tel: href)', 'default' => 'tel:+35799674630' ],
	'phone_2'      => [ 'type' => 'text', 'label' => 'Phone 2 (display)', 'default' => '+30 6982102221' ],
	'phone_2_href' => [ 'type' => 'text', 'label' => 'Phone 2 (tel: href)', 'default' => 'tel:+306982102221' ],
	'address_1'    => [ 'type' => 'text', 'label' => 'Address Line 1',   'default' => '11 Thiseos, 10562 Athens, Greece' ],
	'address_2'    => [ 'type' => 'text', 'label' => 'Address Line 2',   'default' => '13 Thiseos, 10562 Athens, Greece' ],
	'address_3'    => [ 'type' => 'text', 'label' => 'Address Line 3',   'default' => '2 Chavriou, 10562 Athens, Greece' ],
	'gemi'         => [ 'type' => 'text', 'label' => 'ΓΕΜΗ Number',      'default' => 'ΓΕΜΗ: 150824901000' ],

	// Useful links column
	'privacy_label'    => [ 'type' => 'text', 'label' => 'Privacy Policy Link Text',      'default' => 'Privacy Policy' ],
	'cookie_label'     => [ 'type' => 'text', 'label' => 'Cookie Policy Link Text',       'default' => 'Cookie Policy' ],
	'terms_label'      => [ 'type' => 'text', 'label' => 'Terms & Conditions Link Text',  'default' => 'Terms &amp; Conditions' ],

	// Bottom bar
	'all_rights'   => [ 'type' => 'text', 'label' => 'Copyright Suffix', 'default' => 'All rights reserved.' ],
	'developed_by' => [ 'type' => 'text', 'label' => '"Developed by" Label', 'default' => 'Developed by' ],

] );
