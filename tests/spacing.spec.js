/**
 * SPACING RHYTHM — vertical gaps between sibling blocks (helper: tests/helpers/spacing.js, the canonical
 * copy; aifounders_web and aiguild carry byte-identical copies in tests/e2e/helpers/spacing.js).
 *
 * Built 3 Nov 2026 after the Rejstřík (AIF project list) showed broken spacing on a phone that no layout
 * rule caught: a logo tile touching the meta hairline (gap-touch), invisible card padding stacked on a
 * margin (gap-stacked), an h2 margin doubled by a flex gap (gap-doubled, gap-scale).
 *
 * Part 1, rule self-tests: page.setContent() fixtures, no WordPress stack. Every rule has a failing and a
 * passing fixture, plus a miniature of the Rejstřík defect, a closed accordion (skipped) and equal-height
 * cards (alignment, not spacing).
 * Part 2, template pages: the four Templates items of the styleguide (they embed the REAL page) per brand
 * and viewport, judged against the reviewed tests/spacing-baseline.json (only NEW findings fail).
 * The Templates group is served by the ds-tests stack (:8195, this repo's code); the DS copy vendored into
 * the AIF dev stack (:8090) predates it:  BASE_URL=http://localhost:8195 npm run test:spacing
 * Baseline: SPACING_UPDATE_BASELINE=1 BASE_URL=http://localhost:8195 npx playwright test tests/spacing.spec.js --workers=1
 */

const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const { checkSpacing, compareSpacingBaseline, formatSpacing, SPACING_SCALE } = require('./helpers/spacing');

const BASELINE = path.join(__dirname, 'spacing-baseline.json');

// ── Part 1: rule self-tests ─────────────────────────────────────────────────────────────────────────

const BASE_CSS = '<style>*{box-sizing:border-box} body{margin:0;font:16px/24px sans-serif} ' +
  'main{display:block;width:360px} h2,h3,p{margin:0}</style>';

// Rejstřík miniature: two condensed cards (top hairline, padding-bottom kept, bottom border removed) in a
// one-column grid, then the CTA band 56 px below.
const cards = (padBottom) => `
  <div class="grid" style="display:grid;grid-template-columns:1fr;gap:24px">
    <article class="card" style="border-top:1px solid #ccc;padding:24px 0 ${padBottom}px"><h3>Card one</h3><p>Text</p></article>
    <article class="card" style="border-top:1px solid #ccc;padding:24px 0 ${padBottom}px"><h3>Card two</h3><p>Text</p></article>
  </div>
  <div class="cta" style="margin-top:56px"><div class="banner" style="background:#eee;padding:24px">Have your own AI project?</div></div>`;
const writers = (h2MarginBottom) => `
  <div class="writers" style="display:flex;flex-direction:column;gap:32px">
    <h2 style="margin:0 0 ${h2MarginBottom}px;font-size:28px;line-height:36px">Written by people</h2>
    <div class="articles" style="border-top:1px solid #ccc;height:120px">Articles</div>
  </div>`;
const cardHead = (headMarginBottom) => `
  <article class="card">
    <div class="head" style="display:flex;align-items:center;gap:16px;margin-bottom:${headMarginBottom}px">
      <span class="tile" style="display:block;width:48px;height:48px;background:#36f"></span><h3>Project name</h3>
    </div>
    <div class="meta" style="border-top:1px solid #ccc;padding-top:8px">Meta line</div>
  </article>`;
const accordion = (contentHeight) => `
  <div class="accordion">
    <button class="accordion__header" style="display:block;width:100%;height:48px">Filters</button>
    <div class="accordion__content" style="height:${contentHeight};overflow:hidden">
      <div style="display:flex;flex-direction:column;gap:32px">
        <h2 style="margin:0 0 32px;line-height:36px">Inside</h2><div style="height:100px;background:#eee"></div>
      </div>
    </div>
  </div>`;
// Equal-height cards: the short card is stretched to the long one, its action sits at the bottom
// (margin-top:auto), so the 157 px gap above it is alignment. The control fixes the same height by hand
// without stretching: then the gap is real spacing and gap-scale must fire.
const cardGrid = (gridStyle, shortCardStyle) => `
  <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;${gridStyle}">
    <article style="display:flex;flex-direction:column;border:1px solid #ccc;padding:16px;${shortCardStyle}">
      <h3 style="font-size:28px;line-height:36px">Short</h3>
      <a style="display:block;margin-top:auto;padding:8px;background:#36f;color:#fff">Read</a>
    </article>
    <article style="display:flex;flex-direction:column;border:1px solid #ccc;padding:16px">
      <h3 style="font-size:28px;line-height:36px">Long</h3>
      <div style="height:157px;background:#eee"></div>
      <a style="display:block;margin-top:auto;padding:8px;background:#36f;color:#fff">Read</a>
    </article>
  </div>`;

