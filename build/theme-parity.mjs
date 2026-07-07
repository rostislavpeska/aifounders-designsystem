#!/usr/bin/env node
/**
 * THEME-PARITY — the adoption gate (THEME-REFACTOR-SPEC §3).
 *
 * Proves a theme renders pixel-identical across the DS-adoption refactor.
 * Shared tool: the page inventory lives in the THEME repo; this script
 * lives in the DS repo (same spirit as shots.mjs + the L2 sweep).
 *
 *   node build/theme-parity.mjs before --inventory <path>   capture baseline
 *   node build/theme-parity.mjs after  --inventory <path>   compare vs baseline
 *
 * Flags:
 *   --full-matrix        use inventory.fullMatrix viewports (phase closes)
 *   --pages id1,id2      restrict to specific inventory pages
 *
 * BEFORE captures per page × viewport: full-page PNG, DOM fingerprint
 * (element count, sentinel computed styles, pll string sentinels, console
 * errors, overflow offenders, HTTP status, CSS payload), plus one axe scan
 * per page (desktop) and a copy of the container debug.log.
 *
 * AFTER recaptures and FAILS (exit 1) on: pixel diff > threshold (diff
 * heat-map written next to the shots), HTTP status change, sentinel or
 * string regression, NEW console errors, NEW overflow offenders, NEW axe
 * violations, NEW debug.log lines. CSS payload is printed (informational).
 *
 * NEVER WEAKEN: thresholds only go down.
 */

import { chromium } from '@playwright/test';
import { AxeBuilder } from '@axe-core/playwright';
import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

// ── args ──────────────────────────────────────────────────────────────────
const mode = process.argv[2];
if (!['before', 'after'].includes(mode)) {
  console.error('usage: node build/theme-parity.mjs <before|after> --inventory <path> [--full-matrix] [--pages id,id]');
  process.exit(1);
}
const argv = process.argv.slice(3);
const flag = (name) => {
  const i = argv.indexOf(name);
  return i === -1 ? null : argv[i + 1];
};
const inventoryPath = resolve(flag('--inventory') ?? '');
if (!existsSync(inventoryPath)) {
  console.error(`inventory not found: ${inventoryPath}`);
  process.exit(1);
}
const inv = JSON.parse(readFileSync(inventoryPath, 'utf8'));
const fullMatrix = argv.includes('--full-matrix');
const onlyPages = flag('--pages')?.split(',') ?? null;

const viewports = fullMatrix ? inv.fullMatrix : inv.viewports;
const pages = inv.pages.filter((p) => !onlyPages || onlyPages.includes(p.id));
const baseDir = join(dirname(inventoryPath), inv.baselineDir);
const afterDir = join(dirname(inventoryPath), '_after');
const outDir = mode === 'before' ? baseDir : afterDir;
mkdirSync(outDir, { recursive: true });

