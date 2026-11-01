/**
 * L2 — LAYOUT SANITY SWEEP (the broken-screen detector).
 * PLAYWRIGHT-AGENTIC §5 L2, built 2026-07-06 (audit Phase 0).
 *
 * AUTO-DISCOVERY LAW: tabs are crawled from the styleguide sidebar at
 * runtime — a tab added by any agent is covered the moment it exists.
 * Never register tabs here.
 *
 * Named invariants per tab × brand × viewport:
 *   overflow    — the document never scrolls horizontally
 *   wide        — no demo element wider than the viewport (unless inside
 *                 a sanctioned overflow-x scroll container)
 *   occluded    — every visible interactive control is hittable at its
 *                 center (elementFromPoint — Playwright can't see overlap
 *                 natively, ms/playwright#9923 won't-fix)
 *   zero-size   — no visible, class-bearing, content-bearing demo element
 *                 renders at 0×0
 *   console     — zero console errors / pageerrors
 *   requests    — zero ≥400 responses; every rendered <img> decoded
 *
 * Four rules aligned with the site-level layout helper (issue #2), judged
 * against the reviewed tests/layout-baseline.json (only NEW violations fail;
 * thresholds are frozen in THRESHOLDS and are never raised to get green):
 *   padding          — text inside a listed component sits >= 8px from every
 *                      side that has a visible border or background
 *   child-overflow   — text or link text never leaves its parent's content box
 *   box-in-box       — no bordered/filled box inside a bordered/filled box
 *                      (exceptions: badges, buttons, form controls, logo tiles,
 *                      avatars, modal; styleguide chrome `sg-*` is not a box)
 *   input-button-row — an input and a button side by side in one form/group
 *                      have equal height and aligned tops (+-1px)
 * Baseline: LAYOUT_UPDATE_BASELINE=1 npx playwright test tests/layout.spec.js --workers=1
 *
 * OPT-OUTS (visible in markup, greppable — never hidden config):
 *   data-sg-overlap-ok  on an ancestor sanctions intentional overlap
 *   data-sg-zero-ok     on an ancestor sanctions intentional 0×0
 *   data-sg-box-ok      on an ancestor sanctions intentional box-in-box
 *   data-sg-pad-ok      on an ancestor sanctions a tight padding demo
 *
 * Failures are NAMED and agent-actionable:
 *   [aifounders@375] modal :: occluded: BUTTON.btn--primary center hit by DIV.modal__overlay
 */

const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

// Frozen thresholds of the four rules (the design-system rules, not knobs).
const THRESHOLDS = Object.freeze({
  minInsetPx: 8, // padding: minimum text inset from a visible edge
  textOverflowPx: 1, // child-overflow tolerance (anti-aliasing, italic overhang)
  rowPx: 1, // input-button-row: max height / top difference
});

// DS components whose text must keep the inset (components.css classes).
const PADDING_COMPONENTS = [
  '.btn', '.badge', 'td', 'th', '.form-banner', '.persona-card', '.preview-card',
  '.reference-card', '.course-info-card', '.blurb', '.record', '.info-box', '.info-bar',
  '.sticky-bar', '.register-banner', '.nl-card', '.accordion', '.nav-dropdown-item',
  '.selection-item', '.segmented-option', '.aif-engagement-toast', '.modal',
];
// Boxes allowed inside another box (subtree).
const BOX_EXCEPTIONS = [
  '.badge', '.btn', 'button', '[role="button"]', 'input', 'select', 'textarea', '.form-control',
  '.selection-item', '.segmented-option', '.site-logo', '.logos-grid', '[class*="avatar"]',
  '.modal', 'dialog', '[role="dialog"]',
];
// Styleguide chrome and demo scaffolding: not a DS box, not judged.
const SG_CHROME = '[class^="sg-"], [class*=" sg-"]';
// Styleguide documentation tables and colour swatches: reference data, not DS components (subtree).
const SG_DOC = '.sg-table, .sg-props-table, .sg-swatch';