const FIXTURES = [
  // [name, html, expected rules (sorted), expected { selectorPart: px } (optional)]
  ['rejstrik-mini: card padding + band margin', cards(24), ['gap-stacked'], { 'div.cta': 80, 'article.card + article.card': 48 }],
  ['rejstrik-mini fixed: card padding-bottom 0', cards(0), []],
  ['rejstrik-mini: h2 margin + flex gap', writers(32), ['gap-doubled', 'gap-scale'], { 'h2 + div.articles': 64 }],
  ['rejstrik-mini fixed: h2 margin 0', writers(0), []],
  ['rejstrik-mini: logo tile on the meta hairline', cardHead(0), ['gap-touch'], { 'div.head + div.meta': 0 }],
  ['rejstrik-mini fixed: head margin-bottom 8', cardHead(8), []],
  ['gap-doubled: row-gap 16 + margin-bottom 16',
    '<div style="display:flex;flex-direction:column;gap:16px"><div style="height:40px;background:#eee;margin-bottom:16px"></div>' +
    '<div style="height:40px;background:#eee"></div></div>', ['gap-doubled']],
  ['gap-doubled pass: row-gap 16 alone',
    '<div style="display:flex;flex-direction:column;gap:16px"><div style="height:40px;background:#eee"></div>' +
    '<div style="height:40px;background:#eee"></div></div>', []],
  ['gap-stacked: padding-bottom 24 + margin 24',
    '<div style="padding-bottom:24px">Text</div><div style="margin-top:24px">Text</div>', ['gap-stacked']],
  ['gap-stacked pass: margin 24 alone',
    '<div>Text</div><div style="margin-top:24px">Text</div>', []],
  ['gap-touch pass: equal-width boxes abut',
    '<div style="height:40px;background:#eee"></div><div style="height:40px;background:#ddd"></div>', []],
  ['gap-scale: 20 px between two boxes',
    '<div style="height:40px;background:#eee"></div><div style="margin-top:20px;height:40px;background:#eee"></div>', ['gap-scale']],
  ['gap-scale pass: 24 px between two boxes',
    '<div style="height:40px;background:#eee"></div><div style="margin-top:24px;height:40px;background:#eee"></div>', []],
  ['closed accordion: clipped content is skipped', accordion('0'), []],
  ['open accordion control: same content is measured', accordion('auto'), ['gap-doubled', 'gap-scale']],
  ['equal-height cards with margin-top:auto actions', cardGrid('', ''), []],
  ['control: same gap without stretching', cardGrid('align-items:start', 'height:267px'), ['gap-scale']],
];

test.describe('spacing rules: self-tests (no WP stack)', () => {
  for (const [name, html, rules, px] of FIXTURES) {
    test(`fixture: ${name}`, async ({ page }, testInfo) => {
      await page.setContent(`${BASE_CSS}<main>${html}</main>`);
      const v = await checkSpacing(page, testInfo, { page: 'fixture', viewport: 'fixture' });
      const got = [...new Set(v.map((x) => x.rule))].sort();
      expect(got, `rules for "${name}":\n${formatSpacing(v)}`).toEqual(rules);
      for (const [part, want] of Object.entries(px || {})) {
        const hit = v.find((x) => x.selector.includes(part));
        expect(hit && hit.px, `px of the finding at "${part}":\n${formatSpacing(v)}`).toBe(want);
      }
    });
  }

  test('SPACING_SCALE equals the --spacing-* tokens of assets/css/tokens.css (plus 0)', () => {
    const src = fs.readFileSync(path.join(__dirname, '..', 'assets', 'css', 'tokens.css'), 'utf8');
    const px = [...src.matchAll(/--spacing-(\d+)\s*:\s*([\d.]+)rem\s*;/g)].map((m) => {
      expect(Number(m[2]) * 16, `--spacing-${m[1]} is ${m[1]}px`).toBe(Number(m[1]));
      return Number(m[2]) * 16;
    });
    expect(px.length, 'tokens parsed').toBeGreaterThan(5);
    expect([0, ...new Set(px)].sort((a, b) => a - b)).toEqual([...SPACING_SCALE]);
  });

  test('what-if CSS: SPACING_INJECT_CSS applies a fix; refused with SPACING_UPDATE_BASELINE', async ({ page }, testInfo) => {
    const saved = { inject: process.env.SPACING_INJECT_CSS, update: process.env.SPACING_UPDATE_BASELINE };
    const css = testInfo.outputPath('fix.css');
    fs.writeFileSync(css, '.card { padding-bottom: 0 !important; }');
    try {
      await page.setContent(`${BASE_CSS}<main>${cards(24)}</main>`);
      process.env.SPACING_INJECT_CSS = css;
      delete process.env.SPACING_UPDATE_BASELINE;
      expect(await checkSpacing(page, testInfo, { page: 'fixture', viewport: 'fixture' })).toEqual([]);
      expect(testInfo.annotations.some((a) => a.type === 'spacing-what-if' && a.description === css)).toBe(true);
      process.env.SPACING_UPDATE_BASELINE = '1';
      await expect(checkSpacing(page, testInfo, { page: 'fixture', viewport: 'fixture' })).rejects.toThrow(/never generated from injected CSS/);
    } finally {
      for (const [k, v] of [['SPACING_INJECT_CSS', saved.inject], ['SPACING_UPDATE_BASELINE', saved.update]]) {
        if (v === undefined) delete process.env[k];
        else process.env[k] = v;
      }
    }
  });
});

