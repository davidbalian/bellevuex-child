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
	$do_probe  = $verified && ! empty( $_GET['probe'] );
	$do_repair = $verified && ! empty( $_GET['fix_shim'] );
	$repaired  = null;

	if ( $do_repair ) {
		$repaired = chic_el_ensure_dir_shim();
	}

	$probe_url  = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'probe' => '1', '_wpnonce' => $nonce ] ) );
	$repair_url = esc_url( add_query_arg( [ 'page' => 'chic-sitemap-llms', 'fix_shim' => '1', 'probe' => '1', '_wpnonce' => $nonce ] ) );
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

		<?php if ( null !== $repaired ) : ?>
			<div class="notice notice-<?php echo $repaired ? 'success' : 'error'; ?>" style="margin-top:1.5rem;">
				<p><?php echo $repaired
					? '&#10003; Wrote <code>el/index.php</code>. Open <code>/el</code> in a new tab to confirm.'
					: '&#10007; Could not write <code>el/index.php</code>. See the directory row below for why.'; ?></p>
			</div>
		<?php endif; ?>

		<?php chic_el_render_status( $do_probe ); ?>

		<p style="margin-top:1rem;">
			<a class="button button-secondary" href="<?php echo $probe_url; ?>">
				&#128260; Test <code>/el</code> now
			</a>
			&nbsp;
			<a class="button button-secondary" href="<?php echo $repair_url; ?>">
				&#128295; Repair <code>el/index.php</code>
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
 * Request /el over HTTP from the server itself and report the status code.
 * Note that some hosts block loopback requests, in which case this reports a
 * transport error rather than a real result. Always confirm in a browser too.
 *
 * @return array{url:string,code:int,error:string}
 */
function chic_el_probe_route(): array {
	$url = home_url( '/el' );
	$res = wp_remote_get( $url, [
		'timeout'     => 10,
		'redirection' => 3,
		'sslverify'   => false,
		'headers'     => [ 'Cache-Control' => 'no-cache' ],
	] );

	if ( is_wp_error( $res ) ) {
		return [ 'url' => $url, 'code' => 0, 'error' => $res->get_error_message() ];
	}

	return [
		'url'   => $url,
		'code'  => (int) wp_remote_retrieve_response_code( $res ),
		'error' => '',
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

	// Row 1: the directory itself.
	echo '<tr><td><code>el/</code> directory</td>';
	if ( ! $s['dir_exists'] ) {
		echo chic_el_status_cell( 'ok', 'Not present' );
		echo '<td>Nothing shadows the rewrite rule. No shim needed.</td>';
	} elseif ( $s['dir_writable'] ) {
		echo chic_el_status_cell( 'warn', 'Exists, writable' );
		echo '<td>Shadows the <code>^el/?$</code> rewrite rule. Needs a guard below.</td>';
	} else {
		echo chic_el_status_cell( 'bad', 'Exists, NOT writable' );
		echo '<td>PHP cannot write the shim here. Fix permissions on <code>' . esc_html( ABSPATH . 'el' ) . '</code>.</td>';
	}
	echo '</tr>';

	// Row 2: the index.php shim, the guard that works on every server.
	echo '<tr><td><code>el/index.php</code> shim</td>';
	if ( ! $s['dir_exists'] ) {
		echo chic_el_status_cell( 'info', 'Not applicable' );
		echo '<td>Only written when the directory exists.</td>';
	} elseif ( $s['shim_ours'] ) {
		echo chic_el_status_cell( 'ok', 'Present' );
		echo '<td>Boots WordPress for <code>/el/</code>. Works on Apache, Nginx and LiteSpeed.</td>';
	} elseif ( $s['shim_exists'] ) {
		echo chic_el_status_cell( 'warn', 'Present, not ours' );
		echo '<td>A hand-written <code>index.php</code> is here. Left untouched.</td>';
	} else {
		echo chic_el_status_cell( 'bad', 'Missing' );
		echo '<td>This is the likely cause of a 403. Use Repair below.</td>';
	}
	echo '</tr>';

	// Row 3: the Apache-only fallback.
	echo '<tr><td><code>el/.htaccess</code></td>';
	if ( ! $s['dir_exists'] ) {
		echo chic_el_status_cell( 'info', 'Not applicable' );
		echo '<td>Only written when the directory exists.</td>';
	} elseif ( $s['ht_exists'] ) {
		echo chic_el_status_cell( 'ok', 'Present' );
		echo '<td>Secondary guard. Ignored by Nginx and by Apache with <code>AllowOverride None</code>.</td>';
	} else {
		echo chic_el_status_cell( 'warn', 'Missing' );
		echo '<td>Optional. The shim above covers more servers.</td>';
	}
	echo '</tr>';

	// Row 4: what the server actually returns.
	echo '<tr><td>Live <code>/el</code> response</td>';
	if ( ! $probe ) {
		echo chic_el_status_cell( 'info', 'Not tested' );
		echo '<td>Use Test <code>/el</code> now below.</td>';
	} else {
		$p = chic_el_probe_route();
		if ( 0 === $p['code'] ) {
			echo chic_el_status_cell( 'warn', 'No response' );
			echo '<td>Loopback request failed: ' . esc_html( $p['error'] ) . '. Check <code>/el</code> in a browser instead.</td>';
		} elseif ( 200 === $p['code'] ) {
			echo chic_el_status_cell( 'ok', 'HTTP 200' );
			echo '<td><code>/el</code> is serving correctly.</td>';
		} elseif ( 403 === $p['code'] ) {
			echo chic_el_status_cell( 'bad', 'HTTP 403' );
			echo '<td>Still forbidden. The server is serving the folder, not WordPress.</td>';
		} else {
			echo chic_el_status_cell( 'bad', 'HTTP ' . $p['code'] );
			echo '<td>Unexpected status from <code>' . esc_html( $p['url'] ) . '</code>.</td>';
		}
	}
	echo '</tr>';

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
		ABSPATH . 'el/llms.txt' => $txt_el,
	];

	echo '<h2 style="margin-top:1.5rem;">' . ( $dry_run ? 'Dry Run — Preview' : 'Results' ) . '</h2>';

	// Ensure el/ directory exists before writing el/llms.txt.
	// The real directory satisfies !-d and bypasses WordPress's rewrite rules,
	// so /el/ would 403. Two guards against that:
	//   1. .htaccess routes back to index.php (Apache with AllowOverride only).
	//   2. index.php shim boots WP directly (works on Apache, Nginx, LiteSpeed).
	// See chic_el_ensure_dir_shim() in inc/i18n.php.
	if ( ! $dry_run ) {
		wp_mkdir_p( ABSPATH . 'el' );
		$htaccess = ABSPATH . 'el/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Options -Indexes\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^ /index.php [L]\n</IfModule>\n", LOCK_EX );
		}
		chic_el_ensure_dir_shim();
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
		foreach ( $files as $path => $content ) {
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
