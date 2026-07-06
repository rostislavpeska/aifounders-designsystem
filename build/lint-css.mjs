/**
 * CSS law lint — 3-layer edition (operator, 2026-07-03). Runs before every
 * test:tokens.
 *
 * LAW 1 (text inversion): text color must come from the remappable semantic
 *   vocabulary — components never set text to a PALETTE neutral directly
 *   (that's what froze dark sections before). !important rules exempt.
 * LAW 2 (no legacy names): the old vocabularies (--color-*, --surface-*,
 *   --btn-*, --on-surface-*) are dead — any occurrence is an incomplete
 *   rename and fails the build.
 * LAW 3 (no surface wildcards): .section-* * selectors stay banned forever.
 * LAW 4 (breakpoints are a CLOSED SET — operator 2026-07-03, see
 *   docs/proposals/BREAKPOINTS.md): every @media width must sit on a canonical
 *   cut from tokens/breakpoints.json (BOUNDARY LAW: max side = value-1).
 *   Escape hatch = the Apple nav rule: a same-line comment
 *   `bp-exception: <reason>` — local, declared, never a shared tier.
 */
import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const css = readFileSync(join(root, 'assets/css/components.css'), 'utf8')
  .replace(/\/\*[\s\S]*?\*\//g, (m) => m.replace(/[^\n]/g, ' ')); // blank comments, keep line numbers
const lines = css.split('\n');

const violations = [];
lines.forEach((line, i) => {
  // Law 1: LIGHT palette neutrals may not color text directly — that's the
  // freeze-that-broke-dark-sections direction. Baked-dark components pinning
  // paper/dark-* text are the established exception (they pin bg too).
  if (/(?:^|[^-])color:\s*var\(--(gray-\d+|black|white)\)/.test(line) && !line.includes('!important')) {
    violations.push(`${i + 1}: text color from a LIGHT palette neutral (use the semantic vocabulary): ${line.trim()}`);
  }
  // Law 2: dead vocabularies (color AND type migrations)
  const legacy = line.match(
    /--(?:color|surface|on-surface)-[a-z0-9-]+|--btn-[a-z0-9-]+|--text-(?:h[1-5]|title|subtitle|subheadline|perex|article|body|description|small|caption|meta|button)(?![a-z0-9-]*:)[a-z0-9-]*|--font-family-[a-z-]+|--font-weight-[a-z-]+/
  );
  if (legacy) {
    violations.push(`${i + 1}: legacy token name (dead vocabulary): ${legacy[0]}`);
  }
  // Law 3: wildcard color sledgehammers are forbidden
  if (/\.section-(dark|light|brand)\s*\*/.test(line)) {
    violations.push(`${i + 1}: wildcard section selector (deleted 2026-07-03, never again): ${line.trim()}`);
  }
});

// Law 4: the canonical cuts, straight from the token source (single source —
// a value change there retunes the lint automatically)
const bpJson = JSON.parse(readFileSync(join(root, 'tokens/breakpoints.json'), 'utf8'));
const cuts = Object.entries(bpJson).filter(([k]) => !k.startsWith('$')).map(([, v]) => parseInt(v.$value, 10));
const MIN_OK = new Set(cuts);                    // min-width: the value itself
const MAX_OK = new Set(cuts.map((c) => c - 1));  // max-width: value-1 (BOUNDARY LAW)
let exceptions = 0;
for (const file of ['components.css', 'tokens.css']) {
  const rawLines = readFileSync(join(root, 'assets/css', file), 'utf8').split('\n');
  rawLines.forEach((line, i) => {
    if (!/@media/.test(line)) return;
    if (/bp-exception:/.test(line)) { exceptions++; return; }
    for (const m of line.matchAll(/(min|max)-width:\s*([\d.]+)(px|em|rem|%)/g)) {
      const [, side, num, unit] = m;
      const ok = unit === 'px' && (side === 'min' ? MIN_OK : MAX_OK).has(parseInt(num, 10));
      if (!ok) {
        violations.push(`${file}:${i + 1}: @media width off the canonical cuts (${side}: ${cuts.join('/')}${side === 'max' ? ' minus 1' : ''}) — use one or declare /* bp-exception: reason */: ${line.trim()}`);
      }
    }
  });
}

if (violations.length) {
  console.error('CSS LAW VIOLATIONS:\n' + violations.join('\n'));
  process.exit(1);
}
console.log(`css lint: 3-layer + breakpoint laws hold ✓${exceptions ? ` (${exceptions} declared bp-exception${exceptions > 1 ? 's' : ''})` : ' (0 bp-exceptions)'}`);
