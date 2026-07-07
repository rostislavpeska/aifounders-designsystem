---
name: ds-distill
description: Distill a component from the AIF/AIG production themes into the design system — harvest, map onto tokens, unify divergences, build from scratch, specimen, gate, docs. Use when the operator says "distill/harvest/port/reconstruct <component> from the theme(s)", names a production component to bring into the DS ("explore the accordion — two usages...", "the testimonial card from the homepage"), or asks to "transfer" a theme pattern. Encodes the harvest-only law, the HARDENED token law (zero hardcodes; new tokens NEVER without operator approval), the unification precedents, and THE RITUAL.
---

# DS Distill — component distillation from the production themes

Repo: `C:\Users\rosti\Documents\WORKSPACE\aig-desigsystem`. Read
`.claude/skills/ds-colors/SKILL.md` first — THE RITUAL and commit rules apply
identically. ALL token operations route through `ds-tokens` (the generic
entry — it delegates colors → ds-colors, type → ds-typography, and handles
foundations itself).

## Mission

Take ONE component that lives in the production themes and land it in the DS:
harvested (never invented), token-pure, unified across brands, specimen'd,
gate-asserted, documented as a vector-row. The proven pipeline: data-table,
record-list, table-scroll, accordion (2026-07-04).

## Sources of truth

- Theme clones: `C:\Users\rosti\Documents\WORKSPACE\_harvest\aif-theme\...\themes\aifounders`
  and `...\_harvest\aiguild\...\themes\aiguild`. Read-only.
- Live verification: the WP container on `localhost:8090` renders the AIF site
  AND the styleguide (`/?aifds_styleguide=1&theme=aiguild|aifounders`).
- Theme self-docs: each theme's `docs/landing-page-primitives.md` documents
  canonical markup — check it before reverse-engineering.
- DS vocabulary: `assets/css/tokens.css` (generated), `tokens/*.json`,
  `docs/components/` (existing rows), `docs/DECISIONS.md` (precedents).

## INPUT CONTRACT — collect before phase 1

REQUIRED (refuse to run without; ask via AskUserQuestion if missing):
1. **Component + one-line intent** — e.g. "accordion — FAQ disclosure card".
2. **Usage pointers per brand** — page URLs and/or theme locations.
   "AIF-only"/"AIG-only" is valid. No pointers = no harvest = no run.
3. **Scope line** — what's in, what's explicitly out.

OPTIONAL (defaults):
4. **Mode** — `full` (default) or `map-only` (stop after phase 3 with a report).
   AUTO-DOWNGRADE to map-only when the harvest reveals: >2 unruled
   divergences, ANY new token need, or an abstraction question.
5. **Abstraction** — `faithful` (default) or `abstract` (the record-list mode:
   N-anything, forget concrete usage). `abstract` ALWAYS pauses for an
   AskUserQuestion round on the abstraction axes before building.
6. **Variant filter** — variants to drop or queue as deprecated.
7. **Supersedes** — a proposal / tracked ledger item this resolves.

## THE TOKEN LAW (hardened, operator 2026-07-04) — read twice

1. **Hardcoded values are STRICTLY FORBIDDEN** in the new component's CSS.
   Every color, size, spacing, stroke, radius, leading, tracking, transition
   MUST be a `var(--token)`. No exceptions by default — including "it's what
   production has".
2. Map harvested values onto the EXISTING vocabulary first: type bundles
   (`--heading-*/--body-*/...`), spacing scale, strokes, `--icon-size-*`,
   transitions, palette roles. The accordion needed 0 new tokens because the
   harvest-first vocabulary already fit — expect the same; a mapping that
   "needs" many new tokens is usually a wrong mapping.
3. **A value with NO token match = FULL STOP.** Never define a new token, and
   never write the raw value, before the operator rules. Present via
   AskUserQuestion: (a) nearest existing token (state the delta), (b) proposed
   new token (name, tier, evidence — created through the `ds-tokens` router
   AFTER approval), (c) operator-approved CALIBRATED constant (raw px with a
   provenance comment — the button-ladder idiom; this too requires explicit
   approval, it is not a loophole).
4. Component-local LAYOUT knobs (`--record-columns`, `--dt-pad-*` style vars
   set/derived inside the component) are legal and are NOT design tokens —
   but their VALUES must still reference tokens.
