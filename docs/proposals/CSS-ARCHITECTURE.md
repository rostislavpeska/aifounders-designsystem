# CSS-ARCHITECTURE — the additive layering a DS-adopting theme ships (proven on AIF, the default for AIG)

**Status: ARCHITECTURE 2026-07-08.** Written the day the AIF theme flipped
from "delete DS duplicates out of a fighting `components.css`" to "DS base +
one thin additive composition layer" (commit `e40cf8b`, aifounders_web). This
doc is the **target-state blueprint** the AIG migration builds toward: what
the theme's CSS looks like when adoption is done, why the layers sit in this
order, and the repeatable method that got AIF there. It is the companion to:

- **[THEME-REFACTOR-SPEC](THEME-REFACTOR-SPEC.md)** — the phased, gated
  execution plan (P0–P5) and the FOUR LAWS of adoption. This doc refines
  Law 1's enqueue chain to the post-additive-rebuild shape; the other three
  laws stand unchanged.
- **[ADOPTION-PLAYBOOK](ADOPTION-PLAYBOOK.md)** — the per-component migration
  recipe (the three buckets, the class-map, the x-ray). That is the *how you
  move each component*; this is the *what the finished stack looks like*.
- **[ADOPTION-FEEDBACK](ADOPTION-FEEDBACK.md)** — the F1–F4 classification
  every DS-vs-project delta flows through.

---

## 1. THE ARCHITECTURE (the finished layering)

The DS plugin owns **every component**. The theme owns **composition only**.
That single ownership split is the whole architecture; everything below is a
consequence of it.

The enqueue chain, top (loads first, lowest cascade priority) to bottom
(loads last, wins ties), verified in `aifounders_web`
`wp-content/themes/aifounders/functions.php` (~L390–447):

```
fonts
  → DS normalize        ┐
  → DS tokens           │  aifds-components pulls tokens → normalize + fonts
  → DS components.css   ┘  (the plugin chain — owns EVERY component)
  → DS component JS      (front-end engines, theme-support-gated — Law 3)
  → theme legacy tokens  (tokens.css: the private --text-h*/--font-*/--radius-*
                          layer the DS never had — 141 names, 300+ live reads;
                          loads BEFORE the shim so DS values win renamed names)
  → compat-tokens shim   (GENERATED from rename-map.json — old→new aliases;
                          temporary, dies at P5 when old-name read-count = 0)
  → theme.css            (COMPOSITION ONLY — the one thin theme layer)
  → feature CSS          (video-embed, author-forms; scoped, additive)
  → fluent-forms-override.css  (always LAST — bucket-3 remap, Law 1)
```

Key facts, each load-bearing:

- **`theme.css` is the ONLY theme composition file.** It is `page.css` +
  `main.css` merged: containers / max-width, grids, section padding,
  hero/brand bands, dark-section bleed, article layout. **No component
  styling. No `!important` wars.** It loads *after* the DS so composition
  sits additively on top of components that are already correct.
- **The DS loads FIRST among the style sources, on purpose.** It is the
  foundation, not an override. The theme never needs to out-specify it,
  because the theme never re-styles a component — it only places components.
- **The shim sits between legacy tokens and `theme.css`** so any surviving
  theme rule that still reads an old variable name resolves during the
  transition. It is a crutch with a death criterion (`grep -c 'var(--old'` =
  0), not a permanent layer.
- **Feature CSS and the Fluent override are additive tails.** `author-forms`
  is scoped to `.aif-form`; `video-embed` is a STAYS component; the Fluent
  override is bucket-3 (foreign markup, DS-derived styling, loads last). None
  of them fight the DS — they extend into territory the DS doesn't own.

### What lives where (the ownership table)

| concern | owner | file |
|---|---|---|
| every component's base look (button, card, badge, header, footer, modal, blurb, nav-tabs, table, accordion, engagement, comments…) | **DS plugin** | `aifds-components` |
| design tokens (color, type, spacing, stroke, shadow, container…) | **DS plugin** | `aifds-tokens` (generated) |
| component behavior (9 engines) | **DS plugin** | DS component JS |
| containers, max-width, grids, section padding | theme | `theme.css` |
| hero/brand bands, dark-section bleed, article layout | theme | `theme.css` |
| legacy private tokens still read by survivors | theme (temporary) | `tokens.css` |
| old→new token aliases | generated (temporary) | `compat-tokens.css` |
| foreign-markup form styling (Fluent) | theme | `fluent-forms-override.css` |
| STAYS features (video embed, lightbox) | theme | feature CSS |

