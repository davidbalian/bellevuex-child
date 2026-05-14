/* ── Swiper init + Ken Burns ─────────────────────────────────────────────── */
( function () {
	var SLIDER_SELECTOR   = '.js-hero-swiper';
	var READY_CLASS       = 'is-ready';
	var DECODE_TIMEOUT_MS = 1500;
	var TRANSITION_MS     = 1000;
	var AUTOPLAY_DELAY_MS = 4000;
	var KB_KEYFRAMES      = [ { transform: 'scale(1)' }, { transform: 'scale(1.02)' } ];
	var KB_ANIM_KEY       = '_kenBurnsAnim';

	function prefersReducedMotion() {
		return window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function clearKenBurns( img ) {
		if ( ! img ) return;
		var anim = img[ KB_ANIM_KEY ];
		if ( anim ) { anim.cancel(); img[ KB_ANIM_KEY ] = null; }
		img.style.transform = '';
	}

	function startKenBurns( img ) {
		if ( ! img || typeof img.animate !== 'function' ) return;
		clearKenBurns( img );
		img[ KB_ANIM_KEY ] = img.animate( KB_KEYFRAMES, {
			duration: AUTOPLAY_DELAY_MS,
			easing: 'ease-out',
			fill: 'forwards',
		} );
	}

	function getImg( slideEl ) {
		return slideEl ? slideEl.querySelector( 'img' ) : null;
	}

	function whenImageReady( img, timeout ) {
		return new Promise( function ( resolve ) {
			var done = false;
			function finish() { if ( ! done ) { done = true; resolve(); } }
			setTimeout( finish, timeout );
			if ( ! img ) { finish(); return; }
			if ( img.complete && img.naturalWidth > 0 ) {
				typeof img.decode === 'function' ? img.decode().then( finish, finish ) : finish();
				return;
			}
			img.addEventListener( 'load',  finish, { once: true } );
			img.addEventListener( 'error', finish, { once: true } );
		} );
	}

	function warmDecodeImg( img ) {
		if ( img && typeof img.decode === 'function' ) img.decode().catch( function () {} );
	}

	function reveal( root ) {
		requestAnimationFrame( function () {
			requestAnimationFrame( function () { root.classList.add( READY_CLASS ); } );
		} );
	}

	function buildConfig( reducedMotion ) {
		var on = reducedMotion ? {} : {
			afterInit: function () {
				startKenBurns( getImg( this.slides[ this.activeIndex ] ) );
			},
			slideChangeTransitionStart: function () {
				if ( this.previousIndex === this.activeIndex ) return;
				startKenBurns( getImg( this.slides[ this.activeIndex ] ) );
				// Warm the next slide so it's decoded before autoplay reaches it.
				var nextIdx = ( this.activeIndex + 1 ) % this.slides.length;
				warmDecodeImg( getImg( this.slides[ nextIdx ] ) );
			},
			slideChangeTransitionEnd: function () {
				if ( this.previousIndex === this.activeIndex ) return;
				clearKenBurns( getImg( this.slides[ this.previousIndex ] ) );
			},
		};

		return {
			effect: 'fade',
			fadeEffect: { crossFade: true },
			loop: true,
			slidesPerView: 1,
			speed: TRANSITION_MS,
			autoplay: reducedMotion ? false : {
				delay: AUTOPLAY_DELAY_MS,
				disableOnInteraction: false,
			},
			on: on,
		};
	}

	function initSlider( root ) {
		if ( typeof Swiper === 'undefined' ) { reveal( root ); return; }
		var reducedMotion = prefersReducedMotion();
		var firstImg = root.querySelector( 'img' );
		whenImageReady( firstImg, DECODE_TIMEOUT_MS ).then( function () {
			new Swiper( root, buildConfig( reducedMotion ) );
			reveal( root );
		} );
	}

	function init() {
		document.querySelectorAll( SLIDER_SELECTOR ).forEach( initSlider );
	}

	document.readyState === 'loading'
		? document.addEventListener( 'DOMContentLoaded', init )
		: init();
} )();

/* ── Parallax (slider only) ──────────────────────────────────────────────── */
( function () {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	var FACTOR    = 0.25;
	var MOBILE_BP = 768;

	var sliderBleed = document.querySelector( '.js-hero-parallax' ) || document.querySelector( '.home-hero__slider-bleed' );
	var heroSection = sliderBleed ? sliderBleed.closest( '.home-hero' ) : null;
	if ( ! sliderBleed ) return;

	var isMobile  = window.innerWidth <= MOBILE_BP;
	var heroHeight = heroSection ? heroSection.offsetHeight : 0;
	var ticking   = false;

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

/* ── Chevron smooth scroll ───────────────────────────────────────────────── */
document.addEventListener( 'DOMContentLoaded', function () {
	document.querySelectorAll( '.js-hero-scroll-next' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var href = link.getAttribute( 'href' );
			if ( ! href || href.indexOf( '#' ) !== 0 ) return;
			var target = document.querySelector( href );
			if ( target ) target.scrollIntoView( { behavior: 'smooth' } );
		} );
	} );
} );
