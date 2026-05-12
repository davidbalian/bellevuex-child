( function () {
	const html = document.documentElement;
	let rafPending = false;
	/** Full-height header while `scrollY` is at or below this value (px). */
	const EXPAND_MAX_SCROLL_Y = 4;
	/** Cushion above measured header height delta so layout reflow cannot cross both thresholds in one step. */
	const SHRINK_THRESHOLD_MARGIN_PX = 6;
	/** If token parsing fails, assume at least this delta (≈1rem) so hysteresis stays safe. */
	const MIN_LAYOUT_DELTA_FALLBACK_PX = 12;

	let shrinkThresholdPx = EXPAND_MAX_SCROLL_Y + MIN_LAYOUT_DELTA_FALLBACK_PX + SHRINK_THRESHOLD_MARGIN_PX;

	function headerLayoutDeltaPx() {
		const cs = getComputedStyle( document.documentElement );
		const rootFontPx = parseFloat( cs.fontSize ) || 16;
		const parseLen = function ( raw ) {
			const t = String( raw || '' ).trim();
			const n = parseFloat( t );
			if ( ! isFinite( n ) ) {
				return 0;
			}
			if ( /rem$/i.test( t ) ) {
				return n * rootFontPx;
			}
			if ( /em$/i.test( t ) ) {
				return n * rootFontPx;
			}
			return n;
		};
		const hInitial = parseLen( cs.getPropertyValue( '--site-header-height-initial' ) );
		const hScrolled = parseLen( cs.getPropertyValue( '--site-header-height-scrolled' ) );
		return Math.max( 0, hInitial - hScrolled );
	}

	function recalcShrinkThreshold() {
		const delta = Math.max( headerLayoutDeltaPx(), MIN_LAYOUT_DELTA_FALLBACK_PX );
		shrinkThresholdPx = EXPAND_MAX_SCROLL_Y + delta + SHRINK_THRESHOLD_MARGIN_PX;
	}

	function applyScrollState() {
		const y = window.scrollY;
		if ( y > shrinkThresholdPx ) {
			html.classList.add( 'is-header-scrolled' );
		} else if ( y <= EXPAND_MAX_SCROLL_Y ) {
			html.classList.remove( 'is-header-scrolled' );
		}
	}

	function update() {
		rafPending = false;
		applyScrollState();
	}

	function requestUpdate() {
		if ( rafPending ) {
			return;
		}
		rafPending = true;
		requestAnimationFrame( update );
	}

	function onResize() {
		recalcShrinkThreshold();
		applyScrollState();
	}

	recalcShrinkThreshold();
	applyScrollState();

	window.addEventListener( 'scroll', requestUpdate, { passive: true } );
	window.addEventListener( 'resize', onResize, { passive: true } );
} )();
