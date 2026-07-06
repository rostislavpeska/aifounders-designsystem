# BREAKPOINTS — harvest, research, proposal

**Status: RATIFIED 2026-07-03 — Phase 0 implemented** (`tokens/breakpoints.json`,
build emission, lint LAW 4 closed set, Breakpoints styleguide tab, gate 44/44).
Naming ruling: `bp-sm/md/lg/xl`. Phases 1–3 (§4) remain adoption-branch work;
§5 items 3–5 (sticky-bar exception, blue 1920, starter-pack delete) stay open.
Per the harvest-before-values law: every number below is measured from the live
theme repos or quoted from a primary source, never from memory or taste.

---

## 1. Harvest — what the themes actually ship

**Sources (GitHub, harvested 2026-07-03):**

| Repo | Theme | HEAD | @media blocks |
|---|---|---|---|
| `rostislavpeska/aifounders_web` | `aifounders` | `73d4c05` | **125** |
| `rostislavpeska/aiguild` | `aiguild` | `7454e1d` | **134** |
| `rostislavpeska/aiguild-blue` | `aiguild-blue` | `580275d` | **65** |
| | | **total** | **324** |

Method: `grep -rE '@media[^{]+'` over `*.css`, `*.scss`, `*.php`, `*.js` in each
theme directory; width conditions extracted from `@media` lines only (a naive
`min|max-width` grep also catches element sizing — those were excluded).
AIF additionally carries **6 media blocks inside inline `<style>` in PHP
templates** (`page-newsletter-contacts.php`, `page-newsletter-preferences.php`,
`page-author-profile.php`) — invisible to any CSS-file audit.

### 1.1 The two real cuts

Two boundaries carry ~75% of all width conditions:

| Boundary | Conditions (all 3 themes) |
|---|---|
| **phone / tablet at 768** | `max-767` ×73 · `min-768` ×39 · `max-768` ×28 · `min-769` ×6 = **146** |
| **tablet / desktop at 1024** | `max-1023` ×51 · `min-1024` ×26 · `max-1024` ×17 · `min-1025` ×5 = **99** |

Both nav collapses sit on the second cut: AIF+AIG `max-1023`
(`page.css:178/209`), aiguild-blue `max-1024` (`style.css:2225`).

**Off-by-one divergence:** AIF+AIG pair `max-767`/`min-768` (exclusive, correct).
aiguild-blue pairs `max-768`/`min-769` and `max-1024`/`min-1025` — a different
convention for the *same* cut. Worse, AIF+AIG *also* contain 28 stray `max-768`
and 17 `max-1024` blocks, which overlap the `min-768`/`min-1024` side at exactly
768px/1024px (both true → source order decides — the same trap the HANDOFF
already documents for the tokens mobile block).

### 1.2 The secondary cut

**600** is a real, load-bearing small-phone boundary: `max-599` ×12 ·
`min-600` ×8 · `max-600` ×8 = 28 conditions across all three themes
(card grids 1→2 columns, tighter paddings).

### 1.3 The wide tier

`min-1440` ×10 (AIF 3, AIG 6, blue 1) · `min-1280` ×6 · blue-only
`1440–1919` + `min-1920` (ultrawide token overrides, `style.css:2474`).

### 1.4 The long tail — 29 distinct width values in total

Everything not on 600/768/1024/1440, with measured purpose:

