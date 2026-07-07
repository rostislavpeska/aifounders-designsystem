# THEME-REFACTOR-SPEC — roadmap item 5: both sites adopt the DS (executable)

**Status: SPEC 2026-07-07 — written from two full theme inventories (agent
sweeps of both harvest clones) + the DS ledger. Executor: two parallel
agents (Opus-class), one per theme, each phase closed by THE PARITY GATE
(§3). Fable checkpoints at phase boundaries only.** The judgment is in
this document; the execution is mechanical + gated.

## 0. Ground truth

- **DS plugin**: installed & active on the AIF local stack (mounted, slug
  `aig-design-system/aifounders-designsystem.php`, 2.0.0-rc.1). Public
  canon: `github.com/rostislavpeska/aifounders-designsystem` (PUBLIC).
- **Live theme repos (work happens HERE, not in the harvest clones):**
  - AIF: `C:\Users\rosti\Documents\WORKSPACE\aifounders_web` → theme
    `wp-content/themes/aifounders` → serves `http://localhost:8090`
  - AIG: `C:\Users\rosti\Documents\WORKSPACE\aiguild` → theme
    `wp-content/themes/aiguild` → serves `http://localhost:8095`
  - Harvest clones (`WORKSPACE\_harvest\…`) are READ-ONLY references.
- **Setup gap (operator or P0):** the DS plugin must also be
  mounted+activated in the **aiguild** stack (same compose-override
  pattern as aifounders; memory: `local-wp-stack-setup`).
- Inventory numbers below from the 2026-07-07 agent sweeps; re-verify
  counts at execution (themes may have drifted).

## 1. THE FOUR LAWS of adoption (shared architecture)

**Law 1 — enqueue order.** Final chain on both sites:
`fonts → DS normalize → DS tokens → DS components.css → DS component JS →
theme compat-tokens.css (temporary shim, Law 2) → theme page/prose CSS →
theme overrides → fluent-forms-override.css (always last)`.
Theme dequeues its own `normalize.css` + `tokens.css` + (eventually)
`components.css`. The DS plugin needs a small enqueue change: today it
registers styles front-end but **enqueues component JS only in the
styleguide** — add a front-end registration+enqueue for the 9 engines in
`inc/enqueue.php` (all defensive: each no-ops without its markup).
**Refinement (2026-07-07, AIF execution):** the front-end JS enqueue is
GATED on `add_theme_support( 'aifds-engines' )` — markup is DS-native, so
ungated engines would double-bind with the still-alive theme scripts
between P1 and P3 (two listeners on `.burger-toggle` = open-then-close).
The theme declares support at **P3**, in the same commit that dequeues its
dying scripts. Until then the enqueue chain carries no DS JS.

**Law 2 — THE TOKEN COMPAT SHIM (the single biggest breakage risk).**
The DS renamed ~99 variables during distillation
(`tokens/rename-map.json`: `--surface`→`--bg`, `--color-text-secondary`→
`--text-secondary`, …). Surviving theme CSS still reads OLD names
(verified: `page.css` alone carries 169 old `var(--color-*)` reads on AIF,
49 on AIG). Without a shim, every surviving rule silently resolves to
`unset` on day one. **P0 generates `compat-tokens.css` mechanically from
rename-map.json** (`:root { --old-name: var(--new-name); … }` — include
the scope-level old names inside the DS scope selectors where the map
says so), enqueued after DS tokens. It is a TEMPORARY crutch: each P2
sweep re-points the theme rules it touches to real DS names; P5 deletes
the shim when its read-count hits 0 (grep-countable).

**Law 3 — JS engine ownership.** The DS plugin owns the 9 engines
(`accordion, datepicker, dropdown, engagement, menu, modal, nav-tabs,
sticky-bar, table-scroll`). Theme copies DIE, theme WIRING stays:

