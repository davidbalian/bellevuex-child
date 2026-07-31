<?php
/**
 * Admin Tool: Sitemap & LLMs.txt Generator
 * Tools → Sitemap & LLMs.txt
 *
 * Writes four static files into ABSPATH on demand:
 *   sitemap.xml   — Sitemaps 0.9 + xhtml:link hreflang (bilingual, XSL-styled)
 *   sitemap.xsl   — XSL stylesheet for human-readable browser view
 *   llms.txt      — English site summary for LLM crawlers
 *   el/llms.txt   — Greek companion
 *
 * Static files are served by Apache before WP routing, so /sitemap.xml
 * shadows any AIOSEO-generated route without touching plugin settings.
 */
defined( 'ABSPATH' ) || exit;

/* ── Admin page registration ────────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_management_page(
		'Sitemap & LLMs.txt',
		'Sitemap & LLMs.txt',
		'manage_options',
		'chic-sitemap-llms',
		'chic_sitemap_llms_render_page'
	);
} );

/* ── Append Sitemap: line to WP virtual robots.txt ─────────────────────── */

add_filter( 'robots_txt', function ( string $output ): string {
	$sitemap = home_url( '/sitemap.xml' );
	if ( false === strpos( $output, 'Sitemap:' ) ) {
		$output .= "\nSitemap: $sitemap\n";
	}
	return $output;
} );

/* ── Render page ────────────────────────────────────────────────────────── */

function chic_sitemap_llms_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$nonce    = wp_create_nonce( 'chic_sitemap_llms_run' );
	$dry_run  = ! empty( $_GET['dry_run'] ) ? '1' : '0';
	$run_live = isset( $_GET['run'] )
		&& wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ),
			'chic_sitemap_llms_run'
		);

	$dry_url  = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'dry_run' => '1', 'run' => '1', '_wpnonce' => $nonce ] ) );
	$live_url = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'dry_run' => '0', 'run' => '1', '_wpnonce' => $nonce ] ) );

	// Diagnostics actions. Both are nonce-guarded against the same action.
	$verified  = isset( $_GET['_wpnonce'] ) && wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
		'chic_sitemap_llms_run'
	);
	$do_probe = $verified && ! empty( $_GET['probe'] );
	$removed  = ( $verified && ! empty( $_GET['remove_el'] ) ) ? chic_el_remove_dir() : null;

	$probe_url  = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'probe' => '1', '_wpnonce' => $nonce ] ) );
	$remove_url = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'remove_el' => '1', 'probe' => '1', '_wpnonce' => $nonce ] ) );
	?>
	<div class="wrap">
		<h1>Sitemap &amp; LLMs.txt</h1>
		<p>
			Generates and writes four static files into the WordPress root (<code><?php echo esc_html( ABSPATH ); ?></code>):
			<code>sitemap.xml</code>, <code>sitemap.xsl</code>, <code>llms.txt</code>, <code>el/llms.txt</code>.
		</p>
		<p>
			Apache serves these files before WP routing, so <code>/sitemap.xml</code> is
			served directly without going through WordPress. Re-run after publishing new pages or suites.
		</p>
		<p style="margin-top:1rem;">
			<a class="button button-secondary" href="<?php echo $dry_url; ?>">
				&#128065; Dry Run (preview only)
			</a>
			&nbsp;
			<a class="button button-primary" href="<?php echo $live_url; ?>"
				onclick="return confirm('Write sitemap.xml, sitemap.xsl, llms.txt, and el/llms.txt to the WP root?');">
				&#9654; Run Live
			</a>
		</p>
		<?php if ( $run_live ) : ?>
			<?php chic_sitemap_llms_run( '1' === $dry_run ); ?>
		<?php endif; ?>

		<?php if ( null !== $removed ) : ?>
			<div class="notice notice-<?php echo $removed['ok'] ? 'success' : 'error'; ?>" style="margin-top:1.5rem;">
				<p><?php echo ( $removed['ok'] ? '&#10003; ' : '&#10007; ' ) . esc_html( $removed['msg'] ); ?></p>
			</div>
		<?php endif; ?>

		<?php chic_el_render_status( $do_probe ); ?>

		<p style="margin-top:1rem;">
			<a class="button button-secondary" href="<?php echo $probe_url; ?>">
				&#128260; Test now
			</a>
			&nbsp;
			<a class="button button-primary" href="<?php echo $remove_url; ?>"
				onclick="return confirm('Delete the el/ directory and its generated files? Greek llms.txt is already served from the database, so nothing is lost.');">
				&#128465; Remove <code>el/</code> directory
			</a>
			&nbsp;
			<a class="button button-secondary" href="<?php echo esc_url( home_url( '/el' ) ); ?>" target="_blank" rel="noopener">
				&#8599; Open <code>/el</code> in a tab
			</a>
		</p>
	</div>
	<?php
}

