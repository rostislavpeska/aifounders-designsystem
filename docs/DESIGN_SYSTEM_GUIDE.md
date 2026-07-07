# AI Founders Design System — Architecture & Quality Guide

The authoritative architecture guide for the `aifounders-designsystem` WordPress
plugin, serving two brands: **aifounders.cz** (blue) and **aiguild.cz**
(yellow). Rewritten 2026-07-04 (deep-hygiene run) — every claim below is
verifiable in the code it points to.

---

## 1. Topology

A WordPress plugin (`aifounders-designsystem.php`, version 2.0.0-dev), currently
**Stage 1**: registered but not enqueued on the live themes. The styleguide
(`/?aifds_styleguide=1`) is the only consumer until theme adoption. The
harvested production themes (local clones in `WORKSPACE\_harvest`) are the
design source; the plugin is the canonical destination.

```
tokens/*.json ──build/build.mjs──► assets/css/tokens.css + assets/tokens-manifest.json
                                        │
assets/css/components.css  (hand-written, consumes ONLY tokens)
inc/styleguide.php         (specimens for every component)
inc/icons.php              (inline SVG icon system)
js/components/*.js         (class-toggling behavior, zero style injection)
```

Brand switching: `[data-theme='aiguild' | 'aifounders']` on `<html>` —
Tier-1 palette values swap per brand; every downstream layer is brand-blind.

## 2. The token architecture

### 2.1 Color — three layers (the 3-layer law)

| Tier | File(s) | Law |
|---|---|---|
| 1 · Palette | `tokens/palette.aifounders.json` / `palette.aiguild.json` | VALUES ONLY (hex/rgba, incl. alpha colors like `brand-tint`, `overlay-*`). Both brands define the IDENTICAL name set (62 names — parity gated). |
| 2 · Semantic | `tokens/semantic.json` | every token is a SINGLE-HOP `{palette-ref}` — no raw values, no semantic→semantic refs (48 names). |
| 3 · Scopes | `tokens/scopes/*.json` (brand, support, dark-1/2/3, light-2/3) | override EXISTING semantic names per background — scopes add no new vocabulary. |

Surface re-resolution: a scope selector (`.section-dark`, `.content-section--*`,
…) re-declares the semantic vars, so `var(--text)`, `var(--status-warning)`,
`var(--raised)` resolve correctly per surface (e.g. statuses invert to their
`*-bright` palette siblings on dark surfaces). Components never know which
surface they sit on.

### 2.2 Typography — layers (see `.claude/skills/ds-typography`)

`tokens/typography.json` (sizes · leading · flow rhythm · fonts · weights ·
transform primitives like `case-upper`, `tracking-label`) +
`tokens/type-styles.json` (18 style bundles, every prop a single-hop
`{primitive}` ref; mobile = `mobile-size` re-declared inside the bundle —
never a hand-written media query). Laws: sizes are pure (no line-height, no
media queries) · **no 300/Light, ever** · classes set type only (rhythm lives
in prose flow rules) · FLOW LAW: space before a heading ≈ 2× space after.

Responsive MECHANISM LAW (Carbon-style): **display** styles (hero, title,
heading-xl/lg) = fluid `clamp()` · **content ramp** (heading-md/sm, lead) =
one step at 768px · **reading/UI** styles = constant. Mechanisms never
diverge per brand — only values do.

### 2.3 Base, breakpoints, brand divergence

- `tokens/base.tokens.json` — spacing scale (2…120), containers, strokes
  (`stroke-1/1_5/2/3/4/6`), icon sizes, shadows, transitions, field tokens.
- `tokens/breakpoints.json` — the CLOSED SET: 600 / 768 / 1024 / 1440.
  BOUNDARY LAW: max-width side = value − 1 (599/767/1023). Enforced by lint
  LAW 4; escape hatch = same-line `bp-exception:` comment.
- `tokens/brand.*.tokens.json` — the few operator-ratified per-brand diverged
  props (e.g. `heading-md-font`: Lazzer AIG / Inter AIF). Divergences are
  data, listed in `docs/DECISIONS.md`.