5. Surface-awareness comes from roles (`--bg`, `--text`, `--border`,
   `--status-*`) — never hardcode per-surface colors. If the component reads
   a Tier-1 palette color directly (like the accordion's `--deep` icon), flag
   it in the doc's Surfaces section as unharvested territory on dark.
6. Media queries: ONLY the closed set (599/767/1023 max · 600/768/1024/1440
   min). A harvested off-cut breakpoint gets snapped to the nearest canonical
   cut and logged as a GM exception.

## Phases (each has an exit condition — do not proceed past a failed one)

### 1 · HARVEST (read everything, run nothing yet)
- Grep BOTH theme clones for the component: CSS blocks (all files — check
  `components.css`, `page.css`, `blog.css`, `landing/*`), markup (PHP
  templates AND the not-in-PHP cases: DB/WYSIWYG content, shortcodes,
  `landing-page-primitives.md`), JS (files AND inline `<script>` in templates
  — the accordion's AIF toggle lived inline in the rendered page, not in any
  file), enqueues in `functions.php`.
- **Live-verify on :8090**: render the real page, interact (click/scroll),
  measure computed styles with a scratch Playwright script. Code reading
  alone missed the accordion's exclusive mode.
- EXIT: you can state where every rule/behavior comes from, per brand, with
  file:line. Anything you cannot source is NOT in the component.

### 2 · MAP
- Anatomy: class map, required vs optional slots, canonical markup.
- Delta table AIF ↔ AIG (every differing property).
- Behavior modes (interaction differences count as modes, not variants).
- Token mapping per THE TOKEN LAW. Count new-token needs (target 0).
- Row granularity check: does this decompose into multiple decision-space
  rows (VECTOR-DS §4)?
- EXIT: full mapping with zero unsourced values. If mode=map-only or an
  auto-downgrade triggered → write the report (deltas, mapping, open
  questions) and STOP for operator rulings.

### 3 · RULE (divergences)
Apply STANDING PRECEDENTS automatically and log each as a dated row in
`docs/DECISIONS.md` marked as a veto point:
- **Unify to the stronger twin** (cohort-tile precedent) — pick one brand's
  refinement for both, GM exception on the other (e.g. AIG's card border +
  synced transition won for the accordion).
- **Drop Figma-era layout constants** (max-widths) — width belongs to the
  consumer.
- **Snap off-scale values** to the token scale (log GM exception).
- **Legacy/inverse variants** → keep for markup compat, mark deprecated, add
  to the rationalization queue in `IMPLEMENTATION_STATUS.md`.
NO precedent covers it → STOP and ask. New tokens are NEVER a phase-3
auto-decision (see THE TOKEN LAW).

### 4 · BUILD (from scratch — zero patching)
- `assets/css/components.css`: one banner section; the banner comment carries
  harvest provenance (theme file:line), the unifications + GM exceptions, and
  the token story ("0 new tokens" or the approved additions).
- Behavior → own file in `js/components/` (class toggles + inline layout
  metrics only, zero style injection). Port the production engine verbatim
  where one exists; generalize page-specific scoping to opt-in attributes
  (`[data-accordion="exclusive"]` precedent).
- Icons only via `aifds_icon()` (stepped-stroke + currentColor laws hold).

### 5 · SPECIMEN
- Register the slug in `aifds_styleguide_items()` + write
  `aifds_sg_item_<slug>()`. Load any new JS in the styleguide footer.
- **SPEC SHEET, NOT CONTENT INVENTORY** (preview-card lesson, 2026-07-05):
  the specimen demonstrates the COMPONENT — (1) anatomy first: a slot table
  (element / required / contract) + one full-slot card in PLACEHOLDER voice
  ("Headline slot — always present"), (2) each AXIS as a controlled toggle:
  the SAME content twice with ONE variable changing (size, a slot on/off),
  (3) consumer/content-type mappings last, as a reference table — content
  types are compositions, never the demo itself. One placeholder renderer
  closure drives all demos. Realistic editorial content belongs only in the
  mappings section, if anywhere. Surface renders (dark/brand) where the
  component claims surface-awareness.

### 6 · GATE — THE RITUAL
- Add assertions: type voices (size/weight/leading), per-brand colors,
  structural grammar (borders/fills), behavior contracts (class toggles,
  aria), mobile behavior at 390px, surface re-resolution where claimed.
- `node build/build.mjs` → `node build/lint-css.mjs` →
  `npx playwright test tests/tokens.spec.js` — ALL GREEN both brands.
  Never weaken an existing assertion.

### 7 · DOCS + COMMIT
- `docs/components/<row>.md` per the template in `docs/components/README.md`
  (Intent = embeddable prose; token NAMES only; git_path anchor; Known
  friction includes the GM exceptions and any palette-color caution).
- Ledger row in `docs/IMPLEMENTATION_STATUS.md`; ruling rows in
  `docs/DECISIONS.md`; update `Supersedes` targets (proposal status headers).
- Conventional commit (provenance-rich body), push per the session's standing
  instruction.

## Gotchas (cost real hours)

- **Patch archaeology is a COMPLEXITY SIGNAL** (persona-card lesson,
  2026-07-04): if the harvest shows min/max clamps fighting percentages,
  `aspect-ratio` combined with `align-items: stretch`, per-context re-sizing
  of the same variant, or multi-state breakpoint flips — the production
  component is a BROKEN CONSTRAINT SYSTEM, not a component. Do NOT port it
  faithfully and call it shipped; auto-downgrade to map-only, measure which
  constraint the browser breaks (probe with Playwright), inventory every
  instance + patch, research external systems, and propose a replacement
  architecture. Estimate such components at 5–6× the apparent size.

- PowerShell Get/Set-Content mangles UTF-8 (mojibake) — Edit/Write tools or
  Bash for file work; commit via Bash heredoc.
- The styleguide sidebar collapses <768px; screenshot specimens at 390px for
  mobile checks — and remember `box-sizing: content-box` is the default here
  (no global reset): full-width buttons need `border-box`.
- Grid→flex collapses on mobile: neutralize desktop grid pins
  (`align-self`/`justify-self`) explicitly.
- A specimen must never overflow the page body on mobile — the gate pattern
  `document.documentElement.scrollWidth <= clientWidth` catches it.
- Czech demo data is a smell; specimens are English (realistic content ok).