// ── helpers ───────────────────────────────────────────────────────────────
const readDebugLog = () => {
  try {
    return execSync(`docker exec ${inv.container} cat ${inv.debugLog}`, { encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'] });
  } catch {
    return ''; // no log file yet = empty baseline
  }
};

const settle = async (page) => {
  await page.evaluate(() => document.fonts.ready);
  // stepwise pre-scroll: trigger lazy loads deterministically, then return
  await page.evaluate(async () => {
    const step = window.innerHeight;
    for (let y = 0; y <= document.body.scrollHeight; y += step) {
      window.scrollTo(0, y);
      await new Promise((r) => setTimeout(r, 60));
    }
    window.scrollTo(0, 0);
    await new Promise((r) => setTimeout(r, 300));
  });
  await page.waitForLoadState('networkidle').catch(() => {});
  await page.waitForTimeout(500);
};

const fingerprint = async (page, pageSpec, viewport) => {
  const sentinels = {};
  for (const sel of inv.sentinels) {
    sentinels[sel] = await page.evaluate((s) => {
      const el = document.querySelector(s);
      if (!el) return { exists: false };
      const cs = getComputedStyle(el);
      return {
        exists: true,
        fontFamily: cs.fontFamily,
        fontSize: cs.fontSize,
        color: cs.color,
        backgroundColor: cs.backgroundColor,
      };
    }, sel);
  }
  const strings = {};
  for (const str of pageSpec.strings ?? []) {
    strings[str] = await page.evaluate((s) => document.body.innerText.includes(s), str);
  }
  const elementCount = await page.evaluate(() => document.querySelectorAll('*').length);
  const overflow = await page.evaluate(() => {
    const vw = document.documentElement.clientWidth;
    const offenders = [];
    for (const el of document.querySelectorAll('body *')) {
      const r = el.getBoundingClientRect();
      if (r.width > 0 && (r.right > vw + 1 || r.left < -1)) {
        const cs = getComputedStyle(el);
        if (cs.overflow === 'hidden' || cs.overflowX === 'hidden') continue;
        let p = el.parentElement, clipped = false;
        while (p && p !== document.body) {
          const pcs = getComputedStyle(p);
          if (['hidden', 'auto', 'scroll', 'clip'].includes(pcs.overflowX)) { clipped = true; break; }
          p = p.parentElement;
        }
        if (!clipped) offenders.push(`${el.tagName.toLowerCase()}.${[...el.classList].slice(0, 2).join('.')}`);
      }
    }
    return {
      docOverflow: document.documentElement.scrollWidth > vw + 1,
      offenders: [...new Set(offenders)].slice(0, 20),
    };
  });
  const cssBytes = await page.evaluate(async () => {
    let total = 0;
    const hrefs = [];
    for (const l of document.querySelectorAll('link[rel="stylesheet"]')) {
      try {
        const res = await fetch(l.href);
        const t = await res.text();
        total += t.length;
        hrefs.push(l.href.replace(/\?.*$/, ''));
      } catch { /* cross-origin fonts css etc. */ }
    }
    return { total, hrefs };
  });
  return { viewport, elementCount, sentinels, strings, overflow, cssBytes };
};

// ── capture ───────────────────────────────────────────────────────────────
const browser = await chromium.launch();
const results = {};
const consoleErrors = {};
const axeResults = {};

for (const pageSpec of pages) {
  for (const vw of viewports) {
    const key = `${pageSpec.id}-${vw}`;
    const ctx = await browser.newContext({
      viewport: { width: vw, height: 900 },
      reducedMotion: 'reduce',
    });
    const page = await ctx.newPage();
    const errs = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errs.push(msg.text()); });
    page.on('pageerror', (e) => errs.push(String(e)));

    const resp = await page.goto(inv.site + pageSpec.path, { waitUntil: 'load', timeout: 60000 });
    const status = resp?.status() ?? 0;
    const expected = pageSpec.expectStatus ?? 200;
    await settle(page);

    const fp = await fingerprint(page, pageSpec, vw);
    fp.status = status;
    fp.expectedStatus = expected;
    results[key] = fp;
    consoleErrors[key] = errs;

    await page.screenshot({ path: join(outDir, `${key}.png`), fullPage: true });

    if (vw === Math.max(...viewports)) {
      try {
        const axe = await new AxeBuilder({ page }).analyze();
        axeResults[pageSpec.id] = axe.violations.map((v) => ({ id: v.id, impact: v.impact, nodes: v.nodes.length }));
      } catch (e) {
        axeResults[pageSpec.id] = [{ id: `axe-crashed: ${e.message}`, impact: 'error', nodes: 0 }];
      }
    }
    console.log(`${mode} ${key}: HTTP ${status} · ${fp.elementCount} els · css ${(fp.cssBytes.total / 1024).toFixed(0)}kB · ${errs.length} console errs`);
    await ctx.close();
  }
}
await browser.close();

writeFileSync(join(outDir, 'fingerprints.json'), JSON.stringify(results, null, 2));
writeFileSync(join(outDir, 'console-errors.json'), JSON.stringify(consoleErrors, null, 2));
writeFileSync(join(outDir, 'axe.json'), JSON.stringify(axeResults, null, 2));
writeFileSync(join(outDir, 'debug-log.txt'), readDebugLog());

if (mode === 'before') {
  console.log(`\nBASELINE captured → ${baseDir}`);
  process.exit(0);
}

// ── compare (after mode) ──────────────────────────────────────────────────
const { default: pixelmatch } = await import('pixelmatch');
const { PNG } = await import('pngjs');

const base = {
  fingerprints: JSON.parse(readFileSync(join(baseDir, 'fingerprints.json'), 'utf8')),
  consoleErrors: JSON.parse(readFileSync(join(baseDir, 'console-errors.json'), 'utf8')),
  axe: JSON.parse(readFileSync(join(baseDir, 'axe.json'), 'utf8')),
  debugLog: readFileSync(join(baseDir, 'debug-log.txt'), 'utf8'),
};

