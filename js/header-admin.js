/* Header Admin — vanilla JS repeater UI */
( function () {
	'use strict';

	const builder      = document.getElementById( 'chic-header-builder' );
	const placeholderImg = builder ? builder.dataset.placeholderImg : '';

	if ( ! builder ) return;

	// ── Name re-indexing ──────────────────────────────────────────────────────

	/**
	 * After add/remove, walk all inputs inside a container and rewrite array
	 * indexes based on actual DOM position at each nesting level.
	 */
	function reindex( container ) {
		const itemRows = container.querySelectorAll( ':scope > .chic-items-list > .chic-row--item' );
		itemRows.forEach( ( itemRow, i ) => {
			reindexInputs( itemRow, /\[items\]\[\d+\]/, `[items][${i}]` );

			const bldgRows = itemRow.querySelectorAll( ':scope .chic-buildings-list > .chic-row--building' );
			bldgRows.forEach( ( bldgRow, j ) => {
				reindexInputs( bldgRow, /\[mega_groups\]\[\d+\]/, `[mega_groups][${j}]` );

				const suiteRows = bldgRow.querySelectorAll( ':scope .chic-suites-list > .chic-row--suite' );
				suiteRows.forEach( ( suiteRow, k ) => {
					reindexInputs( suiteRow, /\[suites\]\[\d+\]/, `[suites][${k}]` );
				} );
			} );
		} );
	}

	function reindexInputs( scope, pattern, replacement ) {
		scope.querySelectorAll( '[name]' ).forEach( el => {
			el.name = el.name.replace( pattern, replacement );
		} );
	}

	// ── Template cloning helpers ──────────────────────────────────────────────

	function cloneTemplate( id ) {
		const tpl = document.getElementById( id );
		return tpl ? tpl.content.cloneNode( true ) : null;
	}

	function addItem( list ) {
		const frag = cloneTemplate( 'chic-tpl-item' );
		if ( ! frag ) return;
		list.appendChild( frag );
		reindex( builder );
		initRow( list.lastElementChild );
	}

	function addBuilding( list ) {
		const frag = cloneTemplate( 'chic-tpl-building' );
		if ( ! frag ) return;
		list.appendChild( frag );
		reindex( builder );
		initRow( list.lastElementChild );
	}

	function addSuite( list ) {
		const frag = cloneTemplate( 'chic-tpl-suite' );
		if ( ! frag ) return;
		list.appendChild( frag );
		reindex( builder );
		initRow( list.lastElementChild );
	}

	// ── Row title preview ─────────────────────────────────────────────────────

	function bindTitlePreview( row ) {
		const labelInput = row.querySelector( '.chic-item-label, .chic-building-label, .chic-suite-label' );
		const titleEl    = row.querySelector( ':scope > .chic-row__head > .chic-row__title' );
		if ( ! labelInput || ! titleEl ) return;

		labelInput.addEventListener( 'input', () => {
			titleEl.textContent = labelInput.value.trim() || titleEl.dataset.empty || '(unnamed)';
		} );
	}

	// ── Conditional fields ────────────────────────────────────────────────────

	function applyLinkType( row, val ) {
		const urlField  = row.querySelector( ':scope > .chic-row__body > .chic-field-url' );
		const pageField = row.querySelector( ':scope > .chic-row__body > .chic-field-page' );
		if ( urlField )  urlField.style.display  = val === 'url'  ? '' : 'none';
		if ( pageField ) pageField.style.display = val === 'page' ? '' : 'none';
	}

	function bindLinkType( row ) {
		const sel = row.querySelector( ':scope > .chic-row__body .chic-link-type' );
		if ( ! sel ) return;
		applyLinkType( row, sel.value );
		sel.addEventListener( 'change', () => applyLinkType( row, sel.value ) );
	}

	function bindMegaToggle( row ) {
		const cb     = row.querySelector( ':scope > .chic-row__body .chic-is-mega' );
		const groups = row.querySelector( ':scope > .chic-row__body > .chic-mega-groups' );
		if ( ! cb || ! groups ) return;

		const apply = () => { groups.style.display = cb.checked ? '' : 'none'; };
		apply();
		cb.addEventListener( 'change', apply );
	}

	// ── Image picker ──────────────────────────────────────────────────────────

	function bindImagePicker( row ) {
		const pickBtn  = row.querySelector( '.chic-pick-image' );
		const clearBtn = row.querySelector( '.chic-clear-image' );
		const idInput  = row.querySelector( '.chic-image-id' );
		const preview  = row.querySelector( '.chic-image-preview' );
		if ( ! pickBtn || ! idInput || ! preview ) return;

		pickBtn.addEventListener( 'click', () => {
			if ( ! window.wp || ! wp.media ) return;
			const frame = wp.media( {
				title:    'Pick Suite Image',
				multiple: false,
				library:  { type: 'image' },
				button:   { text: 'Use this image' },
			} );
			frame.on( 'select', () => {
				const att = frame.state().get( 'selection' ).first().toJSON();
				idInput.value   = att.id;
				preview.src     = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
				if ( clearBtn ) clearBtn.style.display = '';
			} );
			frame.open();
		} );

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', () => {
				idInput.value   = '0';
				preview.src     = placeholderImg;
				clearBtn.style.display = 'none';
			} );
		}
	}

	// ── Per-row init ──────────────────────────────────────────────────────────

	function initRow( row ) {
		if ( ! row ) return;
		bindTitlePreview( row );
		bindLinkType( row );
		bindMegaToggle( row );
		bindImagePicker( row );
	}

	// ── Global delegated event handler ────────────────────────────────────────

	builder.addEventListener( 'click', function ( e ) {
		const btn = e.target.closest( 'button' );
		if ( ! btn ) return;

		if ( btn.classList.contains( 'chic-remove' ) ) {
			const row = btn.closest( '.chic-row' );
			if ( row ) {
				row.remove();
				reindex( builder );
			}
			return;
		}

		if ( btn.classList.contains( 'chic-add-item' ) ) {
			const list = builder.querySelector( '.chic-items-list' );
			if ( list ) addItem( list );
			return;
		}

		if ( btn.classList.contains( 'chic-add-building' ) ) {
			const list = btn.closest( '.chic-mega-groups' ).querySelector( '.chic-buildings-list' );
			if ( list ) addBuilding( list );
			return;
		}

		if ( btn.classList.contains( 'chic-add-suite' ) ) {
			const list = btn.closest( '.chic-row--building' ).querySelector( '.chic-suites-list' );
			if ( list ) addSuite( list );
			return;
		}
	} );

	// ── Boot: init all existing rows ──────────────────────────────────────────

	builder.querySelectorAll( '.chic-row' ).forEach( initRow );

} )();
