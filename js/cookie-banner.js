(function () {
	'use strict';

	var STORAGE_KEY = 'chic_cookie_consent';

	function grantAnalyticsConsent() {
		if (typeof gtag === 'function') {
			gtag('consent', 'update', { analytics_storage: 'granted' });
		}
	}

	function init() {
		var banner = document.getElementById('chic-cookie-banner');
		if (!banner) return;

		var existing = null;
		try { existing = localStorage.getItem(STORAGE_KEY); } catch (e) {}

		if (existing === 'accepted') {
			grantAnalyticsConsent();
		}

		if (existing && !window.__chicReopenCookieBanner) return;

		banner.removeAttribute('hidden');
		banner.offsetHeight; /* reflow: allow display change before transition */
		banner.classList.add('is-visible');

		function persist(choice) {
			try { localStorage.setItem(STORAGE_KEY, choice); } catch (e) {}
		}

		function hide() {
			banner.classList.remove('is-visible');
			var done = false;
			function finish() {
				if (done) return;
				done = true;
				banner.setAttribute('hidden', '');
			}
			banner.addEventListener('transitionend', finish, { once: true });
			setTimeout(finish, 450);
		}

		banner.querySelector('.cookie-banner__accept').addEventListener('click', function () {
			persist('accepted');
			grantAnalyticsConsent();
			hide();
		});

		banner.querySelector('.cookie-banner__reject').addEventListener('click', function () {
			persist('rejected');
			hide();
		});

		banner.querySelector('.cookie-banner__close').addEventListener('click', function () {
			persist('rejected');
			hide();
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && banner.classList.contains('is-visible')) {
				persist('rejected');
				hide();
			}
		});
	}

	window.chicReopenCookieBanner = function () {
		try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
		window.__chicReopenCookieBanner = true;
		init();
		window.__chicReopenCookieBanner = false;
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
