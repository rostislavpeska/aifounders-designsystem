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
 * OPT-OUTS (visible in markup, greppable — never hidden config):
 *   data-sg-overlap-ok  on an ancestor sanctions intentional overlap
 *   data-sg-zero-ok     on an ancestor sanctions intentional 0×0
 *
 * Failures are NAMED and agent-actionable:
 *   [aifounders@375] modal :: occluded: BUTTON.btn--primary center hit by DIV.modal__overlay
 */

const { test, expect } = require('@playwright/test');

const BRANDS = ['aifounders', 'aiguild'];
const VIEWPORTS = [
  { name: '1280', width: 1280, height: 900 },
  { name: '375', width: 375, height: 812 },
];

// Noise that is not the DS's fault (kept explicit + commented):
const IGNORED_REQUESTS = [
  /\/favicon\.ico$/, // WP default install has none; not a DS asset
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

for (const brand of BRANDS) {
  for (const vp of VIEWPORTS) {
    test(`L2 layout sweep — ${brand} @ ${vp.name}`, async ({ page }) => {
      test.setTimeout(420_000);
      await page.setViewportSize({ width: vp.width, height: vp.height });

      const findings = [];
      const tabs = await discoverTabs(page, brand);

      for (const slug of tabs) {
        const consoleErrors = [];
        const badRequests = [];
        const onConsole = (msg) => {
          if (msg.type() === 'error') consoleErrors.push(msg.text().slice(0, 200));
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

      expect(
        findings,
        `L2 layout findings (${findings.length}):\n` + findings.join('\n')
      ).toEqual([]);
    });
  }
}
