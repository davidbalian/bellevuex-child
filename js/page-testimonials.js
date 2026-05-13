/* ── Parallax (static hero image) — same behavior as single-accommodation ─── */
( function () {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	var FACTOR    = 0.25;
	var MOBILE_BP = 768;

	var sliderBleed = document.querySelector( '.js-hero-parallax' );
	var heroSection = sliderBleed ? sliderBleed.closest( '.home-hero' ) : null;
	if ( ! sliderBleed ) return;

	var isMobile   = window.innerWidth <= MOBILE_BP;
	var heroHeight = heroSection ? heroSection.offsetHeight : 0;
	var ticking    = false;

	function clearTransform() {
		sliderBleed.style.transform = '';
	}

	function update() {
		if ( isMobile ) { ticking = false; return; }
		var scrollY = window.scrollY;
		if ( scrollY <= heroHeight ) {
			sliderBleed.style.transform = 'translate3d(0, ' + ( scrollY * FACTOR ) + 'px, 0)';
		}
		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ticking ) return;
		requestAnimationFrame( update );
		ticking = true;
	}, { passive: true } );

	window.addEventListener( 'resize', function () {
		var wasMobile = isMobile;
		isMobile = window.innerWidth <= MOBILE_BP;
		if ( heroSection ) heroHeight = heroSection.offsetHeight;
		if ( ! wasMobile && isMobile ) clearTransform();
	}, { passive: true } );

	if ( isMobile ) clearTransform();
	else requestAnimationFrame( update );
} )();
