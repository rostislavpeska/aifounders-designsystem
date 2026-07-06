/**
 * ONE-TIME migration: parse assets/css/tokens.css into DTCG token JSON.
 * Scope-faithful lift-and-shift (Batch 1.5 step 1): shared :root → base,
 * [data-theme] blocks → brand files. `var(--x)` values become DTCG aliases
 * `{x}`; values with EMBEDDED var() (e.g. `500ms var(--ease-apple)`) keep the
 * literal var() string (passed through by the emitter; alias graph completed
 * at the tiering step). Comments are not migrated — laws live in docs/.
 *
 * Usage: node build/migrate.mjs   (writes tokens/*.tokens.json)
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
// Strip ALL comments up front — declarations must never be parsed out of comment text.
const css = readFileSync(join(root, 'assets/css/tokens.css'), 'utf8').replace(/\/\*[\s\S]*?\*\//g, '');

// Split into scope blocks: selector { ... } at top level (media queries absent in tokens.css)
const blocks = [];
const blockRe = /([^{}]+)\{([^{}]*)\}/g;
let m;
while ((m = blockRe.exec(css))) {
  const selector = m[1].replace(/\/\*[\s\S]*?\*\//g, '').trim();
  if (selector.startsWith('@font-face')) continue; // moves to fonts.css
  blocks.push({ selector, body: m[2] });
}

function scopeOf(selector) {
  if (selector.includes('data-theme="aifounders"')) return 'aifounders';
  if (selector.includes('data-theme="aiguild"')) return 'aiguild';
  if (selector === ':root') return 'base';
  return null;
}

function inferType(value) {
  if (/^#([0-9a-f]{3,8})$/i.test(value) || /^rgba?\(/.test(value)) return 'color';
  if (/^-?[\d.]+(px|rem|em|ch|%)$/.test(value)) return 'dimension';
  if (/^[\d.]+m?s$/.test(value)) return 'duration';
  if (/^cubic-bezier\(/.test(value)) return 'cubicBezier';
  if (/^[\d.]+$/.test(value)) return 'number';
  return 'string'; // font stacks, clamp(), shadows, composite transitions
}

const out = { base: {}, aiguild: {}, aifounders: {} };

for (const { selector, body } of blocks) {
  const scope = scopeOf(selector);
  if (!scope) continue;
  const declRe = /--([a-z0-9-]+)\s*:\s*([^;]+);/gi;
  let d;
  while ((d = declRe.exec(body))) {
    const name = d[1];
    let value = d[2].replace(/\/\*[\s\S]*?\*\//g, '').trim();

    // Pure single-var alias → DTCG reference (flat path = full var name)
    const pure = value.match(/^var\(--([a-z0-9-]+)\)$/i);
    const token = { $type: inferType(value), $value: value };
    if (pure) {
      token.$value = `{${pure[1]}}`;
      delete token.$type; // inherit from referenced token
    }

    // FLAT structure: key = full var name. Collision-proof by construction
    // (dash-split nesting broke on prefix collisions like size vs size-mobile).
    out[scope][name] = token;
  }
}

mkdirSync(join(root, 'tokens'), { recursive: true });
writeFileSync(join(root, 'tokens/base.tokens.json'), JSON.stringify(out.base, null, 2) + '\n');
writeFileSync(join(root, 'tokens/brand.aiguild.tokens.json'), JSON.stringify(out.aiguild, null, 2) + '\n');
writeFileSync(join(root, 'tokens/brand.aifounders.tokens.json'), JSON.stringify(out.aifounders, null, 2) + '\n');

const count = (o) => JSON.stringify(o).match(/\$value/g)?.length ?? 0;
console.log(`base: ${count(out.base)} tokens · aiguild: ${count(out.aiguild)} · aifounders: ${count(out.aifounders)}`);
