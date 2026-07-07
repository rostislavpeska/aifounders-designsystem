#!/usr/bin/env node
/**
 * adoption-coverage — THE PROGRESS TRACKER for the theme→DS refactor.
 *
 * Answers "what is already on the DS vs still the old theme system?" from
 * GROUND TRUTH: it greps the live theme CSS for every DS ledger class root
 * (the Law-4 kill-list) and reports, per P2 sweep family, how many theme
 * rules still define those classes. 0 rules = DS-owned; >0 = old system.
 *
 * Also tracks the two burn-down meters: the compat shim (old token-name
 * reads) and the theme's legacy tokens.css (--text-h.., --font-.., --radius-..).
 *
 * Usage: node build/adoption-coverage.mjs [--theme <path-to-theme>]
 * Writes theme-parity/COVERAGE.md next to the theme and prints to stdout.
 */

import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const themeArg = (() => {
  const i = process.argv.indexOf('--theme');
  return i !== -1 ? process.argv[i + 1] : 'C:/Users/rosti/Documents/WORKSPACE/aifounders_web/wp-content/themes/aifounders';
})();
const cssDir = join(themeArg, 'assets/css');

// Component CSS files in scope (exclude foundation files — tracked separately).
const COMPONENT_FILES = [
  'components.css', 'page.css', 'main.css', 'author-forms.css',
  'fluent-forms-override.css', 'article-sticky.css', 'video-embed.css',
  'landing/newsletter.css',
];

// Sweep families → ledger class roots (Law-4 kill-list) + declared status.
// status: 'done' | 'active' | 'pending'. classes are matched as SELECTOR roots.
const SWEEPS = [
  { n: 1, name: 'buttons + badges', status: 'done',
    classes: ['btn', 'btn--', 'button-group', 'badge', 'badge--'] },
  { n: 2, name: 'text styles + prose dupes', status: 'done',
    classes: ['text--perex', 'heading-xl', 'heading-lg', 'heading-md', 'heading-sm', 'heading-xs', 'lead', 'body-lg', 'body-md', 'body-sm', 'caption', 'meta', 'numbered-headings'] },
  { n: 3, name: 'forms family', status: 'pending',
    classes: ['form-group', 'form-control', 'form-select', 'dropdown', 'selection-item', 'form-stack', 'input-pair', 'dropzone', 'segmented', 'datepicker', 'calendar-'] },
  { n: 4, name: 'cards (preview/persona/course/reference)', status: 'pending',
    classes: ['preview-card', 'persona-card', 'course-info-card', 'reference-card', 'testimonial-card', 'article--', 'article__'] },
  { n: 5, name: 'engagement + comments', status: 'pending',
    classes: ['aif-engagement', 'aif-aha', 'aif-share', 'article-comments', 'comment-author', 'comment-body', 'comment-respond', 'aif-tombstone'] },
  { n: 6, name: 'header / footer / nav', status: 'pending',
    classes: ['main-header', 'site-logo', 'nav-item', 'nav-dropdown', 'burger-toggle', 'mobile-menu-overlay', 'mobile-nav', 'reading-progress', 'footer', 'footer__', 'footer-blurb', 'breadcrumb', 'archive-pagination', 'page-numbers', 'nav-tabs', 'author-tabs'] },
  { n: 7, name: 'sticky-bar + modal', status: 'pending',
    classes: ['sticky-bar', 'lp-sticky', 'newsletter-modal', 'modal', 'modal__'] },
  { n: 8, name: 'blurb / stack-grid / benefits', status: 'pending',
    classes: ['blurb', 'dark-blurb', 'stack-grid', 'info-bar', 'cert-card', 'benefit'] },
  { n: 9, name: 'surfaces / info-box / tables / records / accordion / avatar', status: 'pending',
    classes: ['section-light', 'section-dark', 'section-brand', 'surface-support', 'content-section', 'info-box', 'data-table', 'table-scroll', 'record-list', 'record__', 'accordion', 'avatar--'] },
];