---

## 2. THE PIVOT (why "delete the dupes" became "retire the file")

The AIF theme's old `components.css` was **3752 lines / 136 `!important`** and
was almost entirely **DS-component DUPLICATES**. The first adoption approach
was to *delete the DS duplicates out of that file, one rule at a time*, while
the file stayed enqueued and kept fighting the DS. That fight was the source
of every collision the AIF run hit:

- **tertiary-hover** — a dead theme override strangled the DS button
  (`de08758`);
- **footer wrapper** — a `.footer-blurb__body` div stopped links being direct
  `.blurb` flex-children (`dea720f`);
- **legal-link** — theme legal-link styling out-specified the DS.

Every one of those was a symptom of the same disease: **a theme
`components.css` loading after the DS and re-asserting component styles.** You
cannot win a per-rule deletion race against a file whose entire job is to
duplicate the layer you're adopting.

**The pivot (commit `e40cf8b`, 2026-07-08): retire the whole file.** The DS
plugin already renders every component `components.css` was duplicating — so
the file was pure liability. It was dropped from the enqueue wholesale (kept
on disk as a rollback reference, not loaded), and `page.css` + `main.css` were
merged into one additive `theme.css`.

**Before:** DS base + a fighting `components.css` you whittle down rule by
rule, chasing collisions.
**After:** DS base + one thin additive composition layer that never touches a
component.

For AIG, this is the **default from the start.** You do not port AIG's
`components.css` and then delete from it. You confirm the DS renders AIG's
components, retire AIG's `components.css` from the enqueue, and keep only
composition. (AIG's `components.css` is 5161 lines, ~95% slated to die per
THEME-REFACTOR-SPEC §4 — the additive rebuild makes that a single retirement,
not 5000 lines of surgery.)

---

## 3. THE METHOD (the repeatable recipe that proved it)

This is the operator's own test, and it is the recipe for AIG. **"Apply only
what is necessary."**

1. **Disable the theme CSS in the browser.** DevTools → uncheck the theme
   stylesheets (`theme.css` / the old `components.css` / `page.css`). Leave
   the DS plugin CSS on.
2. **Confirm the DS renders every component correctly.** Walk the page. Every
   button, card, badge, header, footer, blurb, form field, engagement bar,
   comment thread should already look right — because the DS was distilled
   *from this markup* and the markup is DS-native. What's broken with theme
   CSS off is **composition** (containers collapse, grids unwrap, section
   padding gone, dark bands lose their bleed) — never components.
3. **Add back ONLY composition, page by page, verifying each.** Re-enable /
   author `theme.css` incrementally: containers first, then grids, then
   section rhythm, then the bespoke bands (hero, brand, dark-section bleed),
   then article layout. After each addition, re-check the page. If a
   component changes appearance when you add a composition rule, that rule is
   reaching into component territory — **it does not belong in `theme.css`.**
4. **Verify the two hardest pages live before declaring done.** For AIF that
   was the homepage (hero, grids, info-bar, dark blurbs, footer) and a full
   article (prose links, share row, engagement, comments, comment form). Pick
   AIG's two hardest (a course detail + a long article, or `/` + a
   `landing_html` DB page) and confirm both in Chrome with zero theme
   `components.css`.

The test is diagnostic: **anything that breaks with theme CSS off but is a
component = a DS gap or a markup bug (F2/F1), not a reason to keep theme
component CSS.** Route it through ADOPTION-FEEDBACK, don't paper over it in
`theme.css`.

---

## 4. THE LAWS OF THE COMPOSITION LAYER

Constraints on what may live in `theme.css`. Breaking any of these re-creates
the fighting-`components.css` problem the pivot solved.

- **LAW A — composition only, never components.** `theme.css` may set layout
  (position, display, grid/flex container, padding, max-width, gap) on
  wrappers and hosts. It may **not** set a component's own look (colors,
  borders, typography, radii on `.btn`, `.badge`, `.preview-card`, `.blurb`,
  …). If you're typing a color onto a component selector in `theme.css`, stop
  — that's a DS surface-scope job (`.surface-*`) or an F2/F3 for the DS.
- **LAW B — no `!important`, no specificity wars.** The DS loads first and the
  theme loads after; ties already go to the theme. If you reach for
  `!important` to force a component, you are fighting the DS — which means the
  rule belongs to the DS, not the theme. The additive rebuild shipped with
  **zero** `!important` in `theme.css` (down from 136); AIG's `theme.css`
  target is the same: zero.