| theme file | AIF | AIG | verdict |
|---|---|---|---|
| menu.js (burger+shrink) | ✓ | ✓ | DIES → DS `menu.js` (also owns reading-progress) |
| main.js header-shrink part | — | ✓ | that block DIES; rest of main.js stays |
| reading-progress.js | — | ✓ | DIES → DS `menu.js` |
| engagement.js | ✓ | ✓ | DIES → DS (was a byte-identical port) |
| accordion.js | — | ✓ | DIES → DS |
| article-sticky.js | ✓ | ✓ | DIES → DS `sticky-bar.js` (data-attribute gates replace the hand-rolled 50%-scroll logic; verify the article show/hide behavior per sticky-bar.md) |
| modal-registration.js / modal-reservation.js | ✗ (dead) | ✓ | **CORRECTED 2026-07-07:** AIF's modal-registration.js/.css/template-part are enqueued/included NOWHERE (verified theme + mu-plugins) — dead files, deleted at P0; AIF P3 modal re-wiring is a no-op unless live `data-modal` markup surfaces. AIG's modal-reservation.js STAYS, re-wired onto `window.aifdsModal` API (DS modal.js is the base engine; Fluent wiring is theme territory per modal.md) |
| trackers (reading/course/contact/ad), cohort-bar, testimonials-carousel, persona.js, lightbox, video-zoom, smooth-scroll, scroll-reveal, comment-edit, lazy-loads, fluent-forms-checkbox | ✓ | ✓ | STAY (integration/analytics/UX — no DS equivalent) |

**Law 4 — kill-list authority.** The DS ledger's *Classes* column
(`docs/IMPLEMENTATION_STATUS.md` components table, 40 rows) IS the
selector kill-list: any theme rule whose selector root matches a ledger
class dies in P2. The AIF inventory agent under-counted (31%) because it
treated `.persona-card`, `.course-info-card`, `.aif-aha/.aif-share`,
`.article-comments`, `.dark-blurb` (→ blurb/stack-grid), `.article` card
grid (→ preview-card) as theme-only — ALL are DS rows. Realistic kill on
both themes ≈ **60%** of theme CSS. Exceptions (do NOT kill):
`.hero-card` (hero family = reserved tokens, application row TRACKED —
adopt in a later pass), Gutenberg `.wp-block-*` overrides, prose deep
styles beyond the DS prose contract, and every file marked STAYS in §4/§5.

## 2. What must be true when this is done

Sites render **pixel-identical** (parity gate) with theme CSS roughly
halved, all component styling served by the plugin, all component
behavior by the plugin engines, both brand modes proven (AIF blue on
:8090, AIG yellow on :8095), and no rule anywhere reading a token name
the DS doesn't emit (shim deleted).

## 3. THE PARITY GATE (theme-side; build BEFORE touching any CSS)

`build/theme-parity.mjs` in the DS repo (shared tool, same spirit as
shots.mjs + the L2 sweep — this is what makes Opus safe here):

- **Page inventory** per site (JSON in the theme repo):
  AIF: `/` · a long article (single post w/ comments+engagement+sticky) ·
  archive `/clanky/` (or the blog home) · a signal archive page · an
  events/meetups page · the newsletter landing `/newsletter` · an author
  page · 404. AIG: `/` · a course detail (`kurz`) · courses archive ·
  positions board `/pozice` · an instructor (`lektor`) · a long article ·
  a DB-landing page (`page-landing`) · contact. Both at 1440×full and
  390×full, both with `prefers-reduced-motion` forced (kills animation
  nondeterminism) and JS-settled waits. If Polylang serves a live second
  language, ONE page per extra language joins the inventory.
  **Viewport matrix (expanded 2026-07-07):** per-sweep gates run
  390/768/1440; phase-close gates (P1, P3, P5/C4) run the FULL matrix
  390/600/768/1024/1440 — the DS breakpoints, where BOUNDARY-LAW
  off-by-ones live.
- **BEFORE mode**: capture screenshots + per-page DOM fingerprint
  (element count, key computed styles on sentinel selectors: header, a
  .btn--primary, footer band, body font) on the UNTOUCHED theme → commit
  as `theme-parity-baseline/` in the theme repo (small PNGs, one-time).
- **AFTER mode** (every phase close): recapture → `pixelmatch` per page
  (threshold ~0.1% differing pixels; same machine + same content + fonts
  local = deterministic enough for a deletion-refactor) + console-error
  capture + the L2-style overflow/occlusion sweep on each inventory page
  — the overflow sweep runs at EVERY viewport in the matrix.
