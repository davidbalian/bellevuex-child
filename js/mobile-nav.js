import { initSiteMegaNav, initMegaTabs } from './site-mega-nav.js';

document.addEventListener('DOMContentLoaded', initMobileNav);

export function initMobileNav() {
	initSiteMegaNav();
	initMegaTabs();
	initMegaPanelPlaceholderLinks();
	MobileNavSubmenuAccordion.init();
	MobileNavOverlayController.init();
}

// Prevent placeholder # links inside mega panels from jumping the page.
function initMegaPanelPlaceholderLinks() {
	document.addEventListener('click', e => {
		const a = e.target.closest('.mega-panel__list a[href="#"]');
		if ( a ) e.preventDefault();
	});
}

// ── Mobile accordion ─────────────────────────────────────────────────────────

const MobileNavSubmenuAccordion = {
	mq: window.matchMedia('(max-width: 48rem)'),

	init() {
		const root = document.querySelector('.mobile-nav__links');
		if ( ! root ) return;

		root.addEventListener('click', e => {
			if ( ! this.mq.matches ) return;
			const trigger = e.target.closest('.mobile-nav__links .menu-item-has-children > a');
			if ( ! trigger ) return;

			const li     = trigger.parentElement;
			const isOpen = li.classList.contains('is-submenu-open');

			// Collapse any currently-open sibling at the same level.
			const parent = li.parentElement;
			parent.querySelectorAll(':scope > .menu-item-has-children.is-submenu-open').forEach(el => {
				if ( el !== li ) {
					el.classList.remove('is-submenu-open');
					const a = el.querySelector(':scope > a');
					if ( a ) a.setAttribute('aria-expanded', 'false');
				}
			});

			li.classList.toggle('is-submenu-open', ! isOpen);
			trigger.setAttribute('aria-expanded', String( ! isOpen));
		});
	},
};

// ── Mobile overlay ───────────────────────────────────────────────────────────

const MobileNavOverlayController = {
	overlay:     null,
	toggleBtn:   null,
	closeBtn:    null,
	debounceMs:  240,
	_resizeTimer: null,

	init() {
		this.overlay   = document.getElementById('mobile-nav');
		this.toggleBtn = document.querySelector('.mobile-nav-toggle');
		this.closeBtn  = document.querySelector('.mobile-nav-close');

		if ( ! this.overlay || ! this.toggleBtn ) return;

		// Read debounce from CSS token (unitless ms value).
		const rawMs = parseInt(
			getComputedStyle(document.documentElement).getPropertyValue('--mobile-nav-debounce-ms'),
			10
		);
		if ( ! isNaN(rawMs) ) this.debounceMs = rawMs;

		this.toggleBtn.addEventListener('click', () => this.toggle());
		if ( this.closeBtn ) this.closeBtn.addEventListener('click', () => this.close());

		document.addEventListener('keydown', e => {
			if ( e.key === 'Escape' ) this.close();
		});

		window.addEventListener('resize', () => {
			clearTimeout(this._resizeTimer);
			this._resizeTimer = setTimeout(() => {
				if ( window.innerWidth > 768 ) this.close();
			}, this.debounceMs);
		});
	},

	open() {
		this.overlay.classList.add('is-open');
		this.overlay.setAttribute('aria-hidden', 'false');
		this.toggleBtn.setAttribute('aria-expanded', 'true');
		document.documentElement.classList.add( 'is-menu-scroll-lock' );
	},

	close() {
		if ( ! this.overlay ) return;
		this.overlay.classList.remove('is-open');
		this.overlay.setAttribute('aria-hidden', 'true');
		this.toggleBtn.setAttribute('aria-expanded', 'false');
		document.documentElement.classList.remove( 'is-menu-scroll-lock' );
		// Reset all accordion states.
		this.overlay.querySelectorAll('.is-submenu-open').forEach(el => {
			el.classList.remove('is-submenu-open');
			const a = el.querySelector(':scope > a');
			if ( a ) a.setAttribute('aria-expanded', 'false');
		});
	},

	toggle() {
		this.overlay.classList.contains('is-open') ? this.close() : this.open();
	},
};
