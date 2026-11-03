// Spacing helper: vertical rhythm between sibling blocks (the Rejstřík spacing defect, 3 Nov 2026).
//
// CANONICAL COPY: aifounders-designsystem tests/helpers/spacing.js. It is copied VERBATIM to
// aifounders_web and aiguild as tests/e2e/helpers/spacing.js; the three copies must stay byte-identical
// (same convention as layout.js). Edit the DS copy, copy it over, compare sha256sum.
// Plain CommonJS, no dependencies apart from fs, no repo-specific values: roots and ignores come from the caller.
//
// What it measures. Inside `root`, every vertical stack P (display block / flow-root / list-item, a flex
// column, or a one-column grid) is cut into consecutive block pairs A, B (DOM order, B below A):
//   layout  = B.top - A.bottom, border box to border box (margins and row-gap included)
//   ink     = the first / last thing the eye sees: a visible edge (background, border on that side, shadow,
//             replaced element) or, for a text block, its content box (padding is invisible)
//   visual  = ink(B, top) - ink(A, bottom); hidden trailing / leading = invisible padding inside A / B
// Four named rules (thresholds frozen in SPACING_THRESHOLDS):
//   gap-doubled  flex/grid row-gap >= 16 plus a child margin >= 16: margin and gap doubled
//   gap-stacked  gap >= 16 plus invisible padding >= 16 inside A or B: hidden padding stacked on a gap
//   gap-touch    two visible edges of different width closer than 8 px: a tile glued to a hairline
//   gap-scale    a gap between two blocks >= 32 px tall that is not a --spacing-* token (SPACING_SCALE)
// Gaps inside a stretched cell (equal-height grid or flex-row items, e.g. cards with margin-top:auto
// actions) are alignment, not spacing: gap-stacked and gap-scale skip them.
//
// API
//   const violations = await checkSpacing(target, testInfo, { page, viewport, root, ignore, rules })
//     target = a Playwright Page or Frame -> [{ rule, page, viewport, selector, px, detail, count }]
//     Identical rule + selector findings on one page collapse into one entry (count, max px).
//   const { newViolations, known, fixed, updated } = compareSpacingBaseline(violations, baselinePath, { page, viewport })
//     SPACING_UPDATE_BASELINE=1 rewrites this page+viewport slice (run with --workers=1). It is separate from
//     LAYOUT_UPDATE_BASELINE on purpose: one baseline update never rewrites the other.
//   formatSpacing(list) -> readable multi-line string for assertion messages.
//   SPACING_INJECT_CSS=/path/file.css  what-if: inject that CSS before measuring, to prove a fix (or the
//     old defect) without editing the checkout the stack serves. Refused together with SPACING_UPDATE_BASELINE.
//
// Thresholds and the scale are constants on purpose: they are the design-system rules, not knobs. Never
// raise one to get a green run; fix the CSS or put the finding into the reviewed baseline with a reason.
'use strict';

const fs = require('fs');

const SPACING_THRESHOLDS = Object.freeze({
  stackPx: 16, // gap-doubled / gap-stacked: a row-gap, margin, gap or hidden padding this large counts
  minGapPx: 8, // gap-touch: two visible edges must be at least this far apart
  minBlockPx: 32, // gap-scale: both blocks at least this tall
  tolPx: 1, // rounding tolerance: pair order, scale match, equal widths, same column / row
});

// The --spacing-* tokens of aig-design-system assets/css/tokens.css (identical for both brands), plus 0.
const SPACING_SCALE = Object.freeze([0, 2, 4, 6, 8, 12, 16, 24, 32, 40, 48, 56, 80, 120]);

const SPACING_RULES = Object.freeze(['gap-doubled', 'gap-stacked', 'gap-touch', 'gap-scale']);

const DEFAULTS = {
  page: null, // page name used in reports and the baseline (required)
  viewport: null, // viewport name (default: testInfo.project.name)
  root: 'main', // scan region(s): every match is scanned
  ignore: [], // subtree selectors skipped entirely (third-party widgets, styleguide chrome)
  rules: SPACING_RULES, // subset to run
};

