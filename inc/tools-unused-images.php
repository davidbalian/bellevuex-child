<?php
/**
 * Admin Tool: Unused Images
 * Tools → Unused Images
 *
 * Scans post content, postmeta, and wp_options for image attachment IDs,
 * then lists every image attachment that is not referenced anywhere.
 * Delete in two safe steps: Trash first, then permanently empty the trash.
 */
defined( 'ABSPATH' ) || exit;

// ── Theme-hardcoded URL safelist ──────────────────────────────────────────────

function chic_unused_theme_urls(): array {
	return [
		// functions.php — CardLink payment logo
		'https://chiccentresuites.com/wp-content/uploads/cardlinkLogo.svg',
		// inc/header-nav.php — placeholder + site logo
		'https://davidb1553.sg-host.com/wp-content/uploads/off-your-first-reservation.jpg',
		'https://davidb1553.sg-host.com/wp-content/uploads/only-logo-favicon.png',
		// inc/home-data.php — building images
		'https://davidb1553.sg-host.com/wp-content/uploads/2-chic-centre-suites-athens-thisseos-11-common-seating-area-athens.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-thisseos-13-corridor-athens.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-chavriou-2-reception-desk-athens.webp',
		// inc/home-data.php — fallback hero slides
		'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-suite-no-balcony-ginger-main-room-athens.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/10-chic-centre-suites-athens-nearby-attractions.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/3-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
		// inc/explore-athens-data.php — Athens attraction cards
		'https://davidb1553.sg-host.com/wp-content/uploads/3-chic-centre-suites-athens-nearby-attractions.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/2-chic-centre-suites-athens-nearby-attractions.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/4-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/5-chic-centre-suites-athens-nearby-restaurants.webp',
		'https://davidb1553.sg-host.com/wp-content/uploads/1-chic-centre-suites-athens-nearby-attractions.webp',
		// page-explore-athens.php — static hero
		'https://davidb1553.sg-host.com/wp-content/uploads/7-chic-centre-suites-athens-nearby-attractions-acropolis.webp',
	];
}

// ── Filename pattern safelist (used dynamically — no resolvable ID) ───────────

function chic_unused_is_pattern_safe( string $filename ): bool {
	// inc/testimonials-data.php uses flag-{country-code}.png dynamically
	if ( preg_match( '/^flag-[a-z]{2}\.(png|jpg|jpeg|webp|gif)$/i', $filename ) ) return true;
	if ( $filename === 'cardlinkLogo.svg' ) return true;
	return false;
}

// ── URL normalizer (staging ↔ prod → current site upload URL) ─────────────────

function chic_unused_normalize_url( string $url ): string {
	$upload_dir  = wp_upload_dir();
	$upload_base = trailingslashit( $upload_dir['baseurl'] );
	$upload_path = '/wp-content/uploads/';

	foreach ( [
		'https://chiccentresuites.com',
		'https://davidb1553.sg-host.com',
		'http://chiccentresuites.com',
		'http://davidb1553.sg-host.com',
	] as $host ) {
		if ( str_starts_with( $url, $host . $upload_path ) ) {
			return $upload_base . substr( $url, strlen( $host . $upload_path ) );
		}
	}

	return $url;
}

// ── ID extractor from raw text (post_content) ────────────────────────────────

function chic_unused_ids_from_text( string $text, array $all_ids ): array {
	$found = [];

	// class="wp-image-NNN" — classic editor + Gutenberg <img> tags
	if ( preg_match_all( '/wp-image-(\d+)/', $text, $m ) ) {
		foreach ( $m[1] as $id ) {
			if ( isset( $all_ids[ (int) $id ] ) ) $found[] = (int) $id;
		}
	}

	// Gutenberg block comments — "id":NNN inside image/gallery/cover/media-text
	if ( preg_match_all( '/<!--\s*wp:(?:image|gallery|cover|media-text)\s[^-]*?-->/s', $text, $blocks ) ) {
		foreach ( $blocks[0] as $block ) {
			if ( preg_match_all( '/"id"\s*:\s*(\d+)/', $block, $bm ) ) {
				foreach ( $bm[1] as $id ) {
					if ( isset( $all_ids[ (int) $id ] ) ) $found[] = (int) $id;
				}
			}
		}
	}

	// Gutenberg gallery block — "ids":[N,N,N]
	if ( preg_match_all( '/"ids"\s*:\s*\[([^\]]+)\]/', $text, $gm ) ) {
		foreach ( $gm[1] as $list ) {
			foreach ( explode( ',', $list ) as $id ) {
				$id = (int) trim( $id );
				if ( $id > 0 && isset( $all_ids[ $id ] ) ) $found[] = $id;
			}
		}
	}

	// wp-content/uploads/ URLs → normalize host → resolve to attachment ID
	if ( preg_match_all( '#https?://[^\s"\'<>)]+/wp-content/uploads/[^\s"\'<>)]+\.(?:jpe?g|png|gif|webp|svg|avif)#i', $text, $um ) ) {
		foreach ( $um[0] as $url ) {
			$id = attachment_url_to_postid( chic_unused_normalize_url( $url ) );
			if ( $id > 0 && isset( $all_ids[ $id ] ) ) $found[] = $id;
		}
	}

	return array_unique( $found );
}