/* ── /el route diagnostics ──────────────────────────────────────────────── */

/**
 * Inspect the physical el/ directory and the guards that keep /el/ routable.
 *
 * @return array{dir_exists:bool,dir_writable:bool,shim_exists:bool,shim_ours:bool,ht_exists:bool}
 */
function chic_el_shim_status(): array {
	$dir  = ABSPATH . 'el';
	$shim = $dir . '/index.php';

	$status = [
		'dir_exists'   => is_dir( $dir ),
		'dir_writable' => is_dir( $dir ) && is_writable( $dir ),
		'shim_exists'  => is_file( $shim ),
		'shim_ours'    => false,
		'ht_exists'    => is_file( $dir . '/.htaccess' ),
	];

	if ( $status['shim_exists'] ) {
		$contents           = (string) file_get_contents( $shim );
		$status['shim_ours'] = false !== strpos( $contents, 'chic_el_ensure_dir_shim' );
	}

	return $status;
}

/**
 * Request a URL over HTTP from the server itself and report what came back.
 *
 * Sends a real browser user agent because host firewalls (SiteGround, Cloudflare,
 * Wordfence) routinely 403 the default WordPress user agent on loopback requests.
 * Without this the probe reports a 403 that real visitors never see.
 *
 * Captures the Server header and a body snippet so a firewall block can be told
 * apart from a genuine server-level directory refusal.
 *
 * @return array{url:string,code:int,error:string,server:string,snippet:string}
 */
function chic_el_probe_route( string $path = '/el' ): array {
	$url = home_url( $path );
	$res = wp_remote_get( $url, [
		'timeout'     => 10,
		'redirection' => 3,
		'sslverify'   => false,
		'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
		'headers'     => [
			'Cache-Control' => 'no-cache',
			'Accept'        => 'text/html,application/xhtml+xml',
		],
	] );

	if ( is_wp_error( $res ) ) {
		return [ 'url' => $url, 'code' => 0, 'error' => $res->get_error_message(), 'server' => '', 'snippet' => '' ];
	}

	$body    = (string) wp_remote_retrieve_body( $res );
	$snippet = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $body ) ) );

	return [
		'url'     => $url,
		'code'    => (int) wp_remote_retrieve_response_code( $res ),
		'error'   => '',
		'server'  => (string) wp_remote_retrieve_header( $res, 'server' ),
		'snippet' => mb_substr( $snippet, 0, 300 ),
	];
}

/** Green tick, red cross or amber warning cell. */
function chic_el_status_cell( string $state, string $text ): string {
	$map = [
		'ok'   => [ '#1d7a1d', '&#10003; ' ],
		'bad'  => [ '#d63638', '&#10007; ' ],
		'warn' => [ '#996800', '&#9888; ' ],
		'info' => [ '#2271b1', '&#9432; ' ],
	];
	[ $colour, $icon ] = $map[ $state ] ?? $map['info'];
	return '<td><span style="color:' . $colour . ';">' . $icon . esc_html( $text ) . '</span></td>';
}

/**
 * Render the diagnostics table. Runs the live HTTP probe only when asked,
 * because a blocked loopback request can stall the page for the full timeout.
 */
