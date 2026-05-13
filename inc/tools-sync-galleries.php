<?php

/**
 * Admin tool: Sync MPHB accommodation galleries from the media library.
 *
 * Adds a page under Tools → Sync Suite Galleries.
 * Each suite is processed via sequential AJAX steps so the browser shows
 * live logs as work progresses. Supports dry-run (no writes) mode.
 */

// ── Suite manifest ─────────────────────────────────────────────────────────────
// Each entry: [ 'title' => (MPHB post title), 'files' => [ ...filenames in order ] ]
// Filenames match exactly what was uploaded to the media library.
// Source: FOLDER_STRUCTURE.md (not committed — local reference only).

const CHIC_SUITE_MANIFEST = [

	// ── Chavriou 2 ──────────────────────────────────────────────────────────────
	[
		'title' => 'Suite 1',
		'files' => [
			'1-chic-centre-suites-athens-one-bedroom-suite-1-main-room-athens.webp',
			'2-chic-centre-suites-athens-one-bedroom-suite-1-bedroom-athens.webp',
			'3-chic-centre-suites-athens-one-bedroom-suite-1-living-area-athens.webp',
			'4-chic-centre-suites-athens-one-bedroom-suite-1-kitchen-athens.webp',
			'5-chic-centre-suites-athens-one-bedroom-suite-1-kitchen-athens.webp',
			'6-chic-centre-suites-athens-one-bedroom-suite-1-living-area-athens.webp',
			'7-chic-centre-suites-athens-one-bedroom-suite-1-bathroom-athens.webp',
			'8-chic-centre-suites-athens-one-bedroom-suite-1-balcony-athens.webp',
		],
	],
	[
		'title' => 'Suite 2',
		'files' => [
			'1-chic-centre-suites-athens-one-bedroom-suite-2-main-room-athens.webp',
			'2-chic-centre-suites-athens-one-bedroom-suite-2-bedroom-athens.webp',
			'3-chic-centre-suites-athens-one-bedroom-suite-2-living-area-athens.webp',
			'4-chic-centre-suites-athens-one-bedroom-suite-2-living-area-athens.webp',
			'5-chic-centre-suites-athens-one-bedroom-suite-2-kitchen-athens.webp',
			'6-chic-centre-suites-athens-one-bedroom-suite-2-living-area-athens.webp',
			'7-chic-centre-suites-athens-one-bedroom-suite-2-bathroom-athens.webp',
			'8-chic-centre-suites-athens-one-bedroom-suite-2-bathroom-athens.webp',
			'9-chic-centre-suites-athens-one-bedroom-suite-2-living-area-athens.webp',
			'10-chic-centre-suites-athens-one-bedroom-suite-2-detail-athens.webp',
		],
	],
	[
		'title' => 'Suite 3',
		'files' => [
			'1-chic-centre-suites-athens-suite-no-balcony-3-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-no-balcony-3-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-no-balcony-3-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-no-balcony-3-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-no-balcony-3-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-no-balcony-3-bed-athens.webp',
			'7-chic-centre-suites-athens-suite-no-balcony-3-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-no-balcony-3-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Suite 4',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-4-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-4-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-4-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-4-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-4-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-4-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-4-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-4-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Suite 5',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-5-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-5-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-5-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-5-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-5-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-5-balcony-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-5-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-5-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Suite 6',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-6-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-6-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-6-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-6-detail-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-6-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-6-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-6-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-6-extra-angle-athens.webp',
		],
	],

	// ── Thisseos 11 ─────────────────────────────────────────────────────────────
	[
		'title' => 'Ammos Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-terrace-ammos-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-terrace-ammos-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-terrace-ammos-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-terrace-ammos-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-terrace-ammos-kitchen-athens.webp',
			'6-chic-centre-suites-athens-suite-terrace-ammos-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-terrace-ammos-terrace-athens.webp',
			'8-chic-centre-suites-athens-suite-terrace-ammos-detail-athens.webp',
		],
	],
	[
		'title' => 'Avra Suite',
		'files' => [
			'1-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-main-room-athens.webp',
			'2-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-bedroom-athens.webp',
			'3-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-living-area-athens.webp',
			'4-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-kitchen-athens.webp',
			'5-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-bathroom-athens.webp',
			'6-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-balcony-athens.webp',
			'7-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-kitchen-athens.webp',
			'8-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-layout-athens.webp',
			'9-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-detail-athens.webp',
			'10-chic-centre-suites-athens-one-bedroom-suite-balcony-avra-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Ermou Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-terrace-ermou-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-terrace-ermou-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-terrace-ermou-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-terrace-ermou-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-terrace-ermou-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-terrace-ermou-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-terrace-ermou-terrace-athens.webp',
			'8-chic-centre-suites-athens-suite-terrace-ermou-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Kohili Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-kohili-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-kohili-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-kohili-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-kohili-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-kohili-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-kohili-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-kohili-balcony-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-kohili-detail-athens.webp',
		],
	],
	[
		'title' => 'Korali Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-no-balcony-korali-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-no-balcony-korali-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-no-balcony-korali-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-no-balcony-korali-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-no-balcony-korali-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-no-balcony-korali-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-no-balcony-korali-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-no-balcony-korali-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Mykonos Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-terrace-mykonos-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-terrace-mykonos-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-terrace-mykonos-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-terrace-mykonos-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-terrace-mykonos-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-terrace-mykonos-terrace-athens.webp',
			'7-chic-centre-suites-athens-suite-terrace-mykonos-terrace-athens.webp',
			'8-chic-centre-suites-athens-suite-terrace-mykonos-detail-athens.webp',
			'9-chic-centre-suites-athens-suite-terrace-mykonos-bathroom-athens.webp',
			'10-chic-centre-suites-athens-suite-terrace-mykonos-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Paros Suite',
		'files' => [
			'1-chic-centre-suites-athens-one-bedroom-suite-paros-main-room-athens.webp',
			'2-chic-centre-suites-athens-one-bedroom-suite-paros-bedroom-athens.webp',
			'3-chic-centre-suites-athens-one-bedroom-suite-paros-living-area-athens.webp',
			'4-chic-centre-suites-athens-one-bedroom-suite-paros-kitchen-athens.webp',
			'5-chic-centre-suites-athens-one-bedroom-suite-paros-bathroom-athens.webp',
			'6-chic-centre-suites-athens-one-bedroom-suite-paros-bathroom-athens.webp',
			'7-chic-centre-suites-athens-one-bedroom-suite-paros-extra-angle-athens.webp',
			'8-chic-centre-suites-athens-one-bedroom-suite-paros-kitchen-athens.webp',
			'9-chic-centre-suites-athens-one-bedroom-suite-paros-layout-athens.webp',
			'10-chic-centre-suites-athens-one-bedroom-suite-paros-detail-athens.webp',
		],
	],
	[
		'title' => 'Pelagos Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-terrace-pelagos-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-terrace-pelagos-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-terrace-pelagos-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-terrace-pelagos-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-terrace-pelagos-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-terrace-pelagos-balcony-athens.webp',
			'7-chic-centre-suites-athens-suite-terrace-pelagos-terrace-athens.webp',
			'8-chic-centre-suites-athens-suite-terrace-pelagos-detail-athens.webp',
		],
	],
	[
		'title' => 'Santorini Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-santorini-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-santorini-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-santorini-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-santorini-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-santorini-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-santorini-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-santorini-extra-angle-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-santorini-detail-athens.webp',
		],
	],
	[
		'title' => 'Zakynthos Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-balcony-zakynthos-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-balcony-zakynthos-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-balcony-zakynthos-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-balcony-zakynthos-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-balcony-zakynthos-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-balcony-zakynthos-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-balcony-zakynthos-extra-angle-athens.webp',
			'8-chic-centre-suites-athens-suite-balcony-zakynthos-detail-athens.webp',
		],
	],

	// ── Thisseos 13 ─────────────────────────────────────────────────────────────
	[
		'title' => 'Forest Suite',
		'files' => [
			'1-chic-centre-suites-athens-deluxe-suite-forest-main-room-athens.webp',
			'2-chic-centre-suites-athens-deluxe-suite-forest-bedroom-athens.webp',
			'3-chic-centre-suites-athens-deluxe-suite-forest-living-area-athens.webp',
			'4-chic-centre-suites-athens-deluxe-suite-forest-kitchen-athens.webp',
			'5-chic-centre-suites-athens-deluxe-suite-forest-bathroom-athens.webp',
			'6-chic-centre-suites-athens-deluxe-suite-forest-detail-athens.webp',
			'7-chic-centre-suites-athens-deluxe-suite-forest-detail-athens.webp',
			'8-chic-centre-suites-athens-deluxe-suite-forest-layout-athens.webp',
			'9-chic-centre-suites-athens-deluxe-suite-forest-detail-athens.webp',
			'10-chic-centre-suites-athens-deluxe-suite-forest-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Ginger Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-no-balcony-ginger-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-no-balcony-ginger-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-no-balcony-ginger-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-no-balcony-ginger-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-no-balcony-ginger-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-no-balcony-ginger-layout-athens.webp',
			'7-chic-centre-suites-athens-suite-no-balcony-ginger-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-no-balcony-ginger-extra-angle-athens.webp',
		],
	],
	[
		'title' => 'Gray Suite',
		'files' => [
			'1-chic-centre-suites-athens-deluxe-suite-gray-main-room-athens.webp',
			'2-chic-centre-suites-athens-deluxe-suite-gray-bedroom-athens.webp',
			'3-chic-centre-suites-athens-deluxe-suite-gray-living-area-athens.webp',
			'4-chic-centre-suites-athens-deluxe-suite-gray-extra-angle-athens.webp',
			'5-chic-centre-suites-athens-deluxe-suite-gray-bathroom-athens.webp',
			'6-chic-centre-suites-athens-deluxe-suite-gray-bathroom-athens.webp',
			'7-chic-centre-suites-athens-deluxe-suite-gray-layout-athens.webp',
			'8-chic-centre-suites-athens-deluxe-suite-gray-kitchen-athens.webp',
		],
	],
	[
		'title' => 'Ocean Suite',
		'files' => [
			'1-chic-centre-suites-athens-deluxe-suite-ocean-main-room-athens.webp',
			'2-chic-centre-suites-athens-deluxe-suite-ocean-bedroom-athens.webp',
			'3-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
			'4-chic-centre-suites-athens-deluxe-suite-ocean-living-area-athens.webp',
			'5-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athen.webp',  // note: uploaded with typo "athen"
			'6-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
			'7-chic-centre-suites-athens-deluxe-suite-ocean-bathroom-athens.webp',
			'8-chic-centre-suites-athens-deluxe-suite-ocean-kitchen-athens.webp',
			'9-chic-centre-suites-athens-deluxe-suite-ocean-living-area-athens.webp',
			'10-chic-centre-suites-athens-deluxe-suite-ocean-bathroom-athens.webp',
		],
	],
	[
		'title' => 'Sunshine Suite',
		'files' => [
			'1-chic-centre-suites-athens-suite-no-balcony-sunshine-main-room-athens.webp',
			'2-chic-centre-suites-athens-suite-no-balcony-sunshine-bedroom-athens.webp',
			'3-chic-centre-suites-athens-suite-no-balcony-sunshine-living-area-athens.webp',
			'4-chic-centre-suites-athens-suite-no-balcony-sunshine-kitchen-athens.webp',
			'5-chic-centre-suites-athens-suite-no-balcony-sunshine-bathroom-athens.webp',
			'6-chic-centre-suites-athens-suite-no-balcony-sunshine-bathroom-athens.webp',
			'7-chic-centre-suites-athens-suite-no-balcony-sunshine-detail-athens.webp',
			'8-chic-centre-suites-athens-suite-no-balcony-sunshine-extra-angle-athens.webp',
		],
	],
];

