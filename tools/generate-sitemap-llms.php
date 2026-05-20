<?php
/**
 * Sitemap + llms.txt generators for Chic Centre Suites.
 *
 * Included from inc/tools-sitemap-llms.php; always runs inside a live WP context.
 * All functions are prefixed chic_sitemap_ / chic_llms_ to avoid collisions.
 */
defined( 'ABSPATH' ) || exit;

if ( function_exists( 'chic_sitemap_collect_urls' ) ) return;

/* ── URL collection ─────────────────────────────────────────────────────── */

/**
 * Returns canonical EN paths for the sitemap: front page, key marketing pages,
 * legal policy pages, and all published suites.
 * Each item: [ 'path', 'lastmod', 'priority', 'changefreq' ]
 */
function chic_sitemap_collect_urls(): array {
	$front_id = (int) get_option( 'page_on_front' );
	$deny_slugs = [
		'cart', 'checkout', 'checkout-2', 'my-account', 'order-received',
		'order-tracking', 'lost-password', 'thank-you', 'account', 'register',
	];
	/** Page templates allowed in the sitemap (home is included via $front_id). */
	$sitemap_page_templates = [
		'page-explore-athens.php',
		'page-testimonials.php',
		'page-privacy-policy.php',
		'page-cookie-policy.php',
		'page-terms-and-conditions.php',
	];
	$legal_templates = [
		'page-privacy-policy.php',
		'page-cookie-policy.php',
		'page-terms-and-conditions.php',
	];
	$page_urls  = [];
	$suite_urls = [];

	// Enumerate published pages.
	$pages = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	] );
	foreach ( $pages as $page ) {
		$tpl = get_page_template_slug( $page->ID );
		$in_sitemap = ( $page->ID === $front_id )
			|| ( $tpl && in_array( $tpl, $sitemap_page_templates, true ) );
		if ( ! $in_sitemap ) {
			continue;
		}
		if ( in_array( $page->post_name, $deny_slugs, true ) ) {
			continue;
		}
		if ( '1' === (string) get_post_meta( $page->ID, '_aioseo_robots_noindex', true ) ) {
			continue;
		}

		$is_home  = ( $page->ID === $front_id );
		$is_legal = $tpl && in_array( $tpl, $legal_templates, true );
		$path     = wp_make_link_relative( get_permalink( $page->ID ) );

		$page_urls[] = [
			'path'       => $path,
			'lastmod'    => gmdate( 'Y-m-d', strtotime( $page->post_modified_gmt ) ),
			'priority'   => $is_home ? '1.0' : ( $is_legal ? '0.3' : '0.7' ),
			'changefreq' => $is_home ? 'weekly' : ( $is_legal ? 'yearly' : 'monthly' ),
		];
	}

	// Enumerate published suites in theme display order.
	foreach ( chic_home_get_all_suites_ordered() as $suite ) {
		if ( '1' === (string) get_post_meta( $suite['id'], '_aioseo_robots_noindex', true ) ) {
			continue;
		}
		$modified = get_post_field( 'post_modified_gmt', $suite['id'] );
		$suite_urls[] = [
			'path'       => wp_make_link_relative( $suite['permalink'] ),
			'lastmod'    => $modified ? gmdate( 'Y-m-d', strtotime( $modified ) ) : gmdate( 'Y-m-d' ),
			'priority'   => '0.9',
			'changefreq' => 'weekly',
		];
	}

	// Home first, then remaining pages alpha, then suites in display order.
	usort( $page_urls, function ( $a, $b ) {
		if ( '/' === $a['path'] ) return -1;
		if ( '/' === $b['path'] ) return  1;
		return strcmp( $a['path'], $b['path'] );
	} );

	return array_merge( $page_urls, $suite_urls );
}

/* ── Sitemap XML ────────────────────────────────────────────────────────── */

/**
 * Renders the full sitemap.xml content, with both EN and EL <url> blocks
 * and xhtml:link hreflang alternates per Google's specification.
 */