// Read theme CSS.
const files = {};
for (const f of COMPONENT_FILES) {
  const p = join(cssDir, f);
  files[f] = existsSync(p) ? readFileSync(p, 'utf8').replace(/\r\n/g, '\n') : null;
}

// Count RULE selectors defining a class, split into:
//   base       = the class is in the FIRST compound of the selector (e.g.
//                `.btn {`, `.btn--primary:hover`, `.btn.btn--link`) — the KILL
//                TARGET; must reach 0 when the family is swept.
//   contextual = the class appears only after a descendant/child combinator
//                behind ANOTHER component (`.course-info-card .btn`) — legit
//                composition that belongs to the ancestor's sweep, not a miss.
// Parses selector lists (text before each `{`), comma-splits, splits each
// selector into compounds by combinators. A signal, not an exact rule count.
function countClass(css, cls) {
  if (!css) return { base: 0, contextual: 0 };
  const clean = css.replace(/\/\*[\s\S]*?\*\//g, '');
  const esc = cls.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const inCompound = new RegExp(`\\.${esc}(?=[\\s.,:{>\\[)]|$)`);
  let base = 0, contextual = 0;
  let m; const blockRe = /([^{}]+)\{/g;
  while ((m = blockRe.exec(clean)) !== null) {
    const selectorList = m[1];
    if (selectorList.includes('@')) continue; // at-rules / media prelude
    for (const sel of selectorList.split(',')) {
      const compounds = sel.trim().split(/[\s>~+]+/).filter(Boolean);
      if (!compounds.length) continue;
      if (inCompound.test(compounds[0])) base++;
      else if (compounds.some((c) => inCompound.test(c))) contextual++;
    }
  }
  return { base, contextual };
}

function countReads(pattern) {
  let n = 0;
  for (const [name, css] of Object.entries(files)) {
    if (!css || name === 'tokens.css') continue;
    n += (css.match(pattern) || []).length;
  }
  return n;
}

// Per-sweep tally.
const rows = SWEEPS.map((s) => {
  let base = 0, contextual = 0;
  const perClass = {};
  for (const cls of s.classes) {
    let b = 0, ctx = 0;
    for (const css of Object.values(files)) { const r = countClass(css, cls); b += r.base; ctx += r.contextual; }
    if (b > 0) perClass[cls] = b;
    base += b; contextual += ctx;
  }
  return { ...s, hits: base, contextual, perClass };
});

// Foundation burn-down meters.
const shimReads = countReads(/var\(--(surface|color-[a-z-]+|btn-[a-z-]+|on-surface[a-z-]*)\b/g);
const legacyTokenReads = countReads(/var\(--(text-(h[1-6]|article|body|button|caption|description|meta|perex|small|subheadline|subtitle|title)[a-z-]*|font-family-[a-z]+|font-weight-[a-z]+|radius-[a-z]+|icon-stroke-[a-z]+|icon-base-size)\b/g);

// Enqueued CSS payload (informational — from the theme's own files, kB on disk).
const themeCssBytes = Object.entries(files)
  .filter(([, c]) => c).reduce((sum, [, c]) => sum + Buffer.byteLength(c), 0);

// ── render ──────────────────────────────────────────────────────────────
const icon = (s, hits) => {
  if (s.status === 'done') return hits === 0 ? '✅ DS-owned' : `⚠️ done but ${hits} theme rules linger`;
  if (s.status === 'active') return `🔄 in progress (${hits} theme rules left)`;
  return hits === 0 ? '⬜ pending (already clear)' : `⬜ pending — old system (${hits} theme rules)`;
};

const doneCount = rows.filter((r) => r.status === 'done').length;
const lines = [];
lines.push('# Theme→DS adoption coverage (AIF)');
lines.push('');
lines.push(`_Generated from live theme CSS by \`build/adoption-coverage.mjs\`. Ground truth: greps the theme for DS ledger classes (Law-4 kill-list). Regenerate after every sweep._`);
lines.push('');
lines.push(`**Phase progress:** P0 ✅ · P1 ✅ · P2 sweeps ${doneCount}/9 done · P3–P5 pending`);
lines.push('');
lines.push('## P2 component sweeps — what is on the DS vs the old theme system');
lines.push('');
lines.push('| # | family | status | base theme rules left (the kill target) | composition (styles DS parts in-context; not a miss) |');
lines.push('|---|---|---|---|---|');
for (const r of rows) {
  const detail = Object.keys(r.perClass).length
    ? Object.entries(r.perClass).map(([c, n]) => `\`.${c}\`×${n}`).join(', ')
    : '—';
  lines.push(`| ${r.n} | ${r.name} | ${icon(r, r.hits)} | ${detail} | ${r.contextual || '—'} |`);
}
lines.push('');
lines.push('## Foundation burn-down (must reach 0 before P5 deletes the crutches)');
lines.push('');
lines.push('| meter | reads left | retires when |');
lines.push('|---|---|---|');
lines.push(`| Compat shim (old token names \`var(--surface/--color-*/--btn-*)\`) | ${shimReads} | P5 deletes \`compat-tokens.css\` at 0 |`);
lines.push(`| Legacy theme tokens (\`--text-h*/--font-*/--radius-*\`) | ${legacyTokenReads} | theme \`tokens.css\` dies at 0 |`);
lines.push('');
lines.push(`_Theme component CSS on disk: ${(themeCssBytes / 1024).toFixed(0)} kB across ${COMPONENT_FILES.filter((f) => files[f]).length} files._`);
lines.push('');
lines.push('## Live per-element observability');
lines.push('');
lines.push('- **`theme-parity/ds-xray.js`** (+ `ds-xray.bookmarklet.txt`): paste in the console (or click the bookmarklet) on any page to overlay every component with its TRUE CSS source — 🟥 theme base (old) · 🟧 DS base + theme composition override · 🟩 fully DS — with a per-family tally panel. This is how to SEE what is migrated vs not on the real pages (the DS looks like the theme by design, so appearance alone can\'t tell you).');
lines.push('');
lines.push('## Not class-trackable (element-selector work — manual watch)');
lines.push('');
lines.push('- **Article-prose typography** (headings h1–h4, paragraphs, blockquote, lists): ✅ DONE in sweep 2 — element-scoped so the class grep can\'t show it, but verified via the parity gate (article body now fully DS; perex & blockquote byte-identical). Deep-prose exceptions kept: `pre/code`, figures, galleries, empty-paragraph reset, `.course-syllabus`.');
lines.push('- **Reading-progress shows on every page** (operator-spotted): `.reading-progress` is emitted in `header.php:157` unconditionally, and BOTH engines (theme main.js + DS menu.js) fill any bar present regardless of page type. So the homepage bar is pre-existing (0-width at scroll-top → invisible in top-of-page parity shots; fills on scroll). It violates the DS "articles-only" contract → **F1 fix: gate the header.php markup to `is_singular(\'post\')`**, scheduled in sweep 6 (header/nav) / P4 template touch-ups. Not a refactor regression.');
lines.push('');
lines.push('### Legend');
lines.push('✅ DS-owned (theme rules deleted) · 🔄 sweep in progress · ⬜ pending, still the old hardcoded theme system · ⚠️ regression (a "done" sweep left rules behind — investigate).');

const md = lines.join('\n') + '\n';
const outPath = join(themeArg, '..', '..', '..', 'theme-parity', 'COVERAGE.md');
// theme path is .../wp-content/themes/aifounders → repo root is 3 up
const repoOut = join(themeArg.replace(/[\\/]wp-content[\\/]themes[\\/][^\\/]+$/, ''), 'theme-parity', 'COVERAGE.md');
writeFileSync(repoOut, md);
console.log(md);
console.log(`→ written to ${repoOut}`);
