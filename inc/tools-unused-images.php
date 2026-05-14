<?php
/**
 * Admin Tool: Unused Images
 * Tools → Unused Images
 *
 * An image is considered "in use" if it is:
 *   1. A hardcoded URL in the theme (building images, hero fallbacks, logos, etc.)
 *   2. Referenced by the mega-menu (_chic_header_menu on chic_header_cfg)
 *   3. A featured image (_thumbnail_id) on any mphb_room_type post
 *   4. In the gallery (mphb_gallery) of any mphb_room_type post
 *
 * Everything else is a deletion candidate. Delete in two safe steps:
 * Trash first (recoverable), then permanently empty the trash.
 */
defined( 'ABSPATH' ) || exit;

// ── Hardcoded theme image URLs ────────────────────────────────────────────────

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

// ── URL normalizer (staging ↔ prod → current site) ───────────────────────────

function chic_unused_normalize_url( string $url ): string {
	$base = trailingslashit( wp_upload_dir()['baseurl'] );
	$path = '/wp-content/uploads/';
	foreach ( [
		'https://chiccentresuites.com',
		'https://davidb1553.sg-host.com',
		'http://chiccentresuites.com',
		'http://davidb1553.sg-host.com',
	] as $host ) {
		if ( str_starts_with( $url, $host . $path ) ) {
			return $base . substr( $url, strlen( $host . $path ) );
		}
	}
	return $url;
}

// ── Filename pattern safelist ─────────────────────────────────────────────────

function chic_unused_is_pattern_safe( string $filename ): bool {
	// flag-XX.png used dynamically in inc/testimonials-data.php by ISO country code
	return (bool) preg_match( '/^flag-[a-z]{2}\.(png|jpg|jpeg|webp|gif)$/i', $filename );
}

// ── Build the complete "in use" ID set ────────────────────────────────────────

function chic_unused_build_used_set(): array {
	$used = [];
	$log  = [];

	// 1. Hardcoded theme URLs
	$theme_count = 0;
	foreach ( chic_unused_theme_urls() as $url ) {
		$id = attachment_url_to_postid( chic_unused_normalize_url( $url ) );
		if ( $id > 0 ) { $used[] = $id; $theme_count++; }
	}
	$log[] = [ 'lvl' => 'dim', 'text' => "  Hardcoded theme URLs: $theme_count image(s) safelisted" ];

	// 2. Mega-menu images (_chic_header_menu on chic_header_cfg CPT)
	$menu_count = 0;
	$config_id  = function_exists( 'chic_header_config_id' ) ? chic_header_config_id() : 0;
	if ( ! $config_id ) {
		$cfg_posts = get_posts( [ 'post_type' => 'chic_header_cfg', 'numberposts' => 1,
			'post_status' => 'publish', 'fields' => 'ids' ] );
		$config_id = $cfg_posts ? (int) $cfg_posts[0] : 0;
	}
	if ( $config_id ) {
		$menu = get_post_meta( $config_id, '_chic_header_menu', true );
		if ( is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				foreach ( $item['mega_groups'] ?? [] as $group ) {
					if ( ! empty( $group['image'] ) ) { $used[] = (int) $group['image']; $menu_count++; }
					foreach ( $group['suites'] ?? [] as $suite ) {
						if ( ! empty( $suite['image'] ) ) { $used[] = (int) $suite['image']; $menu_count++; }
					}
				}
			}
		}
	}
	$log[] = [ 'lvl' => 'dim', 'text' => "  Mega-menu images: $menu_count image(s) safelisted" ];

	// 3 & 4. Featured images + galleries on all mphb_room_type posts
	$feat_count    = 0;
	$gallery_count = 0;
	$rooms = get_posts( [
		'post_type'      => 'mphb_room_type',
		'numberposts'    => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	] );
	foreach ( $rooms as $room_id ) {
		$thumb = (int) get_post_meta( $room_id, '_thumbnail_id', true );
		if ( $thumb > 0 ) { $used[] = $thumb; $feat_count++; }

		$gallery_raw = get_post_meta( $room_id, 'mphb_gallery', true );
		if ( $gallery_raw ) {
			foreach ( explode( ',', $gallery_raw ) as $id ) {
				$id = (int) trim( $id );
				if ( $id > 0 ) { $used[] = $id; $gallery_count++; }
			}
		}
	}
	$log[] = [ 'lvl' => 'dim', 'text' => "  Accommodation featured images: $feat_count" ];
	$log[] = [ 'lvl' => 'dim', 'text' => "  Accommodation gallery images: $gallery_count" ];

	return [ 'ids' => array_unique( array_filter( $used ) ), 'log' => $log ];
}

// ── Helper: trash count ───────────────────────────────────────────────────────