// ── Admin page registration ───────────────────────────────────────────────────

add_action( 'admin_menu', function () {
	add_management_page(
		'Sync Suite Galleries',
		'Sync Suite Galleries',
		'manage_options',
		'chic-sync-galleries',
		'chic_sync_galleries_render_page'
	);
} );

// ── Admin page render ─────────────────────────────────────────────────────────

function chic_sync_galleries_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$total = count( CHIC_SUITE_MANIFEST );
	$nonce = wp_create_nonce( 'chic_sync_galleries' );
	?>
	<div class="wrap" id="chic-sync-wrap">
		<h1>Sync Suite Galleries</h1>
		<p>
			Matches each suite in the manifest to its MPHB accommodation post, then
			replaces its gallery (<code>mphb_gallery</code>) and featured image with
			the curated images from the media library — in the correct order.
			<strong>21 suites total.</strong>
		</p>
		<p>
			<strong>Always run Dry Run first</strong> to verify all suites are matched
			and all images are found before committing any changes.
		</p>
		<div id="chic-sync-controls">
			<button id="btn-dry" class="button button-primary">&#9654; Run Dry Run</button>
			<button id="btn-live" class="button" style="margin-left:8px;">&#9888; Run Live (writes changes)</button>
			<button id="btn-clear" class="button" style="margin-left:16px; float:right;">Clear log</button>
			<span id="chic-sync-counter" style="margin-left:16px; font-style:italic; color:#777;"></span>
		</div>
		<pre id="chic-sync-log"></pre>
	</div>

	<style>
		#chic-sync-wrap { max-width: 900px; }
		#chic-sync-controls { display: flex; align-items: center; flex-wrap: wrap; gap: 4px; margin-bottom: 12px; margin-top: 16px; }
		#chic-sync-counter { margin-left: 12px; flex: 1; text-align: right; }
		#chic-sync-log {
			background: #1e1e1e;
			color: #d4d4d4;
			font-size: 12px;
			line-height: 1.6;
			padding: 14px 16px;
			border-radius: 4px;
			height: 520px;
			overflow-y: auto;
			white-space: pre-wrap;
			word-break: break-all;
		}
		#chic-sync-log .lvl-head { color: #9cdcfe; font-weight: bold; }
		#chic-sync-log .lvl-ok   { color: #4ec9b0; }
		#chic-sync-log .lvl-warn { color: #dcdcaa; }
		#chic-sync-log .lvl-err  { color: #f44747; font-weight: bold; }
		#chic-sync-log .lvl-dry  { color: #c586c0; }
		#chic-sync-log .lvl-info { color: #d4d4d4; }
		#chic-sync-log .lvl-dim  { color: #6a9955; }
		#chic-sync-log .lvl-done { color: #4ec9b0; font-weight: bold; font-size: 13px; }
	</style>

	<script>
	(function () {
		var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce   = <?php echo wp_json_encode( $nonce ); ?>;
		var total   = <?php echo (int) $total; ?>;

		var btnDry     = document.getElementById('btn-dry');
		var btnLive    = document.getElementById('btn-live');
		var btnClear   = document.getElementById('btn-clear');
		var log        = document.getElementById('chic-sync-log');
		var counter    = document.getElementById('chic-sync-counter');
		var running    = false;

		btnClear.addEventListener('click', function () {
			log.innerHTML = '';
			counter.textContent = '';
		});

		btnDry.addEventListener('click', function () {
			startRun(true);
		});

		btnLive.addEventListener('click', function () {
			if (!confirm('This will overwrite existing galleries and featured images for all matched suites.\n\nProceed with live run?')) return;
			startRun(false);
		});

		function setRunning(state) {
			running = state;
			btnDry.disabled  = state;
			btnLive.disabled = state;
			btnClear.disabled = state;
		}

		function appendLines(lines) {
			lines.forEach(function (l) {
				var div = document.createElement('div');
				div.className = 'lvl-' + l.level;
				div.textContent = l.msg;
				log.appendChild(div);
			});
			log.scrollTop = log.scrollHeight;
		}

		function startRun(dryRun) {
			if (running) return;
			setRunning(true);
			log.innerHTML = '';
			counter.textContent = '';

			var modeLine = document.createElement('div');
			modeLine.className = 'lvl-head';
			modeLine.textContent = dryRun
				? '═══ DRY RUN — no changes will be written ═══'
				: '═══ LIVE RUN — changes WILL be written ═══';
			log.appendChild(modeLine);

			var acc = { processed: 0, images: 0, warnings: 0, errors: 0 };
			step(0, dryRun, acc);
		}

		function step(cursor, dryRun, acc) {
			var body = new URLSearchParams({
				action:  'chic_sync_galleries_step',
				_nonce:  nonce,
				cursor:  cursor,
				dry_run: dryRun ? '1' : '0',
			});

			fetch(ajaxurl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (data.log) appendLines(data.log);

					// Accumulate totals across all steps.
					if (data.totals) {
						acc.processed += data.totals.processed || 0;
						acc.images    += data.totals.images    || 0;
						acc.warnings  += data.totals.warnings  || 0;
						acc.errors    += data.totals.errors    || 0;
					}

					if (data.cursor !== undefined) {
						counter.textContent = 'Suite ' + data.cursor + ' of ' + total;
					}

					if (data.done) {
						var summary = document.createElement('div');
						summary.className = 'lvl-done';
						summary.textContent = [
							'',
							'══════════════════════════════════════════',
							'  COMPLETE',
							'  Suites processed : ' + acc.processed,
							'  Images linked    : ' + acc.images,
							'  Warnings         : ' + acc.warnings,
							'  Errors           : ' + acc.errors,
							'══════════════════════════════════════════',
						].join('\n');
						log.appendChild(summary);
						log.scrollTop = log.scrollHeight;
						counter.textContent = 'Done (' + total + '/' + total + ')';
						setRunning(false);
						return;
					}

					step(data.cursor, dryRun, acc);
				})
				.catch(function (err) {
					var div = document.createElement('div');
					div.className = 'lvl-err';
					div.textContent = 'FETCH ERROR at cursor ' + cursor + ': ' + err.message;
					log.appendChild(div);
					log.scrollTop = log.scrollHeight;
					setRunning(false);
				});
		}
	})();
	</script>
	<?php
}

