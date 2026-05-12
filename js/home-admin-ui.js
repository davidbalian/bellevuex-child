( function () {
	var list      = document.getElementById( 'chic-hero-slides-list' );
	var dataInput = document.getElementById( 'chic-hero-slides-data' );
	var addBtn    = document.getElementById( 'chic-hero-slides-add' );

	if ( ! list || ! dataInput || ! addBtn ) return;

	function getIds() {
		try { return JSON.parse( dataInput.value ) || []; } catch(e) { return []; }
	}

	function setIds( ids ) {
		dataInput.value = JSON.stringify( ids );
	}

	function buildThumb( id, thumbUrl ) {
		var div = document.createElement( 'div' );
		div.className   = 'chic-hs-thumb';
		div.draggable   = true;
		div.dataset.id  = id;

		var img = document.createElement( 'img' );
		img.src = thumbUrl;
		img.alt = '';

		var btn = document.createElement( 'button' );
		btn.type      = 'button';
		btn.className = 'chic-hs-remove';
		btn.setAttribute( 'aria-label', 'Remove slide' );
		btn.textContent = '×';
		btn.addEventListener( 'click', function () {
			div.remove();
			setIds( readCurrentIds() );
		} );

		div.appendChild( img );
		div.appendChild( btn );
		bindDrag( div );
		return div;
	}

	function readCurrentIds() {
		return Array.from( list.querySelectorAll( '.chic-hs-thumb' ) )
			.map( function ( el ) { return parseInt( el.dataset.id, 10 ); } );
	}

	// ── Drag-to-reorder ───────────────────────────────────────────────────────

	var dragged = null;

	function bindDrag( el ) {
		el.addEventListener( 'dragstart', function () { dragged = el; } );
		el.addEventListener( 'dragend',   function () {
			dragged = null;
			list.querySelectorAll( '.drag-over' ).forEach( function ( n ) { n.classList.remove( 'drag-over' ); } );
			setIds( readCurrentIds() );
		} );
		el.addEventListener( 'dragover', function ( e ) {
			e.preventDefault();
			if ( dragged && dragged !== el ) el.classList.add( 'drag-over' );
		} );
		el.addEventListener( 'dragleave', function () { el.classList.remove( 'drag-over' ); } );
		el.addEventListener( 'drop', function ( e ) {
			e.preventDefault();
			el.classList.remove( 'drag-over' );
			if ( dragged && dragged !== el ) {
				var items = Array.from( list.children );
				var fromIdx = items.indexOf( dragged );
				var toIdx   = items.indexOf( el );
				if ( fromIdx < toIdx ) list.insertBefore( dragged, el.nextSibling );
				else list.insertBefore( dragged, el );
			}
		} );
	}

	// Init drag on existing thumbs
	list.querySelectorAll( '.chic-hs-thumb' ).forEach( bindDrag );

	// ── Media picker ─────────────────────────────────────────────────────────

	addBtn.addEventListener( 'click', function () {
		var frame = wp.media( {
			title:    'Select Hero Slides',
			button:   { text: 'Add to slider' },
			multiple: true,
		} );

		frame.on( 'select', function () {
			var existingIds = readCurrentIds();
			frame.state().get( 'selection' ).each( function ( attachment ) {
				var id   = attachment.id;
				if ( existingIds.indexOf( id ) !== -1 ) return;
				var sizes = attachment.attributes.sizes || {};
				var thumb = ( sizes.thumbnail || sizes.medium || attachment.attributes ).url;
				var el    = buildThumb( id, thumb );
				list.appendChild( el );
				existingIds.push( id );
			} );
			setIds( readCurrentIds() );
		} );

		frame.open();
	} );
} )();