- **Additional assertions (expanded 2026-07-07 — additions only, the spec
  gate stays the floor):**
  · **HTTP 200 assert** per inventory page (a PHP fatal must be a named
    failure, not a pixel diff);
  · **string sentinels**: key `pll__()` outputs asserted present per page
    ("min čtení", badge labels, newsletter titles) — a broken Polylang
    chain fails loudly; sentinel selectors include ACF-driven elements so
    an empty `get_field()` fails the fingerprint;
  · **debug.log ratchet**: WP_DEBUG_LOG enabled at P0, existing entries
    baselined; each phase close asserts NO new notices/warnings/deprecations;
  · **axe baseline** (`@axe-core/playwright`, already a DS dep): violation
    count per page baselined at P0; no NEW violations per phase;
  · **CSS payload metric** (informational): total enqueued CSS bytes
    printed per phase — §2's "roughly halved" becomes a number;
  · **P3 behavior harness**: scripted Playwright interactions on the
    inventory (burger open/close, header shrink, accordion, modal
    open/ESC/focus-trap, aha → toast, sticky show/hide, reading-progress
    on articles only) — C3 stays the operator's manual pass ON TOP;
  · **forms error-path test** (P3+): submit-empty on newsletter capture +
    Fluent forms → validation styling appears, NO external call fires
    (Ecomail is live — the gate never performs a real submission).
- Failures print the diff heat-map path; the agent FIXES or REVERTS the
  sweep that broke parity. Content drift (new posts) is the known false
  positive — the local DBs are static snapshots, so acceptable; if a page
  churns, pin a specific post URL in the inventory.
- **Never weaken:** thresholds only go DOWN.

## 4. AIG plan (`WORKSPACE\aiguild`, :8095) — 13,762 CSS lines, ~62% dies

File dispositions (inventory 2026-07-07):

| file | lines | verdict |
|---|---|---|
| components.css | 5,161 | ~95% DIES (P2) — keep only `.btn-floating`, article selection reset, any rule not on a ledger class |
| blog.css | 3,339 | ~95% DIES (P2) — split first: keep prose-deep (~500: figure captions, gallery/lightbox layout, reading-time badge) |
| tokens.css | 552 | DIES at P1 (DS tokens + shim replace; verify no theme-only vars first — anything AIG-only gets a documented home or a DECISIONS-flagged retirement) |
| normalize.css | 398 | DIES at P1 (DS ships identical) |
| fluent-forms-override(.compact/.backup).css | 898 | DELETE at P0 (dead files, not enqueued twins) |
| fluent-forms-override.css | 620 | STAYS (plugin remap; loads LAST) |
| page.css | 1,362 | ~40% dies (accordion/card/text-style dupes); course/page layout stays |
| main.css 302 · positions.css 249 · persona.css 230 · article-sticky.css 167 · landing-base.css 136 · modal-reservation.css 118 | ~1,200 | partial kills per inventory (~30–80% each); the rest stays |
| lightbox 57 · video-embed 158 · n8n-chat 15 | 230 | STAY |

AIG specifics: markup is ALREADY DS-native (zero class renames, verified
template-by-template). Hardcoded `#63531B` nav-active / `#c9a101` course
eyebrow are already DS tokens (`--nav-active`, `--brand-strong`) — the
dying rules carried them. `.reservation-modal` rides DS `.modal` base.
**Risks:** ~802 `!important` (worst in blog.css — most die with their
rules; P5 burns down survivors) · **`landing_html` DB pages** — audit for
inline `<style>` before P2 touches landing-base.css; per-page CSS files
`/assets/css/landing/*.css` are out of scope for P2 (separate pass) ·
Fluent order per Law 1.

## 5. AIF plan (`WORKSPACE\aifounders_web`, :8090) — 11,663 CSS lines, ~60% dies (corrected)

**Re-verified 2026-07-07 (execution start):** live counts run ~13% below
the inventory (components.css 5,076 · page.css 3,019 · tokens.css 480 ·
normalize.css 311) — verdicts unchanged. **Execution happens on branch
`refactor/ds-adoption`** (operator 2026-07-07); main untouched until C4.

