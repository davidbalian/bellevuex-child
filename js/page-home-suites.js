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

			var inst = new Swiper( root, {
				slidesPerView: 1.5,
				spaceBetween: 16,
				loop: true,
				speed: reduced ? 0 : 300,
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

			// Wire mobile nav buttons (below carousel) to the same instance
			var inner    = root.parentElement;
			var mobileNav = inner && inner.querySelector( '.home-suites__mobile-nav' );
			if ( mobileNav ) {
				mobileNav.querySelector( '[data-nav="prev"]' ).addEventListener( 'click', function () {
					inst.slidePrev();
				} );
				mobileNav.querySelector( '[data-nav="next"]' ).addEventListener( 'click', function () {
					inst.slideNext();
				} );
			}
		} );
	}

	if ( document.readyState === 'complete' ) {
		init();
	} else {
		window.addEventListener( 'load', init );
	}
} )();
