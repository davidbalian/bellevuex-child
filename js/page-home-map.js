( function () {
	'use strict';

	var SELECTOR = '.js-home-map';
	var MAP_ZOOM = 20;
	var TILE_URL = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
	var TILE_OPTS = {
		subdomains: 'abcd',
		maxZoom: 20,
	};

	function parseMarkers( root ) {
		var raw = root.getAttribute( 'data-markers' );
		if ( ! raw ) {
			return [];
		}

		try {
			var parsed = JSON.parse( raw );
			return Array.isArray( parsed ) ? parsed : [];
		} catch ( err ) {
			return [];
		}
	}

	function escapeHtml( value ) {
		return String( value )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function markerHtml( marker ) {
		var name = escapeHtml( marker.name || '' );
		return (
			'<button type="button" class="home-map__marker" aria-label="' + name + '">' +
				'<span class="home-map__label">' + name + '</span>' +
				'<span class="home-map__dot" aria-hidden="true"></span>' +
			'</button>'
		);
	}

	function openMapsUrl( url ) {
		if ( ! url ) {
			return;
		}
		window.open( url, '_blank', 'noopener,noreferrer' );
	}

	function createMarker( map, marker, reducedMotion ) {
		var icon = L.divIcon( {
			className: 'home-map__pin',
			html: markerHtml( marker ),
			iconSize: [ 132, 58 ],
			iconAnchor: [ 66, 52 ],
		} );

		var leafletMarker = L.marker( [ marker.lat, marker.lng ], {
			icon: icon,
			keyboard: false,
		} ).addTo( map );

		leafletMarker.on( 'click', function () {
			openMapsUrl( marker.url );
		} );

		var button = leafletMarker.getElement() && leafletMarker.getElement().querySelector( '.home-map__marker' );
		if ( button ) {
			button.addEventListener( 'click', function ( event ) {
				event.stopPropagation();
				openMapsUrl( marker.url );
			} );
		}

		return leafletMarker;
	}

	function setMapView( map, group, reducedMotion ) {
		var center = group.getBounds().getCenter();
		map.setView( center, MAP_ZOOM, { animate: ! reducedMotion } );
	}

	function initMap( root ) {
		if ( root.dataset.mapReady === '1' || typeof L === 'undefined' ) {
			return;
		}

		var markers = parseMarkers( root );
		if ( ! markers.length ) {
			return;
		}

		root.dataset.mapReady = '1';

		var reducedMotion = window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		var map = L.map( root, {
			scrollWheelZoom: false,
			zoomControl: false,
			attributionControl: false,
			fadeAnimation: ! reducedMotion,
			zoomAnimation: ! reducedMotion,
		} );

		L.tileLayer( TILE_URL, TILE_OPTS ).addTo( map );

		var leafletMarkers = markers.map( function ( marker ) {
			return createMarker( map, marker, reducedMotion );
		} );

		var group = L.featureGroup( leafletMarkers );

		// Fixed street-level zoom — fitBounds was too conservative for three adjacent buildings.
		setMapView( map, group, reducedMotion );

		requestAnimationFrame( function () {
			map.invalidateSize();
			setMapView( map, group, reducedMotion );
		} );
	}

	function observeMap( root ) {
		if ( 'IntersectionObserver' in window ) {
			var observer = new IntersectionObserver( function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}
					initMap( root );
					obs.disconnect();
				} );
			}, {
				rootMargin: '120px 0px',
				threshold: 0,
			} );

			observer.observe( root );
			return;
		}

		initMap( root );
	}

	function boot() {
		document.querySelectorAll( SELECTOR ).forEach( observeMap );
	}

	if ( document.readyState === 'complete' ) {
		boot();
	} else {
		window.addEventListener( 'load', boot );
	}
} )();
