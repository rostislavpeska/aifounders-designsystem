/**
 * ROWS GENERATOR — the VECTOR-DS §7 rows, generated (never authored).
 * Audit Phase C ruling (2026-07-06): NO .ds.yaml — the ledger
 * (docs/IMPLEMENTATION_STATUS.md) + the per-component reference docs
 * (docs/components/*.md) are the single source; this script derives the
 * retrieval rows from them. One source, no drift: if a row is wrong,
 * fix the doc, never the row.
 *
 * Output: assets/ds-rows.json — one entry per ledger component row.
 *   content   — the embedded prose (intent-first: what/when/contract)
 *   metadata  — name, git_path, git_ref (HEAD), classes, specimen,
 *               doc_path, status, token_refs (NAMES only — values ship
 *               whole in tokens.css per §5), type, visibility
 *
 * The Supabase ingest (roadmap item 4) posts these rows to the n8n
 * vector-ingest webhook; `git_ref` drives changed-row detection.
 */

import { readFileSync, writeFileSync } from 'node:fs';
import { execSync } from 'node:child_process';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const led = readFileSync(join(root, 'docs/IMPLEMENTATION_STATUS.md'), 'utf8');
const gitRef = execSync('git rev-parse --short HEAD', { cwd: root }).toString().trim();

// ── Figma backfill (roadmap item 2): assets/figma-map.json is the committed
// import ledger — components are keyed by ds-row name; figma_node_id becomes
// "<fileKey>:<nodeId>". code_connect stays null (deferred: figma plan —
// Code Connect requires Org/Enterprise, we are on Professional).
let figmaMap = null;
try {
  figmaMap = JSON.parse(readFileSync(join(root, 'assets/figma-map.json'), 'utf8'));
} catch {
  /* no Figma import yet — every figma_node_id stays null */
}
const figmaNodeId = (name) => {
  const node =
    figmaMap?.components?.[name] ??
    figmaMap?.componentsS2?.[name] ??
    figmaMap?.componentsS3?.[name] ??
    figmaMap?.componentsS4?.[name];
  return node ? `${figmaMap.fileKey}:${node}` : null;
};

// ── parse the ledger component table ────────────────────────────────────
const start = led.indexOf('## Components & patterns');
const end = led.indexOf('\n## ', start + 10);
const tableBlock = led.slice(start, end === -1 ? undefined : end);
const rows = [];
for (const line of tableBlock.split('\n')) {
  const m = line.match(/^\| (.+?) \| (.+?) \| (.+?) \| (.+?) \| (.+?) \|$/);
  if (!m || m[1] === 'Row' || m[1].startsWith('---')) continue;
  const [, name, classes, specimen, docCell, status] = m.map((s) => s.trim());
  const docMatch = docCell.match(/\(([^)]+\.md)\)/);
  rows.push({
    name,
    classes,
    specimen: specimen.replace(/`/g, ''),
    doc: docMatch ? docMatch[1].replace('components/', '') : null,
    status,
  });
}

// ── per-row: derive content + metadata from the reference doc ──────────
const section = (doc, title) => {
  // no `m` flag: `$` must mean end-of-STRING, not end-of-line
  const re = new RegExp(`\\n## ${title}[^\\n]*\\n([\\s\\S]*?)(?=\\n## |$)`);
  const m = doc.match(re);
  return m ? m[1].trim() : '';
};
const clean = (s) =>
  s
    .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1') // strip links
    .replace(/[*_`>#]/g, '')
    .replace(/\s+/g, ' ')
    .trim();

const out = [];
const gaps = [];
for (const r of rows) {
  if (!r.doc) {
    gaps.push(`${r.name}: no doc link in the ledger row`);
    continue;
  }
  let doc;
  try {
    doc = readFileSync(join(root, 'docs/components', r.doc), 'utf8');
  } catch {
    gaps.push(`${r.name}: doc ${r.doc} missing on disk`);
    continue;
  }
  const header = doc.split('\n').find((l) => l.includes('git_path:')) || '';
  const gitPath = (header.match(/git_path:\*{0,2}\s*`([^`]+)`/) || [])[1] || null;
  const intent = clean(section(doc, 'Intent'));
  const variants = clean(section(doc, 'Variants'));
  const tokensSec = section(doc, 'Tokens referenced');
  const tokenRefs = [...new Set(tokensSec.match(/--[\w-]+/g) || [])];

  if (!intent) gaps.push(`${r.name}: doc ${r.doc} has no ## Intent`);
  if (!tokenRefs.length) gaps.push(`${r.name}: doc ${r.doc} has no ## Tokens referenced`);

  // content = intent-first prose; variants appended when present.
  // Written for RECALL ("when do I reach for this"), per §7 discipline.
  const content =
    `${r.name} — ${intent}` + (variants && variants !== 'None beyond' ? ` Variants: ${variants}` : '');

  out.push({
    content: content.slice(0, 2000),
    metadata: {
      name: r.name,
      git_path: gitPath,
      git_ref: gitRef,
      classes: r.classes.replace(/`/g, ''),
      specimen: `/?aifds_styleguide=1&item=${r.specimen}`,
      doc_path: `docs/components/${r.doc}`,
      status: r.status.split('(')[0].trim(),
      token_refs: tokenRefs,
      figma_node_id: figmaNodeId(r.name), // from assets/figma-map.json (Figma import ledger)
      code_connect: null, // deferred: figma plan (Org/Enterprise-only feature)
      type: 'component',
      visibility: 'public',
    },
  });
}

writeFileSync(join(root, 'assets/ds-rows.json'), JSON.stringify(out, null, 1) + '\n');
console.log(`ds-rows.json written: ${out.length} rows @ ${gitRef}`);
if (gaps.length) {
  console.log(`GAPS (${gaps.length}):`);
  for (const g of gaps) console.log('  -', g);
  process.exitCode = 1;
} else {
  console.log('gaps: none — every ledger row generated a complete retrieval row');
}
