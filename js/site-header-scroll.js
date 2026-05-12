( function () {
	const html = document.documentElement;
	let rafPending = false;

	function update() {
		rafPending = false;
		if ( window.scrollY > 8 ) {
			html.classList.add( 'is-header-scrolled' );
		} else if ( window.scrollY <= 4 ) {
			html.classList.remove( 'is-header-scrolled' );
		}
	}

	function onScroll() {
		if ( rafPending ) return;
		rafPending = true;
		requestAnimationFrame( update );
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	update();
} )();
