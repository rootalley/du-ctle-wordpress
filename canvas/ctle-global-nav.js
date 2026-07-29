/**
 * CTLE global-navigation button — Dominican University Canvas
 * ------------------------------------------------------------------
 * Shows the CTLE button in the Canvas global navigation to faculty only, and points
 * it at the CTLE WordPress SSO-initiation URL. Because Canvas and CTLE WordPress sit
 * on the same Entra tenant, the click completes SSO silently and lands the user
 * logged in — this replaces the withdrawn LTI 1.3 integration (HANDOFF decision 10).
 *
 * Faculty detection reads `declared_user_type` from GET /api/v1/users/self/logins,
 * which is readable by non-admin users (validated 2026-07-28). The value is set to
 * `teacher` for faculty by the nightly SIS `users.csv` import — see README.md.
 *
 * SECURITY NOTE: this gate is COSMETIC. Access to the CTLE site is enforced by the
 * Entra faculty group on the enterprise app (assignment required). A student who
 * discovers the URL still cannot sign in. Do not treat this file as an access control.
 *
 * DEPLOY TO: Canvas Admin → Themes → [DU theme] → Upload → Custom JavaScript.
 *            Canvas allows ONE global JS file per account — append this IIFE to the
 *            existing bundle rather than replacing it. See README.md.
 *
 * Owner: DU Learning Technologies (LT-1 / LT-3)
 */