// ── AJAX step handler ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_chic_sync_galleries_step', 'chic_sync_galleries_step' );

function chic_sync_galleries_step(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Forbidden', 403 );
	}

	if ( ! check_ajax_referer( 'chic_sync_galleries', '_nonce', false ) ) {
		wp_send_json_error( 'Bad nonce', 403 );
	}

	$manifest = CHIC_SUITE_MANIFEST;
	$cursor   = max( 0, (int) ( $_POST['cursor'] ?? 0 ) );
	$dry_run  = ! empty( $_POST['dry_run'] ) && '1' === $_POST['dry_run'];

	if ( $cursor >= count( $manifest ) ) {
		wp_send_json( [
			'log'    => [],
			'cursor' => $cursor,
			'done'   => true,
			'totals' => [ 'processed' => 0, 'images' => 0, 'warnings' => 0, 'errors' => 0 ],
		] );
	}

	$suite     = $manifest[ $cursor ];
	$title     = $suite['title'];
	$files     = $suite['files'];
	$next      = $cursor + 1;
	$done      = $next >= count( $manifest );

	$log      = [];
	$warnings = 0;
	$errors   = 0;
	$images   = 0;

	$log[] = [ 'level' => 'head', 'msg' => '' ];
	$log[] = [ 'level' => 'head', 'msg' => '── ' . $title . ' (' . count( $files ) . ' images) ──' ];

	// 1. Find MPHB post by title (normalized match).
	$post_id = chic_sync_find_room_type( $title );

	if ( ! $post_id ) {
		$log[] = [ 'level' => 'err', 'msg' => '  ERROR: No mphb_room_type post found matching "' . $title . '" — skipping.' ];
		$errors++;
		wp_send_json( [
			'log'    => $log,
			'cursor' => $next,
			'done'   => $done,
			'totals' => [ 'processed' => 0, 'images' => 0, 'warnings' => $warnings, 'errors' => $errors ],
		] );
	}

	$log[] = [ 'level' => 'ok', 'msg' => '  Matched → Post ID ' . $post_id . ' ("' . get_the_title( $post_id ) . '")' ];

	// Show current gallery meta for the user to verify the key is correct.
	$current_gallery = get_post_meta( $post_id, 'mphb_gallery', true );
	$current_thumb   = get_post_thumbnail_id( $post_id );
	$log[] = [ 'level' => 'dim', 'msg' => '  Current mphb_gallery  : ' . ( $current_gallery ?: '(empty)' ) ];
	$log[] = [ 'level' => 'dim', 'msg' => '  Current featured img  : ' . ( $current_thumb ? $current_thumb : '(none)' ) ];

	// 2. Find attachments for each filename in one DB round-trip.
	$filename_map = chic_sync_find_attachments_by_filenames( $files );

	$ordered_ids = [];
	foreach ( $files as $filename ) {
		$matches = $filename_map[ $filename ] ?? [];
		if ( empty( $matches ) ) {
			$log[] = [ 'level' => 'warn', 'msg' => '  WARN: No attachment found for "' . $filename . '"' ];
			$warnings++;
		} elseif ( count( $matches ) > 1 ) {
			$chosen = $matches[0]; // lowest ID
			$log[]  = [ 'level' => 'warn', 'msg' => '  WARN: ' . count( $matches ) . ' attachments match "' . $filename . '" — using ID ' . $chosen . ' (lowest)' ];
			$warnings++;
			$ordered_ids[] = $chosen;
			$images++;
		} else {
			$ordered_ids[] = $matches[0];
			$log[]         = [ 'level' => 'info', 'msg' => '  ID ' . $matches[0] . '  ← ' . $filename ];
			$images++;
		}
	}

	if ( empty( $ordered_ids ) ) {
		$log[]  = [ 'level' => 'err', 'msg' => '  ERROR: Zero images resolved — skipping writes.' ];
		$errors++;
		wp_send_json( [
			'log'    => $log,
			'cursor' => $next,
			'done'   => $done,
			'totals' => [ 'processed' => 0, 'images' => 0, 'warnings' => $warnings, 'errors' => $errors ],
		] );
	}

	$gallery_csv  = implode( ',', $ordered_ids );
	$featured_id  = $ordered_ids[0];

	if ( $dry_run ) {
		$log[] = [ 'level' => 'dry', 'msg' => '  [DRY] Would set mphb_gallery    → ' . $gallery_csv ];
		$log[] = [ 'level' => 'dry', 'msg' => '  [DRY] Would set featured image  → ' . $featured_id ];
	} else {
		update_post_meta( $post_id, 'mphb_gallery', $gallery_csv );
		set_post_thumbnail( $post_id, $featured_id );
		$log[] = [ 'level' => 'ok', 'msg' => '  OK  mphb_gallery updated    → ' . $gallery_csv ];
		$log[] = [ 'level' => 'ok', 'msg' => '  OK  Featured image set      → ' . $featured_id ];
	}

	wp_send_json( [
		'log'    => $log,
		'cursor' => $next,
		'done'   => $done,
		'totals' => [
			'processed' => 1,
			'images'    => $images,
			'warnings'  => $warnings,
			'errors'    => $errors,
		],
	] );
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Find an mphb_room_type post by normalized title.
 * Normalization: lowercase + collapse whitespace + trim.
 * Tries get_page_by_title first (fast), then falls back to a full scan
 * (handles cases where WP title sanitization differs).
 */
