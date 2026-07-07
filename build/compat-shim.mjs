#!/usr/bin/env node
/**
 * compat-shim.mjs — generate the TOKEN COMPAT SHIM (THEME-REFACTOR-SPEC Law 2).
 *
 * Emits `compat-tokens.css`: every OLD token name from tokens/rename-map.json
 * aliased to its NEW name (`--old: var(--new)`), declared on :root AND on
 * every DS scope selector. The scope-level re-declaration is load-bearing:
 * custom properties inherit as COMPUTED values, so an alias declared only on
 * :root would freeze the :root value and never re-capture scoped overrides
 * (.section-dark { --bg: … } would not reach var(--surface)).
 *
 * Scope selectors are PARSED from the built assets/css/tokens.css (any rule
 * that redefines a semantic name the map points at), so scope changes in the
 * DS propagate on regeneration.
 *
 * The shim is a TEMPORARY crutch: each P2 sweep re-points theme rules to real
 * DS names; P5 deletes the file when its read-count hits 0.
 *
 * Usage: node build/compat-shim.mjs <output-path>
 */

import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');

const out = process.argv[2];
if (!out) {
  console.error('usage: node build/compat-shim.mjs <output-path>');
  process.exit(1);
}

const map = JSON.parse(readFileSync(resolve(ROOT, 'tokens/rename-map.json'), 'utf8'));

const entries = Object.entries(map).filter(([oldName, newName]) => {
  if (oldName === newName) return false; // identity (icon-smart-accent) — aliasing would self-reference
  return true;
});

// Safety: an OLD key that is also a NEW value would shadow a real DS token.
const newNames = new Set(Object.values(map));
const collisions = entries.filter(([oldName]) => newNames.has(oldName));
if (collisions.length) {
  console.error('FATAL: rename-map old keys collide with emitted DS names:', collisions.map(([o]) => o).join(', '));
  process.exit(1);
}

// Parse scope selectors from the built tokens.css: any rule block that
// redefines at least one NEW semantic name gets the aliases too.
const tokensCss = readFileSync(resolve(ROOT, 'assets/css/tokens.css'), 'utf8')
  .replace(/\r\n/g, '\n')
  .replace(/\/\*[\s\S]*?\*\//g, ' '); // strip comments — they'd leak into selector lists
const scopeSelectors = new Set();
const blockRe = /([^{}]+)\{([^{}]*)\}/g;
let m;
while ((m = blockRe.exec(tokensCss)) !== null) {
  const selector = m[1].trim();
  const body = m[2];
  if (selector.startsWith('@') || selector.includes('@media')) continue;
  const definesSemantic = [...newNames].some((n) => new RegExp(`--${n}\\s*:`).test(body));
  if (!definesSemantic) continue;
  selector
    .split(',')
    .map((s) => s.trim())
    .filter((s) => s && s !== ':root')
    .forEach((s) => scopeSelectors.add(s));
}

const selectorList = [':root', ...scopeSelectors].join(',\n');
const aliasLines = entries
  .map(([oldName, newName]) => `  --${oldName}: var(--${newName});`)
  .join('\n');

const css = `/* ═══════════════════════════════════════════════════════════════════════
   TOKEN COMPAT SHIM — GENERATED, DO NOT EDIT
   Source: aig-desigsystem tokens/rename-map.json (${entries.length} renames)
   Generator: build/compat-shim.mjs · THEME-REFACTOR-SPEC Law 2
   TEMPORARY: each P2 sweep re-points theme rules to the real DS names;
   this file is DELETED at P5 when \`grep -c 'var(--old' theme-css\` = 0.
   Aliases are re-declared inside every DS scope selector so scoped values
   re-capture (custom properties inherit as computed values).
   ═══════════════════════════════════════════════════════════════════════ */

${selectorList} {
${aliasLines}
}
`;

writeFileSync(out, css);
console.log(`compat shim: ${entries.length} aliases × ${scopeSelectors.size + 1} scope selectors → ${out}`);
