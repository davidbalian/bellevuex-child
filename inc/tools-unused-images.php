<?php
/**
 * Admin Tool: Media Recovery Console
 * Tools → Media Recovery
 *
 * Temporary recovery tool after the unused-images tool misfired.
 * Diagnoses orphaned attachment rows (DB row exists, file missing on disk)
 * and permanently removes those orphaned rows so re-uploads don't collide
 * with stale records.
 *
 * After running this tool + re-uploading originals, use
 * Tools → Sync Suite Galleries to re-bind suite featured images + galleries.
 *
 * This file will be replaced with a fixed version of the unused-images scanner
 * once recovery is complete.
 */
defined( 'ABSPATH' ) || exit;

// ── Admin page registration ───────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_management_page(
		'Media Recovery',
		'Media Recovery',
		'manage_options',
		'chic-media-recovery',
		'chic_recovery_render_page'
	);
} );

// ── Render ────────────────────────────────────────────────────────────────────

function chic_recovery_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$nonce = wp_create_nonce( 'chic_media_recovery' );
	?>
	<div class="wrap" id="chic-rec-wrap">
		<h1>Media Recovery Console</h1>
		<p>
			This tool diagnoses the state of the media library after the unused-images
			tool was run incorrectly. It identifies <strong>orphaned attachment rows</strong>
			— database entries whose files no longer exist on disk — and lets you remove them
			cleanly so you can re-upload originals without filename collisions.
		</p>
		<p>
			<strong>Recovery order:</strong>
			<ol style="margin-left:20px;">
				<li>Click <strong>Diagnose</strong> to see the current state.</li>
				<li>Click <strong>Delete Orphaned Rows</strong> to clean stale DB entries.</li>
				<li>Go to <strong>Media → Add New</strong> and drag-drop all original images from your hard drive (keep original filenames).</li>
				<li>Go to <strong>Tools → Sync Suite Galleries</strong> → Run Dry Run, then Run Live to re-bind all suite galleries + featured images.</li>
				<li>Manually re-pick mega-menu hover images in the Header Config admin screen.</li>
			</ol>
		</p>

		<div id="chic-rec-controls" style="margin:16px 0 12px;">
			<button id="btn-diagnose" class="button button-primary">&#9654; Diagnose</button>
			<button id="btn-clean"    class="button" disabled style="margin-left:8px;color:#b32d2e;">
				&#9888; Delete Orphaned Rows
			</button>
			<button id="btn-clear"    class="button" style="margin-left:16px;float:right;">Clear log</button>
		</div>

		<pre id="chic-rec-log"></pre>

		<div id="chic-rec-summary" style="display:none;margin-top:16px;padding:12px 16px;background:#fff3cd;border-left:4px solid #ffc107;border-radius:2px;">
			<strong id="chic-rec-headline"></strong><br>
			<span id="chic-rec-detail" style="font-size:13px;color:#444;"></span>
		</div>
	</div>

	<style>
		#chic-rec-wrap { max-width: 820px; }
		#chic-rec-log {
			background: #1e1e1e; color: #d4d4d4; font-size: 12px; line-height: 1.7;
			padding: 14px 16px; border-radius: 4px; height: 380px;
			overflow-y: auto; white-space: pre-wrap; word-break: break-all;
		}
		#chic-rec-log .lvl-head { color: #9cdcfe; font-weight: bold; }
		#chic-rec-log .lvl-ok   { color: #4ec9b0; }
		#chic-rec-log .lvl-warn { color: #dcdcaa; }
		#chic-rec-log .lvl-err  { color: #f44747; font-weight: bold; }
		#chic-rec-log .lvl-info { color: #d4d4d4; }
		#chic-rec-log .lvl-dim  { color: #6a9955; }
		#chic-rec-log .lvl-done { color: #4ec9b0; font-weight: bold; font-size: 13px; }
	</style>

	<script>
	(function () {
		var ajaxurl   = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce     = <?php echo wp_json_encode( $nonce ); ?>;
		var btnDiag   = document.getElementById('btn-diagnose');
		var btnClean  = document.getElementById('btn-clean');
		var btnClear  = document.getElementById('btn-clear');
		var logEl     = document.getElementById('chic-rec-log');
		var summaryEl = document.getElementById('chic-rec-summary');
		var headlineEl = document.getElementById('chic-rec-headline');
		var detailEl  = document.getElementById('chic-rec-detail');

		function log(lines) {
			if (!Array.isArray(lines)) lines = [lines];
			lines.forEach(function (l) {
				if (typeof l === 'string') l = {lvl:'info', text:l};
				var span = document.createElement('span');
				span.className = 'lvl-' + (l.lvl || 'info');
				span.textContent = l.text + '\n';
				logEl.appendChild(span);
			});
			logEl.scrollTop = logEl.scrollHeight;
		}

		function ajax(action, data, cb) {
			var fd = new FormData();
			fd.append('action', action);
			fd.append('_nonce', nonce);
			Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
			fetch(ajaxurl, {method:'POST', body:fd})
				.then(function (r) { return r.json(); })
				.then(cb)
				.catch(function (e) { log({lvl:'err', text:'Request failed: ' + e.message}); });
		}

		btnClear.addEventListener('click', function () { logEl.innerHTML = ''; });

		// ── Diagnose ──────────────────────────────────────────────────────────

		btnDiag.addEventListener('click', function () {
			btnDiag.disabled = true;
			summaryEl.style.display = 'none';
			log({lvl:'head', text:'── Diagnosing media library state ───────────────────────'});

			ajax('chic_recovery_diagnose', {}, function (res) {
				btnDiag.disabled = false;
				if (!res.success) { log({lvl:'err', text: res.data.msg || 'Diagnose failed.'}); return; }
				log(res.data.log);

				var d = res.data;
				headlineEl.textContent = d.orphans + ' orphaned rows (file missing on disk) out of ' + d.total + ' total attachment rows.';
				detailEl.textContent = d.orphans > 0
					? 'Click "Delete Orphaned Rows" to clean these up, then re-upload your originals.'
					: 'No orphans found — the database is clean. You can go ahead and re-upload your images.';
				summaryEl.style.display = '';

				if (d.orphans > 0) {
					btnClean.disabled = false;
					btnClean.textContent = '⚠ Delete ' + d.orphans + ' Orphaned Row' + (d.orphans === 1 ? '' : 's');
				}
			});
		});

		// ── Clean ─────────────────────────────────────────────────────────────

		btnClean.addEventListener('click', function () {
			if (!confirm(
				'This will permanently delete the database entries for attachments whose files are no longer on disk.\n\n' +
				'Files are already gone — this just clears the stale records so re-uploads work cleanly.\n\n' +
				'Proceed?'
			)) return;

			btnClean.disabled = true;
			btnDiag.disabled  = true;
			log({lvl:'head', text:'── Removing orphaned attachment rows ────────────────────'});

			ajax('chic_recovery_clean', {}, function (res) {
				btnDiag.disabled = false;
				if (!res.success) { log({lvl:'err', text: res.data.msg || 'Clean failed.'}); return; }
				log(res.data.log);
				summaryEl.style.display = 'none';
			});
		});
	})();
	</script>
	<?php
}

