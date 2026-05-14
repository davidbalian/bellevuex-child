<?php
/**
 * One-off Greek SEO content updater for Chic Centre Suites.
 *
 * Writes Greek meta titles + descriptions to custom postmeta fields
 * (_chic_aioseo_title_el / _chic_aioseo_description_el) on mphb_room_type
 * posts and the home page. AIOSEO filters in inc/i18n.php serve these values
 * on /el/... URLs so Greek pages are properly indexed.
 *
 * Usage:
 *   - Browser: triggered via inc/tools-aioseo-el.php (admin_init hook).
 *   - WP-CLI:  wp eval-file wp-content/themes/bellevuex-child/tools/apply-seo-content-el.php
 *
 * Pass &dry_run=1 (browser) or --dry-run (CLI) to preview without writing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	$root = dirname( __DIR__, 4 );
	if ( ! file_exists( $root . '/wp-load.php' ) ) {
		die( "Cannot locate wp-load.php. Run via WP-CLI or the admin trigger.\n" );
	}
	require_once $root . '/wp-load.php';
}

if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	wp_die( 'Unauthorized.' );
}

$dry_run = ( defined( 'WP_CLI' ) && WP_CLI )
	? in_array( '--dry-run', $_SERVER['argv'] ?? [], true )
	: ! empty( $_GET['dry_run'] );

/* ── Greek SEO content map ──────────────────────────────────────────────── */

// Keys match lowercased WP post titles (same as apply-seo-content.php).
// 'home' is handled separately via page template lookup.

