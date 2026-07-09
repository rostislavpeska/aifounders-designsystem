---
name: ds-figma-import
description: Run the AIF/AIG design-system → Figma import (roadmap item 2). Use when the operator asks to import/build/continue the design system in Figma — variables, text styles, icons, or component sets. Encodes OUR manifest→collection mapping, scope ruling, and session protocol; delegates all Plugin-API mechanics to the bundled figma-use + figma-generate-library skills.
---

# DS → Figma import

You are importing an EXISTING, complete CSS design system into Figma. The
code is the source of truth; Figma receives a faithful port. Never invent
tokens, values, or variants — everything comes from the repo.

## Non-negotiable preconditions

1. Load the bundled **figma-use** skill BEFORE every `use_figma` call
   (hard prerequisite), and **figma-generate-library** for the build order
   and quality bars. Use **figma-create-new-file** before `create_new_file`
   and **figma-code-connect** for mappings.
2. Read **docs/proposals/FIGMA-IMPORT.md** — it is the architecture canon:
   §2 mapping table, §3 scope ruling, §4 THE AUTO-LAYOUT LAWS, §5 typography
   policy, §6 tier order, §7 session protocol, §8 Code Connect. This skill
   does not repeat it; it binds you to it.
3. `whoami` first. Work only in the **"My Projects"** team (Professional,
   Full seat). Professional = **max 10 modes per collection** (the Oct-2025
   Schema-2025 raise, 4→10; the old "4" cap in earlier notes is stale) — the
   architecture uses 8 (the Semantic scopes); never call `addMode` past 10.
4. Source data: `assets/tokens-manifest.json` (palette / semantic+`ref` /
   scopes / base / typePrimitives / typeStyles / brandType), icon SVGs from
   `inc/icons.php`, component contracts from `docs/components/*.md`, live
   specimens at `http://localhost:8090/?aifds_styleguide=1&item=<tab>&theme=<brand>`.

## The five fixed decisions (operator-ratified 2026-07-07 — do not re-derive)

1. **Collections:** Palette (COLOR+brandType, modes AIF·AIG, scopes `[]`,
   hidden) · Semantic (COLOR, modes = **all 8 CSS scopes**: Light-1 ·
   Light-2 · Light-3 · Dark-1 · Dark-2 · Dark-3 · Brand · Support — the
   exact CSS-class mirror; every value a VARIABLE_ALIAS into Palette via the
   manifest `ref`) · Base (single mode) · Type Primitives (modes
   **Desktop·Mobile** — the clamp() answer: 96/80/64 @1440 vs 48/40/36 @390).
2. **One axis per collection, never duplicated:** brand lives ONLY in
   Palette, scope ONLY in Semantic, breakpoint ONLY in Type Primitives —
   Figma cannot link same-named modes across collections.
3. **codeSyntax WEB = the exact CSS custom property** from the manifest,
   `var(--…)` wrapper included. Never derive from the Figma name.
4. **Staged tiers, ratified:** Session 1 = variables + text styles +
   Foundations pages + Icons (117, bulk `createNodeFromSvg`) + Tier-0 atoms;
   operator reviews in Figma; molecules/organisms in follow-up sessions
   (order in FIGMA-IMPORT.md §6). ≤30 variants per set; icons via
   INSTANCE_SWAP; `_`-prefix internals.
5. **Brand × theme never multiply components:** one component, modes do the
   work (`setExplicitVariableModeForCollection` per collection on demo
   frames/variants). Code Connect is plan-gated (Org/Enterprise) — backfill
   `figma_node_id` from `assets/figma-map.json` only; leave `code_connect`
   null with reason.

## Session protocol

- Tag every created node: `setSharedPluginData('dsb','key',<stable-key>)` +
  `'run_id'`. Check-before-create by key — every step idempotent.
- Maintain **`assets/figma-map.json`** (committed): file key, collection/
  variable ids, component-set node ids keyed by ds-row name. It is the
  resume ledger AND the `build/rows.mjs` backfill source for
  `figma_node_id`/`code_connect`.
- Every `use_figma` script returns ALL created/mutated node IDs; one
  `setCurrentPageAsync` per call; `Promise.all` independent awaits;
  `loadFontAsync` before any text.
- Validate each component with `get_screenshot` against the styleguide
  specimen (both brands). On failure: STOP → inspect → fix → re-run.
- After a tier lands: update figma-map.json, run `node build/rows.mjs`,
  commit (conventional message), report to the operator with screenshots.

## Verification gates

The repo ritual still applies to repo-side edits (rows.mjs changes etc.):
`node build/build.mjs` → `node build/lint-css.mjs` → `npx playwright test`.
Figma-side checks: variable count parity (64+8 / 51 / 59 / 42), alias
integrity (zero raw-hex semantic values), swatch boards rendered per mode,
component screenshots ≈ specimens.
