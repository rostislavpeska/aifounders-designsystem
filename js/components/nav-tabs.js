/**
 * Nav tabs — HARVESTED from AIF (components/author-nav.php inline script,
 * verbatim mechanism): on load, scroll the active tab into the horizontal
 * center of its scrollable row so it is visible on narrow viewports.
 * Generalized from the page-specific .author-tabs to the DS .nav-tabs
 * (theme alias author-tabs → nav-tabs at adoption).
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.nav-tabs').forEach(function (nav) {
			var active = nav.querySelector('.nav-tabs__tab--active');
			if (!active) return;
			/* Port fix vs the theme inline script: offsetLeft is relative to the
			   offsetParent, not the scroll container — subtract the nav's own
			   offsetLeft so the math holds in any layout. */
			var left = active.offsetLeft - nav.offsetLeft;
			var scrollPos = left - (nav.offsetWidth - active.offsetWidth) / 2;
			nav.scrollLeft = Math.max(0, scrollPos);
		});
	});
})();
