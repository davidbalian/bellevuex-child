( function () {
	'use strict';

	var SELECTOR = '.js-home-map';
	var MAP_ZOOM = 17;
	var MAP_MIN_ZOOM = 16;
	var MAP_MAX_ZOOM = 20;
	var LABEL_BELOW_TERM = 'thiseos-11';
	var TILE_URL_EL = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
	var STYLE_URL_EN = 'https://tiles.openfreemap.org/styles/positron';
	var TILE_OPTS = {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
		subdomains: 'abcd',
		maxZoom: MAP_MAX_ZOOM,
	};
	var EN_LABEL_TEXT_FIELD = [
		'coalesce',
		[ 'get', 'name:en' ],
		[ 'get', 'name:latin' ],
	];

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
		if ( marker.term === LABEL_BELOW_TERM ) {
			return true;
		}

		return /Thiseos 11|Θησέως 11/i.test( String( marker.name || '' ) );
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

	function getMarkerBounds( markers ) {
		return L.latLngBounds(
			markers.map( function ( marker ) {
				return [ marker.lat, marker.lng ];
			} )
		);
	}

	function openMapsUrl( url ) {
		if ( ! url ) {
			return;
		}
		window.open( url, '_blank', 'noopener,noreferrer' );
	}

	function getMapLang( root ) {
		return root.getAttribute( 'data-map-lang' ) === 'el' ? 'el' : 'en';
	}

	function textFieldUsesPlaceNames( textField ) {
		var raw = JSON.stringify( textField || '' );
		return /name[_:]?(en|latin|nonlatin|el)/.test( raw );
	}

	function shouldApplyEnglishLabels( layer ) {
		if ( layer.type !== 'symbol' || ! layer.layout || ! layer.layout[ 'text-field' ] ) {
			return false;
		}

		if ( /^(highway-name-|label_|water)/.test( layer.id ) || layer.id === 'airport' ) {
			return true;
		}

		return textFieldUsesPlaceNames( layer.layout[ 'text-field' ] );
	}

	function applyEnglishLabels( glMap ) {
		var style = glMap.getStyle();
		if ( ! style || ! style.layers ) {
			return;
		}

		style.layers.forEach( function ( layer ) {
			if ( ! shouldApplyEnglishLabels( layer ) ) {
				return;
			}

			glMap.setLayoutProperty( layer.id, 'text-field', EN_LABEL_TEXT_FIELD );
		} );
	}

	function bindEnglishLabels( glMap ) {
		function apply() {
			applyEnglishLabels( glMap );
		}

		if ( glMap.isStyleLoaded() ) {
			apply();
		} else {
			glMap.once( 'load', apply );
		}

		glMap.on( 'styledata', function ( event ) {
			if ( event.dataType === 'style' && glMap.isStyleLoaded() ) {
				apply();
			}
		} );
	}

	function addBasemap( map, lang ) {
		if ( lang === 'en' && typeof L.maplibreGL === 'function' ) {
			var vectorLayer = L.maplibreGL( {
				style: STYLE_URL_EN,
			} ).addTo( map );

			var glMap = vectorLayer.getMaplibreMap();
			if ( glMap ) {
				bindEnglishLabels( glMap );
			}

			return;
		}

		L.tileLayer( TILE_URL_EL, TILE_OPTS ).addTo( map );
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

	function applyInitialView( map, bounds ) {
		var center = bounds.getCenter();

		map.invalidateSize( { animate: false } );
		map.fitBounds( bounds, {
			maxZoom: MAP_ZOOM,
			padding: [ 32, 32 ],
			animate: false,
		} );

		map.setView( center, MAP_ZOOM, { animate: false } );
	}

	function bindInitialView( map, bounds, root ) {
		function runApply() {
			applyInitialView( map, bounds );
		}

		runApply();

		map.whenReady( runApply );

		if ( 'ResizeObserver' in window ) {
			var resizeObserver = new ResizeObserver( function ( entries ) {
				var entry = entries[ 0 ];
				if ( ! entry || entry.contentRect.width <= 0 || entry.contentRect.height <= 0 ) {
					return;
				}

				runApply();
				resizeObserver.disconnect();
			} );

			resizeObserver.observe( root );
		}

		requestAnimationFrame( function () {
			requestAnimationFrame( runApply );
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
		var bounds = getMarkerBounds( markers );
		var center = bounds.getCenter();

		var map = L.map( root, {
			center: center,
			zoom: MAP_ZOOM,
			scrollWheelZoom: false,
			zoomControl: false,
			minZoom: MAP_MIN_ZOOM,
			maxZoom: MAP_MAX_ZOOM,
			fadeAnimation: ! reducedMotion,
			zoomAnimation: ! reducedMotion,
		} );

		L.control.zoom( { position: 'bottomright' } ).addTo( map );

		if ( map.attributionControl ) {
			map.attributionControl.setPrefix( '' );
		}

		addBasemap( map, getMapLang( root ) );

		markers.forEach( function ( marker ) {
			createMarker( map, marker );
		} );

		bindInitialView( map, bounds, root );
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