function chic_el_render_status( bool $probe ): void {
	$s = chic_el_shim_status();

	echo '<h2 style="margin-top:2rem;">Greek route status (<code>/el</code>)</h2>';
	echo '<table class="widefat striped" style="margin-top:.5rem;max-width:720px;">';
	echo '<thead><tr><th style="width:200px;">Check</th><th>Result</th><th>Meaning</th></tr></thead><tbody>';

	// Row 1: the directory. Its mere existence is the whole problem.
	echo '<tr><td><code>el/</code> directory</td>';
	if ( ! $s['dir_exists'] ) {
		echo chic_el_status_cell( 'ok', 'Not present' );
		echo '<td>Correct. <code>/el/</code> routes through WordPress normally.</td>';
	} else {
		echo chic_el_status_cell( 'bad', 'Exists' );
		echo '<td>This is the cause of the 403. WordPress skips existing directories '
			. '(<code>RewriteCond !-d</code>), so Apache serves the folder and refuses. '
			. 'Use <strong>Remove <code>el/</code> directory</strong> below.</td>';
	}
	echo '</tr>';

	// Row 2: Greek llms.txt, now served from an option rather than disk.
	echo '<tr><td><code>el/llms.txt</code> content</td>';
	$len = strlen( (string) get_option( 'chic_llms_el', '' ) );
	if ( $len > 0 ) {
		echo chic_el_status_cell( 'ok', number_format( $len ) . ' bytes stored' );
		echo '<td>Served virtually from option <code>chic_llms_el</code>. No file on disk needed.</td>';
	} else {
		echo chic_el_status_cell( 'warn', 'Not generated' );
		echo '<td>Press <strong>Run Live</strong> above to generate it.</td>';
	}
	echo '</tr>';

	// Rows 3 and 4: what the server actually returns for each Greek URL.
	foreach ( [ '/el', '/el/llms.txt' ] as $path ) {
		echo '<tr><td>Live <code>' . esc_html( $path ) . '</code> response</td>';

		if ( ! $probe ) {
			echo chic_el_status_cell( 'info', 'Not tested' );
			echo '<td>Use <strong>Test now</strong> below.</td></tr>';
			continue;
		}

		$p    = chic_el_probe_route( $path );
		$meta = '';
		if ( '' !== $p['server'] ) {
			$meta .= '<br><span style="color:#646970;font-size:11px;">Served by: '
				. esc_html( $p['server'] ) . '</span>';
		}
		if ( '' !== $p['snippet'] && 200 !== $p['code'] ) {
			$meta .= '<br><span style="color:#646970;font-size:11px;">Body: '
				. esc_html( mb_substr( $p['snippet'], 0, 160 ) ) . '</span>';
		}

		if ( 0 === $p['code'] ) {
			echo chic_el_status_cell( 'warn', 'No response' );
			echo '<td>Loopback request failed: ' . esc_html( $p['error'] )
				. '. Check the URL in a browser instead.</td>';
		} elseif ( 200 === $p['code'] ) {
			echo chic_el_status_cell( 'ok', 'HTTP 200' );
			echo '<td>Serving correctly.' . $meta . '</td>';
		} elseif ( 403 === $p['code'] ) {
			echo chic_el_status_cell( 'bad', 'HTTP 403' );
			echo '<td>Forbidden. If the body mentions cPanel or WebPros the origin server '
				. 'is refusing the directory, so removing <code>el/</code> is the fix.' . $meta . '</td>';
		} else {
			echo chic_el_status_cell( 'bad', 'HTTP ' . $p['code'] );
			echo '<td>Unexpected status from <code>' . esc_html( $p['url'] ) . '</code>.' . $meta . '</td>';
		}
		echo '</tr>';
	}

	echo '</tbody></table>';
}

/* ── Runner ─────────────────────────────────────────────────────────────── */