/** Measures the vertical gaps on `target` (Page or Frame) and returns the violations. */
async function checkSpacing(target, testInfo, options = {}) {
  const o = { ...DEFAULTS, ...options };
  if (!o.page) throw new Error('checkSpacing: options.page (the name used in reports and the baseline) is required');
  const unknown = o.rules.filter((r) => !SPACING_RULES.includes(r));
  if (unknown.length) throw new Error(`checkSpacing: unknown rule(s) ${unknown.join(', ')}`);
  const viewport = o.viewport || (testInfo && testInfo.project && testInfo.project.name);
  if (!viewport) throw new Error('checkSpacing: pass options.viewport (testInfo.project.name is empty)');

  const inject = process.env.SPACING_INJECT_CSS;
  if (inject) {
    if (process.env.SPACING_UPDATE_BASELINE) {
      throw new Error('checkSpacing: SPACING_INJECT_CSS is set together with SPACING_UPDATE_BASELINE; ' +
        'a baseline is never generated from injected CSS');
    }
    await target.addStyleTag({ path: inject });
    if (testInfo) testInfo.annotations.push({ type: 'spacing-what-if', description: inject });
  }

  await target.evaluate(settleInPage);
  const found = await target.evaluate(scanSpacingInPage, {
    rules: o.rules, root: o.root, ignore: o.ignore, t: SPACING_THRESHOLDS, scale: SPACING_SCALE,
  });
  const violations = found.map((v) => ({
    rule: v.rule, page: o.page, viewport, selector: v.selector, px: v.px, detail: v.detail, count: v.count,
  }));
  if (testInfo) {
    await testInfo.attach(`spacing-violations-${o.page}-${viewport}.json`, {
      body: JSON.stringify(violations, null, 1), contentType: 'application/json',
    });
  }
  return violations;
}

/**
 * Settles the page before measuring (runs inside the browser): lazy images are loaded now, so a late image
 * cannot move blocks, then fonts are awaited and two frames pass.
 */
async function settleInPage() {
  const pending = [];
  for (const img of document.images) {
    if (img.loading === 'lazy') img.loading = 'eager';
    if (!img.complete) {
      pending.push(new Promise((done) => {
        img.addEventListener('load', done, { once: true });
        img.addEventListener('error', done, { once: true });
      }));
    }
  }
  const limit = new Promise((done) => setTimeout(done, 10000));
  await Promise.race([Promise.all(pending), limit]);
  if (document.fonts) await Promise.race([document.fonts.ready, limit]);
  await new Promise((done) => requestAnimationFrame(() => requestAnimationFrame(done)));
  return true;
}

/**
 * In-page scan (runs inside the browser; must stay self-contained, no closures over Node scope).
 * Returns [{ rule, selector, px, detail, count }].
 */