- `tokens/rename-map.json` — v1→v2 name mapping, kept as the **theme-adoption
  artifact** (Phase 1 of `docs/proposals/BREAKPOINTS.md`).

### 2.4 Radius (operator ruling)

All radius tokens are retired except `--radius-full` (avatars). Components
are square-cornered by default.

## 3. Component layer

`assets/css/components.css` — one file (per-component split is Batch 1.5
roadmap, see `docs/proposals/AGENTIC-DS-RESEARCH.md`), organized in banner
sections (`/* ==== NAME ==== */`). Rules:

- Components consume ONLY `var(--token)` values. Raw px is legal solely as a
  **CALIBRATED component constant** with a justifying comment (button size
  ladder, avatar sizes, numbered-heading tile) — harvested values that sit
  deliberately off the token scales.
- Component-local knobs (`--record-columns`, `--dt-pad-x`, `--info-accent`,
  field-scale vars) are LAYOUT variables set by consumers/variants — they are
  not design tokens and live in the component CSS.
- Surface-awareness comes free from the scope system — components must not
  hardcode per-surface colors.
- The full component inventory + status lives in
  `docs/IMPLEMENTATION_STATUS.md`; per-component contracts in
  `docs/components/*.md`.

## 4. Icon system

Inline SVG via `aifds_icon()` (`inc/icons.php`). Taxonomy: **outline**
(stroke-based, Lucide + custom) · **shape** (filled) · **colored**
(multi-color art, size-locked — see `REPOSITORY_RULES.md` §6.2). Laws:
default `currentColor` (icons inherit text color, surface-aware for free);
**STEPPED visual stroke** by rendered size (operator 2026-07-04, supersedes
constant-1.5): <16px → `--stroke-1` · 16–32px → `--stroke-1_5` · >32px →
`--stroke-3`, all via `vector-effect: non-scaling-stroke` so the width is
screen px with no viewBox math (the old per-size compensation ratio tokens
were retired the same day).

## 5. Quality gates — how changes land

**THE RITUAL** (from the `ds-colors` / `ds-typography` skills — applies to
every change):

1. `node build/build.mjs` — regenerate `tokens.css` + manifest.
2. `node build/lint-css.mjs` — the four lint laws: (1) text-inversion,
   (2) no legacy names (`--color-*`, `--surface-*`, `--btn-*`,
   `--on-surface-*` fail the build), (3) no surface wildcards
   (`.section-* *` banned forever), (4) closed breakpoint set.
3. `npx playwright test tests/tokens.spec.js` — **the gate**: 52 assertions ×
   both brands against the live WP container (`localhost:8090`). It asserts
   real computed styles (colors resolve per surface, rhythm values, component
   contracts, mobile behavior).
4. Conventional commit. Push when asked.

**Never weaken a gate assertion to make it pass** — assertions change only
with an operator ruling. The gate doubles as the coordination mechanism
between parallel agent sessions.

## 6. Change workflow

1. **Harvest** — read the production theme code (`_harvest` clones); never
   invent values. External DS research (Apple/Carbon/GOV.UK) informs
   structure, not values.
2. **Propose** — non-trivial changes get a proposal in `docs/proposals/`
   (research + options + recommendation) and an operator ruling.
3. **Build** — from scratch when ordered (zero patching); through the skills
   for color/type changes.
4. **Gate + commit** — THE RITUAL above. Operator rulings land in
   `docs/DECISIONS.md`.

## 7. Roadmap pointers

- **Batch 1.5** — component-token tier + per-component CSS split:
  `docs/proposals/AGENTIC-DS-RESEARCH.md` §3.
- **Breakpoint adoption phases 1–3**: `docs/proposals/BREAKPOINTS.md`.
- **Vector DS** (Supabase pgvector retrieval index; GitHub stays canonical):
  `docs/proposals/VECTOR-DS.md`. The `docs/components/*.md` files are written
  to become its rows: the Intent section is the embedded prose; anatomy /
  variants / token names are the verbatim metadata payload.
