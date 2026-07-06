/**
 * STICKY BAR — visibility + surface engine (one bar per page).
 *
 * PORTED from the AIG data-anchor engine (assets/js/article-sticky.js — the
 * generalized, unit-tested production descendant) and EXTENDED so every
 * production trigger mode expresses as data attributes on [data-sticky-cta]:
 *
 *   data-show-anchor    selector — show once the viewport bottom passes 50%
 *                       of it. EMPTY/absent (with no fraction) → always on.
 *   data-show-gate      "top" — the show-anchor must scroll fully ABOVE the
 *                       viewport instead (the course bar's hero gate).
 *   data-hide-anchor    selector — hide once the viewport bottom reaches its
 *                       top. Fallback: the show-anchor's bottom.
 *   data-show-fraction  number — page-geometry mode (the /newsletter landing
 *                       inline script): show once scrollY > f × viewportH.
 *   data-hide-fraction  number — hide once scrollY > docH − f × viewportH.
 *   data-suppress-key   localStorage key — device-local suppression (the
 *                       production 'aif-subscribed' post-subscribe flag).
 *   data-sticky-sample  THE SAMPLER (generalized from the AIG course bar,
 *                       operator 2026-07-06): reads the RENDERED background
 *                       behind the bar's resting spot and toggles the
 *                       .section-dark SCOPE CLASS on the bar — the bar reads
 *                       roles, so the 3-layer system re-skins it. Sections
 *                       need NO registration: any opaque background works.
 *                       Re-samples only while visible (no slide-out flash).
 *                       Any failure → light (the DS default skin).
 *
 * shouldShow() stays pure (numbers in, boolean out) for unit tests.
 */
(function () {
	'use strict';

	/**
	 * Pure visibility decision.
	 * @param {Object} o
	 *   o.viewBottom    scrollY + innerHeight
	 *   o.scrollY       window.scrollY
	 *   o.viewH         window.innerHeight
	 *   o.docH          document scrollHeight
	 *   o.showTop       show-anchor absolute top, or null
	 *   o.showBottom    show-anchor absolute bottom, or null
	 *   o.showGateTop   true = the anchor must be fully above the viewport
	 *   o.hideTop       hide-anchor absolute top, or null
	 *   o.showFraction  page-geometry show gate (× viewH), or null
	 *   o.hideFraction  page-geometry hide gate (viewports from doc end), or null
	 * @return {boolean}
	 */
	function shouldShow(o) {
		var showOk = true;
		if (o.showTop !== null && o.showBottom !== null) {
			showOk = o.showGateTop
				? o.scrollY > o.showBottom // anchor fully above the viewport (course gate)
				: o.viewBottom > o.showTop + (o.showBottom - o.showTop) * 0.5;
		} else if (o.showFraction !== null) {
			showOk = o.scrollY > o.viewH * o.showFraction; // landing page-geometry
		}
		var hideOk = true;
		if (o.hideTop !== null) {
			hideOk = o.viewBottom < o.hideTop;
		} else if (o.hideFraction !== null) {
			hideOk = o.scrollY < o.docH - o.viewH * o.hideFraction;
		} else if (o.showBottom !== null && !o.showGateTop) {
			hideOk = o.viewBottom < o.showBottom; // article fallback: the body end
		}
		return showOk && hideOk;
	}

	/**
	 * THE SAMPLER — what is BEHIND the bar at its resting position? Walks up
	 * from the topmost element under the bar to the first opaque background
	 * and classifies it by luminance. Bulletproof by structure: ANY failure
	 * (exotic color, transparent chain, exception) returns 'light' — the DS
	 * default skin. (Ported from single-kurz.php; generalized fallback.)
	 */
	function sampleTheme(bar) {
		try {
			var rect = bar.getBoundingClientRect();
			var y = Math.max(0, window.innerHeight - (rect.height || 56) - 4);
			var x = Math.floor(window.innerWidth / 2);
			var els = document.elementsFromPoint(x, y);
			for (var i = 0; i < els.length; i++) {
				if (bar === els[i] || bar.contains(els[i])) continue;
				var n = els[i];
				while (n && n !== document.documentElement) {
					var m = getComputedStyle(n).backgroundColor.match(/rgba?\(([\d.]+)[, ]+([\d.]+)[, ]+([\d.]+)(?:[,/ ]+([\d.]+))?\)/);
					if (m && (m[4] === undefined || parseFloat(m[4]) > 0.5)) {
						return (0.2126 * m[1] + 0.7152 * m[2] + 0.0722 * m[3]) > 127 ? 'light' : 'dark';
					}
					n = n.parentElement;
				}
				break;
			}
		} catch (e) { /* fall through */ }
		return 'light';
	}

	function init() {
		var bar = document.querySelector('[data-sticky-cta]');
		if (!bar) return;

		// Device-local suppression (opt-in; production: 'aif-subscribed').
		var suppressKey = bar.getAttribute('data-suppress-key') || '';
		if (suppressKey) {
			try {
				if (localStorage.getItem(suppressKey) === '1') return;
			} catch (e) { /* storage blocked — show the bar */ }
		}

		var showSel  = bar.getAttribute('data-show-anchor') || '';
		var hideSel  = bar.getAttribute('data-hide-anchor') || '';
		var gateTop  = bar.getAttribute('data-show-gate') === 'top';
		var showFrac = bar.hasAttribute('data-show-fraction') ? parseFloat(bar.getAttribute('data-show-fraction')) : null;
		var hideFrac = bar.hasAttribute('data-hide-fraction') ? parseFloat(bar.getAttribute('data-hide-fraction')) : null;
		var sample   = bar.hasAttribute('data-sticky-sample');
		var showEl   = showSel ? document.querySelector(showSel) : null;
		var hideEl   = hideSel ? document.querySelector(hideSel) : null;

		// A configured show-anchor missing from the page → stay hidden (the
		// original article behavior). An EMPTY show-anchor = always-on mode.
		if (showSel && !showEl) return;

		var ticking = false;

		function update() {
			ticking = false;
			var showTop = null, showBottom = null, hideTop = null;
			if (showEl) {
				var r = showEl.getBoundingClientRect();
				showTop    = r.top + window.scrollY;
				showBottom = r.bottom + window.scrollY;
			}
			if (hideEl) {
				hideTop = hideEl.getBoundingClientRect().top + window.scrollY;
			}
			var visible = shouldShow({
				viewBottom:   window.scrollY + window.innerHeight,
				scrollY:      window.scrollY,
				viewH:        window.innerHeight,
				docH:         document.documentElement.scrollHeight,
				showTop:      showTop,
				showBottom:   showBottom,
				showGateTop:  gateTop,
				hideTop:      hideTop,
				showFraction: Number.isFinite(showFrac) ? showFrac : null,
				hideFraction: Number.isFinite(hideFrac) ? hideFrac : null
			});
			bar.classList.toggle('sticky-bar--visible', visible);
			if (visible && sample) {
				// Surface = a SCOPE CLASS flip; roles do the rest. Only while
				// visible — the slide-out keeps its last theme (no flash).
				bar.classList.toggle('section-dark', sampleTheme(bar) === 'dark');
			}
			bar.setAttribute('aria-hidden', visible ? 'false' : 'true');
		}

		function onScroll() {
			if (ticking) return;
			ticking = true;
			window.requestAnimationFrame(update);
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		update();
	}

	// Browser bootstrap.
	if (typeof document !== 'undefined') {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	}

	// Node export for unit tests (no effect in the browser).
	if (typeof module !== 'undefined' && module.exports) {
		module.exports = { shouldShow: shouldShow };
	}
})();