// ── ID extractor from arbitrary serialized/JSON/scalar value ──────────────────

function chic_unused_ids_from_value( $value, array $all_ids, int $depth = 0 ): array {
	if ( $depth > 8 || $value === null || $value === false || $value === '' ) return [];

	$found = [];

	// Plain integer
	if ( is_int( $value ) ) {
		return ( $value > 0 && isset( $all_ids[ $value ] ) ) ? [ $value ] : [];
	}

	// Array or object — recurse
	if ( is_array( $value ) || is_object( $value ) ) {
		foreach ( (array) $value as $v ) {
			$found = array_merge( $found, chic_unused_ids_from_value( $v, $all_ids, $depth + 1 ) );
		}
		return array_unique( $found );
	}

	if ( ! is_string( $value ) ) return [];

	// Comma-separated integers — mphb_gallery, _product_image_gallery
	if ( preg_match( '/^\d+(?:,\d+)*$/', trim( $value ) ) ) {
		foreach ( explode( ',', $value ) as $id ) {
			$id = (int) trim( $id );
			if ( $id > 0 && isset( $all_ids[ $id ] ) ) $found[] = $id;
		}
		return array_unique( $found );
	}

	// Plain integer string
	if ( ctype_digit( trim( $value ) ) ) {
		$id = (int) $value;
		return ( $id > 0 && isset( $all_ids[ $id ] ) ) ? [ $id ] : [];
	}

	// PHP serialized
	if ( is_serialized( $value ) ) {
		$unserialized = @unserialize( $value, [ 'allowed_classes' => false ] );
		if ( $unserialized !== false ) {
			return chic_unused_ids_from_value( $unserialized, $all_ids, $depth + 1 );
		}
	}

	// JSON array or object
	if ( strlen( $value ) > 1 && ( $value[0] === '[' || $value[0] === '{' ) ) {
		$decoded = json_decode( $value, true );
		if ( $decoded !== null ) {
			return chic_unused_ids_from_value( $decoded, $all_ids, $depth + 1 );
		}
	}

	// String containing uploads URLs
	if ( strpos( $value, 'wp-content/uploads/' ) !== false ) {
		return chic_unused_ids_from_text( $value, $all_ids );
	}

	return [];
}

// ── Helper: image attachment trash count ──────────────────────────────────────

function chic_unused_current_trash_count(): int {
	global $wpdb;
	return (int) $wpdb->get_var( "
		SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_type = 'attachment'
		  AND post_mime_type LIKE 'image/%'
		  AND post_status = 'trash'
	" );
}

// ── Admin page registration ───────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_management_page(
		'Unused Images',
		'Unused Images',
		'manage_options',
		'chic-unused-images',
		'chic_unused_images_render_page'
	);
} );

// ── Admin page render ─────────────────────────────────────────────────────────

