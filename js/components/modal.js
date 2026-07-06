/**
 * MODAL — the one overlay-dialog engine.
 *
 * PORTED from AIF modal-registration.js (the only production copy with the
 * full accessibility contract: aria-hidden flip, body scroll lock, ESC,
 * overlay/[data-close-modal] close, focus management, Fluent Forms success
 * auto-close) and GENERALIZED for any modal:
 *
 *  - trigger:  <button data-modal-open="my-modal-id"
 *                      [data-modal-title="…"]>            (title override)
 *  - markup:   .modal[aria-hidden="true"] > .modal__overlay[data-close-modal]
 *              + .modal__container[role=dialog] > .modal__close[data-close-modal]
 *
 * Production's per-modal wiring (hidden-field population, reservation form
 * switching, consent AJAX) stays THEME territory — themes call the exposed
 * window.aigdsModal.open/close API and do their own field work.
 */
(function () {
	'use strict';

	function openModal(modal, data) {
		data = data || {};

		var title = modal.querySelector('.modal__title');
		if (title && data.title) {
			title.textContent = data.title;
		}

		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('modal-open');

		// Focus the first non-hidden input, else the close button.
		setTimeout(function () {
			var target = modal.querySelector('input:not([type="hidden"]), textarea, select')
				|| modal.querySelector('.modal__close');
			if (target) target.focus();
		}, 100);
	}

	function closeModal(modal) {
		modal.setAttribute('aria-hidden', 'true');
		// Only unlock the body when no other modal stays open.
		if (!document.querySelector('.modal[aria-hidden="false"]')) {
			document.body.classList.remove('modal-open');
		}
	}

	// Openers — any element with data-modal-open="<modal id>".
	document.addEventListener('click', function (e) {
		var opener = e.target.closest('[data-modal-open]');
		if (opener) {
			var modal = document.getElementById(opener.getAttribute('data-modal-open'));
			if (modal) {
				e.preventDefault();
				openModal(modal, { title: opener.getAttribute('data-modal-title') || '' });
			}
			return;
		}

		// Closers — the overlay and anything marked data-close-modal.
		var closer = e.target.closest('[data-close-modal]');
		if (closer) {
			var host = closer.closest('.modal');
			if (host) {
				e.preventDefault();
				closeModal(host);
			}
		}
	});

	// Escape closes the topmost open modal.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') return;
		var open = document.querySelectorAll('.modal[aria-hidden="false"]');
		if (open.length) closeModal(open[open.length - 1]);
	});

	/**
	 * Fluent Forms submission success → auto-close after the success message
	 * shows (harvested 2s delay). Guarded: jQuery + FF only exist on theme
	 * pages, never in the styleguide.
	 */
	function setupFluentFormsTracking() {
		if (typeof jQuery === 'undefined') return;
		jQuery(document).on('fluentform_submission_success', '.modal .frm-fluent-form', function () {
			var modal = this.closest ? this.closest('.modal') : null;
			if (!modal) return;
			setTimeout(function () { closeModal(modal); }, 2000);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupFluentFormsTracking);
	} else {
		setupFluentFormsTracking();
	}

	// Public API (generalizes production's window.aifRegistrationModal).
	window.aigdsModal = {
		open: function (id, data) {
			var m = document.getElementById(id);
			if (m) openModal(m, data);
		},
		close: function (id) {
			var m = document.getElementById(id);
			if (m) closeModal(m);
		}
	};
})();
