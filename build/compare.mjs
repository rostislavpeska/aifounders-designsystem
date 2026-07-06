/**
 * Equivalence proof: compares two tokens.css files at the PROPERTY level —
 * per scope, the exact set of custom properties and their normalized values
 * must match. Comments/order/whitespace ignored (browsers don't see them).
 *
 * Usage: node build/compare.mjs <old.css> <new.css>
 * Exit 0 = equivalent · exit 1 = differences printed
 */
import { readFileSync } from 'node:fs';

const [, , oldPath, newPath] = process.argv;

function parse(css) {
  css = css.replace(/\/\*[\s\S]*?\*\//g, ''); // strip comments BEFORE any parsing
  const scopes = {};
  const blockRe = /([^{}]+)\{([^{}]*)\}/g;
  let m;
  while ((m = blockRe.exec(css))) {
    const selector = m[1].replace(/\/\*[\s\S]*?\*\//g, '').replace(/\s+/g, ' ').trim();
    if (selector.startsWith('@font-face')) continue;
    const scope =
      selector.includes('data-theme="aifounders"') ? 'aifounders'
      : selector.includes('data-theme="aiguild"') ? 'aiguild'
      : selector === ':root' ? 'base' : selector;
    scopes[scope] = scopes[scope] || {};
    const declRe = /--([a-z0-9-]+)\s*:\s*([^;]+);/gi;
    let d;
    while ((d = declRe.exec(m[2]))) {
      const val = d[2].replace(/\/\*[\s\S]*?\*\//g, '').replace(/\s+/g, ' ').trim();
      scopes[scope]['--' + d[1]] = val;
    }
  }
  return scopes;
}

const a = parse(readFileSync(oldPath, 'utf8'));
const b = parse(readFileSync(newPath, 'utf8'));

const additiveOk = process.argv.includes('--additive-ok');
let changed = 0, removed = 0, added = 0;
for (const scope of new Set([...Object.keys(a), ...Object.keys(b)])) {
  const pa = a[scope] || {}, pb = b[scope] || {};
  for (const key of new Set([...Object.keys(pa), ...Object.keys(pb)])) {
    if (pa[key] === pb[key]) continue;
    if (!(key in pa)) { added++; continue; } // new token/scope
    const kind = key in pb ? (changed++, 'CHANGED') : (removed++, 'REMOVED');
    console.log(`${kind} [${scope}] ${key}\n  old: ${pa[key] ?? '∅'}\n  new: ${pb[key] ?? '∅'}`);
  }
}
const bad = changed + removed;
console.log(`changed: ${changed} · removed: ${removed} · added: ${added}`);
if (bad === 0 && added === 0) console.log('EQUIVALENT ✓ — identical property sets in every scope');
else if (bad === 0 && additiveOk) console.log('ADDITIVE ✓ — nothing pre-existing changed or removed');
process.exit(bad === 0 && (added === 0 || additiveOk) ? 0 : 1);