// ── AJAX: Diagnose ────────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_recovery_diagnose', 'chic_recovery_ajax_diagnose' );

function chic_recovery_ajax_diagnose(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_media_recovery', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	global $wpdb;

	$rows = $wpdb->get_results( "
		SELECT ID, post_title, post_status FROM {$wpdb->posts}
		WHERE post_type = 'attachment'
		ORDER BY ID ASC
	", ARRAY_A );

	$total   = count( $rows );
	$by_status = [];
	$orphans = [];
	$present = 0;

	foreach ( $rows as $row ) {
		$status = $row['post_status'];
		$by_status[ $status ] = ( $by_status[ $status ] ?? 0 ) + 1;

		$filepath = get_attached_file( (int) $row['ID'] );
		if ( ! $filepath || ! file_exists( $filepath ) ) {
			$orphans[] = (int) $row['ID'];
		} else {
			$present++;
		}
	}

	$log = [];
	$log[] = [ 'lvl' => 'info', 'text' => "Total attachment rows in DB : $total" ];
	foreach ( $by_status as $status => $count ) {
		$log[] = [ 'lvl' => 'dim', 'text' => "  status=$status : $count" ];
	}
	$log[] = [ 'lvl' => 'info', 'text' => "Files present on disk      : $present" ];
	$log[] = [ 'lvl' => ( count( $orphans ) > 0 ? 'warn' : 'ok' ),
		'text' => 'Orphaned rows (file gone)  : ' . count( $orphans ),
	];

	if ( count( $orphans ) > 0 && count( $orphans ) <= 50 ) {
		foreach ( $orphans as $id ) {
			$title = get_the_title( $id ) ?: "(no title)";
			$meta  = get_attached_file( $id );
			$path  = $meta ?: '(no file meta)';
			$log[] = [ 'lvl' => 'dim', 'text' => "  ID $id: $title → $path" ];
		}
	} elseif ( count( $orphans ) > 50 ) {
		$log[] = [ 'lvl' => 'dim', 'text' => '  (listing first 50 of ' . count( $orphans ) . ')' ];
		foreach ( array_slice( $orphans, 0, 50 ) as $id ) {
			$path = get_attached_file( $id ) ?: '(no file meta)';
			$log[] = [ 'lvl' => 'dim', 'text' => "  ID $id → $path" ];
		}
	}

	$log[] = [ 'lvl' => count( $orphans ) > 0 ? 'warn' : 'done',
		'text' => count( $orphans ) > 0
			? 'Action needed: delete ' . count( $orphans ) . ' orphaned row(s), then re-upload your images.'
			: 'DB is clean — go ahead and re-upload, then run Sync Suite Galleries.',
	];

	// Store orphan IDs for the clean step
	set_transient( 'chic_recovery_orphans', $orphans, 30 * MINUTE_IN_SECONDS );

	wp_send_json_success( [
		'log'     => $log,
		'total'   => $total,
		'present' => $present,
		'orphans' => count( $orphans ),
	] );
}

// ── AJAX: Clean ───────────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_recovery_clean', 'chic_recovery_ajax_clean' );

function chic_recovery_ajax_clean(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'chic_media_recovery', '_nonce', false ) ) {
		wp_send_json_error( [ 'msg' => 'Unauthorized' ], 403 );
	}

	$orphans = get_transient( 'chic_recovery_orphans' );
	if ( ! is_array( $orphans ) || empty( $orphans ) ) {
		wp_send_json_error( [ 'msg' => 'No orphan list found — please run Diagnose first.' ] );
	}

	$log     = [];
	$deleted = 0;
	$errors  = 0;

	foreach ( $orphans as $id ) {
		$id    = (int) $id;
		$title = get_the_title( $id ) ?: "ID $id";
		// Files are already gone — force=true just removes the DB row + meta
		$result = wp_delete_attachment( $id, true );
		if ( $result !== false ) {
			$log[] = [ 'lvl' => 'ok',  'text' => "  Removed: $title (ID $id)" ];
			$deleted++;
		} else {
			$log[] = [ 'lvl' => 'err', 'text' => "  Failed to remove: $title (ID $id)" ];
			$errors++;
		}
	}

	delete_transient( 'chic_recovery_orphans' );

	$log[] = [ 'lvl' => $errors ? 'warn' : 'done',
		'text' => sprintf(
			'Removed %d orphaned row%s.%s  Next step: re-upload your original images via Media → Add New.',
			$deleted,
			$deleted === 1 ? '' : 's',
			$errors ? " ($errors failed.)" : ''
		),
	];

	wp_send_json_success( [ 'log' => $log ] );
}
