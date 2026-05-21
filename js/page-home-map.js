( function () {
	'use strict';

	var SELECTOR = '.js-home-map';
	var MAP_ZOOM = 20;
	var MAP_MIN_ZOOM = 16;
	var MAP_MAX_ZOOM = 20;
	var LABEL_BELOW_TERM = 'thiseos-11';
	var TILE_URL = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
	var TILE_OPTS = {
		subdomains: 'abcd',
		maxZoom: MAP_MAX_ZOOM,
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

	function isLabelBelow( marker ) {
		return marker.term === LABEL_BELOW_TERM;
	}

	function markerHtml( marker ) {
		var name = escapeHtml( marker.name || '' );
		var below = isLabelBelow( marker );
		var markerClass = 'home-map__marker' + ( below ? ' home-map__marker--label-below' : '' );
		var label = '<span class="home-map__label">' + name + '</span>';
		var dot = '<span class="home-map__dot" aria-hidden="true"></span>';

		return (
			'<button type="button" class="' + markerClass + '" aria-label="' + name + '">' +
				( below ? dot + label : label + dot ) +
			'</button>'
		);
	}

	function markerIconOptions( marker ) {
		if ( isLabelBelow( marker ) ) {
			return {
				iconSize: [ 132, 58 ],
				iconAnchor: [ 66, 7 ],
			};
		}

		return {
			iconSize: [ 132, 58 ],
			iconAnchor: [ 66, 52 ],
		};
	}

	function openMapsUrl( url ) {
		if ( ! url ) {
			return;
		}
		window.open( url, '_blank', 'noopener,noreferrer' );
	}

	function createMarker( map, marker ) {
		var iconOpts = markerIconOptions( marker );
		var icon = L.divIcon( {
			className: 'home-map__pin',
			html: markerHtml( marker ),
			iconSize: iconOpts.iconSize,
			iconAnchor: iconOpts.iconAnchor,
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

	function getGroupCenter( group ) {
		return group.getBounds().getCenter();
	}

	function finalizeMapView( map, group ) {
		map.invalidateSize( { animate: false } );
		map.setView( getGroupCenter( group ), MAP_ZOOM, { animate: false } );
	}

	function bindViewFinalizers( map, group, root ) {
		var finalized = false;

		function runFinalize() {
			finalizeMapView( map, group );
		}

		runFinalize();

		map.whenReady( runFinalize );

		if ( 'ResizeObserver' in window ) {
			var resizeObserver = new ResizeObserver( function ( entries ) {
				var entry = entries[ 0 ];
				if ( ! entry || entry.contentRect.width <= 0 || entry.contentRect.height <= 0 ) {
					return;
				}

				runFinalize();

				if ( ! finalized ) {
					finalized = true;
					resizeObserver.disconnect();
				}
			} );

			resizeObserver.observe( root );
		}

		requestAnimationFrame( function () {
			requestAnimationFrame( runFinalize );
		} );
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
			minZoom: MAP_MIN_ZOOM,
			maxZoom: MAP_MAX_ZOOM,
			fadeAnimation: ! reducedMotion,
			zoomAnimation: ! reducedMotion,
		} );

		L.control.zoom( { position: 'bottomright' } ).addTo( map );

		var tileLayer = L.tileLayer( TILE_URL, TILE_OPTS ).addTo( map );

		var leafletMarkers = markers.map( function ( marker ) {
			return createMarker( map, marker );
		} );

		var group = L.featureGroup( leafletMarkers );

		tileLayer.on( 'load', function () {
			finalizeMapView( map, group );
		} );

		bindViewFinalizers( map, group, root );
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
