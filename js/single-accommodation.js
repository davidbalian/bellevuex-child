/* ── Parallax (static hero image) ────────────────────────────────────────── */
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

/* ── "View Photos" smooth scroll ─────────────────────────────────────────── */
document.addEventListener( 'DOMContentLoaded', function () {
	document.querySelectorAll( '.js-view-photos' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var href = link.getAttribute( 'href' );
			if ( ! href || href.indexOf( '#' ) !== 0 ) return;
			var target = document.querySelector( href );
			if ( target ) target.scrollIntoView( { behavior: 'smooth' } );
		} );
	} );
} );

/* ── Gallery swiper (native aspect ratios, auto-width slides) ────────────── */
( function () {
	function init() {
		if ( typeof Swiper === 'undefined' ) return;

		var reduced = window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		document.querySelectorAll( '.js-gallery-swiper' ).forEach( function ( root ) {
			var inner    = root.closest( '.home-suites__inner' );
			var controls = inner && inner.querySelector( '.home-suites__controls' );
			var prev     = controls && controls.querySelector( '[data-nav="prev"]' );
			var next     = controls && controls.querySelector( '[data-nav="next"]' );

			new Swiper( root, {
				slidesPerView: 'auto',
				spaceBetween:  16,
				loop:          true,
				speed:         reduced ? 0 : 400,
				grabCursor:    true,
				autoplay:      reduced ? false : {
					delay:                3000,
					disableOnInteraction: false,
				},
				navigation:    { prevEl: prev, nextEl: next },
			} );
		} );
	}

	// Wait for full load so WP lazy images have intrinsic dimensions set
	if ( document.readyState === 'complete' ) {
		init();
	} else {
		window.addEventListener( 'load', init );
	}
} )();
