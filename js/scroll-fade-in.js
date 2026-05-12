( function () {
	var THRESHOLD = 0.15;

	// Auto-stagger: direct children of [data-fade-stagger] get fade-in + delay classes
	document.querySelectorAll( '[data-fade-stagger]' ).forEach( function ( parent ) {
		Array.from( parent.children ).forEach( function ( child, i ) {
			child.classList.add( 'fade-in', 'fade-in-delay-' + Math.min( i, 10 ) );
		} );
	} );

	var targets = document.querySelectorAll( '.fade-in' );

	// Reduced motion or no IntersectionObserver: show everything immediately
	if (
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ||
		typeof IntersectionObserver === 'undefined'
	) {
		targets.forEach( function ( el ) { el.classList.add( 'visible' ); } );
		return;
	}

	var observer = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( ! entry.isIntersecting ) return;

			var el = entry.target;
			observer.unobserve( el );
			el.classList.add( 'visible' );

			// Strip animation classes after animation ends so hover/transform effects work cleanly
			el.addEventListener( 'animationend', function cleanup() {
				el.removeEventListener( 'animationend', cleanup );
				el.classList.remove( 'visible', 'fade-in' );
				// Remove any delay class
				el.classList.forEach( function ( cls ) {
					if ( /^fade-in-delay-/.test( cls ) ) el.classList.remove( cls );
				} );
			} );
		} );
	}, { threshold: THRESHOLD } );

	targets.forEach( function ( el ) { observer.observe( el ); } );
} )();