$seo_map_el = [

	/* Home page */
	'home' => [
		'title'       => 'Διαμερίσματα στο Κέντρο της Αθήνας | Chic Centre Suites',
		'description' => 'Μείνετε στο κέντρο της Αθήνας, κοντά στην Ακρόπολη, το Μοναστηράκι και την Ερμού. Σύγχρονες σουίτες για ζευγάρια, οικογένειες και επαγγελματίες.',
	],

	/* Chavriou 2 */
	'suite 1' => [
		'title'       => 'Σουίτα 1 | Σουίτα με Μπαλκόνι στο Κέντρο Αθηνών',
		'description' => 'Μοντέρνο στούντιο 30τ.μ. κοντά στην Ερμού με μπαλκόνι, κουζίνα και Smart TV. Ιδανικό για 2 άτομα κοντά στα αξιοθέατα της Αθήνας.',
	],
	'suite 2' => [
		'title'       => 'Σουίτα 2 | Σουίτα Ενός Υπνοδωματίου με Μπαλκόνι',
		'description' => 'Ευρύχωρο διαμέρισμα 41τ.μ. στο κέντρο της Αθήνας με μπαλκόνι, κουζίνα και πλυντήριο. Ιδανικό για έως 4 άτομα κοντά σε κύρια αξιοθέατα.',
	],
	'suite 3' => [
		'title'       => 'Σουίτα 3 | Σουίτα χωρίς Μπαλκόνι στο Κέντρο',
		'description' => 'Άνετο στούντιο 23τ.μ. στο κέντρο της Αθήνας με εξοπλισμένη κουζίνα, Smart TV και γρήγορο Wi-Fi. Ιδανικό για ζευγάρια κοντά στα αξιοθέατα.',
	],
	'suite 4' => [
		'title'       => 'Σουίτα 4 | Σουίτα με Μπαλκόνι στο Κέντρο Αθηνών',
		'description' => 'Μοντέρνο στούντιο 24τ.μ. στο κέντρο της Αθήνας με μπαλκόνι, κουζίνα και Smart TV. Ιδανικό για ζευγάρια κοντά στα κύρια αξιοθέατα.',
	],
	'suite 5' => [
		'title'       => 'Σουίτα 5 | Σουίτα με Μπαλκόνι στο Κέντρο Αθηνών',
		'description' => 'Άνετο στούντιο 24τ.μ. στο κέντρο της Αθήνας με μπαλκόνι, κουζίνα και Smart TV. Ιδανικό για ζευγάρια που αναζητούν κεντρική τοποθεσία.',
	],
	'suite 6' => [
		'title'       => 'Σουίτα 6 | Σουίτα με Μπαλκόνι στο Κέντρο Αθηνών',
		'description' => 'Ευρύχωρο στούντιο 26τ.μ. στο κέντρο της Αθήνας με μπαλκόνι, κουζίνα και Smart TV. Ιδανικό για ζευγάρια κοντά σε κύρια αξιοθέατα.',
	],

	/* Thiseos 11 */
	'avra suite' => [
		'title'       => 'Σουίτα Avra | Σουίτα Ενός Υπνοδωματίου με Τζακούζι',
		'description' => 'Ευρύχωρη σουίτα 52τ.μ. στο κέντρο της Αθήνας με τζακούζι, μπαλκόνι και κουζίνα. Ιδανικό για οικογένειες κοντά στην Ακρόπολη και το Μοναστηράκι.',
	],
	'zakynthos suite' => [
		'title'       => 'Σουίτα Zakynthos | Σουίτα με Μπαλκόνι στην Αθήνα',
		'description' => 'Κομψό στούντιο 28τ.μ. στο κέντρο της Αθήνας με ιδιωτικό μπαλκόνι, κουζίνα και σύγχρονες ανέσεις. Ιδανικό για ζευγάρια κοντά στα αξιοθέατα.',
	],
	'santorini suite' => [
		'title'       => 'Σουίτα Santorini | Σουίτα με Μπαλκόνι στην Αθήνα',
		'description' => 'Κομψό στούντιο 28τ.μ. στο ιστορικό κέντρο της Αθήνας με μπαλκόνι, κουζίνα και σύγχρονες ανέσεις. Ιδανικό για ζευγάρια κοντά στην Ακρόπολη.',
	],
	'kohili suite' => [
		'title'       => 'Σουίτα Kohili | Premium Σουίτα με Τζακούζι και Μπαλκόνι',
		'description' => 'Ευρύχωρη premium σουίτα 37τ.μ. στο κέντρο της Αθήνας με τζακούζι, ιδιωτικό μπαλκόνι και κουζίνα. Ιδανικό για ζευγάρια που αναζητούν άνεση.',
	],
	'korali suite' => [
		'title'       => 'Σουίτα Korali | Σουίτα χωρίς Μπαλκόνι στην Αθήνα',
		'description' => 'Κομψό στούντιο 20τ.μ. στο κέντρο της Αθήνας με κουζίνα και σύγχρονες ανέσεις. Ιδανικό για ζευγάρια κοντά στα κύρια αξιοθέατα της πόλης.',
	],
	'mykonos suite' => [
		'title'       => 'Σουίτα Mykonos | Σουίτα με Βεράντα για 4 Άτομα',
		'description' => 'Ευρύχωρη σουίτα 35τ.μ. στο κέντρο της Αθήνας με ιδιωτική βεράντα, κουζίνα και Smart TV. Ιδανικό για έως 4 άτομα κοντά σε κύρια αξιοθέατα.',
	],
	'paros suite' => [
		'title'       => 'Σουίτα Paros | Σουίτα Ενός Υπνοδωματίου για 4 Άτομα',
		'description' => 'Ευρύχωρη σουίτα 36τ.μ. στο κέντρο της Αθήνας με κουζίνα και κυκλαδίτικο design. Ιδανικό για έως 4 άτομα κοντά στα κύρια αξιοθέατα.',
	],
	'ammos suite' => [
		'title'       => 'Σουίτα Ammos | Φωτεινό Στούντιο με Βεράντα στην Αθήνα',
		'description' => 'Φωτεινό στούντιο 19τ.μ. στο κέντρο της Αθήνας με ιδιωτική βεράντα, κουζίνα και Smart TV. Ιδανικό για ζευγάρια που αναζητούν κεντρική τοποθεσία.',
	],
	'ermou suite' => [
		'title'       => 'Σουίτα Ermou | Σουίτα με Βεράντα κοντά στην Ερμού',
		'description' => 'Μοντέρνο στούντιο 19τ.μ. κοντά στην Ερμού στο κέντρο της Αθήνας με βεράντα, κουζίνα και Smart TV. Ιδανικό για ζευγάρια ή μεμονωμένους ταξιδιώτες.',
	],
	'pelagos suite' => [
		'title'       => 'Σουίτα Pelagos | Σουίτα με Βεράντα στο Κέντρο Αθηνών',
		'description' => 'Κομψό στούντιο 27τ.μ. στο κέντρο της Αθήνας με ιδιωτική βεράντα, κουζίνα και γρήγορο Wi-Fi. Ιδανικό για 2 άτομα κοντά στα κύρια αξιοθέατα.',
	],

	/* Thiseos 13 */
	'ocean suite' => [
		'title'       => 'Σουίτα Ocean | Σουίτα χωρίς Μπαλκόνι στην Αθήνα',
		'description' => 'Μοντέρνο στούντιο 30τ.μ. στο κέντρο της Αθήνας με εμπνευσμένο από τη θάλασσα design, κουζίνα και Smart TV. Ιδανικό για ζευγάρια κοντά σε αξιοθέατα.',
	],
	'ginger suite' => [
		'title'       => 'Σουίτα Ginger | Σουίτα χωρίς Μπαλκόνι στην Αθήνα',
		'description' => 'Συμπαγές στούντιο 17τ.μ. στο κέντρο της Αθήνας με κουζίνα, γρήγορο Wi-Fi και σύγχρονες ανέσεις. Ιδανικό για ζευγάρια κοντά στα κύρια αξιοθέατα.',
	],
	'gray suite' => [
		'title'       => 'Σουίτα Grey | Ευρύχωρο Στούντιο χωρίς Μπαλκόνι',
		'description' => 'Ευρύχωρο στούντιο 32τ.μ. στο κέντρο της Αθήνας με κουζίνα, γρήγορο Wi-Fi και σύγχρονο design. Ιδανικό για ζευγάρια κοντά στα κύρια αξιοθέατα.',
	],
	'sunshine suite' => [
		'title'       => 'Σουίτα Sunshine | Φωτεινό Στούντιο στην Αθήνα',
		'description' => 'Φωτεινό στούντιο 15τ.μ. στο κέντρο της Αθήνας με κουζίνα, γρήγορο Wi-Fi και σύγχρονες ανέσεις. Ιδανικό για ζευγάρια κοντά στα κύρια αξιοθέατα.',
	],
	'forest suite' => [
		'title'       => 'Σουίτα Forest | Ευρύχωρο Διαμέρισμα για 4 Άτομα',
		'description' => 'Ευρύχωρο διαμέρισμα 35τ.μ. στο κέντρο της Αθήνας για έως 4 άτομα με κουζίνα, Smart TV και γρήγορο Wi-Fi. Ιδανικό για οικογένειες και παρέες.',
	],
];

