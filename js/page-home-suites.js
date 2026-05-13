( function () {
	var SELECTOR = '.js-suites-swiper';

	function init() {
		if ( typeof Swiper === 'undefined' ) return;

		var reduced = window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		document.querySelectorAll( SELECTOR ).forEach( function ( root ) {
			var id   = root.id;
			var prev = document.querySelector( '[data-nav="prev"][data-target="' + id + '"]' );
			var next = document.querySelector( '[data-nav="next"][data-target="' + id + '"]' );

			new Swiper( root, {
				slidesPerView: 1.5,
				spaceBetween: 16,
				loop: true,
				speed: reduced ? 0 : 600,
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
