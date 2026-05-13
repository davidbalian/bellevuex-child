( function () {
	var SELECTOR = '.js-suites-swiper';

	function init() {
		if ( typeof Swiper === 'undefined' ) return;

		var reduced = window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		document.querySelectorAll( SELECTOR ).forEach( function ( root ) {
			var controls = root.closest( '.home-suites__inner' ) &&
				root.closest( '.home-suites__inner' ).querySelector( '.home-suites__controls' );
			var prev = controls && controls.querySelector( '[data-nav="prev"]' );
			var next = controls && controls.querySelector( '[data-nav="next"]' );

			new Swiper( root, {
				slidesPerView: 1.25,
				spaceBetween: 16,
				loop: true,
				speed: reduced ? 0 : 400,
				grabCursor: true,
				autoplay: reduced ? false : {
					delay: 3000,
					disableOnInteraction: false,
				},
				navigation: { prevEl: prev, nextEl: next },
				breakpoints: {
					768:  { slidesPerView: 2.5, spaceBetween: 20 },
					1024: { slidesPerView: 3.5, spaceBetween: 24 },
				},
			} );
		} );
	}

	if ( document.readyState === 'complete' ) {
		init();
	} else {
		window.addEventListener( 'load', init );
	}
} )();