// Template-keyed entries for one-off pages (each WP page identified by its
// _wp_page_template meta). Same shape as $seo_map_el values.
$seo_map_el_templates = [
	'page-explore-athens.php' => [
		'title'       => 'Εξερευνήστε την Αθήνα | Chic Centre Suites',
		'description' => 'Ανακαλύψτε την Ακρόπολη, την Πλάκα, το Μοναστηράκι και τα κορυφαία αξιοθέατα της Αθήνας από την κεντρική τοποθεσία των Chic Centre Suites.',
	],
	'page-testimonials.php' => [
		'title'       => 'Κριτικές Επισκεπτών | Chic Centre Suites Αθήνα',
		'description' => 'Διαβάστε αυθεντικές κριτικές από επισκέπτες των Chic Centre Suites για την άνεση, την τοποθεσία και την εξυπηρέτηση στο κέντρο της Αθήνας.',
	],
	'page-privacy-policy.php' => [
		'title'       => 'Πολιτική Απορρήτου | Chic Centre Suites',
		'description' => 'Μάθετε πώς τα Chic Centre Suites συλλέγουν, χρησιμοποιούν και προστατεύουν τα προσωπικά σας δεδομένα σύμφωνα με τον GDPR.',
	],
	'page-terms-and-conditions.php' => [
		'title'       => 'Όροι και Προϋποθέσεις | Chic Centre Suites',
		'description' => 'Διαβάστε τους όρους και τις προϋποθέσεις χρήσης της ιστοσελίδας και των υπηρεσιών των Chic Centre Suites στην Αθήνα.',
	],
	'page-cookie-policy.php' => [
		'title'       => 'Πολιτική Cookies | Chic Centre Suites',
		'description' => 'Μάθετε πώς τα Chic Centre Suites χρησιμοποιούν cookies στην ιστοσελίδα και πώς να διαχειριστείτε τις προτιμήσεις σας.',
	],
];