function chic_sitemap_render_xml( array $urls ): string {
	$site = rtrim( home_url( '/' ), '/' );
	$xsl  = esc_url( home_url( '/sitemap.xsl' ) );
	$o    = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$o   .= '<?xml-stylesheet type="text/xsl" href="' . $xsl . '"?>' . "\n";
	$o   .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
	$o   .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

	foreach ( $urls as $u ) {
		$en  = $site . $u['path'];
		$el  = $site . '/el' . $u['path'];
		$lm  = esc_html( $u['lastmod'] );
		$cf  = esc_html( $u['changefreq'] );
		$pri = esc_html( $u['priority'] );

		foreach ( [ $en, $el ] as $loc ) {
			$o .= "  <url>\n";
			$o .= '    <loc>' . esc_url( $loc ) . "</loc>\n";
			$o .= "    <lastmod>$lm</lastmod>\n";
			$o .= "    <changefreq>$cf</changefreq>\n";
			$o .= "    <priority>$pri</priority>\n";
			$o .= '    <xhtml:link rel="alternate" hreflang="en"        href="' . esc_url( $en ) . '"/>' . "\n";
			$o .= '    <xhtml:link rel="alternate" hreflang="el"        href="' . esc_url( $el ) . '"/>' . "\n";
			$o .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . esc_url( $en ) . '"/>' . "\n";
			$o .= "  </url>\n";
		}
	}

	$o .= '</urlset>' . "\n";
	return $o;
}

/* ── XSL stylesheet ─────────────────────────────────────────────────────── */

/**
 * Returns the sitemap.xsl content that transforms sitemap.xml into a
 * clean, human-readable HTML table when opened in a browser.
 *
 * Only EN (non-/el/) rows are shown; EL versions appear as clickable badges.
 */
