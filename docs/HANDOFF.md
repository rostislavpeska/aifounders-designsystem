# HANDOFF BRIEF — agentic design system (2026-07-03 · hygiene addendum 2026-07-04)

**2026-07-04 milestone — deep-hygiene run:** full audit (token graph + CSS +
PHP/JS + docs) came back law-clean; legacy `icon-stroke-*` compensation tokens
retired (superseded by `--stroke-1_5` + `non-scaling-stroke`); mono-label
tracking tokenized (`--tracking-label`); stale docs killed (TOKEN_INDEX,
footer-fix) and `REPOSITORY_RULES.md` / `DESIGN_SYSTEM_GUIDE.md` rewritten to
current reality; `docs/IMPLEMENTATION_STATUS.md` is now the component ledger;
**per-component contracts live in `docs/components/*.md`** (written as the
future VECTOR-DS rows). Also new since 2026-07-03: data tables, record list
(abstract N-column, subgrid), brand-tint alpha tokens, styleguide sidebar
collapses on mobile. Gate is 52/52.

For the next agent continuing this work on another device. Everything below
is pushed to `github.com/rostislavpeska/aig-desigsystem` (main). The
operator's session memory does NOT travel — this file is your context.

## What this is

A WordPress-plugin design system (`aig-design-system`) serving TWO brands:
**aifounders.cz** (AIF, blue) and **aiguild.cz** (AIG, yellow). Built as a
public case study. Canonical = this git repo. The themes adopt it later
(rollout: DS standalone → AIF adoption branch w/ pixel golden-masters →
AIG → prod). Operating principle after the operator's de-enterprising
ruling: **deliberately small and transparent — every layer readable in one
sitting; machinery only where hand-maintenance provably breaks.**

## READ FIRST — the operating manuals

- `.claude/skills/ds-tokens/SKILL.md` — **THE GENERIC ENTRY for any token
  operation** (nested-skill architecture, operator 2026-07-04): routes color
  work → ds-colors, type work → ds-typography, and handles all FOUNDATION
  tokens (spacing, containers, strokes, shadows, icon sizes, transitions,
  field tokens, breakpoints) itself. When unsure, start here.
  - `.claude/skills/ds-colors/SKILL.md` — the color specialist: 3-layer
    architecture, THE LAWS, THE RITUAL (build → gate → never weaken
    assertions → commit), gotchas.
  - `.claude/skills/ds-typography/SKILL.md` — the type specialist: layers +
    MECHANISM LAW.
- `.claude/skills/ds-distill/SKILL.md` — component distillation from the
  production themes (harvest → map → rule → build → specimen → gate → docs);
  the HARDENED token law (zero hardcodes; new tokens never without operator
  approval). Use it for every remaining component harvest.

## Environment you need

- The live local WP stack: **docker container `aifounders-wp` on
  `localhost:8090`** (runs the styleguide for BOTH brands via
  `?theme=aiguild|aifounders`). Without it the gate cannot run — do not
  ship token changes without the gate.
- Styleguide: `http://localhost:8090/design-system/` (brand toggle top-right).
- Theme repos (read-only harvest sources) live in WSL:
  `\\wsl$\Ubuntu-24.04\home\rostislavpeska\projects\{aifounders,aiguild,aiguild-blue}`.
- Commands: `node build/build.mjs` (regenerate tokens.css + manifest) ·
  `npm run test:tokens` (lint + 40-assertion Playwright gate, both brands).

## Architecture (all generated from `tokens/*.json` by `build/build.mjs`)

**Colors — 3 layers:**
1. `palette.{aiguild,aifounders}.json` — 61 NAMED colors; values live ONLY here.
2. `semantic.json` — ONE vocabulary (45 tokens: text, border, button-bg,
   field-bg…), each a single-hop `{palette-name}` ref. One file, both brands.
3. `scopes/*.json` — per-background DELTAS re-declaring the SAME names
   (dark-1/2/3, brand, support, light-2/3). NO `-dark` suffixed names, ever.