(function () {
	'use strict';

	// ── Configuration ────────────────────────────────────────────────────────────
	var CONFIG = {
		// Master switch. While false the script exits immediately and touches nothing,
		// so deploying it early is a guaranteed no-op on the button that exists today.
		// Flip to true in Canvas beta for testing, and in production only at launch.
		enabled: false,

		// The CTLE SSO-initiation URL. PLACEHOLDER — replace once DU IT delivers the
		// Entra app registration and the WordPress SSO plugin is configured. Until
		// then `enabled` must stay false: this URL is the whole point of the retarget.
		ssoUrl: 'https://ctle.dom.edu/',

		// How the existing global-nav item is located. The first match wins:
		//   1. an element carrying [data-ctle-nav]
		//   2. a link whose href contains `hrefMatch`
		//   3. a link whose trimmed text equals `label`
		label: 'CTLE',
		hrefMatch: 'ctle',

		// If no existing item is found, inject one. Injection happens only for faculty,
		// so it produces no flash of a button that then disappears.
		createIfMissing: true,

		// What happens when the logins lookup fails (network error, 401, Canvas change).
		// false → button stays hidden. true → button shows to everyone.
		// Keep false: Entra is the real gate, so a stray button only creates a dead end
		// for students. Flip to true only if a Canvas-side outage is hiding it from faculty.
		failOpen: false,

		// declared_user_type values treated as faculty. Canvas enum:
		// administrative | observer | staff | student | teacher | unknown
		facultyTypes: ['teacher'],

		// Cache the lookup for the browser session so it costs one API call, not one
		// per page view. Cleared when the tab closes.
		cacheTtlMinutes: 120,

		debug: false
	};

	var CACHE_KEY = 'ctleNavFaculty';
	var HIDDEN_CLASS = 'ctle-nav-hidden';
	var LOGINS_ENDPOINT = '/api/v1/users/self/logins';

	if (!CONFIG.enabled) {
		return;
	}

	// ── Helpers ──────────────────────────────────────────────────────────────────

	function log() {
		if (CONFIG.debug && window.console) {
			console.log.apply(console, ['[ctle-nav]'].concat([].slice.call(arguments)));
		}
	}

	/**
	 * Inject the hide rule once, at parse time, so the class is ready before any
	 * element is tagged with it.
	 */
	function injectHideStyle() {
		if (document.getElementById('ctle-nav-style')) {
			return;
		}
		var style = document.createElement('style');
		style.id = 'ctle-nav-style';
		style.textContent = '.' + HIDDEN_CLASS + ' { display: none !important; }';
		(document.head || document.documentElement).appendChild(style);
	}

	/**
	 * Locate the existing CTLE item in the global nav list.
	 *
	 * @return {Element|null} the <li> wrapping the link, or null.
	 */
	function findNavItem() {
		var menu = document.querySelector('#menu');
		if (!menu) {
			return null;
		}

		var tagged = menu.querySelector('[data-ctle-nav]');
		if (tagged) {
			return tagged.closest('li') || tagged;
		}

		var links = menu.querySelectorAll('a[href]');
		for (var i = 0; i < links.length; i++) {
			var link = links[i];
			var href = (link.getAttribute('href') || '').toLowerCase();
			var text = (link.textContent || '').trim();

			if (href.indexOf(CONFIG.hrefMatch.toLowerCase()) !== -1 || text === CONFIG.label) {
				return link.closest('li') || link;
			}
		}

		return null;
	}

	/**
	 * Build a nav item matching Canvas's global-nav markup.
	 *
	 * @return {Element} the new <li>.
	 */
	function createNavItem() {
		var item = document.createElement('li');
		item.className = 'ic-app-header__menu-list-item';
		item.setAttribute('data-ctle-nav', '');

		var link = document.createElement('a');
		link.className = 'ic-app-header__menu-list-link';
		link.href = CONFIG.ssoUrl;

		var icon = document.createElement('div');
		icon.className = 'menu-item-icon-container';
		icon.setAttribute('aria-hidden', 'true');
		icon.innerHTML =
			'<svg class="ic-icon-svg ic-icon-svg--apps" viewBox="0 0 1920 1920" role="presentation" ' +
			'width="1em" height="1em" fill="currentColor">' +
			'<path d="M960 114 137 522v876l823 408 823-408V522L960 114zm0 214 502 249-502 249-502-249 502-249zM274 741l617 306v588l-617-306V741zm754 894v-588l617-306v588l-617 306z"/>' +
			'</svg>';

		var text = document.createElement('div');
		text.className = 'menu-item__text';
		text.textContent = CONFIG.label;

		link.appendChild(icon);
		link.appendChild(text);
		item.appendChild(link);

		return item;
	}

	function setHidden(element, hidden) {
		if (!element) {
			return;
		}
		element.classList[hidden ? 'add' : 'remove'](HIDDEN_CLASS);
	}

	function readCache() {
		try {
			var raw = window.sessionStorage.getItem(CACHE_KEY);
			if (!raw) {
				return null;
			}
			var entry = JSON.parse(raw);
			var age = Date.now() - entry.at;
			if (age > CONFIG.cacheTtlMinutes * 60 * 1000) {
				return null;
			}
			return entry.faculty;
		} catch (e) {
			return null;
		}
	}

	function writeCache(faculty) {
		try {
			window.sessionStorage.setItem(CACHE_KEY, JSON.stringify({ faculty: faculty, at: Date.now() }));
		} catch (e) {
			// Private browsing or a full quota — the lookup simply repeats per page.
		}
	}

	/**
	 * Ask Canvas whether this user is declared faculty.
	 *
	 * A user may hold more than one login (SIS-provisioned and manual); any login
	 * declaring a faculty type is sufficient.
	 *
	 * @return {Promise<boolean>}
	 */
	function isFaculty() {
		var cached = readCache();
		if (cached !== null) {
			log('cache hit:', cached);
			return Promise.resolve(cached);
		}

		return fetch(LOGINS_ENDPOINT, {
			credentials: 'same-origin',
			headers: { Accept: 'application/json+canvas-string-ids' }
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('logins request returned ' + response.status);
				}
				return response.text();
			})
			.then(function (body) {
				// Some Canvas endpoints prefix JSON with `while(1);` to defeat JSON
				// hijacking. This one does not (verified 2026-07-28), but stripping it
				// costs nothing and keeps a future Canvas change from breaking the gate.
				return JSON.parse(body.replace(/^while\s*\(1\);?/, ''));
			})
			.then(function (logins) {
				var faculty = Array.isArray(logins) && logins.some(function (login) {
					return CONFIG.facultyTypes.indexOf(login.declared_user_type) !== -1;
				});
				log('declared_user_type lookup →', faculty);
				writeCache(faculty);
				return faculty;
			});
	}

	// ── Main ─────────────────────────────────────────────────────────────────────

	function init() {
		injectHideStyle();

		var existing = findNavItem();

		// Retarget first: whether or not this user turns out to be faculty, the item
		// should point at the SSO-initiation URL rather than the legacy destination.
		if (existing) {
			var link = existing.matches('a') ? existing : existing.querySelector('a');
			if (link) {
				link.href = CONFIG.ssoUrl;
			}
			// Hide until proven faculty, so non-faculty never settle on a visible button.
			setHidden(existing, true);
		}

		isFaculty()
			.then(function (faculty) {
				if (existing) {
					setHidden(existing, !faculty);
					return;
				}
				if (faculty && CONFIG.createIfMissing) {
					var menu = document.querySelector('#menu');
					if (menu) {
						menu.appendChild(createNavItem());
					} else {
						log('no #menu element found; nothing to inject into');
					}
				}
			})
			.catch(function (error) {
				log('lookup failed:', error && error.message);
				if (existing) {
					setHidden(existing, !CONFIG.failOpen);
				} else if (CONFIG.failOpen && CONFIG.createIfMissing) {
					var menu = document.querySelector('#menu');
					if (menu) {
						menu.appendChild(createNavItem());
					}
				}
			});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