const BASELINE_PATH = path.join(__dirname, 'layout-baseline.json');
const UPDATE_BASELINE = !!process.env.LAYOUT_UPDATE_BASELINE;

const BRANDS = ['aifounders', 'aiguild'];
const VIEWPORTS = [
  { name: '1280', width: 1280, height: 900 },
  { name: '375', width: 375, height: 812 },
];

// Noise that is not the DS's fault (kept explicit + commented):
const IGNORED_REQUESTS = [
  /\/favicon\.ico$/, // WP default install has none; not a DS asset
  // Template items embed the REAL live page (zero-hallucination rebuild
  // 2026-07-16); the embedded page's own third-party telemetry legitimately
  // fails off-production (Umami 403s the localhost origin). Foreign-origin
  // failures are environment noise, never DS defects.
  /^https?:\/\/(?!localhost)[^/]*\//,
];

function describeFinding(brand, vp, slug, kind, detail) {
  return `[${brand}@${vp}] ${slug} :: ${kind}: ${detail}`;
}

async function discoverTabs(page, brand) {
  await page.goto(`/?aifds_styleguide=1&item=colors&theme=${brand}`);
  const tabs = await page.$$eval('.sg-nav a', (links) =>
    links.map((a) => {
      const u = new URL(a.href);
      return u.searchParams.get('item');
    })
  );
  if (!tabs.length) {
    throw new Error('sidebar discovery returned 0 tabs — the sweep is blind');
  }
  return tabs;
}

/** In-page scan: wide, occluded, zero-size — one evaluate per tab. */
async function scanPage(page) {
  return page.evaluate(() => {
    const findings = [];
    const root = document.querySelector('.sg-content');
    if (!root) {
      return ['no .sg-content demo region rendered'];
    }
    const docW = document.documentElement.clientWidth;

    const label = (el) => {
      const id = el.id ? `#${el.id}` : '';
      const cls = el.classList.length ? '.' + [...el.classList].slice(0, 3).join('.') : '';
      return `${el.tagName}${id}${cls}`;
    };
    const inScrollContainer = (el) => {
      for (let n = el.parentElement; n && n !== document.body; n = n.parentElement) {
        const o = getComputedStyle(n).overflowX;
        if (o === 'auto' || o === 'scroll' || o === 'hidden') return true;
      }
      return false;
    };

    // ── wide: element wider than the viewport ──────────────────────────
    for (const el of root.querySelectorAll('*')) {
      if (!(el instanceof HTMLElement)) continue;
      const r = el.getBoundingClientRect();
      if (r.width > docW + 2 && !inScrollContainer(el)) {
        findings.push(`wide: ${label(el)} is ${Math.round(r.width)}px vs viewport ${docW}px`);
      }
    }

    // ── occluded: interactive control centers must be hittable ─────────
    const controls = root.querySelectorAll(
      'a, button, input, select, textarea, [role="button"]'
    );
    for (const el of controls) {
      if (el.closest('[data-sg-overlap-ok]')) continue;
      // controls inside CLOSED overlays (opacity/visibility-hidden modals)
      // have boxes but are not user-reachable — not an overlap
      if (el.checkVisibility && !el.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true })) continue;
      if (el.closest('[aria-hidden="true"]')) continue;
      const r = el.getBoundingClientRect();
      if (r.width < 2 || r.height < 2) continue; // zero-size handled below
      el.scrollIntoView({ block: 'center', inline: 'nearest' });
      const rr = el.getBoundingClientRect();
      const cx = Math.min(Math.max(rr.left + rr.width / 2, 1), docW - 1);
      const cy = rr.top + rr.height / 2;
      if (cy < 1 || cy > document.documentElement.clientHeight - 1) continue;
      const hit = document.elementFromPoint(cx, cy);
      if (!hit) continue;
      // styleguide CHROME floating above scrolled content is not a DS
      // overlap (the control is reachable at other scroll positions)
      if (hit.closest && hit.closest('.sg-topbar, .sg-sidebar')) continue;
      const related =
        hit === el || el.contains(hit) || hit.contains(el) ||
        (el.labels && [...el.labels].some((l) => l === hit || l.contains(hit)));
      if (!related) {
        findings.push(`occluded: ${label(el)} center hit by ${label(hit)}`);
      }
    }

    // ── zero-size: visible, class-bearing, content-bearing, but 0×0 ────
    for (const el of root.querySelectorAll('[class]')) {
      if (!(el instanceof HTMLElement)) continue;
      if (el.closest('[data-sg-zero-ok]')) continue;
      if (!el.offsetParent) continue; // display:none subtree / fixed — skip
      const r = el.getBoundingClientRect();
      if (r.width === 0 && r.height === 0 && (el.children.length || el.textContent.trim())) {
        findings.push(`zero-size: ${label(el)} renders 0×0 with content`);
      }
    }

    return findings;
  });
}

