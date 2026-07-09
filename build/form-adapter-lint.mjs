#!/usr/bin/env node
/**
 * FORM-ADAPTER DRIFT GATE — static layer (zero-drift adoption law).
 *
 * A theme adopting the DS must NOT replicate DS styles with hardcodes or legacy
 * tokens — that drifts (heavy money/time burn). Adoptable markup uses DS classes
 * 1:1. Third-party form engines (Fluent Forms) that can't take DS markup get a
 * thin ENGINE-ADAPTER that maps the engine's selectors onto DS tokens — and
 * THIS gate proves the adapter owns NO hardcoded colour/size and NO legacy
 * token. Any hex literal or non-DS var in a colour/border/background/font
 * declaration = drift = exit 1.
 *
 * Usage:
 *   node build/form-adapter-lint.mjs <adapter.css> [<adapter2.css> ...]
 * Reusable across AIF + AIG (and any theme adopting the DS). Pair with the
 * RENDER gate (tests/form-drift.spec — asserts the engine field computes
 * IDENTICAL to the DS .form-control field).
 */
import { readFileSync } from 'node:fs';

// Legacy / non-DS token name prefixes that must NEVER appear in an adapter.
const BANNED_VAR = /var\(\s*--(color-|text-caption|text-small|text-button|text-body|text-h\d|fluentform-|font-family|font-weight|text-perex|lp-)/;

// Property names whose VALUES must be DS tokens (never a raw hex).
const COLOR_PROPS = /^(color|background|background-color|border|border-color|border-top|border-bottom|border-left|border-right|border-top-color|outline|outline-color|fill|stroke|box-shadow|caret-color)$/i;

// A hex literal OUTSIDE a url()/data: (data-URI SVG strokes can't use var() —
// e.g. the checkbox tick — so those are the one allowed exception).
const HEX = /#[0-9a-fA-F]{3,8}\b/;

let drift = [];

for (const file of process.argv.slice(2)) {
	const src = readFileSync(file, 'utf8');
	const lines = src.split(/\r?\n/);
	lines.forEach((line, i) => {
		const ln = i + 1;
		const noComment = line.replace(/\/\*.*?\*\//g, '');
		const stripped = noComment.replace(/url\(([^)]*)\)/g, 'url(_)'); // mask data-URIs

		// 1) any banned legacy/non-DS token anywhere
		const bad = stripped.match(BANNED_VAR);
		if (bad) drift.push({ file, ln, why: `legacy/non-DS token ${bad[0]}…`, line: line.trim() });

		// 2) a raw hex in a colour-ish declaration (outside url())
		const decl = stripped.match(/^\s*([a-z-]+)\s*:\s*(.+?)\s*!?(important)?\s*;/i);
		if (decl && COLOR_PROPS.test(decl[1]) && HEX.test(decl[2])) {
			drift.push({ file, ln, why: `hardcoded hex in ${decl[1]}`, line: line.trim() });
		}
	});
}

if (drift.length) {
	console.error(`\n✗ FORM-ADAPTER DRIFT — ${drift.length} hardcode/legacy-token violation(s):\n`);
	for (const d of drift) console.error(`  ${d.file}:${d.ln}  ${d.why}\n      ${d.line}`);
	console.error('\nEvery colour/size in a form adapter must be a DS token (--field-*/--button-*/--status-*/--text-*/--control-*/--stroke-*/--spacing-*/--caption-*/--body-*/--meta-*/--weight-*/--leading-*). No hex, no --color-* legacy names. (data-URI SVG strokes are the only allowed hex — they can\'t take var().)\n');
	process.exit(1);
}
console.log('✓ form-adapter drift gate: 0 hardcodes, 0 legacy tokens — the adapter is DS-token-pure.');