function chic_unused_images_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$nonce     = wp_create_nonce( 'chic_unused_images' );
	$trash_cnt = chic_unused_current_trash_count();
	?>
	<div class="wrap" id="chic-unused-wrap">
		<h1>Unused Images</h1>
		<p>
			Scans all post content, post meta, and site options to find images in the media library
			that are not referenced anywhere. Move candidates to <strong>Trash</strong> first
			(recoverable via Media&nbsp;→&nbsp;Trash), then <strong>permanently delete</strong> the trash
			to free disk space.
		</p>

		<div id="chic-unused-controls">
			<button id="btn-scan"  class="button button-primary">&#9654; Scan Library</button>
			<button id="btn-trash" class="button" disabled style="margin-left:8px;">&#128465; Move Selected to Trash</button>
			<button id="btn-empty" class="button" style="margin-left:8px; color:#b32d2e;">
				&#9888;&nbsp;Empty Image Trash
				<span id="trash-cnt" style="<?php echo $trash_cnt ? '' : 'display:none;'; ?>font-weight:600;">
					(<?php echo (int) $trash_cnt; ?>)
				</span>
			</button>
			<button id="btn-clear" class="button" style="margin-left:16px; float:right;">Clear log</button>
			<span id="chic-unused-counter"></span>
		</div>

		<pre id="chic-unused-log"></pre>

		<div id="chic-unused-results" style="display:none; margin-top:16px;">
			<p id="chic-unused-summary" style="font-weight:600; font-size:14px;"></p>
			<table id="chic-unused-table" class="widefat striped" style="table-layout:fixed;">
				<thead>
					<tr>
						<th style="width:28px;"><input type="checkbox" id="chk-all" title="Select all"></th>
						<th style="width:72px;">Preview</th>
						<th>Filename</th>
						<th style="width:80px;">Size</th>
						<th style="width:110px;">Dimensions</th>
						<th style="width:100px;">Uploaded</th>
						<th style="width:44px;"></th>
					</tr>
				</thead>
				<tbody id="chic-unused-tbody"></tbody>
			</table>
		</div>
	</div>

	<style>
		#chic-unused-wrap { max-width: 1000px; }
		#chic-unused-controls {
			display: flex; align-items: center; flex-wrap: wrap; gap: 4px;
			margin: 16px 0 12px;
		}
		#chic-unused-counter {
			margin-left: 12px; flex: 1; text-align: right;
			font-style: italic; color: #777;
		}
		#chic-unused-log {
			background: #1e1e1e; color: #d4d4d4; font-size: 12px; line-height: 1.6;
			padding: 14px 16px; border-radius: 4px; height: 300px;
			overflow-y: auto; white-space: pre-wrap; word-break: break-all;
			margin-bottom: 4px;
		}
		#chic-unused-log .lvl-head { color: #9cdcfe; font-weight: bold; }
		#chic-unused-log .lvl-ok   { color: #4ec9b0; }
		#chic-unused-log .lvl-warn { color: #dcdcaa; }
		#chic-unused-log .lvl-err  { color: #f44747; font-weight: bold; }
		#chic-unused-log .lvl-info { color: #d4d4d4; }
		#chic-unused-log .lvl-dim  { color: #6a9955; }
		#chic-unused-log .lvl-done { color: #4ec9b0; font-weight: bold; font-size: 13px; }
		#chic-unused-table img { width: 60px; height: 60px; object-fit: cover; border-radius: 2px; }
		#chic-unused-table td  { vertical-align: middle; }
		#chic-unused-table td:nth-child(3) {
			font-family: monospace; font-size: 11px;
			overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
		}
	</style>

	<script>
	(function () {
		var ajaxurl    = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce      = <?php echo wp_json_encode( $nonce ); ?>;
		var btnScan    = document.getElementById('btn-scan');
		var btnTrash   = document.getElementById('btn-trash');
		var btnEmpty   = document.getElementById('btn-empty');
		var btnClear   = document.getElementById('btn-clear');
		var logEl      = document.getElementById('chic-unused-log');
		var counter    = document.getElementById('chic-unused-counter');
		var resultsEl  = document.getElementById('chic-unused-results');
		var tbody      = document.getElementById('chic-unused-tbody');
		var summaryEl  = document.getElementById('chic-unused-summary');
		var chkAll     = document.getElementById('chk-all');
		var trashCnt   = document.getElementById('trash-cnt');
		var running    = false;
		var candidates = [];

		function log(lines) {
			if (!Array.isArray(lines)) lines = [lines];
			lines.forEach(function (l) {
				if (typeof l === 'string') l = {lvl: 'info', text: l};
				var span = document.createElement('span');
				span.className = 'lvl-' + (l.lvl || 'info');
				span.textContent = l.text + '\n';
				logEl.appendChild(span);
			});
			logEl.scrollTop = logEl.scrollHeight;
		}

		function setRunning(state) {
			running = state;
			btnScan.disabled = state;
		}

		function updateTrashBtn() {
			var n = tbody.querySelectorAll('input[type=checkbox]:checked').length;
			btnTrash.disabled   = n === 0;
			btnTrash.textContent = n > 0
				? '🗑 Move ' + n + ' to Trash'
				: '🗑 Move Selected to Trash';
		}

		function formatBytes(b) {
			b = b || 0;
			if (b < 1024)    return b + ' B';
			if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
			return (b / 1048576).toFixed(1) + ' MB';
		}

		function esc(s) {
			return String(s)
				.replace(/&/g, '&amp;').replace(/</g, '&lt;')
				.replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		}

		function ajax(action, data, cb) {
			var fd = new FormData();
			fd.append('action', action);
			fd.append('_nonce', nonce);
			Object.keys(data).forEach(function (k) {
				var v = data[k];
				if (Array.isArray(v)) {
					v.forEach(function (item) { fd.append(k + '[]', item); });
				} else {
					fd.append(k, v);
				}
			});
			fetch(ajaxurl, {method: 'POST', body: fd})
				.then(function (r) { return r.json(); })
				.then(cb)
				.catch(function (e) {
					log({lvl: 'err', text: 'Request failed: ' + e.message});
					setRunning(false);
				});
		}

		// ── Scan sequence ─────────────────────────────────────────────────────

		btnScan.addEventListener('click', function () {
			if (running) return;
			setRunning(true);
			resultsEl.style.display = 'none';
			tbody.innerHTML = '';
			candidates = [];
			chkAll.checked = false;
			updateTrashBtn();
			log({lvl: 'head', text: '── Initializing scan ────────────────────────────────────'});

			ajax('chic_unused_scan', {step: 'init'}, function (res) {
				if (!res.success) {
					log({lvl: 'err', text: res.data.msg || 'Init failed'}); setRunning(false); return;
				}
				log(res.data.log);
				counter.textContent = res.data.total_attachments + ' image attachments to check';
				log({lvl: 'head', text: '── Scanning post content ────────────────────────────────'});
				scanContent(0, 0);
			});
		});

		function scanContent(cursor, total) {
			ajax('chic_unused_scan', {step: 'content', cursor: cursor}, function (res) {
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); setRunning(false); return; }
				log(res.data.log);
				total += res.data.ids_found || 0;
				if (res.data.done) {
					log({lvl: 'head', text: '── Scanning post meta ───────────────────────────────────'});
					scanMeta(0, 0);
				} else {
					scanContent(res.data.cursor, total);
				}
			});
		}

		function scanMeta(cursor, total) {
			ajax('chic_unused_scan', {step: 'meta', cursor: cursor}, function (res) {
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); setRunning(false); return; }
				log(res.data.log);
				total += res.data.ids_found || 0;
				if (res.data.done) {
					log({lvl: 'head', text: '── Scanning site options ────────────────────────────────'});
					scanOptions(0, 0);
				} else {
					scanMeta(res.data.cursor, total);
				}
			});
		}

		function scanOptions(cursor, total) {
			ajax('chic_unused_scan', {step: 'options', cursor: cursor}, function (res) {
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); setRunning(false); return; }
				log(res.data.log);
				total += res.data.ids_found || 0;
				if (res.data.done) {
					log({lvl: 'head', text: '── Computing candidates ─────────────────────────────────'});
					scanFinalize();
				} else {
					scanOptions(res.data.cursor, total);
				}
			});
		}

		function scanFinalize() {
			ajax('chic_unused_scan', {step: 'finalize'}, function (res) {
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); setRunning(false); return; }
				log(res.data.log);
				candidates = res.data.candidates || [];
				renderTable(candidates);
				setRunning(false);
			});
		}

		function renderTable(items) {
			var totalBytes = items.reduce(function (s, i) { return s + (i.bytes || 0); }, 0);
			summaryEl.textContent = items.length === 0
				? 'No unused images found — the library is clean.'
				: items.length + ' unused image' + (items.length === 1 ? '' : 's') + ' · ' + formatBytes(totalBytes) + ' can be freed';

			tbody.innerHTML = '';
			items.forEach(function (item) {
				var tr = document.createElement('tr');
				tr.innerHTML =
					'<td><input type="checkbox" data-id="' + item.id + '"></td>' +
					'<td>' + (item.thumb
						? '<img src="' + esc(item.thumb) + '" loading="lazy" alt="">'
						: '<span style="color:#bbb;font-size:11px;">no thumb</span>') + '</td>' +
					'<td title="' + esc(item.filename) + '">' + esc(item.filename) + '</td>' +
					'<td>' + esc(item.size) + '</td>' +
					'<td>' + esc(item.dims || '—') + '</td>' +
					'<td>' + esc(item.date) + '</td>' +
					'<td><a href="' + esc(item.edit_url) + '" target="_blank" style="font-size:11px;">Edit</a></td>';
				tbody.appendChild(tr);
			});

			tbody.querySelectorAll('input[type=checkbox]').forEach(function (c) {
				c.addEventListener('change', updateTrashBtn);
			});

			resultsEl.style.display = '';
			updateTrashBtn();
		}

		chkAll.addEventListener('change', function () {
			tbody.querySelectorAll('input[type=checkbox]').forEach(function (c) {
				c.checked = chkAll.checked;
			});
			updateTrashBtn();
		});

		btnClear.addEventListener('click', function () {
			logEl.innerHTML = '';
			counter.textContent = '';
		});

		// ── Trash selected ────────────────────────────────────────────────────

		btnTrash.addEventListener('click', function () {
			var ids = [];
			tbody.querySelectorAll('input[type=checkbox]:checked').forEach(function (c) {
				ids.push(parseInt(c.dataset.id, 10));
			});
			if (ids.length === 0) return;
			if (!confirm('Move ' + ids.length + ' image(s) to Trash?\n\nYou can restore them from Media → Trash before permanently deleting.')) return;

			btnTrash.disabled = true;
			btnScan.disabled  = true;
			log({lvl: 'head', text: '── Moving ' + ids.length + ' image(s) to Trash ─────────────────────'});

			ajax('chic_unused_trash', {ids: ids}, function (res) {
				btnScan.disabled = false;
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); return; }
				log(res.data.log);

				ids.forEach(function (id) {
					var cb = tbody.querySelector('input[data-id="' + id + '"]');
					if (cb) cb.closest('tr').remove();
				});
				candidates = candidates.filter(function (c) { return ids.indexOf(c.id) === -1; });
				chkAll.checked = false;
				updateTrashBtn();

				var totalBytes = candidates.reduce(function (s, i) { return s + (i.bytes || 0); }, 0);
				summaryEl.textContent = candidates.length === 0
					? 'All candidates moved to Trash.'
					: candidates.length + ' unused image' + (candidates.length === 1 ? '' : 's') + ' · ' + formatBytes(totalBytes) + ' can be freed';

				var cnt = res.data.trash_count || 0;
				trashCnt.textContent = '(' + cnt + ')';
				trashCnt.style.display = cnt > 0 ? '' : 'none';
			});
		});

		// ── Empty trash ───────────────────────────────────────────────────────

		btnEmpty.addEventListener('click', function () {
			var m = trashCnt.textContent.match(/\d+/);
			var cnt = m ? parseInt(m[0], 10) : 0;
			var msg = cnt > 0
				? 'Permanently delete ' + cnt + ' trashed image(s)?\n\nThis cannot be undone — files will be removed from disk.'
				: 'Permanently delete all trashed images?\n\nThis cannot be undone.';
			if (!confirm(msg)) return;

			btnEmpty.disabled = true;
			btnScan.disabled  = true;
			log({lvl: 'head', text: '── Permanently deleting trashed images ──────────────────'});

			ajax('chic_unused_empty', {confirm: 'YES'}, function (res) {
				btnScan.disabled  = false;
				btnEmpty.disabled = false;
				if (!res.success) { log({lvl: 'err', text: res.data.msg}); return; }
				log(res.data.log);
				trashCnt.textContent = '(0)';
				trashCnt.style.display = 'none';
			});
		});
	})();
	</script>
	<?php
}

