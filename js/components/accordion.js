/**
 * Accordion — HARVESTED from production.
 *
 * Engine = the AIG theme's accordion.js verbatim: actual measured height
 * (not a max-height hack) for perfect timing regardless of content length;
 * CSS owns the easing (--transition-smooth); inline height clears to `auto`
 * after the transition so content can reflow.
 *
 * EXCLUSIVE mode = the AIF /newsletter FAQ behavior (its inline script closed
 * open siblings before opening), generalized from the page-specific `.lp-faq`
 * scoping to an opt-in wrapper: <div data-accordion="exclusive">…accordions…</div>.
 * Default (no wrapper) = independent toggles, the AIG course-detail behavior.
 */

(function () {
	'use strict';

	function collapse(accordion) {
		var content = accordion.querySelector('.accordion__content');
		var header = accordion.querySelector('.accordion__header');
		if (!content) return;
		// Snapshot current height, force reflow, then animate to 0.
		content.style.height = content.scrollHeight + 'px';
		content.offsetHeight; // eslint-disable-line no-unused-expressions
		content.style.height = '0px';
		accordion.classList.remove('accordion--open');
		if (header) header.setAttribute('aria-expanded', 'false');
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.accordion').forEach(function (accordion) {
			var header = accordion.querySelector('.accordion__header');
			var content = accordion.querySelector('.accordion__content');
			if (!header || !content) return;

			header.addEventListener('click', function () {
				var isOpen = accordion.classList.contains('accordion--open');

				// EXCLUSIVE mode: close open siblings inside the wrapper first.
				var group = accordion.closest('[data-accordion="exclusive"]');
				if (group && !isOpen) {
					group.querySelectorAll('.accordion--open').forEach(function (other) {
						if (other !== accordion) collapse(other);
					});
				}

				if (isOpen) {
					collapse(accordion);
				} else {
					accordion.classList.add('accordion--open');
					header.setAttribute('aria-expanded', 'true');
					// Measure natural height, then animate from 0 to it.
					content.style.height = content.scrollHeight + 'px';
				}
			});

			// After the transition, clear the inline height so content reflows.
			content.addEventListener('transitionend', function (e) {
				if (e.propertyName !== 'height') return;
				if (accordion.classList.contains('accordion--open')) {
					content.style.height = 'auto';
				}
			});
		});
	});
})();