/* ── Writer helper ───────────────────────────────────────────────────────── */

$log   = [];
$wrote = 0;

function chic_seo_el_write( int $post_id, string $title, string $description, bool $dry_run, array &$log, int &$wrote ): void {
	$label         = get_the_title( $post_id ) . ' (ID ' . $post_id . ')';
	$current_title = get_post_meta( $post_id, '_chic_aioseo_title_el', true );
	$current_desc  = get_post_meta( $post_id, '_chic_aioseo_description_el', true );

	if ( $current_title === $title && $current_desc === $description ) {
		$log[] = "SKIP  $label — already up to date";
		return;
	}

	if ( ! $dry_run ) {
		update_post_meta( $post_id, '_chic_aioseo_title_el', $title );
		update_post_meta( $post_id, '_chic_aioseo_description_el', $description );
	}

	$log[] = ( $dry_run ? 'DRY   ' : 'WROTE ' ) . $label;
	$log[] = "      title: $title";
	$log[] = "      desc:  $description";
	$wrote++;
}

/* ── Home page + template-keyed pages ───────────────────────────────────── */

$home_pages = get_pages( [
	'meta_key'   => '_wp_page_template',
	'meta_value' => 'page-home.php',
	'number'     => 1,
] );

if ( $home_pages ) {
	$home_id = (int) $home_pages[0]->ID;
	chic_seo_el_write(
		$home_id,
		$seo_map_el['home']['title'],
		$seo_map_el['home']['description'],
		$dry_run,
		$log,
		$wrote
	);
} else {
	$log[] = 'WARN  Home page (page-home.php template) not found — skipped.';
}

foreach ( $seo_map_el_templates as $tpl => $entry ) {
	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => $tpl,
		'number'     => 1,
	] );
	if ( ! $pages ) {
		$log[] = 'WARN  Page with template ' . $tpl . ' not found — skipped.';
		continue;
	}
	chic_seo_el_write(
		(int) $pages[0]->ID,
		$entry['title'],
		$entry['description'],
		$dry_run,
		$log,
		$wrote
	);
}

/* ── Suite posts ─────────────────────────────────────────────────────────── */

$suite_posts = get_posts( [
	'post_type'      => 'mphb_room_type',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

foreach ( $suite_posts as $pid ) {
	$key = strtolower( trim( get_the_title( $pid ) ) );
	if ( ! isset( $seo_map_el[ $key ] ) ) {
		$log[] = 'WARN  "' . get_the_title( $pid ) . '" (ID ' . $pid . ') — no Greek SEO entry in map, skipped.';
		continue;
	}
	chic_seo_el_write(
		$pid,
		$seo_map_el[ $key ]['title'],
		$seo_map_el[ $key ]['description'],
		$dry_run,
		$log,
		$wrote
	);
}

/* ── Output ──────────────────────────────────────────────────────────────── */

$mode   = $dry_run ? 'DRY RUN (nothing written)' : 'LIVE RUN';
$header = "[Chic Greek SEO Updater — $mode] " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
$header .= str_repeat( '-', 70 ) . "\n";
$footer = str_repeat( '-', 70 ) . "\n";
$footer .= ( $dry_run ? 'Would write: ' : 'Wrote: ' ) . $wrote . ' post(s).' . "\n";

$output = $header . implode( "\n", $log ) . "\n" . $footer;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::log( $output );
} else {
	echo '<pre style="font-family:monospace;font-size:13px;line-height:1.6;padding:2rem;background:#f4f4f4;color:#222;">';
	echo esc_html( $output );
	echo '</pre>';
}