// ── AJAX: Scan ────────────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_unused_scan', 'chic_unused_ajax_scan' );

function chic_unused_ajax_scan(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_unused_images', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	$step   = sanitize_key( $_POST['step'] ?? 'init' );
	$cursor = max( 0, (int) ( $_POST['cursor'] ?? 0 ) );

	switch ( $step ) {
		case 'init':     chic_unused_step_init(); break;
		case 'content':  chic_unused_step_content( $cursor ); break;
		case 'meta':     chic_unused_step_meta( $cursor ); break;
		case 'options':  chic_unused_step_options( $cursor ); break;
		case 'finalize': chic_unused_step_finalize(); break;
		default:         wp_send_json_error( [ 'msg' => 'Unknown step.' ] );
	}
}

function chic_unused_step_init(): void {
	global $wpdb;

	// Fetch all non-trash image attachment IDs as a lookup map (key = id, value = true)
	$raw_ids = $wpdb->get_col( "
		SELECT ID FROM {$wpdb->posts}
		WHERE post_type = 'attachment'
		  AND post_mime_type LIKE 'image/%'
		  AND post_status != 'trash'
	" );
	$all_map = array_fill_keys( array_map( 'intval', $raw_ids ), true );

	// Clear previous scan state
	delete_transient( 'chic_unused_all_ids' );
	delete_transient( 'chic_unused_used_ids' );
	delete_transient( 'chic_unused_candidates' );

	set_transient( 'chic_unused_all_ids',  $all_map, 2 * HOUR_IN_SECONDS );
	set_transient( 'chic_unused_used_ids', [],        2 * HOUR_IN_SECONDS );

	// Pre-resolve hardcoded theme URLs into the used set
	$safelist_ids = [];
	foreach ( chic_unused_theme_urls() as $url ) {
		$id = attachment_url_to_postid( chic_unused_normalize_url( $url ) );
		if ( $id > 0 ) $safelist_ids[] = $id;
	}

	// Attachment-parent safety net: if a post owns the attachment, keep it
	$parented = $wpdb->get_col( "
		SELECT a.ID FROM {$wpdb->posts} a
		INNER JOIN {$wpdb->posts} p ON p.ID = a.post_parent
		WHERE a.post_type     = 'attachment'
		  AND a.post_mime_type LIKE 'image/%'
		  AND a.post_status   != 'trash'
		  AND a.post_parent   > 0
		  AND p.post_status   NOT IN ('trash', 'auto-draft')
	" );

	$used = array_unique( array_merge( $safelist_ids, array_map( 'intval', $parented ) ) );
	set_transient( 'chic_unused_used_ids', $used, 2 * HOUR_IN_SECONDS );

	wp_send_json_success( [
		'log' => [
			[ 'lvl' => 'ok',  'text' => sprintf( 'Found %d image attachments to analyze.', count( $all_map ) ) ],
			[ 'lvl' => 'dim', 'text' => sprintf( 'Safelisted %d IDs from hardcoded theme URLs + attachment parents.', count( $used ) ) ],
		],
		'total_attachments' => count( $all_map ),
	] );
}

function chic_unused_step_content( int $cursor ): void {
	global $wpdb;

	$all_ids = get_transient( 'chic_unused_all_ids' );
	if ( $all_ids === false ) {
		wp_send_json_error( [ 'msg' => 'Scan session expired — please re-scan.' ] );
	}

	$chunk = 200;
	$rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT ID, post_content FROM {$wpdb->posts}
		 WHERE ID > %d
		   AND post_status NOT IN ('trash', 'auto-draft')
		   AND post_type NOT IN ('revision', 'attachment')
		 ORDER BY ID ASC
		 LIMIT %d",
		$cursor, $chunk
	), ARRAY_A );

	$new_ids = [];
	foreach ( $rows as $row ) {
		$new_ids = array_merge( $new_ids, chic_unused_ids_from_text( $row['post_content'], $all_ids ) );
	}
	$new_ids = array_unique( $new_ids );

	if ( ! empty( $new_ids ) ) {
		$used = get_transient( 'chic_unused_used_ids' ) ?: [];
		set_transient( 'chic_unused_used_ids', array_unique( array_merge( $used, $new_ids ) ), 2 * HOUR_IN_SECONDS );
	}

	$done       = count( $rows ) < $chunk;
	$new_cursor = empty( $rows ) ? $cursor : (int) end( $rows )['ID'];

	$log = [];
	if ( ! empty( $rows ) ) {
		$log[] = [ 'lvl' => 'dim', 'text' => sprintf( '  posts %d–%d: %d image ref(s) found', $cursor + 1, $new_cursor, count( $new_ids ) ) ];
	}
	if ( $done ) {
		$log[] = [ 'lvl' => 'ok', 'text' => 'Post content scan complete.' ];
	}

	wp_send_json_success( [ 'log' => $log, 'done' => $done, 'cursor' => $new_cursor, 'ids_found' => count( $new_ids ) ] );
}

