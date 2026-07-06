/**
 * ENGAGEMENT — Aha! + share count + post-Aha toast.
 *
 * PORTED VERBATIM from the production engine (AIF assets/js/engagement.js,
 * byte-identical to the AIG copy), generalized for the DS:
 *  - the AJAX counting engine (aif_aha / aif_share actions, nonce, 5-min
 *    server dedup) stays THEME/PLUGIN territory — when data-ajax-url is
 *    absent the widget degrades gracefully (optimistic UI only, no network).
 *  - the toast clones the FIRST .a2a_kit on the page (AddToAny output in
 *    production; any kit-shaped stand-in works — the DS specimen ships one).
 *
 * Behaviour (production contract):
 *  - Aha! click → optimistic UI (counter+1, fill icon via .aif-aha--clicked,
 *    localStorage) → AJAX → reconcile with server → toast slides down with
 *    cloned share buttons.
 *  - Share click (toast OR standalone kit) → AJAX with platform name.
 *  - localStorage is UX-only ("you already aha'd this"); the server
 *    transient is the dedup source of truth.
 */
(function () {
	'use strict';

	const root = document.querySelector('.aif-engagement');
	if (!root) return;

	const postId   = root.dataset.postId;
	const nonce    = root.dataset.nonce;
	const ajaxUrl  = root.dataset.ajaxUrl;
	const i18n     = JSON.parse(root.dataset.i18n || '{}');
	const ahaBtn   = root.querySelector('.aif-aha');
	const ahaCountEl = root.querySelector('.aif-aha__count');
	const ahaLabelEl = root.querySelector('.aif-aha__label');
	const shareBtn   = root.querySelector('.aif-share');
	const shareCountEl = root.querySelector('.aif-share__count');
	const toast      = root.querySelector('.aif-engagement-toast');
	const toastBtns  = root.querySelector('.aif-engagement-toast__buttons');
	const toastClose = root.querySelector('.aif-engagement-toast__close');

	const lsKey = 'aif_aha_' + postId;
	const TOAST_AUTO_HIDE_MS = 8000;
	let toastTimer = null;

	/* ------------------------------------------------------------------ */
	/* HELPERS                                                            */
	/* ------------------------------------------------------------------ */

	function setAhaCount(n) {
		// Label stays "Aha!" — only the count number changes.
		ahaCountEl.textContent = n;
	}

	function setShareCount(n) {
		if (!shareCountEl) return;
		shareCountEl.textContent = n;
		if (n > 0) {
			shareCountEl.removeAttribute('hidden');
		} else {
			shareCountEl.setAttribute('hidden', '');
		}
	}

	function markAhaClicked() {
		// Just swap icon (CSS handles via .aif-aha--clicked) — label and count
		// stay unchanged. Toast carries the "Glad it helped" message instead.
		ahaBtn.classList.add('aif-aha--clicked');
		ahaBtn.setAttribute('aria-pressed', 'true');
		try { localStorage.setItem(lsKey, '1'); } catch (e) { /* private mode etc. */ }
	}

	function postJSON(action, body) {
		// DS generalization: no ajax-url = no counting engine on this page —
		// resolve to null so callers' optimistic UI simply stands.
		if (!ajaxUrl) return Promise.resolve(null);
		const params = new URLSearchParams(Object.assign({
			action: action,
			post_id: postId,
			nonce: nonce
		}, body || {}));
		return fetch(ajaxUrl, {
			method: 'POST',
			body: params,
			credentials: 'same-origin'
		}).then(function (r) { return r.json(); });
	}

	/* ------------------------------------------------------------------ */
	/* RESTORE PERSISTENT "ALREADY AHA'D" STATE                           */
	/* ------------------------------------------------------------------ */

	try {
		if (localStorage.getItem(lsKey) === '1') {
			ahaBtn.classList.add('aif-aha--clicked');
			ahaBtn.setAttribute('aria-pressed', 'true');
		}
	} catch (e) { /* ignore */ }

	/* ------------------------------------------------------------------ */
	/* TOAST: open / close / auto-hide                                    */
	/* ------------------------------------------------------------------ */

	function openToast(opts) {
		// opts.swapAhaLabel — when true (default), swap the Aha! label to
		// the "thanks" message while the toast is open. Share-button
		// triggered opens pass false so the Aha! state is untouched.
		opts = opts || {};
		const swapAhaLabel = opts.swapAhaLabel !== false;
		populateToastButtons();
		toast.removeAttribute('hidden');
		// Force reflow before adding "open" class so CSS transition runs.
		void toast.offsetWidth;
		toast.classList.add('aif-engagement-toast--open');
		if (swapAhaLabel) showThanksLabel();
		clearTimeout(toastTimer);
		toastTimer = setTimeout(closeToast, TOAST_AUTO_HIDE_MS);
	}

	function closeToast() {
		clearTimeout(toastTimer);
		toast.classList.remove('aif-engagement-toast--open');
		// Restore the count label (no-op if it was never swapped).
		showCountLabel();
		// Wait for slide-up transition before hiding entirely.
		setTimeout(function () {
			if (!toast.classList.contains('aif-engagement-toast--open')) {
				toast.setAttribute('hidden', '');
			}
		}, 250);
	}

	function showThanksLabel() {
		ahaLabelEl.textContent = i18n.ahaThanks || ahaLabelEl.textContent;
		ahaCountEl.style.display = 'none';
	}

	function showCountLabel() {
		ahaLabelEl.textContent = i18n.ahaLabel || ahaLabelEl.textContent;
		ahaCountEl.style.display = '';
	}

	toastClose.addEventListener('click', closeToast);

	// Cancel auto-hide if user interacts inside the toast.
	toast.addEventListener('mouseenter', function () { clearTimeout(toastTimer); });
	toast.addEventListener('mouseleave', function () {
		clearTimeout(toastTimer);
		toastTimer = setTimeout(closeToast, TOAST_AUTO_HIDE_MS);
	});

	/* ------------------------------------------------------------------ */
	/* TOAST: clone AddToAny buttons so user can share without scrolling   */
	/* ------------------------------------------------------------------ */

	/**
	 * Build a real share URL per platform.
	 *
	 * AddToAny stores placeholder hrefs ("/#facebook") and intercepts clicks
	 * with its own JS to build the real URL at click-time. Cloned anchors
	 * are NOT bound to that JS, so we build the URLs ourselves using the
	 * current page URL + title.
	 */
	function buildShareUrl(platform, pageUrl, pageTitle) {
		const u = encodeURIComponent(pageUrl);
		const t = encodeURIComponent(pageTitle);
		switch (platform) {
			case 'facebook':  return 'https://www.facebook.com/sharer/sharer.php?u=' + u;
			case 'twitter':
			case 'x':         return 'https://twitter.com/intent/tweet?url=' + u + '&text=' + t;
			case 'linkedin':  return 'https://www.linkedin.com/sharing/share-offsite/?url=' + u;
			case 'email':     return 'mailto:?subject=' + t + '&body=' + u;
			// Mastodon is decentralized — mastodonshare.com asks user for their instance.
			case 'mastodon':  return 'https://mastodonshare.com/?url=' + u + '&text=' + t;
			case 'reddit':    return 'https://www.reddit.com/submit?url=' + u + '&title=' + t;
			case 'whatsapp':  return 'https://api.whatsapp.com/send?text=' + t + '%20' + u;
			case 'telegram':  return 'https://t.me/share/url?url=' + u + '&text=' + t;
			case 'copy_link': return null; // handled separately
			default:          return null;
		}
	}

	function populateToastButtons() {
		if (toastBtns.children.length > 0) return; // already populated
		const sourceKit = document.querySelector('.a2a_kit');
		if (!sourceKit) {
			toastBtns.textContent = '—';
			return;
		}
		// Clone the AddToAny kit so we keep its visual styling (icons, layout).
		const cloned = sourceKit.cloneNode(true);
		cloned.removeAttribute('id');
		cloned.removeAttribute('hidden');
		toastBtns.appendChild(cloned);

		// Rewrite hrefs to real share URLs based on the current page.
		const pageUrl   = window.location.href;
		const pageTitle = document.title;
		cloned.querySelectorAll('a').forEach(function (a) {
			const cls = a.className || '';
			const m = cls.match(/a2a_button_([a-z0-9_]+)/i);
			if (!m) return;
			const platform = m[1].toLowerCase();
			const url = buildShareUrl(platform, pageUrl, pageTitle);
			if (url) {
				a.setAttribute('href', url);
				a.setAttribute('target', '_blank');
				a.setAttribute('rel', 'noopener noreferrer');
			} else if (platform === 'copy_link') {
				// Custom click handler for Copy link
				a.setAttribute('href', '#copy-link');
				a.addEventListener('click', function (e) {
					e.preventDefault();
					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(pageUrl);
					}
				});
			}
		});

		// Bind our own share-click tracking (counter AJAX) on the cloned buttons.
		bindShareClicks(cloned);
	}

	/* ------------------------------------------------------------------ */
	/* SHARE: detect platform from anchor + AJAX + dedup-aware UI update  */
	/* ------------------------------------------------------------------ */

	function detectPlatformFromAnchor(a) {
		// Try AddToAny class names: .a2a_button_twitter / facebook / linkedin / copy_link / etc.
		const cls = a.className || '';
		const m = cls.match(/a2a_button_([a-z0-9_]+)/i);
		if (m) return m[1].toLowerCase();
		// Try data attribute
		if (a.dataset && a.dataset.a2aService) return a.dataset.a2aService.toLowerCase();
		// Heuristics from URL
		const href = (a.href || '').toLowerCase();
		if (href.includes('twitter') || href.includes('x.com')) return 'twitter';
		if (href.includes('linkedin')) return 'linkedin';
		if (href.includes('facebook')) return 'facebook';
		if (href.includes('mailto:')) return 'email';
		return 'other';
	}

	function bindShareClicks(scope) {
		const root = scope || document;
		const links = root.querySelectorAll('.a2a_kit a');
		links.forEach(function (a) {
			if (a.dataset.aifBound === '1') return;
			a.dataset.aifBound = '1';
			a.addEventListener('click', function () {
				const platform = detectPlatformFromAnchor(a);
				postJSON('aif_share', { platform: platform })
					.then(function (j) {
						if (j && j.success && typeof j.data.count === 'number') {
							// Update counter in main row only if server confirms an actual
							// (non-deduped) increment.
							setShareCount(j.data.count);
						}
					})
					.catch(function () { /* silent */ });
			});
		});
	}

	// Bind clicks on the standalone AddToAny block (rendered after the_content).
	bindShareClicks(document);

	/* ------------------------------------------------------------------ */
	/* SHARE button click handler — opens toast without Aha! side effects */
	/* ------------------------------------------------------------------ */

	if (shareBtn) {
		shareBtn.addEventListener('click', function () {
			openToast({ swapAhaLabel: false });
		});
	}

	/* ------------------------------------------------------------------ */
	/* AHA! click handler                                                 */
	/* ------------------------------------------------------------------ */

	ahaBtn.addEventListener('click', function () {
		// If already clicked (per localStorage), still open the toast but no
		// optimistic increment.
		const alreadyClicked = ahaBtn.classList.contains('aif-aha--clicked');

		if (!alreadyClicked) {
			// Optimistic: increment counter and mark as clicked immediately.
			const current = parseInt(ahaCountEl.textContent || '0', 10);
			setAhaCount(current + 1);
			markAhaClicked();
		}

		openToast();

		postJSON('aif_aha', {})
			.then(function (j) {
				if (j && j.success && typeof j.data.count === 'number') {
					// Reconcile: server is source of truth.
					setAhaCount(j.data.count);
					// If server says already_aha, ensure UI reflects that.
					if (j.data.already_aha) {
						markAhaClicked();
					}
				}
			})
			.catch(function () {
				// On failure, leave optimistic UI as-is. Worst case: visitor
				// sees +1 they didn't truly get; reload corrects it.
			});
	});
})();