function chic_unused_current_trash_count(): int {
	global $wpdb;
	return (int) $wpdb->get_var( "
		SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_status = 'trash'
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
			Keeps only images referenced by <strong>hardcoded theme URLs</strong>,
			the <strong>mega-menu</strong>, and each accommodation's
			<strong>featured image</strong> and <strong>gallery</strong>.
			Everything else is a candidate for deletion.<br>
			Move candidates to <strong>Trash</strong> first (recoverable via Media&nbsp;→&nbsp;Trash),
			then permanently delete the trash to free disk space.
		</p>

		<div id="chic-unused-controls">
			<button id="btn-scan"  class="button button-primary">&#9654; Scan Library</button>
			<button id="btn-trash" class="button" disabled style="margin-left:8px;">&#128465; Move Selected to Trash</button>
			<button id="btn-empty" class="button" style="margin-left:8px;color:#b32d2e;">
				&#9888;&nbsp;Empty Image Trash
				<span id="trash-cnt" style="<?php echo $trash_cnt ? '' : 'display:none;'; ?>font-weight:600;">
					(<?php echo (int) $trash_cnt; ?>)
				</span>
			</button>
			<button id="btn-clear" class="button" style="margin-left:16px;float:right;">Clear log</button>
			<span id="chic-unused-counter"></span>
		</div>

		<pre id="chic-unused-log"></pre>

		<div id="chic-unused-results" style="display:none;margin-top:16px;">
			<p id="chic-unused-summary" style="font-weight:600;font-size:14px;"></p>
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
			display: flex; align-items: center; flex-wrap: wrap; gap: 4px; margin: 16px 0 12px;
		}
		#chic-unused-counter { margin-left: 12px; flex: 1; text-align: right; font-style: italic; color: #777; }
		#chic-unused-log {
			background: #1e1e1e; color: #d4d4d4; font-size: 12px; line-height: 1.6;
			padding: 14px 16px; border-radius: 4px; height: 260px;
			overflow-y: auto; white-space: pre-wrap; word-break: break-all; margin-bottom: 4px;
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
			font-family: monospace; font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
		}
	</style>

	<script>
	(function () {
		var ajaxurl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce    = <?php echo wp_json_encode( $nonce ); ?>;
		var btnScan  = document.getElementById('btn-scan');
		var btnTrash = document.getElementById('btn-trash');
		var btnEmpty = document.getElementById('btn-empty');
		var btnClear = document.getElementById('btn-clear');
		var logEl    = document.getElementById('chic-unused-log');
		var counter  = document.getElementById('chic-unused-counter');
		var resultsEl = document.getElementById('chic-unused-results');
		var tbody    = document.getElementById('chic-unused-tbody');
		var summaryEl = document.getElementById('chic-unused-summary');
		var chkAll   = document.getElementById('chk-all');
		var trashCnt = document.getElementById('trash-cnt');
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

		function updateTrashBtn() {
			var n = tbody.querySelectorAll('input[type=checkbox]:checked').length;
			btnTrash.disabled = n === 0;
			btnTrash.textContent = n > 0 ? '🗑 Move ' + n + ' to Trash' : '🗑 Move Selected to Trash';
		}

		function formatBytes(b) {
			b = b || 0;
			if (b < 1024)    return b + ' B';
			if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
			return (b / 1048576).toFixed(1) + ' MB';
		}

		function esc(s) {
			return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
		}

		function ajax(action, data, cb) {
			var fd = new FormData();
			fd.append('action', action);
			fd.append('_nonce', nonce);
			Object.keys(data).forEach(function (k) {
				var v = data[k];
				if (Array.isArray(v)) { v.forEach(function (i) { fd.append(k + '[]', i); }); }
				else { fd.append(k, v); }
			});
			fetch(ajaxurl, {method: 'POST', body: fd})
				.then(function (r) { return r.json(); })
				.then(cb)
				.catch(function (e) { log({lvl: 'err', text: 'Request failed: ' + e.message}); btnScan.disabled = false; });
		}

		// ── Scan ─────────────────────────────────────────────────────────────

		btnScan.addEventListener('click', function () {
			btnScan.disabled = true;
			resultsEl.style.display = 'none';
			tbody.innerHTML = '';
			candidates = [];
			chkAll.checked = false;
			updateTrashBtn();
			counter.textContent = '';
			log({lvl: 'head', text: '── Scanning ─────────────────────────────────────────────'});

			ajax('chic_unused_scan', {}, function (res) {
				btnScan.disabled = false;
				if (!res.success) { log({lvl: 'err', text: res.data.msg || 'Scan failed.'}); return; }
				log(res.data.log);
				candidates = res.data.candidates || [];
				counter.textContent = res.data.total_attachments + ' image attachments checked';
				renderTable(candidates);
			});
		});

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
						: '<span style="color:#bbb;font-size:11px;">—</span>') + '</td>' +
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
			tbody.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = chkAll.checked; });
			updateTrashBtn();
		});

		btnClear.addEventListener('click', function () { logEl.innerHTML = ''; counter.textContent = ''; });

		// ── Trash selected ────────────────────────────────────────────────────

		btnTrash.addEventListener('click', function () {
			var ids = [];
			tbody.querySelectorAll('input[type=checkbox]:checked').forEach(function (c) {
				ids.push(parseInt(c.dataset.id, 10));
			});
			if (!ids.length) return;
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

	global $wpdb;

	// All non-trash image attachment IDs
	$all_ids = array_map( 'intval', $wpdb->get_col( "
		SELECT ID FROM {$wpdb->posts}
		WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_status != 'trash'
	" ) );

	$result  = chic_unused_build_used_set();
	$used    = array_fill_keys( $result['ids'], true );
	$log     = $result['log'];

	$candidates = [];
	foreach ( $all_ids as $id ) {
		if ( isset( $used[ $id ] ) ) continue;

		$post     = get_post( $id );
		if ( ! $post ) continue;

		$filepath = get_attached_file( $id );
		$filename  = $filepath ? basename( $filepath ) : '';

		if ( chic_unused_is_pattern_safe( $filename ) ) continue;

		$meta  = wp_get_attachment_metadata( $id );
		$bytes = 0;
		$dims  = '';

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

	// Largest files first
	usort( $candidates, fn( $a, $b ) => $b['bytes'] <=> $a['bytes'] );

	$total_bytes = array_sum( array_column( $candidates, 'bytes' ) );
	$log[] = [ 'lvl' => 'done', 'text' => sprintf(
		'Found %d unused image%s · %s can be freed.',
		count( $candidates ),
		count( $candidates ) === 1 ? '' : 's',
		size_format( $total_bytes )
	) ];

	set_transient( 'chic_unused_candidates', array_column( $candidates, 'id' ), HOUR_IN_SECONDS );

	wp_send_json_success( [
		'log'              => $log,
		'candidates'       => $candidates,
		'total_attachments' => count( $all_ids ),
	] );
}

// ── AJAX: Trash ───────────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_unused_trash', 'chic_unused_ajax_trash' );

function chic_unused_ajax_trash(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_unused_images', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	$candidate_set = array_fill_keys(
		array_map( 'intval', get_transient( 'chic_unused_candidates' ) ?: [] ),
		true
	);

	$raw = $_POST['ids'] ?? [];
	if ( ! is_array( $raw ) ) $raw = [ $raw ];
	$ids = array_filter(
		array_map( 'intval', $raw ),
		fn( $id ) => $id > 0 && isset( $candidate_set[ $id ] )
	);

	if ( empty( $ids ) ) {
		wp_send_json_error( [ 'msg' => 'No valid IDs — run Scan first.' ] );
	}

	$log = []; $trashed = 0; $errors = 0;

	foreach ( $ids as $id ) {
		$filename = basename( get_attached_file( $id ) ?: '' ) ?: "ID $id";
		if ( wp_trash_post( $id ) ) {
			$log[] = [ 'lvl' => 'ok',  'text' => "  Trashed: $filename" ]; $trashed++;
		} else {
			$log[] = [ 'lvl' => 'err', 'text' => "  Failed to trash: $filename" ]; $errors++;
		}
	}

	$log[] = [ 'lvl' => $errors ? 'warn' : 'done',
		'text' => "Trashed $trashed image" . ( $trashed === 1 ? '' : 's' ) . ( $errors ? " ($errors failed)" : '' ) . '.',
	];

	wp_send_json_success( [ 'log' => $log, 'trash_count' => chic_unused_current_trash_count() ] );
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
		WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_status = 'trash'
	" );

	if ( empty( $ids ) ) {
		wp_send_json_success( [ 'log' => [ [ 'lvl' => 'info', 'text' => 'Image trash is already empty.' ] ] ] );
	}

	$log = []; $deleted = 0; $errors = 0; $bytes = 0;

	foreach ( $ids as $id ) {
		$id       = (int) $id;
		$filepath = get_attached_file( $id );
		$meta     = wp_get_attachment_metadata( $id );
		$filename  = $filepath ? basename( $filepath ) : "ID $id";

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

		if ( wp_delete_attachment( $id, true ) !== false ) {
			$log[]  = [ 'lvl' => 'ok', 'text' => sprintf( '  Deleted: %s (%s)', $filename, size_format( $file_bytes ) ) ];
			$bytes += $file_bytes; $deleted++;
		} else {
			$log[] = [ 'lvl' => 'err', 'text' => "  Failed to delete: $filename" ]; $errors++;
		}
	}

	$log[] = [ 'lvl' => $errors ? 'warn' : 'done',
		'text' => sprintf( 'Permanently deleted %d image%s · %s freed.%s',
			$deleted, $deleted === 1 ? '' : 's', size_format( $bytes ),
			$errors ? " ($errors failed)" : ''
		),
	];

	wp_send_json_success( [ 'log' => $log, 'bytes_freed' => $bytes ] );
}