- **LAW C — single ownership of edge inset.** For a "box IS the grid"
  instance (one element carries both the host class and the grid class, e.g.
  AIF's `.aif-dark-section__blurbs.stack-grid--divided`), the host owns the
  outer edge padding and the grid owns only interior dividers. The DS
  single-ownership rule drops cells' outer padding **when there is a separate
  crust to own it**; where the box *is* the grid (its own background is the
  divider line-layer, so it can't be padded as a crust), the **cells** own the
  all-sides inset instead (commit `c4ff345`). Decide per instance: is there a
  separate host crust, or is the box the grid? That answers who owns the edge.
- **LAW D — surface scopes carry background, not component variants.** A
  section's background tint comes from a `.surface-*` scope class on the host
  (which sets `--bg`), never from a component variant or a hardcoded hex in
  `theme.css`. This is the same law ds-surfaces enforces; it is why
  `theme.css` never needs to know a component's colors.

---

## 5. TRAPS TO CARRY FORWARD (each cost time on AIF)

These bit the AIF run. They are the same on AIG — walk in expecting them.

- **CRLF is the theme's line ending.** Every theme file in `aifounders_web`
  (and AIG) is **CRLF**, verified (`functions.php`, `theme.css`). An editor
  that writes LF turns a 1-line change into an 800-line EOL-churn diff. After
  any programmatic Edit, **sed-restore CRLF** (`sed -i 's/$/\r/'` on lines you
  touched, or normalize the whole file back) before committing. Confirm with
  `git diff --stat` — a real one-line fix should be `+1/-1`, not `+800/-800`.
- **Retiring an enqueue handle silently un-renders its dependents.** When the
  additive rebuild retired the old CSS handles (`aif-main`, `aif-components`,
  `aif-page`) for `aif-theme`, WordPress **stopped printing** every stylesheet
  that still listed a retired handle as a dependency — no error, the sheet just
  vanishes (WP skips a `wp_enqueue_style` whose dep is unregistered; and note
  styles/scripts are SEPARATE namespaces, so a surviving *script* handle of the
  same name does NOT satisfy a *style* dep). It silently cost AIF the article
  newsletter bar (`article-sticky.css`) AND the entire per-slug landing loader
  in `page-landing.php` — every landing page, incl. `/newsletter`, rendered
  unstyled. After retiring/renaming any handle, grep the WHOLE theme (`*.php` +
  `inc/`, including conditional template enqueues in `single.php` / `page-*.php`,
  not just the `functions.php` chain) for `wp_enqueue_style(... 'old-handle' ...)`.
  The regression is invisible in the diff and the console; only a live
  "is this sheet in `document.styleSheets`?" check catches it.
- **`tokens.css` is GENERATED (DS side).** The DS `aifds-tokens` is emitted by
  the build, not hand-edited. Never patch a token value in the theme to fix a
  component — that's an F2/F3 for the DS token pipeline (ds-tokens /
  ds-colors), and the theme's own `tokens.css` is the *legacy* private layer,
  also not where component fixes go.
- **Retire the global link-exclusion chain FIRST.** The theme's
  `main a:not(.btn):not(.badge)…` global link chain fights the DS link system
  (it out-specified `.card-title-link { color: inherit }`, rendering card
  titles blue+underlined). Retire the theme's global link/exclusion chains
  and let the DS superset chain govern site-wide (`b01c004`) **before** the
  card sweeps, or every renamed component fights it. General rule: after any
  rename, grep the theme's global/exclusion selectors for the DS classes you
  just introduced.
- **The `.section-dark *` wildcard is BANNED (LAW 3 of surfaces).** A
  descendant-wildcard paint (`.section-dark * { color: … }`) captures every
  DS component inside a dark band and overrides its scoped colors. Retire it;
  the DS `.surface-*` scopes govern dark sections (`167496d`). Never
  reintroduce a `* ` wildcard in `theme.css`.
- **The `--bg-alt == --bg` surface-token collision is PARKED.** On the
  light-2/light-3 surfaces, `--bg-alt` currently resolves equal to `--bg`, so
  a surface that should separate visually doesn't. This is a known DS-side
  collision filed as an F3 — **do not "fix" it in `theme.css`** with a
  hardcoded alt background. It's parked for an operator token verdict; work
  around it only via the documented surface scopes, and flag any AIG page that
  depends on the distinction.
- **CSS transitions corrupt `getComputedStyle` hover reads.** When you
  measure a hover color to verify a fix, an in-flight `transition` makes
  `getComputedStyle` return an intermediate value — you'll misread the state.
  Measure with **transitions disabled** (`* { transition: none !important }`
  injected for the read, the same `prefers-reduced-motion` / `transition:none`
  posture the parity gate uses at capture). This is a measurement trap, not a
  code fix.
- **Box-IS-grid crust padding** — see LAW C above; called out here too because
  it re-surfaces on every divided-grid composition (footer, dark blurbs,
  benefits). Single-ownership: host owns the edge inset, unless the box *is*
  the grid, then the cells do.

---

## 6. TRACKING — the two migration boards + the x-ray

Adoption is invisible to the eye (the DS was distilled from the theme, so it
looks almost identical), so the migration is tracked in **two NocoDB boards**
plus the in-browser x-ray. Build/populate both on day one for AIG.

- **`Migration_Component_Status`** — one row per DS component family
  (button, badge, preview-card, persona-card, course-card, header, footer,
  blurb, engagement, comments, modal, sticky-bar, nav-tabs, table…). Columns:
  bucket (1 name-matched / 2 rename / 3 foreign-markup / STAYS), sweep phase,
  x-ray state (🟥 theme / 🟧 partial / 🟦 stays / 🟩 DS), and the **operator
  feedback column** (F1–F4 verdict + note). This is the burn-down: every row
  must reach 🟩 or 🟦.
- **`Migration_Page_QA`** — one row per parity-inventory page (per site).
  Columns: viewport pass/fail, parity-diff status, HTTP 200, debug.log clean,
  axe clean, and the **operator feedback column** (spotted regressions,
  polish-list items). This is the page-level sign-off ledger the operator
  reads at checkpoints.

Both boards' feedback columns are where operator rulings land between agent
sweeps — they are the async equivalent of the C0–C4 checkpoints in
ADOPTION-FEEDBACK. The x-ray (`ds-xray.js`, bookmarklet, reads true CSS
source from `document.styleSheets`) is the live per-page truth that feeds the
`Migration_Component_Status` states; the parity gate + `COVERAGE.md` feed
`Migration_Page_QA`.

---

## 7. AIG APPLICABILITY (what carries over unchanged)

- **Same DS → same target architecture.** AIG ends in the identical shape:
  DS base + one additive `theme.css`, `components.css` retired, shim +
  legacy-tokens temporary, Fluent override last. The enqueue chain is the
  same; only the file names differ (`aiguild` theme, `:8095`).
- **AIG's markup is already DS-native** (verified template-by-template per
  THEME-REFACTOR-SPEC §4 — zero class renames), which means the disable-CSS
  test in §3 should show even *cleaner* results than AIF: nearly every
  component correct with theme CSS off. That makes the wholesale
  `components.css` retirement lower-risk on AIG than it was on AIF.
