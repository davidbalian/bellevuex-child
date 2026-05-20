<?php
defined( 'ABSPATH' ) || exit;

/**
 * ACF page-meta bootstrap.
 * Loads schema registry, resolver, ACF field groups, and seeder.
 * Required from functions.php after all other inc/ files.
 */

require_once __DIR__ . '/class-chic-page-meta-schema.php';
require_once __DIR__ . '/class-chic-page-content.php';

// Schema files (loaded by registry on demand — still require all at once so
// the static array is populated before the seeder or any template calls).
require_once __DIR__ . '/schema/schema-home.php';
require_once __DIR__ . '/schema/schema-explore-athens.php';
require_once __DIR__ . '/schema/schema-testimonials.php';
require_once __DIR__ . '/schema/schema-single-suite.php';
require_once __DIR__ . '/schema/schema-site-settings.php';
require_once __DIR__ . '/schema/schema-404.php';
require_once __DIR__ . '/schema/schema-footer.php';
require_once __DIR__ . '/schema/schema-header.php';

// ACF field group registrations — only when ACF is active.
add_action( 'acf/init', static function () {
	require_once __DIR__ . '/acf/class-chic-acf-site-settings.php';
	require_once __DIR__ . '/acf/class-chic-acf-home.php';
	require_once __DIR__ . '/acf/class-chic-acf-explore-athens.php';
	require_once __DIR__ . '/acf/class-chic-acf-testimonials.php';
	require_once __DIR__ . '/acf/class-chic-acf-single-suite.php';
	require_once __DIR__ . '/acf/class-chic-acf-header.php';

	new Chic_Acf_Site_Settings();
	new Chic_Acf_Home();
	new Chic_Acf_Explore_Athens();
	new Chic_Acf_Testimonials();
	new Chic_Acf_Single_Suite();
	new Chic_Acf_Header();
}, 10 );

// Seeder — runs once after field groups are registered.
add_action( 'acf/init', static function () {
	require_once __DIR__ . '/class-chic-acf-seeder.php';
	Chic_Acf_Seeder::maybe_run();
}, 20 );
