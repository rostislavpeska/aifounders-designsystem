# Agentic Design System — Research & Target Architecture

Date: 2026-07-02 · Reference clones in `dev/research/` (gitignored, ~190MB, shallow/sparse)
Sources cloned: IBM Carbon (tokens/themes/type/layout packages), GOV.UK Frontend, Style Dictionary,
DTCG spec, Storybook server renderer, Open Props, shadcn-ui (sparse), Anthropic skills.

## 1. The honest gap

What we built in Batches 0–1 is a **faithful, law-governed component inventory** — harvested
truth, brand theming, real-markup styleguide. What it is NOT yet is a **semantic system**:

| Layer | Enterprise DS (Carbon/GOV.UK class) | Our current state |
|---|---|---|
| Token tiers | primitive → semantic → **component tokens**; components never touch primitives | 2 fuzzy tiers; components consume primitives/semantics directly; NO component-token layer |
| CSS architecture | explicit layers (ITCSS: settings→tools→core→objects→components→overrides) | one flat components.css with ordered sections |
| Component definition | **machine-readable anatomy** (GOV.UK `button.yaml`: params, types, required, examples) | CSS + showcase only; anatomy lives in my head and the styleguide |
| Distribution | registry-as-data (shadcn `registry.json`) consumable by agents/CLIs | COPY_MANIFEST (files, not components) |
| Workbench | Storybook (server-rendered variant exists) | custom styleguide page (good, but bespoke) |

The four laws we established (harvest/token-fiction/real-markup/axis-only-overrides) are the
*truth discipline*. This document adds the *structure discipline*.

## 2. Extracted mechanics (paths = evidence in dev/research/)

### 2.1 Three-tier tokens — Carbon
- Tier 1 primitives: `carbon/packages/colors/src/colors.ts` (`yellow10 = '#fcf4d6'`, scales 10–100)
- Tier 2 semantic: `carbon/packages/themes/src/white.ts` (`layer01 = gray10`) — one file per theme,
  same token NAMES, different values = theme swap
- Tier 3 component: `carbon/packages/themes/src/component-tokens/button/tokens.ts`
  (`buttonPrimary = { whiteTheme: ..., g90: ... }`)
- DTCG law (spec + style-dictionary): **components reference semantic, never primitives**

### 2.2 Machine-readable component spec — GOV.UK
`govuk-frontend/.../components/button/button.yaml`: `params` (name/type/required/description each)
+ `examples` (named option sets, screenshot flags). Component folder = scss + yaml + template + js.
This is the file an AGENT reads to use a component correctly. ITCSS layer dirs:
`settings/ tools/ helpers/ core/ objects/ components/ overrides/`.

### 2.3 Token pipeline — Style Dictionary + DTCG
DTCG JSON (`$value/$type/$description`, `{alias.path}` refs; stable spec 2025.10) → style-dictionary
config → multi-output (CSS custom props, JSON, PHP-consumable flat JSON). One source, all surfaces
(web CSS, Figma variables, theme.json later). Example: `style-dictionary/examples/advanced/*/config.json`.

### 2.4 Server-rendered Storybook — the WordPressbook enabler
`storybook-server/code/renderers/server/src/render.ts`: Storybook fetches
`GET {server.url}/{story-id}?arg1=...&globals...` and injects the returned HTML. Stories are JSON
(args/argTypes drive the controls UI). **The server can be WordPress.** Our plugin already renders
real components server-side — it needs one endpoint that renders ONE component from query args.

### 2.5 Registry-as-data — shadcn pattern
Per-item JSON: name, type, files, dependencies, cssVars — consumed by CLIs/agents via MCP
("add a login form" → registry command). Sparse clone was thin here; verify exact schema against
https://ui.shadcn.com/docs/registry when we design ours. The WP flavor of this does not exist → case-study opening.

### 2.6 Utility patterns — Open Props
`:where(html)` zero-specificity token host; numeric scales; `--size-fluid-*: clamp(...)` = the
Apple-grade spacing dynamics we already planned for v2.1 semantic spacing.

## 3. Target architecture — "WordPressbook"

```
aig-design-system/
├── tokens/                          # SOURCE OF TRUTH (DTCG JSON)
│   ├── primitives.tokens.json      #   tier 1: scales (color ramps, space, type, radius…)
│   ├── brand.aiguild.tokens.json   #   tier 2: semantic per brand (same names, diff values)
│   ├── brand.aifounders.tokens.json
│   └── components/*.tokens.json    #   tier 3: --btn-*, --badge-*… referencing tier 2 ONLY
├── build/ (style-dictionary, Node)  → assets/css/tokens.css (+ figma sync + registry JSON)
├── assets/css/
│   ├── normalize.css               # layer: generic
│   ├── tokens.css                  # GENERATED — never hand-edited after migration
│   ├── core.css                    # elements/prose defaults (main h*, p, lists, links)
│   ├── objects.css                 # layout primitives (container, sections, grids, button-group)
│   ├── components/*.css            # one file per component, consuming ONLY component tokens
│   └── vendor/*.css                # FluentForms, n8n-chat, cookies
├── components/                      # component = folder (GOV.UK anatomy)
│   └── button/
│       ├── button.css
│       ├── button.yaml             # params/axes/types/required/examples — THE agent contract
│       ├── button.php              # render function (real markup, one source)
│       └── stories.json            # generated from yaml examples → Storybook server stories
├── inc/
│   ├── registry.php                # REST: GET /aigds/v1/components, /components/{name}, /tokens
│   └── preview.php                 # REST/route: render ONE component from query args (Storybook target)
└── docs/DESIGN_SYSTEM.md
```