- **Reuse verbatim:** this method (§3), the composition laws (§4), the trap
  list (§5), the two boards + x-ray + parity gate (§6), the compat shim, the
  F1–F4 protocol. The only AIG-specific artifacts are its own `theme.css`
  composition content and its own board rows.
- **AIG-specific watch items** (from THEME-REFACTOR-SPEC §4): ~802
  `!important` in the dying AIG CSS (most die with their rules; `theme.css`
  target is zero), the `landing_html` DB pages (audit for inline `<style>`
  before touching landing-base), and the per-page `/assets/css/landing/*.css`
  files (out of scope for the composition layer — separate pass).

---

## 8. THE ONE-PARAGRAPH SUMMARY

The DS plugin owns every component and loads first. The theme ships **one
thin additive `theme.css`** — containers, grids, section rhythm, hero/brand
bands, dark-section bleed, article layout — that sits on top and never touches
a component, never uses `!important`. You get there by **retiring the theme's
`components.css` wholesale** (not deleting duplicates from a file that keeps
fighting), proven by the disable-the-theme-CSS test: the DS already renders
every component, so you add back **only composition, page by page**. Carry the
traps (CRLF churn, the banned `.section-dark *` wildcard, the link-exclusion
chain retired first, box-IS-grid edge ownership, the parked `--bg-alt`
collision, transitions corrupting hover reads), track it in the two NocoDB
boards, and route every DS-vs-theme delta through F1–F4.