function scanSpacingInPage(o) {
  const T = o.t;
  const on = (r) => o.rules.includes(r);
  const roots = [...document.querySelectorAll(o.root)];
  if (!roots.length) throw new Error(`checkSpacing: root ${o.root} not found`);
  const IGNORE = o.ignore && o.ignore.length ? o.ignore.join(', ') : null;
  const num = (v) => parseFloat(v) || 0;
  const round = (n) => Math.round(n);

  const styles = new Map();
  const css = (el) => {
    let c = styles.get(el);
    if (!c) { c = getComputedStyle(el); styles.set(el, c); }
    return c;
  };
  const rects = new Map();
  const rect = (el) => {
    let r = rects.get(el);
    if (!r) { r = el.getBoundingClientRect(); rects.set(el, r); }
    return r;
  };
  // Tag plus up to 2 classes. Classes with a digit (post-6087, page-id-3) are content IDs: they would break
  // baseline keys across stacks.
  const label = (el) => {
    const cls = [...el.classList].filter((c) => !/\d/.test(c)).slice(0, 2);
    return el.tagName.toLowerCase() + (cls.length ? '.' + cls.join('.') : '');
  };
  // Up to 3 labels ending at el, never above the scan root.
  const pathOf = (el, root) => {
    const parts = [];
    for (let n = el; n && parts.length < 3; n = n.parentElement) {
      parts.unshift(label(n));
      if (n === root) break;
    }
    return parts.join(' > ');
  };
  const ignored = (el) => !!(IGNORE && el.closest(IGNORE));

  // An ancestor that clips and has no area hides everything inside (closed accordion: 0-height overflow hidden).
  const clipMemo = new Map();
  const hidesChildren = (p) => {
    if (!p || p === document.documentElement) return false;
    if (clipMemo.has(p)) return clipMemo.get(p);
    const cs = css(p);
    const r = rect(p);
    const res = ((cs.overflowX !== 'visible' || cs.overflowY !== 'visible') && (r.height === 0 || r.width === 0)) ||
      hidesChildren(p.parentElement);
    clipMemo.set(p, res);
    return res;
  };
  const NOT_BLOCK = new Set(['SCRIPT', 'STYLE', 'TEMPLATE']);
  // A block: visible, in flow, not inline-level, not clipped away.
  const isBlock = (el) => {
    if (NOT_BLOCK.has(el.tagName.toUpperCase())) return false;
    const cs = css(el);
    if (cs.display === 'none' || cs.display === 'contents' || cs.display.startsWith('inline')) return false;
    if (cs.visibility === 'hidden' || cs.visibility === 'collapse') return false;
    if (cs.position === 'absolute' || cs.position === 'fixed') return false;
    const r = rect(el);
    if (!(r.width > 0 && r.height > 0)) return false;
    return !hidesChildren(el.parentElement);
  };
  const kidsMemo = new Map();
  const blocksOf = (p) => {
    let k = kidsMemo.get(p);
    if (!k) { k = [...p.children].filter(isBlock); kidsMemo.set(p, k); }
    return k;
  };
  const multiColumn = (p) => {
    const ks = blocksOf(p);
    return ks.some((k) => Math.abs(rect(k).left - rect(ks[0]).left) > T.tolPx);
  };

  const alphaOf = (c) => {
    if (!c || c === 'transparent') return 0;
    let m = c.match(/\/\s*([\d.]+)(%?)\s*\)$/);
    if (m) return m[2] ? num(m[1]) / 100 : num(m[1]);
    m = c.match(/^rgba\((?:[^,]+,){3}\s*([\d.]+)\s*\)$/);
    return m ? num(m[1]) : 1;
  };
  const REPLACED = new Set(['IMG', 'SVG', 'VIDEO', 'IFRAME', 'CANVAS', 'INPUT', 'SELECT', 'TEXTAREA', 'BUTTON', 'HR', 'PICTURE']);
  const edgeOn = (el, side) => {
    if (REPLACED.has(el.tagName.toUpperCase())) return true;
    const cs = css(el);
    if (alphaOf(cs.backgroundColor) > 0) return true;
    if (cs.backgroundImage && cs.backgroundImage !== 'none') return true;
    if (cs.boxShadow && cs.boxShadow !== 'none') return true;
    return num(cs.getPropertyValue(`border-${side}-width`)) > 0 &&
      !/none|hidden/.test(cs.getPropertyValue(`border-${side}-style`)) &&
      alphaOf(cs.getPropertyValue(`border-${side}-color`)) > 0;
  };
  const ownText = (el) => [...el.childNodes].some((n) => n.nodeType === 3 && n.data.trim());

  // The first (top) / last (bottom) thing the eye sees in el: { y, box, w, el }.
  const ink = (el, side) => {
    const top = side === 'top';
    const r = rect(el);
    if (edgeOn(el, side) || ignored(el)) return { y: top ? r.top : r.bottom, box: true, w: r.width, el };
    const kids = blocksOf(el);
    if (ownText(el) || !kids.length) {
      const cs = css(el);
      const y = top ? r.top + num(cs.borderTopWidth) + num(cs.paddingTop)
        : r.bottom - num(cs.borderBottomWidth) - num(cs.paddingBottom);
      return { y, box: false, w: r.width, el };
    }
    let pick = kids[0];
    for (const k of kids) {
      if (top ? rect(k).top < rect(pick).top : rect(k).bottom > rect(pick).bottom) pick = k;
    }
    return ink(pick, side);
  };

  // Gaps inside an item of a multi-column grid or a flex row (with a sibling at the same top) that the
  // container stretches are alignment, not spacing.
  const stretchMemo = new Map();
  const stretchedItem = (n) => {
    if (stretchMemo.has(n)) return stretchMemo.get(n);
    const p = n.parentElement;
    let res = false;
    if (p) {
      const pcs = css(p);
      const grid = pcs.display === 'grid' || pcs.display === 'inline-grid';
      const flexRow = (pcs.display === 'flex' || pcs.display === 'inline-flex') && pcs.flexDirection.startsWith('row');
      let cell = false;
      if (grid) cell = multiColumn(p);
      else if (flexRow) cell = blocksOf(p).some((s) => s !== n && Math.abs(rect(s).top - rect(n).top) <= T.tolPx);
      if (cell) {
        const ai = pcs.alignItems;
        const as = css(n).alignSelf;
        res = (ai === 'normal' || ai === 'stretch') && (as === 'auto' || as === 'normal' || as === 'stretch');
      }
    }
    stretchMemo.set(n, res);
    return res;
  };
  const inStretchedCell = (P) => {
    for (let n = P; n && n !== document.body && n !== document.documentElement; n = n.parentElement) {
      if (stretchedItem(n)) return true;
    }
    return false;
  };
  const verticalStack = (P) => {
    const cs = css(P);
    const d = cs.display;
    if (d === 'block' || d === 'flow-root' || d === 'list-item') return true;
    if (d === 'flex' || d === 'inline-flex') return cs.flexDirection.startsWith('column');
    if (d === 'grid' || d === 'inline-grid') return !multiColumn(P);
    return false;
  };
  const nearest = (v) => {
    const below = o.scale.filter((s) => s < v).pop();
    const above = o.scale.find((s) => s > v);
    return above === undefined ? `${below}` : `${below} / ${above}`;
  };

  const hits = new Map();
  const add = (rule, selector, px, detail) => {
    const key = `${rule} | ${selector}`;
    const h = hits.get(key);
    if (!h) hits.set(key, { rule, selector, px, detail, count: 1 });
    else {
      h.count += 1;
      if (px > h.px) { h.px = px; h.detail = detail; }
    }
  };

  const scanRoots = roots.filter((r) => !roots.some((x) => x !== r && x.contains(r)));
  for (const R of scanRoots) {
    if (ignored(R)) continue;
    for (const P of [R, ...R.querySelectorAll('*')]) {
      if (ignored(P)) continue;
      const kids = blocksOf(P);
      if (kids.length < 2 || !verticalStack(P)) continue;
      const pcs = css(P);
      const flexOrGrid = /flex|grid/.test(pcs.display);
      const rowGap = flexOrGrid ? round(num(pcs.rowGap)) : 0;
      let stretched = null;
      const isStretched = () => (stretched === null ? (stretched = inStretchedCell(P)) : stretched);

      for (let i = 0; i + 1 < kids.length; i++) {
        const A = kids[i];
        const B = kids[i + 1];
        if (ignored(A) || ignored(B)) continue;
        const ra = rect(A);
        const rb = rect(B);
        if (rb.top < ra.bottom - T.tolPx) continue;
        const layout = round(rb.top - ra.bottom);
        const ia = ink(A, 'bottom');
        const ib = ink(B, 'top');
        const underA = round(ra.bottom - ia.y); // space under A's last ink, inside A
        const overB = round(ib.y - rb.top); // space over B's first ink, inside B
        const ht = ia.box ? 0 : underA;
        const hl = ib.box ? 0 : overB;
        const visual = round(ib.y - ia.y);
        const selector = `${pathOf(A, R)} + ${label(B)}`;

        if (on('gap-doubled') && rowGap >= T.stackPx) {
          const mb = round(num(css(A).marginBottom));
          const mt = round(num(css(B).marginTop));
          if (mb >= T.stackPx || mt >= T.stackPx) {
            add('gap-doubled', selector, layout, `${layout}px = parent row-gap ${rowGap}` +
              (mb > 0 ? ` + ${label(A)} margin-bottom ${mb}` : '') + (mt > 0 ? ` + ${label(B)} margin-top ${mt}` : ''));
          }
        }

        if (on('gap-stacked') && layout >= T.stackPx) {
          const hidden = (ht >= T.stackPx ? ht : 0) + (hl >= T.stackPx ? hl : 0);
          if (hidden >= T.stackPx && !isStretched()) {
            // Space inside A or B around an inner visible edge is not "hidden" (spec), but it is part of the
            // visible gap: name it so the parts add up.
            const parts = [];
            if (ht > 0) parts.push(`hidden trailing ${ht} (${label(A)})`);
            else if (underA > T.tolPx) parts.push(`${underA} under the last edge inside ${label(A)}`);
            parts.push(`gap ${layout}`);
            if (hl > 0) parts.push(`hidden leading ${hl} (${label(B)})`);
            else if (overB > T.tolPx) parts.push(`${overB} above the first edge inside ${label(B)}`);
            add('gap-stacked', selector, visual, `visible gap ${visual}px = ${parts.join(' + ')}`);
          }
        }

        if (on('gap-touch') && ia.box && ib.box && visual >= 0 && visual < T.minGapPx && layout >= 0 &&
          Math.abs(ia.w - ib.w) > T.tolPx) {
          add('gap-touch', selector, visual, `visible edges ${visual}px apart (< ${T.minGapPx}): ` +
            `${label(ia.el)} (w ${round(ia.w)}) above ${label(ib.el)} (w ${round(ib.w)})`);
        }

        if (on('gap-scale') && ra.height >= T.minBlockPx && rb.height >= T.minBlockPx && layout > T.tolPx &&
          !o.scale.some((s) => Math.abs(layout - s) <= T.tolPx) && !isStretched()) {
          add('gap-scale', selector, layout, `gap ${layout}px is not a spacing token (nearest ${nearest(layout)})`);
        }
      }
    }
  }
  return [...hits.values()].sort((a, b) => `${a.rule} ${a.selector}`.localeCompare(`${b.rule} ${b.selector}`));
}