| Value | Where | What it does |
|---|---|---|
| 420 | blue `style.css:1028` ×1 | tighter-phone tweak |
| 480 | AIF `page-newsletter-contacts.php` ×1 | inline-PHP phone tweak |
| 640 | modals (AIF `modal-registration`, AIG `modal-reservation`) + misc ×8 | modal goes full-width |
| 720 | blue `style.css:2089` ×1 | one-off |
| 760, 960, 1260 | AIF `main.starter-pack.20260126_203002.css` ×6 | dated landing one-off file |
| 900 + 901–1150 | `article-sticky.css` in AIF *and* AIG | sticky newsletter bar: pitch+form width is content-driven |
| 1099 | AIG `page.css:949` | hero headline desktop/mobile variant swap (headline length driven) |
| 1199 + 768–1199 | AIF `page.css`, AIG `blog.css` ×4 | grid column drops |
| 1279 | AIG `components.css:3082` ×1 | one-off |
| 1439 | AIG `positions.css` (range endpoint of the 1440 cut — legit) | positions grid 2→3 col |
| 1600 | AIG `blog.css:981` | course-card grid needs full width for side-by-side horizontal cards — **deliberate, documented in a comment** |
| 1919/1920 | blue `style.css` | ultrawide (FULL HD / 1920+) art direction |

### 1.5 Non-width queries (not breakpoints — out of scope, untouched)

`prefers-reduced-motion` ×9 · `hover`/`pointer` ×5 · `min-resolution` ×1 ·
two height-qualified combos. These are capability queries, not layout tiers.

### 1.6 Direction mix

Desktop-first (`max-width`) dominates ~2:1, and the DS itself already emits its
tokens mobile block as `max-width: 767px` (`build/build.mjs:96`). `min-width`
appears mainly for the tablet-and-up side and the wide tier.

---

## 2. Research — how the two reference systems do it

### 2.1 IBM Carbon (`@carbon/layout`, carbondesignsystem.com)

- **Five breakpoints, mobile-first, `min-width`, rem-based:**
  sm 320 / md 672 / lg 1056 / xlg 1312 / max 1584 (px at 16px root).
- Values are **grid-derived**, not device-derived: each is chosen so
  `(width − 2×margin) / columns` lands on a clean 8px mini-unit multiple
  (672−32)/8 = 80px, (1056−32)/16 = 64px, etc.
- Exposed as Sass mixins (`breakpoint-up/down/between`) and JS tokens —
  **not** CSS custom properties (custom props can't drive media queries).
  `breakpoint-down` subtracts 0.02px to dodge Safari fractional-viewport
  rounding (their answer to the off-by-one problem).
- **Typography:** two classes — *fixed* styles (one size everywhere) and
  *fluid* styles (CSS-locks linear interpolation `calc(min + (max−min)*…)`
  between adjacent breakpoints as anchors). Same mechanism-by-style-class
  idea as our MECHANISM LAW.
- **Exceptions:** official guidance — *"Create custom breakpoints to
  accommodate special needs, by writing your own media queries."* The five
  cover the system grid; components may deviate. No container queries.

### 2.2 Apple (apple.com shipped CSS + HIG, verified live July 2026)

- **Two structural cuts + one enhancement, desktop-first `max-width`:**
  `max-width:734px` (small) · `max-width:1068px` (medium) · base = large
  (1069–1440) · `min-width:1441px` (xlarge). iPhone page counts: 192× /
  132× / 18×.
- Cuts are **content-driven, not device-driven**: 734/1068 are where the
  fixed content columns (980px / 692px) stop fitting. Buckets are named
  (`.small-*`, `.medium-*`, `.large-*`, `.xlarge-*`), not the pixel values.
- **Typography: fixed steps per bucket, zero `clamp()`** in the homepage CSS.
  Each type style (`.typography-headline` 48→40→32) carries its own
  per-bucket sizes — responsive behavior belongs to the **style**, exactly
  our MECHANISM LAW's shape.
- **Exceptions:** global buckets carry the page; components ship their own
  content-driven queries where needed — global nav breaks at **833/1023**
  (where nav items stop fitting), sub-phone tweaks at 480, height-qualified
  hero queries. Exceptions don't become new global tiers.
- HIG native side: only two size classes (compact/regular) — the same
  few-buckets-late-switching philosophy.

### 2.3 What to take

Apple's model fits this system better than Carbon's: few content-driven cuts,
desktop-first, stepped type per style, named buckets, tolerated-but-contained
component exceptions. From Carbon take the machine side: breakpoints as build
tokens with generated helpers, and the discipline that *system* values are
closed while custom queries are the documented escape hatch. The harvest
already agrees: the themes de facto run Apple's shape (two big cuts + small
phone + wide) — they've just never had it written down, so 29 values grew
where 4 belong.