**Typography — operator's six layers:**
1-3. `typography.json` — pure sizes (harvested list + fluid clamps),
   leading, flow (rhythm), fonts (display=Lazzer, primary=Inter,
   accent=Space Grotesk, mono), weights (NO 300), `case-upper`.
5. `type-styles.json` — 19 style bundles with `$evidence` harvest records.
4. mobile — `mobile-size` inside a bundle; ONE media block, emitted AFTER
   the brand blocks (cascade order matters!).
6. transforms — article context (heading slots step one style down),
   7 brand-diverged props in `brand.*.tokens.json`, decorations, emphasis.

**Key laws** (all machine-enforced by `build/lint-css.mjs` + the gate):
- Growth: new element → 0 tokens · new background → 1 scope file · new
  token/palette name/style → operator sign-off.
- MECHANISM LAW: responsive behavior by style CLASS — display styles FLUID
  (clamp) · content ramp (heading-md/sm/lead) ONE step at 768 · rest
  CONSTANT. Mechanisms never diverge per brand, only values.
- FLOW LAW: space before a heading ≈ 2× after (48/24, 40/24, 32/16);
  paragraph 24; first heading resets 0.
- Styleguide-is-data: pages generated from the manifest; specimens NEVER
  capped/faked; specimen documents use realistic harvested shapes.
- Harvest before values: rulings come from measured live pages
  (Playwright computed styles), never from memory or taste.

## State as of f1ba2a6 (all pushed, gate 40/40 both brands)

DONE: 3-layer color migration · palette page (8 groups, AIG|AIF
side-by-side, intent column) · one Colors tab (palette / tokens with
transforms-on column / transforms MATRIX full-bleed) · typography
migration (six layers, 19 evidence-backed styles, fiction styles
subheadline/lead-quote/quote deleted, hero+title real clamps, eyebrow
added with case-upper) · FLOW law + real-article rhythm specimen ·
mobile ramp law (strict decrease, gate-pinned) · Style Dictionary
REMOVED (build.mjs is the whole build, ~150 readable lines) ·
`rename-map.json` kept = adoption mapping for old theme var names ·
ds-colors + ds-typography skills.

## PENDING — operator verdicts (do not decide alone)

Both former pending items RULED 2026-07-03:
1. **`subtitle` style** — RETIRED (zero live usage; bundle + `.subtitle` class removed).
2. **Decisions tab** — RETIRED (content → `docs/DECISIONS.md`).

## Roadmap (operator-ratified order)

1. Surface-usage measurement on the live sites → possible scope collapse
   (8 scopes may become ~5). Measure first, never collapse blind.
2. Batch 2 component harvest (header/footer/sections/cards — incl. the
   32px card-title voices logged in the styles-table note; article-card
   consumes heading-sm/caption/body-md).
3. AIF adoption branch: pixel-identical golden masters, mobile VRT;
   `rename-map.json` maps the theme's legacy vars; six legacy AIG theme
   aliases (--color-primary/-text/-bg/…) need a compat block or theme
   rename; AIF theme carries warm #fffdf6 + the live stray SG-18-w300 —
   both corrected in the DS, land at adoption.
4. PARKED by de-enterprising (do NOT build): WordPressbook/@storybook/
   server/REST registry · ITCSS file split · fluid clamp spacing v2.1.

## Gotchas that cost real hours (details in the skills)

- Windows bash heredocs turn `\b` into a literal BACKSPACE (0x08) in
  files → ghost failures. Write regexes with the Edit tool; `od -c` to check.
- Custom-prop computed values: Chromium returns strings like
  `calc(1.41px)` / clamp text — regex-parse before comparing.
- Media/mobile blocks must be emitted AFTER the `[data-theme]` brand
  blocks (equal specificity — source order decides).
- Python slice-edits in build.mjs can swallow adjacent emission lines —
  the gate catches it (that's its job); never skip the gate.
- The operator authorized `git push origin main` for THIS arc; keep
  pushing after each green-gate commit so devices stay in sync.