function chic_sitemap_render_xsl(): string {
	// phpcs:disable Generic.Files.LineLength
	return '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="sm xhtml">
<xsl:output method="html" encoding="UTF-8" indent="yes"/>
<xsl:template match="/">
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Sitemap &#x2014; Chic Centre Suites</title>
<style>
:root{--ink:#1a1a1a;--soft:#6b6b6b;--line:#e5e5e5;--bg:#fafaf8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:\'Manrope\',system-ui,-apple-system,sans-serif;font-size:14px;line-height:1.6;color:var(--ink);background:var(--bg)}
.wrap{max-width:880px;margin:0 auto;padding:3.5rem 1.5rem 6rem}
.brand{font-size:.65rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--soft);display:block;margin-bottom:.6rem}
h1{font-size:1.55rem;font-weight:600;letter-spacing:.01em;margin-bottom:.35rem}
.meta{color:var(--soft);font-size:.82rem;margin-bottom:2.5rem}
.meta a{color:var(--soft);text-decoration:underline}
table{width:100%;border-collapse:collapse}
thead th{text-align:left;font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);padding:.5rem .75rem;border-bottom:2px solid var(--ink)}
td{padding:.6rem .75rem;border-bottom:1px solid var(--line);vertical-align:middle}
tr:hover td{background:#f3f3ef}
a{color:var(--ink);text-decoration:none}
a:hover{text-decoration:underline}
.badge{display:inline-flex;align-items:center;font-size:.62rem;font-weight:700;letter-spacing:.07em;padding:.12rem .42rem;border-radius:2px;margin-right:.25rem;line-height:1.4}
.b-en{background:#1a1a1a;color:#fff}
.b-el{background:#1d4e89;color:#fff}
.prio,.date{color:var(--soft);font-size:.82rem;white-space:nowrap}
@media(max-width:560px){.hide-sm{display:none}}
</style>
</head>
<body>
<div class="wrap">
<span class="brand">Chic Centre Suites</span>
<h1>Sitemap</h1>
<p class="meta">
  <xsl:value-of select="count(sm:urlset/sm:url[not(contains(sm:loc,\'/el/\'))])"/>
  <xsl:text> pages &#xB7; </xsl:text>
  <a href="/llms.txt">llms.txt</a>
  <xsl:text> &#xB7; </xsl:text>
  <a href="/el/llms.txt">llms.txt (EL)</a>
</p>
<table>
<thead><tr>
  <th>URL</th>
  <th class="hide-sm">Last modified</th>
  <th class="hide-sm">Priority</th>
  <th>Languages</th>
</tr></thead>
<tbody>
<xsl:apply-templates select="sm:urlset/sm:url[not(contains(sm:loc,\'/el/\'))]"/>
</tbody>
</table>
</div>
</body>
</html>
</xsl:template>
<xsl:template match="sm:url">
<tr>
  <td><a href="{sm:loc}"><xsl:value-of select="sm:loc"/></a></td>
  <td class="date hide-sm"><xsl:value-of select="substring(sm:lastmod,1,10)"/></td>
  <td class="prio hide-sm"><xsl:value-of select="sm:priority"/></td>
  <td>
    <a href="{xhtml:link[@hreflang=\'en\']/@href}" class="badge b-en">EN</a>
    <xsl:if test="xhtml:link[@hreflang=\'el\']">
      <a href="{xhtml:link[@hreflang=\'el\']/@href}" class="badge b-el">EL</a>
    </xsl:if>
  </td>
</tr>
</xsl:template>
</xsl:stylesheet>';
	// phpcs:enable Generic.Files.LineLength
}

/* ── llms.txt ───────────────────────────────────────────────────────────── */

/**
 * Markdown contact lines for each building (address + map link).
 */
function _chic_llms_contact_map_lines( bool $is_el ): string {
	$map_word = $is_el ? 'Χάρτης' : 'Map';
	$out      = '';

	foreach ( chic_home_buildings() as $b ) {
		$label = $is_el
			? t( $b['label'] )
			: $b['short_label'] . ': ' . $b['label'];
		$out .= '- ' . $label . ', [' . $map_word . '](' . $b['maps'] . ")\n";
	}

	return $out;
}

/**
 * Renders the llms.txt body for the given language.
 *
 * @param string $lang  'en' or 'el'
 */
function chic_llms_render( string $lang = 'en' ): string {
	$site       = rtrim( home_url( '/' ), '/' );
	$is_el      = ( 'el' === $lang );
	$pfx        = $is_el ? $site . '/el' : $site;
	$suites     = _chic_llms_suite_rows( $lang );
	$map_lines  = _chic_llms_contact_map_lines( $is_el );

	if ( $is_el ) {
		return "# Chic Centre Suites\n"
			. "\n"
			. "> Μπουτίκ διαμερίσματα βραχυχρόνιας μίσθωσης στην καρδιά της Αθήνας.\n"
			. "\n"
			. "Η Chic Centre Suites προσφέρει σύγχρονα, πλήρως εξοπλισμένα διαμερίσματα στο κέντρο της Αθήνας, λίγα βήματα από την Ακρόπολη, το Μοναστηράκι, την Πλατεία Συντάγματος και την πεζόδρομο Ερμού. Τα καταλύματα βρίσκονται σε τρία κτίρια στις οδούς Θησέως και Χαβρίου, ιδανικά για ζευγάρια, οικογένειες και μεμονωμένους ταξιδιώτες.\n"
			. "\n"
			. "## Βασικές Σελίδες\n"
			. "\n"
			. "- [Αρχική]($pfx/)\n"
			. "- [Εξερευνήστε την Αθήνα]($pfx/explore-athens/)\n"
			. "- [Αξιολογήσεις]($pfx/testimonials/)\n"
			. "- [Πολιτική Απορρήτου]($pfx/privacy-policy/)\n"
			. "- [Πολιτική Cookies]($pfx/cookie-policy/)\n"
			. "- [Όροι και Προϋποθέσεις]($pfx/terms-and-conditions/)\n"
			. "\n"
			. "## Σουίτες\n"
			. "\n"
			. "Τα καταλύματα βρίσκονται σε τρία κτίρια στις οδούς Θησέως και Χαβρίου, στην περιοχή Μοναστηρακίου/Συντάγματος (10562 Αθήνα).\n"
			. "\n"
			. "$suites\n"
			. "\n"
			. "## Γλώσσες\n"
			. "\n"
			. "Ο ιστότοπος είναι διαθέσιμος σε δύο γλώσσες:\n"
			. "- English: $site/\n"
			. "- Ελληνικά: $site/el/\n"
			. "\n"
			. "## Επικοινωνία\n"
			. "\n"
			. "- Email: contact@chiccentresuites.com\n"
			. "- Τηλέφωνο: +357 99674630 / +30 6982102221\n"
			. $map_lines
			. "\n"
			. "## Κράτηση\n"
			. "\n"
			. "Απευθείας κράτηση: https://direct-book.com/properties/chiccentresuitesathens\n";
	}

	return "# Chic Centre Suites\n"
		. "\n"
		. "> Boutique short-stay suites in the heart of Athens, Greece.\n"
		. "\n"
		. "Chic Centre Suites offers stylish, fully equipped serviced apartments in Athens city center, steps from the Acropolis, Monastiraki, Syntagma Square, and Ermou Street. Spread across three buildings on Thiseos and Chavriou streets (10562 Athens), the suites combine modern comfort with easy access to the city's cultural, historical, and commercial highlights. Ideal for couples, families, and solo travelers on short breaks or extended stays.\n"
		. "\n"
		. "## Key Pages\n"
		. "\n"
		. "- [Home]($pfx/)\n"
		. "- [Explore Athens]($pfx/explore-athens/)\n"
		. "- [Testimonials]($pfx/testimonials/)\n"
		. "- [Privacy Policy]($pfx/privacy-policy/)\n"
		. "- [Cookie Policy]($pfx/cookie-policy/)\n"
		. "- [Terms & Conditions]($pfx/terms-and-conditions/)\n"
		. "\n"
		. "## Suites\n"
		. "\n"
		. "Suites are spread across three buildings at Thiseos 11, Thiseos 13, and Chavriou 2, all in the Monastiraki/Syntagma area (10562 Athens).\n"
		. "\n"
		. "$suites\n"
		. "\n"
		. "## Languages\n"
		. "\n"
		. "This site is available in two languages:\n"
		. "- English: $site/\n"
		. "- Greek (Ελληνικά): $site/el/\n"
		. "\n"
		. "## Contact\n"
		. "\n"
		. "- Email: contact@chiccentresuites.com\n"
		. "- Phone: +357 99674630 / +30 6982102221\n"
		. $map_lines
		. "\n"
		. "## Booking\n"
		. "\n"
		. "Direct booking: https://direct-book.com/properties/chiccentresuitesathens\n";
}

/* ── Internal: suite rows for llms.txt ──────────────────────────────────── */

function _chic_llms_suite_rows( string $lang ): string {
	$is_el     = ( 'el' === $lang );
	$site      = rtrim( home_url( '/' ), '/' );
	$pfx       = $is_el ? $site . '/el' : $site;
	$all_data  = _chic_suite_all_data();
	$buildings = chic_home_buildings();

	$el_building_labels = [
		'Thiseos 11'  => 'Θησέως 11',
		'Thiseos 13'  => 'Θησέως 13',
		'Chavriou 2'  => 'Χαβρίου 2',
	];

	$out = '';

	foreach ( $buildings as $b ) {
		$heading = $is_el
			? ( $el_building_labels[ $b['short_label'] ] ?? $b['short_label'] )
			: $b['short_label'];

		$out .= '### ' . $heading . "\n\n";

		$suites = chic_home_get_suites( $b['term'] );

		foreach ( $suites as $suite ) {
			$title = $suite['title'];
			$key   = strtolower( trim( $title ) );
			$data  = $all_data[ $key ] ?? null;
			$url   = $pfx . wp_make_link_relative( $suite['permalink'] );

			$display_title = $is_el
				? _chic_llms_translate_suite_title( $title )
				: $title;

			$summary_parts = [];
			if ( $data ) {
				if ( ! empty( $data['size'] ) )     $summary_parts[] = $data['size'] . 'm²';
				if ( ! empty( $data['capacity'] ) ) $summary_parts[] = strtolower( $data['capacity'] );
				if ( ! empty( $data['highlight'] ) ) $summary_parts[] = $data['highlight'];
			}

			$line = '- [' . $display_title . '](' . $url . ')';
			if ( $summary_parts ) $line .= ', ' . implode( ', ', $summary_parts );
			$out .= $line . "\n";
		}

		$out .= "\n";
	}

	return trim( $out );
}

/**
 * Mirrors chic_translate_suite_title() without a request-context dependency.
 * "Forest Suite" → "Σουίτα Forest", "Suite 3" → "Σουίτα 3".
 */
function _chic_llms_translate_suite_title( string $title ): string {
	if ( preg_match( '/^(.+?)\s+Suite$/i', $title, $m ) ) {
		return 'Σουίτα ' . $m[1];
	}
	if ( preg_match( '/^Suite\s+(.+)$/i', $title, $m ) ) {
		return 'Σουίτα ' . $m[1];
	}
	return $title;
}