function chic_unused_step_meta( int $cursor ): void {
	global $wpdb;

	$all_ids = get_transient( 'chic_unused_all_ids' );
	if ( $all_ids === false ) {
		wp_send_json_error( [ 'msg' => 'Scan session expired — please re-scan.' ] );
	}

	$chunk = 500;
	$rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
		 WHERE meta_id > %d
		 ORDER BY meta_id ASC
		 LIMIT %d",
		$cursor, $chunk
	), ARRAY_A );

	$new_ids = [];
	foreach ( $rows as $row ) {
		$new_ids = array_merge( $new_ids, chic_unused_ids_from_value( $row['meta_value'], $all_ids ) );
	}
	$new_ids = array_unique( $new_ids );

	if ( ! empty( $new_ids ) ) {
		$used = get_transient( 'chic_unused_used_ids' ) ?: [];
		set_transient( 'chic_unused_used_ids', array_unique( array_merge( $used, $new_ids ) ), 2 * HOUR_IN_SECONDS );
	}

	$done       = count( $rows ) < $chunk;
	$new_cursor = empty( $rows ) ? $cursor : (int) end( $rows )['meta_id'];

	$log = [];
	if ( ! empty( $rows ) ) {
		$log[] = [ 'lvl' => 'dim', 'text' => sprintf( '  meta rows %d–%d: %d image ref(s) found', $cursor + 1, $new_cursor, count( $new_ids ) ) ];
	}
	if ( $done ) {
		$log[] = [ 'lvl' => 'ok', 'text' => 'Post meta scan complete.' ];
	}

	wp_send_json_success( [ 'log' => $log, 'done' => $done, 'cursor' => $new_cursor, 'ids_found' => count( $new_ids ) ] );
}

