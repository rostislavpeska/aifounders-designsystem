/**
 * SHOTS — screenshots for OPERATOR EYES, never a gate.
 * PLAYWRIGHT-AGENTIC §5: pixel assertions were rejected on evidence
 * (baseline non-determinism, agent-velocity churn); what screenshots ARE
 * for is operator judgment — this formalizes the chat-screenshot ritual
 * that caught a real fake.
 *
 *   npm run shots                    → every tab, both brands, desktop
 *   npm run shots -- modal           → one tab, both brands, desktop
 *   npm run shots -- modal mobile    → 390×844 (mobile-<tab>-<brand>.png)
 *   npm run shots -- all mobile      → every tab, mobile
 *
 * Output: _shots/[mobile-]<tab>-<brand>.png (full page). Git-ignored.
 */

import { chromium } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const OUT = join(root, '_shots');
mkdirSync(OUT, { recursive: true });

const only = process.argv[2] && process.argv[2] !== 'all' ? process.argv[2] : null;
const mobile = process.argv.includes('mobile');
const BASE = 'http://localhost:8090';

const browser = await chromium.launch();
const page = await browser.newPage({
  viewport: mobile ? { width: 390, height: 844 } : { width: 1440, height: 1000 },
});

await page.goto(`${BASE}/?aifds_styleguide=1&item=colors&theme=aifounders`);
const tabs = await page.$$eval('.sg-nav a', (ls) =>
  ls.map((a) => new URL(a.href).searchParams.get('item'))
);

for (const brand of ['aifounders', 'aiguild']) {
  for (const tab of tabs) {
    if (only && tab !== only) continue;
    await page.goto(`${BASE}/?aifds_styleguide=1&item=${tab}&theme=${brand}`, {
      waitUntil: 'networkidle',
    });
    const name = `${mobile ? 'mobile-' : ''}${tab}-${brand}.png`;
    await page.screenshot({ path: join(OUT, name), fullPage: true });
    console.log(name);
  }
}
await browser.close();