**The agentic loop this enables:** agent reads `button.yaml` + token JSON via REST → composes valid
markup/shortcode → previews via the render endpoint → screenshots → self-verifies. Storybook
(`@storybook/server`) is a thin consumer of the same two endpoints = **WordPressbook**, and the
approach is WP-agnostic (any server-rendered CMS).

## 3.5 Surface layer — Carbon's layering model, our friction killer (operator-approved 2026-07-02)

Carbon's Layer mechanism (layer01/02/03 remapped per nesting context) generalizes to our surfaces:
a **surface-alias tier (2.5)** between semantic and component tokens. Sections (.section-light/dark/brand)
become token-remapping scopes redefining role aliases (--on-surface-accent, --surface-text,
--surface-border, …); component tokens consume ROLES, never colors. Custom-property inheritance does
the cascade — no wildcards, no !important.

Consequences: `.btn--primary` on `.section-brand` automatically renders dark (the AIF newsletter CTA
stops being a class — it's what primary MEANS on the brand surface); the `-inverted` class family and
the `.section-dark *` wildcard become unnecessary; GM-exception bookkeeping shrinks to genuine design
deviations (e.g. AIG footer's subtle-filled button), which remain thin token overrides per the clean rule.
Migration: alias values chosen to reproduce harvested rendering exactly; -inverted kept as deprecated
aliases until adoption proves equivalence. Styleguide gains a "Surface matrix" item (same markup ×
3 surfaces, VRT-covered).

## 3.6 Surface census results (2026-07-02, project-wide sweep of both themes)

**57 distinct surface contexts** found (38 section-level + 19 micro-surfaces: cards, bars, form
wrappers). Proposed scale — 8 named surfaces mapped to existing bg tokens:

| Surface | Backing token | Examples |
|---|---|---|
| light-1 | bg-primary | body, .section-light, .content-section |
| light-2 | bg-secondary | --secondary sections, form wrappers, AIG cohort rows |
| light-3 | bg-tertiary | --tertiary sections (AIF), hovers |
| dark-1 | bg-inverse-primary | .section-dark, footer, hero-card, --dark sections |
| dark-2 | bg-inverse-secondary | --dark-secondary, footer newsletter, dark-blurb--secondary |
| dark-3 | bg-inverse-tertiary | --dark-tertiary, persona-card, AIF cert/cohort cards |
| brand | primary-brand | heroes, article-hero |
| support | primary-support | smart-btn, promo/product cards |

**9 remap ROLES** the alias tier must carry (harvested from ~25–30 manual child remaps per dark
family): surface · surface-text · surface-text-secondary · surface-border · surface-link ·
surface-link-hover · surface-bullet · surface-accent (what "primary" means here) · surface-action-hover.

**Nesting reality:** dark card ON brand hero (.hero-card) and cards inside dark sections exist →
aliases must re-resolve at each surface boundary (custom-property inheritance gives this for free).
Light-inside-dark re-nesting not found → prohibited by policy, not machinery.

**Oddballs for operator decisions:** hardcoded #FFFFFF in modal/forms (HIGH — blocks token control),
modal overlay rgba(0,0,0,.6) vs AIF rgba(7,7,8,.7) (needs --overlay token), AIF cohort blue #98C3D9,
AIF #E0B000 label, AIG #63531B header hover, quaternary-alpha rgba(160,201,47,.15).
Cross-theme: cohort cards/cert-card use OPPOSITE backgrounds per theme (light-2 AIG vs dark-3 AIF) —
likely intentional per page context; decide at component migration, not in the tier.

⚠ Census values are directional — per the harvest procedure, every alias value gets re-verified
against rendered rules at token-design time (agent sweeps have mislabeled single values before).

## 4. Migration from current state (non-destructive, harvest continues)

**Batch 1.5 — foundation refactor (before more component batches; every later batch multiplies rework):**
1. Tokens → DTCG JSON three-tier source; style-dictionary build emits tokens.css byte-equivalent
   to today's (verifiable!); THEN introduce tier-3 component tokens for Batch-1 components
   (btn/badge/avatar/icons) and repoint their CSS. Newsletter CTAs become pure token remaps —
   validating the architecture on day one.
2. Split components.css → core/objects/components-per-file with explicit ordering (ITCSS-lite;
   evaluate CSS @layer once adoption pixel-tests exist).
3. `button/button.yaml` as the metadata pilot + registry.php returning it.
4. preview endpoint + minimal @storybook/server setup in dev/ consuming it (WordPressbook v0).

**Then Batch 2+ (header/footer/sections/cards…) harvests directly INTO this structure** — same
laws, same styleguide gate, but each component lands as folder + yaml + tokens, not CSS section.

## 5. Case-study framing

Chapters this research adds: "Why a component list is not a design system", "Three-tier tokens on
WordPress via DTCG + Style Dictionary", "GOV.UK yaml → the component contract agents actually need",
"WordPressbook: Storybook for server-rendered WordPress", "A shadcn-style registry for WP".
