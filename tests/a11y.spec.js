/**
 * L3 — A11Y SWEEP (axe), graduated Carbon/Storybook-style.
 * PLAYWRIGHT-AGENTIC §5 L3, built 2026-07-06 (audit Phase D).
 *
 * PHASE A (todo-mode, current): the gate is NO NEW VIOLATIONS versus the
 * committed baseline (tests/a11y-baseline.json — rule id + selector per
 * tab, diffable and agent-readable). Regenerate deliberately with:
 *   A11Y_UPDATE_BASELINE=1 npx playwright test tests/a11y.spec.js
 * PHASE B (ratchet, later): burn tabs down to []; a clean tab can never
 * regress (the baseline entry disappearing IS the ratchet).
 *
 * Scoped to the demo region (.sg-content) so findings always point at DS
 * components, never the styleguide shell. Auto-discovery law: tabs are
 * crawled from the sidebar at runtime.
 */

const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;
const { readFileSync, writeFileSync } = require('node:fs');
const { join } = require('node:path');

const BRANDS = ['aifounders', 'aiguild'];
const BASELINE_PATH = join(__dirname, 'a11y-baseline.json');
const UPDATE = !!process.env.A11Y_UPDATE_BASELINE;

function loadBaseline() {
  try {
    return JSON.parse(readFileSync(BASELINE_PATH, 'utf8'));
  } catch {
    return {};
  }
}

async function discoverTabs(page, brand) {
  await page.goto(`/?aifds_styleguide=1&item=colors&theme=${brand}`);
  const tabs = await page.$$eval('.sg-nav a', (links) =>
    links.map((a) => new URL(a.href).searchParams.get('item'))
  );
  if (!tabs.length) throw new Error('sidebar discovery returned 0 tabs');
  return tabs;
}

for (const brand of BRANDS) {
  test(`L3 a11y sweep — ${brand}`, async ({ page }) => {
    test.setTimeout(600_000);
    const baseline = loadBaseline();
    const current = {};
    const regressions = [];

    const tabs = await discoverTabs(page, brand);
    for (const slug of tabs) {
      await page.goto(`/?aifds_styleguide=1&item=${slug}&theme=${brand}`, {
        waitUntil: 'networkidle',
      });
      const results = await new AxeBuilder({ page })
        .include('.sg-content')
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();

      const key = `${brand}/${slug}`;
      const entries = [];
      for (const v of results.violations) {
        for (const n of v.nodes) {
          entries.push(`${v.id} :: ${(n.target || []).join(' ')}`);
        }
      }
      entries.sort();
      if (entries.length) current[key] = entries;

      const allowed = new Set(baseline[key] || []);
      for (const e of entries) {
        if (!allowed.has(e)) {
          regressions.push(`[${key}] NEW ${e}`);
        }
      }
    }

    if (UPDATE) {
      // merge this brand's slice into the committed baseline
      const merged = loadBaseline();
      for (const k of Object.keys(merged)) {
        if (k.startsWith(`${brand}/`)) delete merged[k];
      }
      Object.assign(merged, current);
      const sorted = Object.fromEntries(Object.entries(merged).sort());
      writeFileSync(BASELINE_PATH, JSON.stringify(sorted, null, 1) + '\n');
      console.log(
        `baseline updated for ${brand}: ${Object.keys(current).length} tabs carry violations, ` +
          `${Object.values(current).reduce((a, b) => a + b.length, 0)} entries`
      );
      return;
    }

    expect(
      regressions,
      `NEW a11y violations vs baseline (${regressions.length}):\n` + regressions.join('\n')
    ).toEqual([]);
  });
}