// ── Part 2: template pages ──────────────────────────────────────────────────────────────────────────

const BRANDS = ['aifounders', 'aiguild'];
const ITEMS = ['template-homepage', 'template-page', 'template-article', 'template-archive'];
const VIEWPORTS = [{ width: 390, height: 844 }, { width: 1280, height: 900 }];
const SG_CHROME = ['[class^="sg-"]', '[class^="sgt-"]'];
// The embedded real pages carry production links and telemetry: never let them reach a production host.
const PROD_HOSTS = ['aifounders.cz', 'aiguild.cz', 'aiguild.blue', 'googletagmanager.com', 'google-analytics.com'];

test.describe('spacing: template pages (live stack)', () => {
  for (const brand of BRANDS) {
    for (const item of ITEMS) {
      for (const vp of VIEWPORTS) {
        const viewport = `${brand}@${vp.width}`;
        test(`spacing: ${item} ${viewport}`, async ({ page }, testInfo) => {
          test.setTimeout(90_000);
          await page.route('**/*', (route) => {
            const host = new URL(route.request().url()).hostname;
            return PROD_HOSTS.some((h) => host === h || host.endsWith('.' + h)) ? route.abort() : route.continue();
          });
          await page.setViewportSize(vp);
          const sg = `/?aifds_styleguide=1&item=${item}&theme=${brand}`;
          await page.goto(sg, { waitUntil: 'load' });
          const frameEl = page.locator('iframe.sgt-frame');
          expect(await frameEl.count(), `${sg} has no iframe.sgt-frame: this BASE_URL serves no Templates group ` +
            '(the DS copy vendored into the AIF dev stack :8090 predates it); run BASE_URL=http://localhost:8195').toBe(1);
          const src = await frameEl.getAttribute('src');

          // Measure the real page at the real viewport when it renders this brand by itself; otherwise inside
          // the styleguide iframe, where the brand is re-stamped onto the embedded document.
          let target = page;
          let ignore = [];
          await page.goto(src, { waitUntil: 'load' });
          if (await page.evaluate((b) => document.documentElement.getAttribute('data-theme') === b, brand)) {
            testInfo.annotations.push({ type: 'spacing-target', description: `${src} loaded directly` });
          } else {
            await page.goto(sg, { waitUntil: 'load' });
            target = await (await page.locator('iframe.sgt-frame').elementHandle()).contentFrame();
            await expect.poll(() => target.evaluate(() => document.documentElement.getAttribute('data-theme')),
              { timeout: 20_000 }).toBe(brand);
            ignore = SG_CHROME;
            const w = await target.evaluate(() => document.documentElement.clientWidth);
            testInfo.annotations.push({ type: 'spacing-target', description: `${src} inside the styleguide iframe (${brand} re-stamped), frame width ${w}px` });
          }

          const violations = await checkSpacing(target, testInfo, { page: item, viewport, ignore });
          const { newViolations, known, fixed } = compareSpacingBaseline(violations, BASELINE, { page: item, viewport });
          testInfo.annotations.push({ type: 'spacing-known', description: `${known} baseline spacing finding(s) still present` });
          if (fixed.length) {
            testInfo.annotations.push({
              type: 'spacing-fixed',
              description: `${fixed.length} baseline entr(ies) no longer occur; remove them from spacing-baseline.json`,
            });
          }
          expect(newViolations, `NEW spacing violations (${newViolations.length}):\n${formatSpacing(newViolations)}`).toEqual([]);
        });
      }
    }
  }
});
