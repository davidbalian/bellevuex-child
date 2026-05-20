<?php
defined( 'ABSPATH' ) || exit;

Chic_Page_Meta_Schema::register( '404', [
	'aria'         => [ 'type' => 'text',     'label' => 'Section Aria-Label',  'default' => '404 error' ],
	'error_label'  => [ 'type' => 'text',     'label' => '404 Error Label',     'default' => '404 Error' ],
	'heading'      => [ 'type' => 'text',     'label' => 'Page Heading',        'default' => 'Page Not Found' ],
	'body'         => [ 'type' => 'textarea', 'label' => 'Body Text',           'default' => "The page you're looking for doesn't exist or may have been moved." ],
	'return_label' => [ 'type' => 'text',     'label' => 'Return Link Label',   'default' => 'Return to Homepage' ],
] );