**AIF dead files — DELETE at P0 (operator verdict 2026-07-07; none is
enqueued/included anywhere, verified theme + mu-plugins):**
`main.starter-pack.20260126_203002.css` ·
`normalize.starter-pack.20260126_203002.css` ·
`assets/css/archive/eu-cookies-bar-override.css` ·
`modal-registration.css` · `modal-registration.js` ·
`archive-lazy-load.js` · `signal-archive-lazy-load.js` ·
`template-parts/modal-registration.php` (orphaned template part — double-
check variable `get_template_part()` calls before deleting).

File dispositions (inventory corrected per Law 4):

| file | lines | verdict |
|---|---|---|
| components.css | 5,883 | ~75% DIES (P2): btn/badge/accordion/avatar/section rules (~940) PLUS persona-card 466, course-info-card 257 (declared dead at distill), engagement 519, comments 136, dark-blurb 675 (blurb family), `.article` card grid 449 (preview-card), dark-section typography ~416 (scope+prose). KEEP: `.hero-card` ~420 (deferred), `.aif-form` remainder after form-system overlap check, Gutenberg overrides ~833 |
| page.css | 3,507 | mostly STAYS (~90%) — page composition; kill text-style/dark-typography dupes (~315+) |
| tokens.css 548 · normalize.css 398 | 946 | **CORRECTED at P1 execution 2026-07-07:** normalize.css dies at P1 (verified byte-identical to the DS copy). tokens.css does NOT die at P1 — the theme-only-vars check found **141 orphan names with 300+ live reads** (the legacy `--text-h*` bundles, `--font-weight-*`, `--font-family-*`, `--radius-*`) that neither the DS nor the rename-map shim emits. tokens.css stays enqueued BEFORE the shim (DS values win for all 99 renamed names); the P2 text-styles sweep re-points the legacy reads; the file dies when its read-count hits 0 (same criterion as the shim, grep-countable). |
| fluent-forms-override.css | 512 | STAYS but shrinks (~70% of it duplicated DS field styles — re-point to DS classes, keep the Fluent remap core) |
| modal-registration.css | 110 | **CORRECTED 2026-07-07: dead file** (never enqueued) — deleted at P0 with its JS + template part |
| landing/newsletter.css | ~750 | **ADDED 2026-07-07 (operator): IN P2 SCOPE** — loaded only via `page-landing.php` (incl. `/newsletter`, which is in the parity inventory); 25 old-token reads (shim covers, incl. on landing pages); ledger-class rules die in the family sweeps, survivors re-point to DS names |
| main.css | 244 | ~60% dies (generics); rest stays |
| author-forms 406 · article-sticky 229 · lightbox 57 · video-embed 158 | 850 | STAY (article-sticky.css dies only if the DS sticky-bar skin fully covers it — verify against sticky-bar.md before deleting) |