function chic_sync_find_room_type( string $title ): int {
	$normalize = fn( string $s ): string => strtolower( preg_replace( '/\s+/', ' ', trim( $s ) ) );

	// Fast path.
	$post = get_page_by_title( $title, OBJECT, 'mphb_room_type' );
	if ( $post ) return (int) $post->ID;

	// Scan path — handles minor title mismatches.
	$needle = $normalize( $title );
	$posts  = get_posts( [
		'post_type'      => 'mphb_room_type',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'all',
		'no_found_rows'  => true,
	] );

	foreach ( $posts as $p ) {
		if ( $normalize( $p->post_title ) === $needle ) {
			return (int) $p->ID;
		}
	}

	return 0;
}

/**
 * Given an array of bare filenames, returns a map of filename => [attachment_ids...].
 * Uses a single SQL query per call. _wp_attached_file stores "year/month/filename",
 * so we match the trailing segment.
 */
function chic_sync_find_attachments_by_filenames( array $filenames ): array {
	global $wpdb;

	if ( empty( $filenames ) ) return [];

	// Build LIKE conditions: meta_value LIKE '%/filename' OR meta_value = 'filename'
	$conditions = [];
	$values     = [];

	foreach ( $filenames as $f ) {
		$f_esc        = $wpdb->esc_like( $f );
		$conditions[] = '( pm.meta_value LIKE %s OR pm.meta_value = %s )';
		$values[]     = '%/' . $f_esc;
		$values[]     = $f_esc;
	}

	$sql = $wpdb->prepare(
		'SELECT pm.post_id, pm.meta_value
		 FROM ' . $wpdb->postmeta . ' pm
		 INNER JOIN ' . $wpdb->posts . ' p ON p.ID = pm.post_id
		 WHERE pm.meta_key = \'_wp_attached_file\'
		   AND p.post_type = \'attachment\'
		   AND ( ' . implode( ' OR ', $conditions ) . ' )
		 ORDER BY pm.post_id ASC',
		$values
	);

	$rows = $wpdb->get_results( $sql );

	// Build map: basename => [post_ids]
	$map = [];
	foreach ( $rows as $row ) {
		$basename = basename( $row->meta_value );
		$map[ $basename ][] = (int) $row->post_id;
	}

	// Re-key by the original requested filenames (handles any case normalisation).
	$result = [];
	foreach ( $filenames as $f ) {
		$result[ $f ] = $map[ $f ] ?? [];
	}

	return $result;
}