function chic_sitemap_llms_run( bool $dry_run ): void {
	require_once __DIR__ . '/../tools/generate-sitemap-llms.php';

	$urls = chic_sitemap_collect_urls();
	$xml  = chic_sitemap_render_xml( $urls );
	$xsl  = chic_sitemap_render_xsl();
	$txt_en = chic_llms_render( 'en' );
	$txt_el = chic_llms_render( 'el' );

	$files = [
		ABSPATH . 'sitemap.xml' => $xml,
		ABSPATH . 'sitemap.xsl' => $xsl,
		ABSPATH . 'llms.txt'    => $txt_en,
	];

	echo '<h2 style="margin-top:1.5rem;">' . ( $dry_run ? 'Dry Run — Preview' : 'Results' ) . '</h2>';

	// Greek llms.txt is stored in an option and served virtually at /el/llms.txt
	// by the route in inc/i18n.php. It is deliberately NOT written to disk: a real
	// el/ directory makes Apache refuse every request under /el/ with a 403,
	// because WordPress's root .htaccess skips existing directories (!-d).
	if ( ! $dry_run ) {
		update_option( 'chic_llms_el', $txt_el, false );
	}

	// Results table.
	echo '<table class="widefat striped" style="margin-top:1rem;max-width:720px;">';
	echo '<thead><tr><th>File</th><th>Size</th><th>Status</th></tr></thead><tbody>';

	foreach ( $files as $path => $content ) {
		$rel    = str_replace( ABSPATH, '', $path );
		$size   = number_format( strlen( $content ) ) . ' bytes';
		$status = '';

		if ( $dry_run ) {
			$status = '<span style="color:#2271b1;">&#9432; Preview only — not written</span>';
		} else {
			$written = file_put_contents( $path, $content, LOCK_EX );
			if ( false !== $written ) {
				$status = '<span style="color:#1d7a1d;">&#10003; Written (' . number_format( $written ) . ' bytes)</span>';
			} else {
				$status = '<span style="color:#d63638;">&#10007; Failed — check directory permissions</span>';
			}
		}

		echo '<tr>';
		echo '<td><code>' . esc_html( $rel ) . '</code></td>';
		echo '<td>' . esc_html( $size ) . '</td>';
		echo '<td>' . $status . '</td>';
		echo '</tr>';
	}

	// Greek llms.txt lives in an option, not on disk.
	echo '<tr>';
	echo '<td><code>el/llms.txt</code><br><span style="color:#646970;font-size:11px;">virtual, served by WordPress</span></td>';
	echo '<td>' . esc_html( number_format( strlen( $txt_el ) ) . ' bytes' ) . '</td>';
	echo '<td>' . ( $dry_run
		? '<span style="color:#2271b1;">&#9432; Preview only, not saved</span>'
		: '<span style="color:#1d7a1d;">&#10003; Saved to option <code>chic_llms_el</code></span>' );
	echo '</td></tr>';

	echo '</tbody></table>';

	if ( ! $dry_run ) {
		echo '<p style="margin-top:1rem;">';
		echo '<a href="' . esc_url( home_url( '/sitemap.xml' ) ) . '" target="_blank">&#8594; View sitemap.xml</a> &nbsp; ';
		echo '<a href="' . esc_url( home_url( '/llms.txt' ) ) . '" target="_blank">&#8594; View llms.txt</a> &nbsp; ';
		echo '<a href="' . esc_url( home_url( '/el/llms.txt' ) ) . '" target="_blank">&#8594; View el/llms.txt</a>';
		echo '</p>';
	}

	// Dry-run: show generated content in collapsible <details> blocks.
	if ( $dry_run ) {
		foreach ( $files + [ ABSPATH . 'el/llms.txt' => $txt_el ] as $path => $content ) {
			$rel = str_replace( ABSPATH, '', $path );
			echo '<details style="margin-top:1.5rem;">';
			echo '<summary style="cursor:pointer;font-weight:600;">' . esc_html( $rel ) . '</summary>';
			echo '<pre style="background:#f6f7f7;border:1px solid #e0e0e0;padding:1rem;margin-top:.5rem;overflow:auto;font-size:11px;max-height:400px;">';
			echo esc_html( $content );
			echo '</pre></details>';
		}
	}

	echo '<p style="margin-top:1.5rem;"><strong>' . count( $urls ) . '</strong> pages enumerated (' . count( $urls ) * 2 . ' URL entries including EL alternates).</p>';

	// Url list for reference.
	echo '<details style="margin-top:.75rem;">';
	echo '<summary style="cursor:pointer;">Enumerated paths</summary>';
	echo '<ul style="margin-top:.5rem;font-size:12px;line-height:1.8;">';
	foreach ( $urls as $u ) {
		echo '<li><code>' . esc_html( $u['path'] ) . '</code>';
		echo ' <span style="color:#6b6b6b;font-size:11px;">priority=' . esc_html( $u['priority'] ) . ', ' . esc_html( $u['changefreq'] ) . ', ' . esc_html( $u['lastmod'] ) . '</span></li>';
	}
	echo '</ul></details>';
}