---

## 3. Proposal — the standard set

### 3.1 Four canonical cuts, five buckets

| Token | Value | Opens bucket | Query forms |
|---|---|---|---|
| — | — | **base** = phone (<600) | no query — base styles |
| `bp-sm` | **600** | big phone / small tablet (600–767) | `(min-width: 600px)` / `(max-width: 599px)` |
| `bp-md` | **768** | tablet (768–1023) | `(min-width: 768px)` / `(max-width: 767px)` |
| `bp-lg` | **1024** | desktop (1024–1439) | `(min-width: 1024px)` / `(max-width: 1023px)` |
| `bp-xl` | **1440** | wide (≥1440) | `(min-width: 1440px)` |

All four values are already the harvest's own dominant cuts — nothing is
invented. 768 is simultaneously the type content-ramp step (MECHANISM LAW)
and the tokens mobile block (`max-767`) — one cut, every mechanism.

### 3.2 Laws

1. **BOUNDARY LAW — exclusive pairs, minus-one on the max side.**
   `max` always uses *value − 1* (599/767/1023); `min` uses the value.
   `max-768`, `min-769`, `max-1024`, `min-1025` are lint errors. (Carbon's
   −0.02px trick solves the same problem; −1px is what 112 of our existing
   blocks already do and stays greppable.)