AIF specifics: markup DS-native; template touch-ups (P4): `single.php`
line ~263 `.text-h3` (NOT a DS class → `.heading-sm`), the inline
`<style>` block lines ~293–311 (persona-card link colors w/ `!important`
— dies; DS link chain covers it), front-page inline blockquote/h2 styles
→ classes. Hardcoded `#0067b1` in fluent override = `--deep` (token
exists). `.hero-card` adoption = a LATER dedicated pass (hero tokens are
reserved API; don't improvise it mid-refactor).

## 6. Phases (each ends: parity gate green → conventional commit; per theme)

- **P0 — setup**: DS plugin mounted+active on AIG stack (operator/compose)
  · plugin front-end JS enqueue added (DS repo change, gated by the DS
  92-gate; theme-support-gated per Law 1 refinement) · generate
  compat-tokens.css from rename-map.json · delete AIG dead files (898
  lines) + AIF dead files (§5 list) · enable WP_DEBUG_LOG + baseline
  debug.log + axe counts · build theme-parity.mjs + page inventories ·
  capture BASELINES (before any change!).
- **P1 — foundations swap**: dequeue theme normalize+tokens, enqueue DS
  chain per Law 1 (+shim; NO DS JS yet — that flips at P3). Theme
  components.css still loaded (harmless duplication during transition).
  Gate expects ~zero visual delta.
- **P2 — component sweeps** (one ledger family per commit, gate each):
  buttons/badges → text styles/prose dupes → forms family → cards
  (preview/persona/course/reference) → engagement+comments →
  header/footer/nav → sticky-bar+modal → blurb/stack-grid/dark-sections →
  surfaces/sections. Each sweep: delete the theme block, re-point any
  surviving neighbors to DS token names (shrinks the shim), gate.
- **P3 — JS engine swap** (Law 3 table): theme declares
  `add_theme_support( 'aifds-engines' )` + dequeues dead theme scripts in
  ONE commit (no double-binding window), re-wire modal wiring onto
  `aifdsModal` (AIG only — AIF's is dead per corrected Law 3), verify
  behaviors via the scripted behavior harness (§3) on the inventory pages
  (menu shrink, burger, progress on articles only, aha toast, sticky
  show/hide, accordion, modal open/close/focus).
- **P4 — template touch-ups**: §5 AIF items; AIG none known.
- **P5 — cleanup**: delete the shim (read-count 0 proven), `!important`
  burn-down on survivors, dead-file sweep, bump theme versions, final
  full-inventory parity run + operator eyes on both sites.

## 6b. Feedback + human-in-the-loop (binding)

**[ADOPTION-FEEDBACK.md](ADOPTION-FEEDBACK.md) is part of this spec.**
Every DS-vs-project mismatch is classified F1–F4 there; F2 bugs are
agent-fixable under the specimen-first bug ritual (DS gate must GROW);
F3 gaps are ALWAYS operator verdicts (file the queue row, park the
family, continue); F4 deltas are DECISIONS-backed and adopted as-is.
Operator checkpoints C0–C4 are defined there — the agents STOP at
checkpoints and batch F3 verdicts, never mid-sweep. DS changes land in
the factory repo (the ritual) and mirror-sync to the public repo.

## 7. Operator handoffs

AIG compose mount + plugin activation (P0) · `landing_html` DB audit
verdicts (which landings carry inline styles) · production deploy
decisions per site after local sign-off (rsync/git flow is theirs) ·
Lazzer font upload in Figma (unrelated leftover, noted here so it isn't
lost).

## 8. Kickoff prompts (one per parallel agent, fresh sessions)

```
Execute docs/proposals/THEME-REFACTOR-SPEC.md for the AIF theme ONLY
(§5 + shared §§1-3, 6): repo C:\Users\rosti\Documents\WORKSPACE\aifounders_web,
theme wp-content/themes/aifounders, local site http://localhost:8090.
Work phase by phase (P0→P5); NEVER touch CSS before the P0 parity
baseline is captured and committed. Close every phase with the parity
gate green + a conventional commit. The DS ledger's Classes column is
the kill-list authority (Law 4); when unsure whether a rule is DS or
theme territory, check the component's doc in the DS repo
(docs/components/*.md) — and if still unsure, ask, don't guess. Never
weaken gate thresholds. The DS repo also runs its own 92-check gate for
any plugin-side change (the JS enqueue in P0).
DS mismatches follow docs/proposals/ADOPTION-FEEDBACK.md: classify
F1-F4; F2 bugs = specimen-first fix with the DS gate growing; F3 gaps =
queue row + park the family + continue (operator verdicts at
checkpoints); stop at checkpoints C0/C1/C3/C4 for the operator.
```

```
Execute docs/proposals/THEME-REFACTOR-SPEC.md for the AIG theme ONLY
(§4 + shared §§1-3, 6): repo C:\Users\rosti\Documents\WORKSPACE\aiguild,
theme wp-content/themes/aiguild, local site http://localhost:8095.
[same rules as above verbatim]
```

Coordination: the two agents share ONLY the DS repo (P0's JS-enqueue
change lands ONCE — first agent does it, second pulls). Theme repos are
disjoint — true parallelism. [[parallel-chat-coordination]]: check git
log/status before every commit.
