/**
 * HEADER ENGINE — shrink + mobile menu + dropdown toggle + progress fallback.
 * Distilled from the byte-identical theme twins (HEADER-MAP 2026-07-06);
 * demo-aware: inside a .header-demo frame the engine binds locally and
 * never touches document scroll / body state.
 *
 * 1 · THE SHRINK — threshold 50px (harvested "à la davidkoci.cz"): toggles
 *     .main-header--scrolled; boolean-guarded; bails on --overlay mode.
 * 2 · MOBILE MENU — burger ↔ overlay: classes + aria-expanded + the
 *     scroll-lock canon (body.menu-open); Escape / backdrop / nav-away close.
 * 3 · DROPDOWN TOGGLE — hover + :focus-within live in CSS; here only the
 *     touch/click path for non-link triggers: .nav-item--open + aria.
 * 4 · PROGRESS FALLBACK — writes --reading-progress (rAF-throttled) when
 *     animation-timeline: scroll() is unsupported (harvested dual engine).
 */

(function () {
    'use strict';

    var SCROLL_THRESHOLD = 50; /* CALIBRATED harvested shrink threshold */

    function initShrink() {
        var header = document.querySelector(
            'body > .main-header, body > header.main-header'
        ) || document.querySelector('.main-header:not(.header-demo *)');
        if (!header || header.closest('.header-demo')) {
            return;
        }
        if (header.classList.contains('main-header--overlay')) {
            return; /* hero pages scroll the header away — no shrink */
        }
        var scrolled = false;
        function onScroll() {
            var now = window.scrollY > SCROLL_THRESHOLD;
            if (now !== scrolled) {
                scrolled = now;
                header.classList.toggle('main-header--scrolled', now);
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); /* refreshed mid-page */
    }

    function initMobileMenu() {
        document.querySelectorAll('.burger-toggle').forEach(function (burger) {
            var demo = burger.closest('.header-demo');
            var overlay = demo
                ? demo.querySelector('.mobile-menu-overlay')
                : document.querySelector('body > .mobile-menu-overlay') ||
                  document.querySelector('.mobile-menu-overlay:not(.header-demo *)');
            if (!overlay) {
                return;
            }
            if (!burger.hasAttribute('aria-expanded')) {
                burger.setAttribute('aria-expanded', 'false');
            }

            function setOpen(open) {
                burger.classList.toggle('burger-toggle--open', open);
                overlay.classList.toggle('mobile-menu-overlay--open', open);
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (!demo) {
                    document.body.classList.toggle('menu-open', open);
                }
            }

            burger.addEventListener('click', function (e) {
                e.preventDefault();
                setOpen(!burger.classList.contains('burger-toggle--open'));
            });

            /* backdrop click (the overlay itself, not its content) */
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    setOpen(false);
                }
            });

            /* nav-away: any item click closes */
            overlay.querySelectorAll('.mobile-nav-item, .mobile-lang-item').forEach(
                function (item) {
                    item.addEventListener('click', function () {
                        setOpen(false);
                    });
                }
            );

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && burger.classList.contains('burger-toggle--open')) {
                    setOpen(false);
                }
            });
        });
    }

    function initDropdowns() {
        document.querySelectorAll('.nav-item--has-dropdown').forEach(function (drop) {
            var trigger = drop.querySelector('button');
            if (!trigger) {
                return; /* link triggers: hover + :focus-within (CSS) suffice */
            }
            trigger.setAttribute('aria-haspopup', 'true');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.addEventListener('click', function () {
                var open = drop.classList.toggle('nav-item--open');
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && drop.classList.contains('nav-item--open')) {
                    drop.classList.remove('nav-item--open');
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    function initProgressFallback() {
        if (
            window.CSS &&
            CSS.supports &&
            CSS.supports('animation-timeline: scroll()')
        ) {
            return; /* the zero-JS modern path owns the fill */
        }
        var bars = document.querySelectorAll('.reading-progress');
        if (!bars.length) {
            return;
        }
        var ticking = false;
        function update() {
            var doc = document.documentElement;
            var max = doc.scrollHeight - window.innerHeight;
            var pct = max > 0 ? Math.min(1, Math.max(0, doc.scrollTop / max)) : 0;
            bars.forEach(function (bar) {
                bar.style.setProperty('--reading-progress', pct);
            });
            ticking = false;
        }
        window.addEventListener(
            'scroll',
            function () {
                if (!ticking) {
                    ticking = true;
                    window.requestAnimationFrame(update);
                }
            },
            { passive: true }
        );
        update();
    }

    function init() {
        initShrink();
        initMobileMenu();
        initDropdowns();
        initProgressFallback();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