2. **DIRECTION LAW — desktop-first by default.** Overrides written as
   `max-width` (matches the tokens mobile block and the 2:1 harvest
   majority); `min-width` reserved for the wide tier (≥1440 is additive
   enhancement, Apple's 1441 pattern) and explicit between-ranges.
3. **PX LAW — values stay px.** The harvest is 100% px; Apple ships px;
   Carbon's rem is a Carbon-ism. px media queries respond to browser zoom
   the same as em in all modern engines.
4. **CLOSED-SET LAW (the Carbon discipline).** Any `@media` width in DS CSS
   must be a canonical value — enforced by `build/lint-css.mjs`, escape
   hatch below. Gate pins the distinct-width-value count.
5. **Capability queries are free.** `prefers-reduced-motion`, `hover`,
   `pointer`, `resolution`, height — not width tiers, not restricted.

### 3.3 Storage and generation

`tokens/breakpoints.json` — four named values with `$evidence` harvest
records (same shape as `type-styles.json`). `build.mjs` reads it and emits
the query strings it already hardcodes (`build.mjs:96` becomes generated).
No Sass mixins, no runtime registry — breakpoints are build-time constants,
which is all CSS allows anyway (custom props can't drive media queries;
even Carbon doesn't expose them that way).

### 3.4 Exceptions — the Apple nav rule

A component may deviate when its **content** demands a cut the system
doesn't have (Apple's nav at 833/1023; our sticky bar at 900). Conditions:

1. **Local** — lives in the component's own CSS, never in tokens/shared files.
2. **Declared** — same-line comment `/* bp-exception: <reason> */`;
   lint allowlists only commented exceptions and reports the count.
3. **Never a tier** — the moment two components want the same exception
   value, that's a proposal for a canonical cut, not two exceptions.

Grandfathered candidates from the harvest (each gets a golden-master try at
the nearest canonical cut first; exception status only if VRT shows it breaks):

| Current | Disposition |
|---|---|
| sticky bar 900 + 901–1150 (AIF+AIG) | strongest exception candidate — content-driven, already duplicated across both themes identically |
| AIG course grid 1600 | keep as declared exception — deliberate, comment documents why 1440 is too narrow |
| AIG hero headline swap 1099 | try 1023; exception if headline wraps |
| blue ultrawide 1920 | operator ruling: keep as blue-only exception or retire at adoption |

### 3.5 Long-tail mapping (adoption-time normalization)

| From | To | Notes |
|---|---|---|
| `max-768` / `min-769` | `max-767` / `min-768` | pure rename, blue-wide |
| `max-1024` / `min-1025` | `max-1023` / `min-1024` | pure rename |
| `max-600` (exclusive intent) | `max-599` | boundary law |
| 420, 480 | base or `max-599` | VRT-verify |
| 640 (modals ×2 + misc) | `max-599` or `max-767` | VRT decides which side |
| 720, 760 | `max-767` | |
| 960 | `max-1023` | |
| 1150, 1199, 1279, 1260 | `max-1023` or `max-1439` | grid col-drops; VRT per case |
| `min-1280` (×6) | `min-1024` or `min-1440` | VRT per case |
| 1919/1440–1919 | collapses if 1920 exception retires | |
| AIF `main.starter-pack.20260126_203002.css` | **delete candidate** | dated one-off landing file carrying 8 blocks on 3 non-canonical values |
| AIF inline-PHP `<style>` blocks (×6) | migrate into CSS files | separate housekeeping item |

---

## 4. Consolidation plan (the theme-load problem)

Honesty first: the browser cost of *evaluating* many media blocks is minor.
The real theme-load problem is bytes and duplication — 324 blocks across
~25 CSS files means the same `max-767` override logic is re-stated dozens
of times per theme, plus whole files (starter-pack) and inline PHP styles
that ship on every load. The wins, in order of size:

**Phase 0 — DS (now, before any responsive component work):**
`tokens/breakpoints.json` + build emission + lint CLOSED-SET/BOUNDARY laws +
gate assertion pinning the canonical set. The DS's own CSS already complies
(767, 600) — this just locks the door before Batch 2 components arrive.

**Phase 1 — adoption mapping:** the §3.5 table joins `rename-map.json`'s
job: mechanical old-query → new-query rewriting on the AIF adoption branch,
verified by the pixel golden masters + mobile VRT already planned in the
roadmap. Off-by-one renames are pixel-identical by construction; long-tail
remaps are visible diffs the VRT will catch.

**Phase 2 — block grouping:** within each stylesheet, gather rules into one
block per cut per file (base → `max-1023` → `max-767` → `max-599` →
`min-1440`, mirroring cascade order — mobile blocks after brand blocks, the
HANDOFF gotcha). Estimated effect:

| Theme | Blocks now | After grouping (est.) |
|---|---|---|
| aifounders | 125 | ~35–40 |
| aiguild | 134 | ~40–45 |
| aiguild-blue | 65 | ~25 |

Distinct width values: **29 → 4 canonical + ≤4 declared exceptions.**
Distinct query strings: ~60 → ~10.

**Phase 3 — components carry less:** as Batch 2 components land in the DS,
their responsive behavior rides tokens (the mobile block swaps the variable;
the component never writes its own query) — the same move that let
`tokens.css` handle the whole type mobile ramp in ONE block. Every component
that adopts a token-driven size is one fewer theme media block forever.

---

## 5. Operator sign-off needed

1. **The canonical four** — 600 / 768 / 1024 / 1440. (Alternative considered:
   drop 600 for a purer three-cut set — rejected because 28 harvested
   conditions and the DS's own `components.css` already use it.)
2. **Naming** — `bp-sm/md/lg/xl` (Carbon-terse, recommended) vs
   `bp-phone/tablet/desktop/wide` (device words lie over time).
3. **Sticky-bar 900** — pre-approve as the first declared exception, or
   force the 1023 VRT try first.
4. **Blue ultrawide 1920** — keep (blue-only exception) or retire.
5. **`main.starter-pack.*.css`** — confirm delete candidate at AIF adoption.
6. Green-light Phase 0 (tokens/breakpoints.json + lint laws + gate pin).