/**
 * In-page scan of the four helper rules (ported from the site-level layout
 * helper). Self-contained: runs in the browser. Returns
 * [{ rule, selector, side?, detail, px }].
 */
function scanBoxRules(o) {
  const out = [];
  const T = o.t;
  const root = document.querySelector('.sg-content');
  if (!root) return out;
  const docEl = document.documentElement;
  const docW = docEl.clientWidth;
  const round = (n) => Math.round(n * 10) / 10;
  const num = (v) => parseFloat(v) || 0;
  const styles = new Map();
  const css = (el) => {
    let c = styles.get(el);
    if (!c) { c = getComputedStyle(el); styles.set(el, c); }
    return c;
  };
  const label = (el) => `${el.tagName.toLowerCase()}${el.id ? '#' + el.id : ''}` +
    (el.classList && el.classList.length ? '.' + [...el.classList].slice(0, 3).join('.') : '');
  // Short unique CSS path: climb until the path matches exactly one node.
  const pathOf = (el) => {
    const parts = [];
    for (let n = el; n && n.nodeType === 1 && n !== docEl; n = n.parentElement) {
      if (n.id && document.querySelectorAll('#' + CSS.escape(n.id)).length === 1) {
        parts.unshift('#' + CSS.escape(n.id));
        break;
      }
      let part = n.tagName.toLowerCase() + [...n.classList].slice(0, 2).map((c) => '.' + CSS.escape(c)).join('');
      const p = n.parentElement;
      if (p && [...p.children].filter((c) => c !== n && c.matches(part)).length) {
        part += `:nth-child(${[...p.children].indexOf(n) + 1})`;
      }
      parts.unshift(part);
      try { if (document.querySelectorAll(parts.join(' > ')).length === 1) break; } catch (e) { /* keep climbing */ }
    }
    return parts.join(' > ');
  };
  const shown = (el) => (el.checkVisibility
    ? el.checkVisibility({ checkOpacity: true, checkVisibilityCSS: true })
    : !!el.offsetParent);
  // Visually hidden (sr-only boxes, collapsed accordions, parked off-screen): users never see it.
  const hiddenMemo = new Map();
  const visuallyHidden = (el) => {
    if (!el || el === document.body || el === docEl) return false;
    if (hiddenMemo.has(el)) return hiddenMemo.get(el);
    const cs = css(el);
    const r = el.getBoundingClientRect();
    const clips = cs.overflowX !== 'visible' || cs.overflowY !== 'visible' || cs.clip !== 'auto' || cs.clipPath !== 'none';
    const tiny = (r.width <= 1 || r.height <= 1) && clips && cs.display !== 'contents';
    const parked = cs.display !== 'contents' && (r.width > 0 || r.height > 0) &&
      (r.right <= 0 || r.bottom + window.scrollY <= 0 || r.left >= docW);
    const res = tiny || parked || visuallyHidden(el.parentElement);
    hiddenMemo.set(el, res);
    return res;
  };
  const skip = (el) => !shown(el) || visuallyHidden(el) || !!el.closest(o.doc);

  const alphaOf = (c) => {
    if (!c || c === 'transparent') return 0;
    let m = c.match(/\/\s*([\d.]+)(%?)\s*\)$/);
    if (m) return m[2] ? num(m[1]) / 100 : num(m[1]);
    m = c.match(/^rgba\((?:[^,]+,){3}\s*([\d.]+)\s*\)$/);
    return m ? num(m[1]) : 1;
  };
  const SIDES = ['top', 'right', 'bottom', 'left'];
  const borderOn = (cs, s) => num(cs.getPropertyValue(`border-${s}-width`)) > 0 &&
    !/none|hidden/.test(cs.getPropertyValue(`border-${s}-style`)) &&
    alphaOf(cs.getPropertyValue(`border-${s}-color`)) > 0;
  const backdrop = (el) => {
    for (let n = el.parentElement; n; n = n.parentElement) {
      const c = css(n).backgroundColor;
      if (alphaOf(c) > 0) return c;
    }
    return 'rgb(255, 255, 255)';
  };
  // A background counts only when it differs from what is behind it.
  const visibleBg = (el) => {
    const cs = css(el);
    if (cs.backgroundImage && cs.backgroundImage !== 'none') return true;
    const c = cs.backgroundColor;
    return alphaOf(c) > 0 && c !== backdrop(el);
  };
  // A "box" has visible edges: border on all four sides, or a visible background. Full-bleed bands
  // and styleguide chrome are not boxes; a single divider line is a separator.
  const boxMemo = new Map();
  const boxKind = (el) => {
    if (boxMemo.has(el)) return boxMemo.get(el);
    let k = '';
    if (el !== document.body && el !== docEl && el instanceof HTMLElement && !el.matches(o.chrome)) {
      const r = el.getBoundingClientRect();
      const band = r.left <= 0.5 && r.right >= docW - 0.5;
      if (!band && r.width > 0 && r.height > 0) {
        const cs = css(el);
        if (SIDES.every((s) => borderOn(cs, s))) k = 'border';
        else if (visibleBg(el)) k = 'background';
      }
    }
    boxMemo.set(el, k);
    return k;
  };
  const range = document.createRange();
  const textRects = (el) => {
    const rects = [];
    const w = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
    for (let t = w.nextNode(); t; t = w.nextNode()) {
      if (!t.data.trim()) continue;
      const p = t.parentElement;
      if (!p || skip(p)) continue;
      range.selectNodeContents(t);
      for (const q of range.getClientRects()) if (q.width > 0 && q.height > 0) rects.push(q);
    }
    return rects;
  };
  const all = [...root.querySelectorAll('*')].filter((el) => el instanceof HTMLElement);

  // ── padding: text inset >= 8px from every visible edge of a listed component ───────────────────
  const seen = new Set();
  for (const el of root.querySelectorAll(o.components.join(', '))) {
    if (seen.has(el) || !(el instanceof HTMLElement) || skip(el)) continue;
    seen.add(el);
    if (el.closest('[data-sg-pad-ok]') || el.matches(o.chrome)) continue;
    const cs = css(el);
    const bg = visibleBg(el);
    const edged = SIDES.filter((s) => bg || borderOn(cs, s));
    if (!edged.length) continue;
    const rects = textRects(el);
    if (!rects.length) continue;
    const t = rects.reduce((u, q) => ({
      top: Math.min(u.top, q.top), right: Math.max(u.right, q.right),
      bottom: Math.max(u.bottom, q.bottom), left: Math.min(u.left, q.left),
    }), { top: Infinity, right: -Infinity, bottom: -Infinity, left: Infinity });
    const r = el.getBoundingClientRect();
    const inset = { top: t.top - r.top, right: r.right - t.right, bottom: r.bottom - t.bottom, left: t.left - r.left };
    for (const s of edged) {
      if (inset[s] < T.minInsetPx - 0.05) {
        out.push({
          rule: 'padding', selector: pathOf(el), side: s,
          detail: `${label(el)} text inset ${s} ${round(inset[s])}px < ${T.minInsetPx}px (${bg ? 'background' : 'border'} edge)`,
          px: round(inset[s]),
        });
      }
    }
  }

  // ── child-overflow: text leaves its parent's content box ───────────────────────────────────────
  {
    const boxOf = (el) => {
      for (let n = el; n; n = n.parentElement) {
        const dsp = css(n).display;
        if (dsp !== 'inline' && dsp !== 'contents') return n;
      }
      return null;
    };
    // Scrolling and deliberate truncation (ellipsis / line-clamp) are by design; plain hidden is not.
    const byDesign = (el, axis) => {
      const cs = css(el);
      const ov = axis === 'x' ? cs.overflowX : cs.overflowY;
      if (ov === 'auto' || ov === 'scroll') return true;
      const clamp = cs.webkitLineClamp && cs.webkitLineClamp !== 'none';
      return (ov === 'hidden' || ov === 'clip') && (cs.textOverflow === 'ellipsis' || clamp);
    };
    const reported = new Set();
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    for (let t = w.nextNode(); t; t = w.nextNode()) {
      if (!t.data.trim()) continue;
      const p = t.parentElement;
      if (!p || reported.has(p) || skip(p) || p.matches(o.chrome)) continue;
      const box = boxOf(p);
      if (!box || box === document.body || box === docEl || box === root) continue;
      let exX = false;
      let exY = false;
      for (let n = p; n; n = n.parentElement) {
        if (byDesign(n, 'x')) exX = true;
        if (byDesign(n, 'y')) exY = true;
        if (n === box) break;
      }
      if (exX && exY) continue;
      const bcs = css(box);
      const br = box.getBoundingClientRect();
      const cl = br.left + num(bcs.borderLeftWidth) + num(bcs.paddingLeft);
      const cr = br.right - num(bcs.borderRightWidth) - num(bcs.paddingRight);
      const ct = br.top + num(bcs.borderTopWidth) + num(bcs.paddingTop);
      const cb = br.bottom - num(bcs.borderBottomWidth) - num(bcs.paddingBottom);
      const lh = parseFloat(css(p).lineHeight); // NaN for "normal"
      range.selectNodeContents(t);
      let worst = 0;
      let side = '';
      for (const q of range.getClientRects()) {
        if (!q.width || !q.height) continue;
        const cand = [];
        if (!exX) cand.push(['left', cl - q.left], ['right', q.right - cr]);
        if (!exY) {
          // Glyph boxes are taller than the line box when line-height < ~1.2: that half-leading
          // deficit is how fonts render, not overflow.
          const slack = Number.isNaN(lh) ? 0 : Math.max(0, (q.height - lh) / 2);
          cand.push(['top', ct - q.top - slack], ['bottom', q.bottom - cb - slack]);
        }
        for (const [s, v] of cand) if (v > worst) { worst = v; side = s; }
      }
      if (worst > T.textOverflowPx) {
        reported.add(p);
        const kind = p.closest('a') ? 'link text' : 'text';
        out.push({
          rule: 'child-overflow', selector: pathOf(p), side,
          detail: `${kind} "${t.data.trim().slice(0, 40)}" in ${label(p)} exceeds ${label(box)} content box by ${round(worst)}px (${side})`,
          px: round(worst),
        });
      }
    }
  }

  // ── box-in-box: bordered/filled box inside a bordered/filled box ────────────────────────────────
  {
    const EXEMPT = [...o.boxExceptions, 'img', 'svg', 'picture', 'video', 'canvas', 'iframe'].join(', ');
    for (const el of all) {
      const k = boxKind(el);
      if (!k || el.closest(EXEMPT) || el.closest('[data-sg-box-ok]') || skip(el)) continue;
      let outer = null;
      for (let n = el.parentElement; n && n !== root; n = n.parentElement) {
        if (boxKind(n)) { outer = n; break; }
      }
      if (!outer) continue;
      const r = el.getBoundingClientRect();
      const R = outer.getBoundingClientRect();
      const gap = Math.min(r.top - R.top, R.right - r.right, R.bottom - r.bottom, r.left - R.left);
      out.push({
        rule: 'box-in-box', selector: pathOf(el),
        detail: `${label(el)} (${k}) inside ${label(outer)} (${boxKind(outer)}) at ${pathOf(outer)}; closest gap ${round(gap)}px`,
        px: round(gap),
      });
    }
  }

  // ── input-button-row: same row in the same form/group -> equal height, aligned tops ────────────
  {
    const GROUP = 'form, fieldset, [role="group"], [role="search"], .input-pair, .form-group, .button-group';
    const INPUTS = 'input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=submit])' +
      ':not([type=button]):not([type=image]):not([type=reset]):not([type=range]):not([type=color]):not([type=file]), select';
    const BUTTONS = 'button, input[type=submit], input[type=button], input[type=image]';
    const groupOf = (el) => el.form || el.closest(GROUP) || null;
    const inputs = [...root.querySelectorAll(INPUTS)].filter((el) => !skip(el));
    const buttons = [...root.querySelectorAll(BUTTONS)].filter((el) => !skip(el));
    for (const a of inputs) {
      const g = groupOf(a);
      if (!g) continue;
      // The DS draws the visible field on .form-control-wrapper; the bare <input> inside is smaller.
      const ra = (a.closest('.form-control-wrapper') || a).getBoundingClientRect();
      for (const b of buttons) {
        if (groupOf(b) !== g) continue;
        const rb = b.getBoundingClientRect();
        const vOverlap = Math.min(ra.bottom, rb.bottom) - Math.max(ra.top, rb.top);
        if (vOverlap <= 0) continue; // not on the same row
        // A button drawn inside the field (password reveal, clear icon) is not side by side.
        const hOverlap = Math.min(ra.right, rb.right) - Math.max(ra.left, rb.left);
        if (hOverlap > 0) continue;
        const dh = Math.abs(ra.height - rb.height);
        const dt = Math.abs(ra.top - rb.top);
        if (dh > T.rowPx || dt > T.rowPx) {
          out.push({
            rule: 'input-button-row', selector: `${pathOf(a)} + ${pathOf(b)}`,
            detail: `${label(a)} (h ${round(ra.height)}px) vs ${label(b)} "${(b.textContent || b.value || '').trim().slice(0, 30)}" ` +
              `(h ${round(rb.height)}px): height differs ${round(dh)}px, tops differ ${round(dt)}px`,
            px: round(Math.max(dh, dt)),
          });
        }
      }
    }
  }
  return out;
}

