( function () {
	const taglines = [
		'Our suites, spread across three buildings on the same road.',
		'Book now for a comfortable, enjoyable and memorable accommodation in Athens!',
		'Our luxury suites will offer you an unforgettable experience through your stay!',
	];

	const el = document.querySelector( '.home-hero__tagline' );
	if ( ! el || taglines.length < 2 ) return;

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

	let index    = 0;
	let timer    = null;

	function advance() {
		el.classList.add( 'is-fading' );
		setTimeout( function () {
			index = ( index + 1 ) % taglines.length;
			el.textContent = taglines[ index ];
			el.classList.remove( 'is-fading' );
		}, 400 );
	}

	function start() {
		timer = setInterval( advance, 4000 );
	}

	function stop() {
		clearInterval( timer );
	}

	el.textContent = taglines[ 0 ];
	start();

	el.closest( '.home-hero' )?.addEventListener( 'mouseenter', stop );
	el.closest( '.home-hero' )?.addEventListener( 'mouseleave', start );
} )();
