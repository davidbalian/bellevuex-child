export function initSiteMegaNav() {
	new SiteMegaNavController();
}

export function initMegaTabs() {
	document.querySelectorAll('.mega-panel__tabs').forEach(tabsRow => {
		const panel         = tabsRow.closest('.mega-panel');
		if ( ! panel ) return;
		const tabs          = tabsRow.querySelectorAll('.mega-panel__tab');
		const panels        = panel.querySelectorAll('.mega-panel__panel');
		const previewGroups = panel.querySelectorAll('.mega-panel__preview-group');
		const heightEl      = panel.querySelector('[data-mega-height]');
		const surface       = panel.querySelector('.mega-panel__surface');

		function activateSuitePreview( bIdx, sIdx ) {
			const group = panel.querySelector( `.mega-panel__preview-group[data-mega-preview-group="${bIdx}"]` );
			if ( ! group ) return;
			const target = `${bIdx}-${sIdx}`;
			group.querySelectorAll('.mega-panel__preview-img').forEach(img => {
				img.classList.toggle('is-active', img.dataset.megaPreviewSuite === target);
			});
		}

		function resetGroupToFirst( bIdx ) {
			const group = panel.querySelector( `.mega-panel__preview-group[data-mega-preview-group="${bIdx}"]` );
			if ( ! group ) return;
			group.querySelectorAll('.mega-panel__preview-img').forEach((img, i) => {
				img.classList.toggle('is-active', i === 0);
			});
		}

		// ── Tab switch ────────────────────────────────────────────────────────
		tabs.forEach(tab => {
			tab.addEventListener('click', e => {
				e.preventDefault();
				e.stopPropagation();
				const idx = tab.dataset.megaTab;

				tabs.forEach(t => {
					const on = t.dataset.megaTab === idx;
					t.classList.toggle('is-active', on);
					t.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				panels.forEach(p => p.classList.toggle('is-active', p.dataset.megaPanel === idx));

				// Switch preview group; reset newly visible group back to first suite.
				previewGroups.forEach(g => {
					const on = g.dataset.megaPreviewGroup === idx;
					g.classList.toggle('is-active', on);
					if ( on ) resetGroupToFirst( idx );
				});

				// Remeasure desktop fixed panel height if it is currently sized open.
				if ( heightEl && surface && heightEl.style.height && heightEl.style.height !== '0px' ) {
					heightEl.style.height = surface.scrollHeight + 'px';
				}
			});
		});

		// ── Suite link hover / focus → swap preview image ─────────────────────
		panel.querySelectorAll('[data-mega-preview-target]').forEach(link => {
			const [bIdx, sIdx] = link.dataset.megaPreviewTarget.split('-');
			link.addEventListener('mouseenter', () => activateSuitePreview(bIdx, sIdx));
			link.addEventListener('focusin',    () => activateSuitePreview(bIdx, sIdx));
		});

		// ── Mouse leaves a suite list → reset to first suite of that building ─
		panel.querySelectorAll('.mega-panel__list').forEach(list => {
			const parentPanel = list.closest('[data-mega-panel]');
			if ( ! parentPanel ) return;
			const bIdx = parentPanel.dataset.megaPanel;
			list.addEventListener('mouseleave', () => resetGroupToFirst(bIdx));
		});
	});
}

class SiteMegaNavController {
	constructor() {
		this._header   = document.querySelector('.site-header');
		this._backdrop = document.getElementById('site-mega-backdrop');
		this._menu     = document.querySelector('.primary-navigation .nav-menu');

		if ( ! this._header || ! this._backdrop || ! this._menu ) return;

		this._mq        = window.matchMedia('(min-width: 48.0625rem)');
		this._openItem  = null;
		this._ro        = null;
		this._reduced   = window.matchMedia('(prefers-reduced-motion: reduce)');
		this._megaSpacer        = null;
		this._megaScrollLockY   = null;

		this._onMegaClick   = this._onMegaClick.bind(this);
		this._onDocClick    = this._onDocClick.bind(this);
		this._onKeyDown     = this._onKeyDown.bind(this);
		this._onMediaChange = this._onMediaChange.bind(this);

		this._menu.addEventListener('click', this._onMegaClick);
		document.addEventListener('click', this._onDocClick);
		document.addEventListener('keydown', this._onKeyDown);
		this._mq.addEventListener('change', this._onMediaChange);
	}

	// ── Interaction handlers ─────────────────────────────────────────────────

	_onMegaClick(e) {
		if ( ! this._mq.matches ) return;
		const trigger = e.target.closest('li.menu-item-has-mega > a');
		if ( ! trigger ) return;
		e.preventDefault();
		e.stopPropagation();
		const li     = trigger.parentElement;
		const isOpen = li.classList.contains('is-mega-open');
		if ( isOpen ) {
			this._close(li);
		} else if ( this._openItem && this._openItem !== li ) {
			this._switchMega(li);
		} else {
			this._open(li);
		}
	}

	_onDocClick(e) {
		if ( ! this._openItem || ! this._mq.matches ) return;
		if ( ! e.target.closest('.site-header') ) {
			this._close(this._openItem);
			return;
		}
		if ( e.target.closest('#site-mega-backdrop') ) {
			this._close(this._openItem);
		}
	}

	_onKeyDown(e) {
		if ( e.key === 'Escape' && this._openItem ) {
			this._close(this._openItem);
		}
	}

	_onMediaChange(e) {
		if ( ! e.matches ) this._closeImmediate();
	}

	// ── Open / close ─────────────────────────────────────────────────────────

	_open(li) {
		this._openItem = li;
		li.classList.add('is-mega-open');
		li.querySelector(':scope > a').setAttribute('aria-expanded', 'true');

		const alreadyPinned = this._header.classList.contains( 'site-header--mega-open' );
		if ( this._mq.matches ) {
			if ( ! alreadyPinned ) {
				this._megaScrollLockY = window.scrollY;
				const h = this._header.getBoundingClientRect().height;
				this._insertOrUpdateMegaSpacer( h );
			} else {
				const h = this._header.getBoundingClientRect().height;
				this._insertOrUpdateMegaSpacer( h );
			}
		}
		this._header.classList.add( 'site-header--mega-open' );

		this._backdrop.setAttribute('aria-hidden', 'false');
		document.documentElement.classList.add( 'is-menu-scroll-lock' );
		this._animHeight(li, false);
		this._watchResize(li);
	}

	_close(li) {
		if ( ! li ) return;
		li.classList.remove('is-mega-open');
		const a = li.querySelector(':scope > a');
		if ( a ) a.setAttribute('aria-expanded', 'false');
		this._header.classList.remove('site-header--mega-open');
		this._removeMegaSpacer();
		if ( this._megaScrollLockY !== null ) {
			window.scrollTo( { top: this._megaScrollLockY, behavior: 'instant' } );
			this._megaScrollLockY = null;
		}
		this._backdrop.setAttribute('aria-hidden', 'true');
		document.documentElement.classList.remove( 'is-menu-scroll-lock' );
		this._animHeight(li, false, true);
		this._stopResize();
		this._openItem = null;
	}

	_closeImmediate() {
		const li = this._openItem;
		if ( ! li ) return;
		const heightEl = li.querySelector('[data-mega-height]');
		if ( heightEl ) heightEl.style.height = '0';
		li.classList.remove('is-mega-open');
		const a = li.querySelector(':scope > a');
		if ( a ) a.setAttribute('aria-expanded', 'false');
		this._header.classList.remove('site-header--mega-open');
		this._removeMegaSpacer();
		if ( this._megaScrollLockY !== null ) {
			window.scrollTo( { top: this._megaScrollLockY, behavior: 'instant' } );
			this._megaScrollLockY = null;
		}
		this._backdrop.setAttribute('aria-hidden', 'true');
		document.documentElement.classList.remove( 'is-menu-scroll-lock' );
		this._stopResize();
		this._openItem = null;
	}

	_switchMega(newLi) {
		const oldLi     = this._openItem;
		const oldHeight = oldLi.querySelector('[data-mega-height]');

		// Collapse the old panel instantly (no animation).
		oldLi.classList.remove('is-mega-open');
		const oldA = oldLi.querySelector(':scope > a');
		if ( oldA ) oldA.setAttribute('aria-expanded', 'false');
		if ( oldHeight ) {
			oldHeight.classList.add('mega-panel__height--instant');
			oldHeight.style.height = '0';
		}
		this._stopResize();
		this._openItem = null;

		// Open the new panel, also without the open animation.
		this._open(newLi);
		const newHeight = newLi.querySelector('[data-mega-height]');
		if ( newHeight ) {
			newHeight.classList.add('mega-panel__height--instant');
			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					if ( newHeight ) newHeight.classList.remove('mega-panel__height--instant');
					if ( oldHeight ) oldHeight.classList.remove('mega-panel__height--instant');
				});
			});
		}
	}

	// ── Height animation ─────────────────────────────────────────────────────

	_animHeight(li, instant, closing = false) {
		const heightEl = li.querySelector('[data-mega-height]');
		const surface  = li.querySelector('.mega-panel__surface');
		if ( ! heightEl || ! surface ) return;

		if ( this._reduced.matches || instant ) {
			heightEl.style.height = closing ? '0' : surface.scrollHeight + 'px';
			return;
		}

		if ( closing ) {
			heightEl.style.height = surface.scrollHeight + 'px';
			requestAnimationFrame(() => { heightEl.style.height = '0'; });
		} else {
			heightEl.style.height = '0';
			requestAnimationFrame(() => { heightEl.style.height = surface.scrollHeight + 'px'; });
		}
	}

	// ── Backdrop inset tracking ───────────────────────────────────────────────

	_updateBackdropInset(li) {
		const clip = li.querySelector('[data-mega-clip]');
		if ( ! clip ) return;
		this._backdrop.style.top = clip.getBoundingClientRect().bottom + 'px';
	}

	_watchResize(li) {
		this._stopResize();
		const panel = li.querySelector('[data-mega-clip]');
		if ( ! panel ) return;
		this._ro = new ResizeObserver(() => this._updateBackdropInset(li));
		this._ro.observe(panel);
		this._updateBackdropInset(li);
	}

	_stopResize() {
		if ( this._ro ) { this._ro.disconnect(); this._ro = null; }
	}

	_insertOrUpdateMegaSpacer( heightPx ) {
		if ( ! this._megaSpacer ) {
			const el = document.createElement( 'div' );
			el.className = 'site-header-mega-spacer';
			el.setAttribute( 'aria-hidden', 'true' );
			this._header.parentNode.insertBefore( el, this._header );
			this._megaSpacer = el;
		}
		this._megaSpacer.style.height = heightPx + 'px';
	}

	_removeMegaSpacer() {
		if ( this._megaSpacer ) {
			this._megaSpacer.remove();
			this._megaSpacer = null;
		}
	}

}