function chic_unused_step_options( int $cursor ): void {
	global $wpdb;

	$all_ids = get_transient( 'chic_unused_all_ids' );
	if ( $all_ids === false ) {
		wp_send_json_error( [ 'msg' => 'Scan session expired — please re-scan.' ] );
	}

	$chunk    = 100;
	$t_like   = $wpdb->esc_like( '_transient_' )      . '%';
	$st_like  = $wpdb->esc_like( '_site_transient_' ) . '%';

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT option_id, option_value FROM {$wpdb->options}
		 WHERE option_id > %d
		   AND option_name NOT LIKE %s
		   AND option_name NOT LIKE %s
		   AND option_name NOT IN ('cron', 'chic_unused_all_ids', 'chic_unused_used_ids', 'chic_unused_candidates')
		 ORDER BY option_id ASC
		 LIMIT %d",
		$cursor, $t_like, $st_like, $chunk
	), ARRAY_A );

	$new_ids = [];
	foreach ( $rows as $row ) {
		$new_ids = array_merge( $new_ids, chic_unused_ids_from_value( $row['option_value'], $all_ids ) );
	}
	$new_ids = array_unique( $new_ids );

	if ( ! empty( $new_ids ) ) {
		$used = get_transient( 'chic_unused_used_ids' ) ?: [];
		set_transient( 'chic_unused_used_ids', array_unique( array_merge( $used, $new_ids ) ), 2 * HOUR_IN_SECONDS );
	}

	$done       = count( $rows ) < $chunk;
	$new_cursor = empty( $rows ) ? $cursor : (int) end( $rows )['option_id'];

	$log = [];
	if ( ! empty( $rows ) ) {
		$log[] = [ 'lvl' => 'dim', 'text' => sprintf( '  option rows %d–%d: %d image ref(s) found', $cursor + 1, $new_cursor, count( $new_ids ) ) ];
	}
	if ( $done ) {
		$log[] = [ 'lvl' => 'ok', 'text' => 'Options scan complete.' ];
	}

	wp_send_json_success( [ 'log' => $log, 'done' => $done, 'cursor' => $new_cursor, 'ids_found' => count( $new_ids ) ] );
}