// ── reviewed baseline (violations that exist today); only NEW ones fail ─────────────────────────
function loadBaseline() {
  try {
    const j = JSON.parse(fs.readFileSync(BASELINE_PATH, 'utf8'));
    return Array.isArray(j.violations) ? j : { ...j, violations: [] };
  } catch (e) {
    if (e.code === 'ENOENT') return { violations: [] };
    throw e;
  }
}
const keyOf = (v) => [v.rule, v.brand, v.viewport, v.page, v.selector, v.side || ''].join(' | ');

for (const brand of BRANDS) {
  for (const vp of VIEWPORTS) {
    test(`L2 layout sweep — ${brand} @ ${vp.name}`, async ({ page }) => {
      test.setTimeout(420_000);
      await page.setViewportSize({ width: vp.width, height: vp.height });

      const findings = [];
      const boxViolations = [];
      const tabs = await discoverTabs(page, brand);

      for (const slug of tabs) {
        const consoleErrors = [];
        const badRequests = [];
        const onConsole = (msg) => {
          if (msg.type() !== 'error') return;
          // Same environment-noise rule as IGNORED_REQUESTS: resource errors
          // raised by a foreign origin (embedded real page's telemetry) are
          // not DS defects. msg.location() carries the resource URL.
          const src = (msg.location() && msg.location().url) || '';
          if (IGNORED_REQUESTS.some((re) => re.test(src))) return;
          consoleErrors.push(msg.text().slice(0, 200));
        };
        const onPageError = (err) => consoleErrors.push(`pageerror: ${String(err).slice(0, 200)}`);
        const onResponse = (res) => {
          if (res.status() >= 400 && !IGNORED_REQUESTS.some((re) => re.test(res.url()))) {
            badRequests.push(`${res.status()} ${res.url().slice(0, 160)}`);
          }
        };
        page.on('console', onConsole);
        page.on('pageerror', onPageError);
        page.on('response', onResponse);

        await page.goto(`/?aifds_styleguide=1&item=${slug}&theme=${brand}`, {
          waitUntil: 'networkidle',
        });

        // overflow — the classic broken screen
        const overflow = await page.evaluate(() => {
          const d = document.documentElement;
          return d.scrollWidth > d.clientWidth + 1
            ? `document ${d.scrollWidth}px vs ${d.clientWidth}px`
            : null;
        });
        if (overflow) findings.push(describeFinding(brand, vp.name, slug, 'overflow', overflow));

        // padding / child-overflow / box-in-box / input-button-row (before scanPage: it scrolls)
        await page.evaluate(() => (document.fonts ? document.fonts.ready.then(() => true) : true));
        for (const v of await page.evaluate(scanBoxRules, {
          t: THRESHOLDS, components: PADDING_COMPONENTS, boxExceptions: BOX_EXCEPTIONS, chrome: SG_CHROME, doc: SG_DOC,
        })) {
          boxViolations.push({
            rule: v.rule, brand, viewport: vp.name, page: slug, selector: v.selector,
            ...(v.side ? { side: v.side } : {}), px: v.px, detail: v.detail,
          });
        }

        // wide / occluded / zero-size
        for (const f of await scanPage(page)) {
          const kind = f.split(':')[0];
          findings.push(describeFinding(brand, vp.name, slug, kind, f.slice(kind.length + 2)));
        }

        // broken images
        const deadImgs = await page.$$eval('.sg-content img', (imgs) =>
          imgs
            .filter((i) => i.offsetParent && i.complete && i.naturalWidth === 0)
            .map((i) => (i.getAttribute('src') || '').slice(0, 120))
        );
        for (const src of deadImgs) {
          findings.push(describeFinding(brand, vp.name, slug, 'dead-img', src));
        }

        for (const e of consoleErrors) {
          findings.push(describeFinding(brand, vp.name, slug, 'console', e));
        }
        for (const r of badRequests) {
          findings.push(describeFinding(brand, vp.name, slug, 'request', r));
        }

        page.off('console', onConsole);
        page.off('pageerror', onPageError);
        page.off('response', onResponse);
      }

      // Baseline compare for the four helper rules (this brand x viewport slice).
      const base = loadBaseline();
      const inSlice = (b) => b.brand === brand && b.viewport === vp.name;
      if (UPDATE_BASELINE) {
        const pick = ({ rule, brand: b, viewport, page: p, selector, side, px, detail }) =>
          ({ rule, brand: b, viewport, page: p, selector, ...(side ? { side } : {}), px, detail });
        const merged = base.violations.filter((b) => !inSlice(b)).concat(boxViolations.map(pick))
          .sort((a, b) => keyOf(a).localeCompare(keyOf(b)));
        fs.writeFileSync(BASELINE_PATH, JSON.stringify({
          note: 'Reviewed layout baseline: violations that exist today (rule, brand, viewport, page=tab, selector, px). ' +
            'New ones fail; remove entries as they get fixed. Regenerate: LAYOUT_UPDATE_BASELINE=1 npx playwright test tests/layout.spec.js --workers=1',
          count: merged.length,
          violations: merged,
        }, null, 1) + '\n');
      } else {
        const known = new Set(base.violations.filter(inSlice).map(keyOf));
        for (const v of boxViolations) {
          if (!known.has(keyOf(v))) {
            findings.push(describeFinding(brand, vp.name, v.page, v.rule, `${v.selector}${v.side ? ` (${v.side})` : ''} :: ${v.detail}`));
          }
        }
      }

      expect(
        findings,
        `L2 layout findings (${findings.length}):\n` + findings.join('\n')
      ).toEqual([]);
    });
  }
}
