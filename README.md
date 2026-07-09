# AI Founders design system

A WordPress-plugin design system where **code is the source of truth** —
design tokens compile to CSS, components ship with real-markup specimens,
and a Playwright contract gate keeps every promise honest. Figma is a
projection of the system, never its canon.

Built for the AI Founders platform ([aifounders.cz](https://aifounders.cz))
and consumed by projects built on it — including
[AI Guild](https://aiguild.cz) — as **two brand themes of one system**:
`aifounders` (blue) and `aiguild` (yellow), mirror-law twins that share
every mechanism and differ only in palette values.

> **Status: release candidate (`2.0.0-rc.1`).** The full component inventory
> is distilled and gated. **AIF theme adoption (Stage 2) is underway** — the
> theme retired its 3752-line `components.css` *wholesale* for the DS plugin in
> an additive rebuild (2026-07-08) and its hand-rolled forms now render on DS
> markup (2026-07-09); the theme enqueues `aifds-components` with a graceful
> fallback when the plugin is absent. AIG adoption (Stage 3) follows. Method +
> lessons: [ADOPTION-PLAYBOOK](docs/proposals/ADOPTION-PLAYBOOK.md). See
> [docs/IMPLEMENTATION_STATUS.md](docs/IMPLEMENTATION_STATUS.md) for the live
> ledger and roadmap.

## What's inside

- **Design tokens** (`tokens/`, DTCG JSON) — a 3-layer color architecture:
  per-brand **palette** (64 names, perfect cross-brand parity) → shared
  **semantic roles** (51) → **surface scopes** (7). Components consume
  roles, never raw values; surfaces re-skin components with zero variant
  classes ("surfaces replace variants"). Typography, spacing, strokes,
  shadows, breakpoints (a closed set), and the icon system live here too.
- **Components** (`assets/css/components.css` + `inc/styleguide.php`) —
  the full inventory from text styles and forms through cards, modal,
  accordion, comments, engagement, and the site chrome (header, footer,
  sticky bar). One row per *decision*, not per CSS class — see the
  [component ledger](docs/IMPLEMENTATION_STATUS.md) and the per-component
  reference docs in [docs/components/](docs/components/).
- **The styleguide** — activate the plugin and open
  `/?aifds_styleguide=1` (admin/`WP_DEBUG`-gated). Every specimen renders
  the real markup on both brands and both color surfaces. A sandbox
  (`/?aigds_sandbox=1`) hosts full-page experiments without touching canon.
- **The gate** (`tests/tokens.spec.js` + `build/lint-css.mjs`) — 80+
  Playwright contract tests per brand (token values × surfaces × scales ×
  behaviors) plus lint-enforced CSS laws. CI runs the headless layer;
  the WordPress-bound layer runs against a local stack.
- **Provenance** — every divergence, unification, and veto is a dated
  ruling in [docs/DECISIONS.md](docs/DECISIONS.md). Values were harvested
  from rendered production reality, never invented.
- **Figma projection** (`assets/figma-map.json`) — the token layers are
  imported into Figma as variable collections (Palette · Semantic · Base ·
  Type Primitives) with the CSS surface scopes as modes; components are built
  with Auto Layout. The map is the committed import ledger (its `figma_node_id`s
  backfill into the vector rows). Manual component refinement is in progress.
  Code stays canon; Figma is the projection — see
  [FIGMA-IMPORT.md](docs/proposals/FIGMA-IMPORT.md).

## Install

Standard WordPress plugin: drop this repo into `wp-content/plugins/` (or
upload a ZIP of it) and activate. **Stage 1 is inert by design** — styles
are registered but never enqueued on theme pages, so activation changes
nothing user-visible; the plugin only renders its own styleguide routes.
Theme adoption is the explicit, separate step.

## Develop

```sh
node build/build.mjs      # tokens JSON → assets/css/tokens.css (+ manifest)
node build/lint-css.mjs   # the CSS laws (3-layer, breakpoints)
npx playwright test tests/tokens.spec.js   # the contract gate (needs the local WP stack)
```

The ritual for every change: build → lint → gate → conventional commit.
Never weaken a gate assertion to make it pass.

## The agentic layer

This system is built to be **operated and consumed by agents**:

- The per-component docs in `docs/components/` are written at
  decision-space granularity — "when do I reach for this, what's the
  contract, what breaks" — and double as retrieval rows for the vector
  index described in
  [docs/proposals/VECTOR-DS.md](docs/proposals/VECTOR-DS.md)
  (GitHub canonical → Supabase pgvector as a rebuildable router; the
  vector finds the component, the repo remains the truth).
- Architecture research and the target end-state live in
  [docs/proposals/AGENTIC-DS-RESEARCH.md](docs/proposals/AGENTIC-DS-RESEARCH.md).

## License

[MIT](LICENSE).