function chic_unused_step_finalize(): void {
	$all_ids = get_transient( 'chic_unused_all_ids' );
	$used    = get_transient( 'chic_unused_used_ids' ) ?: [];

	if ( $all_ids === false ) {
		wp_send_json_error( [ 'msg' => 'Scan session expired — please re-scan.' ] );
	}

	$used_set   = array_fill_keys( $used, true );
	$candidates = [];

	foreach ( array_keys( $all_ids ) as $id ) {
		if ( isset( $used_set[ $id ] ) ) continue;

		$post = get_post( $id );
		if ( ! $post || $post->post_status === 'trash' ) continue;

		$filepath = get_attached_file( $id );
		$filename  = $filepath ? basename( $filepath ) : '';

		if ( chic_unused_is_pattern_safe( $filename ) ) continue;

		$meta  = wp_get_attachment_metadata( $id );
		$dims  = '';
		$bytes = 0;

		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			$dims = $meta['width'] . '×' . $meta['height'];
		}

		if ( $filepath && file_exists( $filepath ) ) {
			$bytes = (int) filesize( $filepath );
			if ( ! empty( $meta['sizes'] ) ) {
				$dir = dirname( $filepath );
				foreach ( $meta['sizes'] as $size ) {
					$sf = $dir . '/' . $size['file'];
					if ( file_exists( $sf ) ) $bytes += (int) filesize( $sf );
				}
			}
		}

		$candidates[] = [
			'id'       => $id,
			'filename' => $filename ?: "(ID $id)",
			'size'     => size_format( $bytes ),
			'bytes'    => $bytes,
			'dims'     => $dims,
			'date'     => get_the_date( 'Y-m-d', $post ),
			'thumb'    => wp_get_attachment_image_url( $id, 'thumbnail' ) ?: '',
			'edit_url' => admin_url( 'post.php?post=' . $id . '&action=edit' ),
		];
	}

	// Largest files first — highest impact at the top
	usort( $candidates, fn( $a, $b ) => $b['bytes'] <=> $a['bytes'] );

	set_transient( 'chic_unused_candidates', array_column( $candidates, 'id' ), HOUR_IN_SECONDS );

	$total_bytes = array_sum( array_column( $candidates, 'bytes' ) );

	wp_send_json_success( [
		'log' => [ [
			'lvl'  => 'done',
			'text' => sprintf(
				'Scan complete: %d unused image%s found · %s can be freed.',
				count( $candidates ),
				count( $candidates ) === 1 ? '' : 's',
				size_format( $total_bytes )
			),
		] ],
		'candidates' => $candidates,
	] );
}