const failures = [];

for (const pageSpec of pages) {
  for (const vw of viewports) {
    const key = `${pageSpec.id}-${vw}`;
    const b = base.fingerprints[key];
    const a = results[key];
    if (!b) { console.log(`  (no baseline for ${key} — skipped)`); continue; }

    if (a.status !== b.status) failures.push(`${key}: HTTP ${b.status} → ${a.status}`);

    for (const [sel, bs] of Object.entries(b.sentinels)) {
      const as = a.sentinels[sel];
      if (bs.exists && !as.exists) failures.push(`${key}: sentinel VANISHED: ${sel}`);
      else if (bs.exists && as.exists) {
        for (const prop of ['fontFamily', 'fontSize', 'color', 'backgroundColor']) {
          if (bs[prop] !== as[prop]) failures.push(`${key}: ${sel} ${prop}: "${bs[prop]}" → "${as[prop]}"`);
        }
      }
    }
    for (const [str, was] of Object.entries(b.strings)) {
      if (was && !a.strings[str]) failures.push(`${key}: string sentinel VANISHED: "${str}"`);
    }
    const newErrs = consoleErrors[key].filter((e) => !base.consoleErrors[key]?.includes(e));
    if (newErrs.length) failures.push(`${key}: NEW console errors: ${newErrs.slice(0, 3).join(' | ')}`);

    const newOffenders = a.overflow.offenders.filter((o) => !b.overflow.offenders.includes(o));
    if (!b.overflow.docOverflow && a.overflow.docOverflow) failures.push(`${key}: document now overflows horizontally`);
    if (newOffenders.length) failures.push(`${key}: NEW overflow offenders: ${newOffenders.join(', ')}`);

    // pixels
    const bPng = PNG.sync.read(readFileSync(join(baseDir, `${key}.png`)));
    const aPng = PNG.sync.read(readFileSync(join(afterDir, `${key}.png`)));
    if (bPng.width !== aPng.width || bPng.height !== aPng.height) {
      failures.push(`${key}: size changed ${bPng.width}×${bPng.height} → ${aPng.width}×${aPng.height}`);
    } else {
      const diff = new PNG({ width: bPng.width, height: bPng.height });
      const n = pixelmatch(bPng.data, aPng.data, diff.data, bPng.width, bPng.height, { threshold: 0.1 });
      const ratio = n / (bPng.width * bPng.height);
      if (ratio > inv.pixelThreshold) {
        const diffPath = join(afterDir, `${key}.diff.png`);
        writeFileSync(diffPath, PNG.sync.write(diff));
        failures.push(`${key}: ${(ratio * 100).toFixed(3)}% pixels differ (max ${inv.pixelThreshold * 100}%) → ${diffPath}`);
      }
    }

    const delta = a.cssBytes.total - b.cssBytes.total;
    console.log(`  ${key}: css ${(b.cssBytes.total / 1024).toFixed(0)} → ${(a.cssBytes.total / 1024).toFixed(0)}kB (${delta > 0 ? '+' : ''}${(delta / 1024).toFixed(0)}kB)`);
  }

  // axe: no NEW violations per page
  const bAxe = base.axe[pageSpec.id] ?? [];
  const aAxe = axeResults[pageSpec.id] ?? [];
  const newViolations = aAxe.filter((v) => !bAxe.some((bv) => bv.id === v.id));
  if (newViolations.length) failures.push(`${pageSpec.id}: NEW axe violations: ${newViolations.map((v) => v.id).join(', ')}`);
}

// debug.log ratchet: no new lines
const afterLog = readFileSync(join(afterDir, 'debug-log.txt'), 'utf8');
if (afterLog.length > base.debugLog.length) {
  const newLines = afterLog.slice(base.debugLog.length).trim().split('\n').slice(0, 5);
  failures.push(`debug.log GREW: ${newLines.join(' | ')}`);
}

console.log('');
if (failures.length) {
  console.error(`PARITY GATE: ${failures.length} FAILURE(S)`);
  for (const f of failures) console.error(`  ✗ ${f}`);
  process.exit(1);
}
console.log(`PARITY GATE GREEN — ${pages.length} pages × ${viewports.length} viewports`);