const keyOf = (v) => [v.rule, v.page, v.viewport, v.selector].join(' | ');
const sortKey = (a, b) => keyOf(a).localeCompare(keyOf(b));

function loadBaseline(baselinePath) {
  try {
    const j = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
    return Array.isArray(j.violations) ? j : { ...j, violations: [] };
  } catch (e) {
    if (e.code === 'ENOENT') return { violations: [] };
    throw e;
  }
}

/**
 * Splits violations into new (fail) and known (in the reviewed baseline). Matching key:
 * rule + page + viewport + selector. `fixed` = baseline entries of this page+viewport that no longer occur
 * (tighten the baseline). With SPACING_UPDATE_BASELINE=1 the slice is rewritten instead (requires scope;
 * run the spec with --workers=1 so slices do not race).
 */
function compareSpacingBaseline(violations, baselinePath, scope = {}) {
  const page = scope.page || (violations[0] && violations[0].page);
  const viewport = scope.viewport || (violations[0] && violations[0].viewport);
  const base = loadBaseline(baselinePath);
  const inScope = (b) => b.page === page && b.viewport === viewport;
  const slice = base.violations.filter(inScope);
  const known = new Set(slice.map(keyOf));
  const current = new Set(violations.map(keyOf));
  const newViolations = violations.filter((v) => !known.has(keyOf(v)));
  const fixed = slice.filter((b) => !current.has(keyOf(b)));
  let updated = false;
  if (process.env.SPACING_UPDATE_BASELINE) {
    if (process.env.SPACING_INJECT_CSS) throw new Error('compareSpacingBaseline: never update the baseline from injected CSS');
    if (!scope.page || !scope.viewport) throw new Error('compareSpacingBaseline: update mode needs scope { page, viewport }');
    const pick = ({ rule, page: p, viewport: vp, selector, px, count, detail }) =>
      ({ rule, page: p, viewport: vp, selector, px, count, detail });
    const merged = base.violations.filter((b) => !inScope(b)).concat(violations.map(pick)).sort(sortKey);
    const doc = {
      note: base.note || 'Reviewed spacing baseline: findings that exist today. New ones fail; remove entries as they get fixed.',
      generated: new Date().toISOString().slice(0, 10),
      count: merged.length,
      violations: merged,
    };
    fs.writeFileSync(baselinePath, JSON.stringify(doc, null, 1) + '\n');
    updated = true;
  }
  return { newViolations: updated ? [] : newViolations, known: violations.length - newViolations.length, fixed, updated };
}

/** "[rejstrik-en@mobile] gap-doubled: div.rj-out > div.rj-writers > h2 + div.articles-grid :: 64px = ..." per line. */
function formatSpacing(list) {
  return list.map((v) => `[${v.page}@${v.viewport}] ${v.rule}: ${v.selector} :: ${v.detail}` +
    (v.count > 1 ? ` (x${v.count})` : '')).join('\n');
}

module.exports = { checkSpacing, compareSpacingBaseline, formatSpacing, SPACING_RULES, SPACING_THRESHOLDS, SPACING_SCALE };