// ── AJAX: Trash ───────────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_unused_trash', 'chic_unused_ajax_trash' );

function chic_unused_ajax_trash(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_unused_images', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	$candidate_ids = get_transient( 'chic_unused_candidates' ) ?: [];
	$candidate_set = array_fill_keys( array_map( 'intval', $candidate_ids ), true );

	$raw = $_POST['ids'] ?? [];
	if ( ! is_array( $raw ) ) $raw = [ $raw ];
	$ids = array_filter(
		array_map( 'intval', $raw ),
		fn( $id ) => $id > 0 && isset( $candidate_set[ $id ] )
	);

	if ( empty( $ids ) ) {
		wp_send_json_error( [ 'msg' => 'No valid IDs provided or IDs not in candidate list.' ] );
	}

	$log     = [];
	$trashed = 0;
	$errors  = 0;

	foreach ( $ids as $id ) {
		$filename = basename( get_attached_file( $id ) ?: '' ) ?: "ID $id";
		$result   = wp_trash_post( $id );
		if ( $result ) {
			$log[] = [ 'lvl' => 'ok',  'text' => "  Trashed: $filename" ];
			$trashed++;
		} else {
			$log[] = [ 'lvl' => 'err', 'text' => "  Failed to trash: $filename" ];
			$errors++;
		}
	}

	$log[] = [ 'lvl' => $errors > 0 ? 'warn' : 'done',
		'text' => sprintf( 'Trashed %d image%s.%s',
			$trashed,
			$trashed === 1 ? '' : 's',
			$errors > 0 ? " ($errors failed)" : ''
		),
	];

	wp_send_json_success( [
		'log'         => $log,
		'trash_count' => chic_unused_current_trash_count(),
	] );
}

// ── AJAX: Empty Trash ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_unused_empty', 'chic_unused_ajax_empty' );

function chic_unused_ajax_empty(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_unused_images', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	if ( ( $_POST['confirm'] ?? '' ) !== 'YES' ) {
		wp_send_json_error( [ 'msg' => 'Confirmation token missing.' ] );
	}

	global $wpdb;
	$ids = $wpdb->get_col( "
		SELECT ID FROM {$wpdb->posts}
		WHERE post_type      = 'attachment'
		  AND post_mime_type LIKE 'image/%'
		  AND post_status    = 'trash'
	" );

	if ( empty( $ids ) ) {
		wp_send_json_success( [ 'log' => [ [ 'lvl' => 'info', 'text' => 'Image trash is already empty.' ] ] ] );
	}

	$log     = [];
	$deleted = 0;
	$errors  = 0;
	$bytes   = 0;

	foreach ( $ids as $id ) {
		$id       = (int) $id;
		$filepath = get_attached_file( $id );
		$meta     = wp_get_attachment_metadata( $id );
		$filename  = $filepath ? basename( $filepath ) : "ID $id";

		// Calculate bytes before deletion
		$file_bytes = 0;
		if ( $filepath && file_exists( $filepath ) ) {
			$file_bytes += (int) filesize( $filepath );
			if ( ! empty( $meta['sizes'] ) ) {
				$dir = dirname( $filepath );
				foreach ( $meta['sizes'] as $size ) {
					$sf = $dir . '/' . $size['file'];
					if ( file_exists( $sf ) ) $file_bytes += (int) filesize( $sf );
				}
			}
		}

		$result = wp_delete_attachment( $id, true );
		if ( $result !== false ) {
			$log[]  = [ 'lvl' => 'ok', 'text' => sprintf( '  Deleted: %s (%s)', $filename, size_format( $file_bytes ) ) ];
			$bytes += $file_bytes;
			$deleted++;
		} else {
			$log[] = [ 'lvl' => 'err', 'text' => "  Failed to delete: $filename" ];
			$errors++;
		}
	}

	$log[] = [ 'lvl' => $errors > 0 ? 'warn' : 'done',
		'text' => sprintf( 'Permanently deleted %d image%s · %s freed.%s',
			$deleted,
			$deleted === 1 ? '' : 's',
			size_format( $bytes ),
			$errors > 0 ? " ($errors failed)" : ''
		),
	];

	wp_send_json_success( [ 'log' => $log, 'bytes_freed' => $bytes ] );
}
