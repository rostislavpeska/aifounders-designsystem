# ADOPTION-PLAYBOOK — how to migrate a theme onto the DS (learned on AIF, reused for AIG)

**Status: PLAYBOOK 2026-07-07.** Written mid-AIF-adoption after the
CSS-deletion approach proved structurally insufficient. This is the method
that actually works, so the AIG migration doesn't repeat the AIF mistakes.

## THE CENTRAL INSIGHT (the thing we got wrong first)

The DS was **distilled FROM the production themes**, so DS components share
the themes' **DOM structure** — but the DS gave them **new canonical class
names**. The theme markup still carries its **own BEM class names**.

Consequence: **deleting theme CSS only migrates components whose markup
already uses DS class names.** For AIF that was the accidental minority —
buttons (`.btn`), badges (`.badge`), prose elements (`main h2/p/blockquote`),
because those names coincide. For everything else the DS component is
**dormant**: no markup references it, so deleting the theme CSS just
*unstyles* the element — it does not adopt the DS.

**Proof (AIF preview card):** identical nesting, different names.

| theme markup | DS component | delta |
|---|---|---|
| `article.article` | `article.preview-card` | name |
| `.article__image-wrapper` | `.preview-card__photo` | name |
| `.article__content` | `.preview-card__content` | name |
| `.article__title` (`h3`>`a`) | `.preview-card__headline` (`h3`>`a.card-title-link`) | name + add link class |
| `.article__meta` / `.article__meta-bar` | `.preview-card__meta` | 2 names collapse to 1 |
| `.article__badges` | `.preview-card__badges` | name |
| `.article__excerpt` / `.article__description` | `.preview-card__text` | 2 names collapse to 1 |
| `.article__button` (`a.btn`) | `.preview-card__actions` > `a.btn` | name + wrapper |
| `.article--archive/--editorial/--has-image/--no-image` | (gone) `--condensed` only | variant collapse |

~7 renames + 1 wrapper + a variant collapse. Mechanical, not design work.

## THE THREE COMPONENT CLASSES (triage every component into one)

1. **NAME-MATCHED** — theme markup already uses the DS entry class
   (buttons, badges, prose, and on AIF: persona-card, comments, engagement,
   sticky, header, footer, modal, avatar, breadcrumbs). → **Delete the theme
   CSS**; the DS (loaded first) takes over. This is the original sweep model
   and it works here.
2. **NAME-MISMATCHED, STRUCTURE-ALIGNED** — theme markup uses its own BEM,
   DS structure matches (AIF: preview-card, course-card, reference-card,
   and the record/table/nav-tabs/info-box shortcode families). → **Rename
   the markup to DS classes** (class-map + sweep), THEN delete theme CSS.
3. **THIRD-PARTY MARKUP (can't rename)** — the HTML is emitted by a plugin
   whose classes we don't control (Fluent Forms `.ff-el-*`, MC4WP, WP core).
   → **Bucket-3 override**: write override CSS that TARGETS the plugin's
   own classes but is **derived 1:1 from the DS** — same tokens, same
   component recipe, visual parity, just reached via the foreign selectors.
   NOT "leave as theme": the override is REBUILT from DS tokens/components so
   it matches the DS field/button/label exactly (operator ruling 2026-07-07:
   "FF overrides will need new styles but styles derived from the DS
   components — 1:1 visual only using FF classes"). Keep the file loaded LAST
   (Law 1). This is how forms that can't adopt DS markup still look DS.
4. **STAYS (genuinely theme territory)** — lightbox, video embed, `.hero-card`
   (deferred), chatbot, one-off page layout. → **Leave it.** Mark 🟦 in the
   x-ray so "still theme" doesn't read as "TODO".

Audit which bucket each component is in FIRST (grep the templates for the DS
entry class; 0 hits + DS CSS present = bucket 2 or 3, decide by intent).

## THE GLOBAL-RULE TRAP (bit us on the card pilot — watch for it on AIG)

When you rename a component's markup to DS classes, the theme's **global
rules** may still capture it because they don't know the DS utility classes.
Concrete case: after the preview-card markup gained `.card-title-link`, the
card titles rendered **blue + underlined** instead of black. Cause: the
theme's global link chain (`main a:not(.btn):not(.badge)…`, deferred to its
own sweep) did **not** exclude `.card-title-link`, so it out-specified the DS
`.card-title-link { color: inherit }`. Fix: add
`:not(.card-title-link):not(.card-image-link)` to the theme link chain (the
DS chain already excludes them) — a small, correct step of the link-system
migration that unblocks the card pilot. **General rule: after a rename,
grep the theme's global/exclusion selectors (link chains, `main *`, reset
lists) for the DS classes you just introduced and add the exclusions, or
migrate that global system.**

## THE MIGRATION RECIPE (per component, bucket 2)

1. **Map** the theme classes → DS classes (slots map in DOM order; note
   variant collapses + structural nuances like added wrappers/link classes).
2. **Sweep the markup**: rename in the PHP template partials AND any JS that
   queries those classes (check first: `grep querySelector/classList`). Edit
   PHP variant logic by hand where a class string is computed.
3. **Structural nuances** (enumerable): add the DS wrappers/link classes the
   template lacks (e.g. `.preview-card__actions`, `.card-title-link`).
4. **Delete the theme component CSS** wholesale — now dead.
5. **Gate** (parity: should be ~pixel-identical since the DS was distilled
   from this markup; real deltas = DS canon, classify F1–F4 per
   [ADOPTION-FEEDBACK](ADOPTION-FEEDBACK.md)).
6. **X-ray** the page — the component must flip 🟩.

## OBSERVABILITY (build this DAY ONE next time)

The DS looks almost identical to the theme (it was distilled from it), so
**appearance cannot tell you what is migrated.** Two ground-truth tools —
both live in the theme repo's `theme-parity/`, driven by the DS repo's
`build/`:

- **`ds-xray.js`** (+ bookmarklet) — paste in the console on any page;
  outlines every component by its TRUE CSS source read from
  `document.styleSheets`: 🟥 theme base (old) · 🟧 DS base + theme
  composition override · 🟦 stays-by-design · 🟩 fully DS. Per-family tally
  panel. Alt-click logs the winning rules. THIS is how the operator audits.
- **`build/adoption-coverage.mjs`** → `COVERAGE.md` — greps the theme CSS
  for DS ledger classes; per-family base-rule (kill-target) vs contextual
  (composition) counts + shim/legacy-token burn-down meters. The birds-eye.

Plus the **parity gate** (`build/theme-parity.mjs`, screenshots + DOM
fingerprints + HTTP/console/overflow/axe/debug-log ratchets, `transition:none`
at capture) and the **compat-tokens shim** (`build/compat-shim.mjs`, old→new
token aliases so surviving theme CSS resolves during the transition).

## AIG APPLICABILITY (read before starting AIG)

- Same DS → AIG will hit the **same three buckets**. Do the template-class
  audit FIRST; do not trust "AIG markup is already DS-native" (that belief,
  held for AIF, was false for every card family). VERIFY per component.
- Reuse verbatim: the x-ray, coverage tracker, parity gate, compat shim, and
  this recipe. The only AIG-specific artifact is its own **class-map**.
- AIG's theme classes differ from AIF's, so its map is separate — but the
  DS target classes are identical, so the mapping *method* and the DS-side
  tooling carry over unchanged.

## HEADER PILOT LESSONS (bucket-1 name-matched, two extra traps)

- **Surface variants → `.surface-*` scopes.** The theme header used
  `.main-header--light/--dark` for its background tint; the DS header rides
  `var(--bg)` (white by default). To preserve the AIF pale-blue faithfully,
  the markup rides `.surface-support` (sets `--bg` = support) instead of the
  dead `--light` class. General rule: theme `--light/--dark/--tint` chrome
  variants map to DS surface scope classes, not to DS component variants.
- **Container-icon vs background-icon.** The theme drew the nav chevron as a
  CSS `background-image` on an empty `<span class="nav-item-icon">`. The DS
  `.nav-item-icon` is a flex CONTAINER expecting a real icon child — so the
  empty span rendered 0×0 (chevron vanished). Fix: put a real icon in the
  markup (`aifds_icon('chevron-down')`). Watch for this wherever the theme
  faked an icon with a background image.
- **Subject-based CSS deletion needs a `:not()` guard.** Deleting theme rules
  whose KEY compound is a DS class works, BUT `:not(.kept-class)` in a
  selector fooled the naive parser into keeping DS rules (e.g.
  `.nav-item:hover:not(.nav-item--cta)` looked like a `--cta` rule). Strip
  `:not(...)` args before extracting the subject, or sweep the few leftovers
  by hand afterward (grep the DS subjects post-deletion).

## SYSTEMATIC PASS LESSONS (the operator's "map → test → fix → track" loop)

- **"partial" (🟧) in the x-ray = theme CSS leaking over a name-matched DS
  component.** persona-card + engagement both rendered wrong (persona had a
  stray `border-bottom` divider the DS doesn't have; DS uses `border-left`)
  purely because the theme's own `.persona-card`/`.aif-engagement` rules load
  after the DS and win. Fix = delete the theme component CSS (bucket 1). The
  x-ray's amber is the tell; chase every amber to zero.
- **Container-query components need their slot wrapper in the markup.** The DS
  persona-card derives orientation from `.persona-card-slot` (`@container
  persona ≥560`); `.persona-card--horizontal` is a NO-OP alias. Deleting the
  theme CSS (which forced horizontal) makes the card go vertical unless the
  markup wraps it in `.persona-card-slot`. Same shape will hit course-card
  (`@container course-card ≥720`). Add the slot wrapper.
- **Integration boundaries stay theme.** Engagement's AddToAny share buttons
  (`.a2a_kit` inside `.aif-engagement-toast`) are a documented DS boundary —
  the theme wires the 3rd-party widget; the DS doesn't own it. Delete the
  engagement component CSS but KEEP the `.a2a_kit` integration rules.
- **The x-ray must be map-driven to see 3rd-party markup.** v1 only found
  DS-classed elements, so Fluent Forms + author-tabs were invisible on the
  author portal (operator: "most components not even detected"). v2 reads
  `component-map.json` (DS ∪ theme ∪ ff selectors per component) so nothing
  is invisible. Build this for AIG on day one — its author portal has the
  same Fluent Forms.
- **Composition stays theme, component becomes DS.** The homepage benefits
  "bleeding container" (`.aif-dark-section__blurbs`: bleeds below the band,
  dark-2 bg, inset dividers) is a page-specific layout — KEEP it, just put DS
  `.blurb` components inside (re-point its `> .dark-blurb` child selectors to
  `> .blurb`). Don't replace a bespoke container with a generic DS one.

## LESSONS (chronological, AIF)

- CSS-delete migrated only name-matched components → **marginal visible
  result**; the operator correctly called it a near-failure. Fixed by
  reframing as **class-mapping migration** (this doc).
- Split a unified unit once (perex migrated, blockquote didn't → visible
  seam). **Sweep by coherent visual unit**, not by grep convenience.
- Box-sizing F2: the DS `.btn/.badge` omitted `box-sizing` (theme reset had
  masked it) → surfaced only when the theme reset was removed. Adoption
  pressure finds real DS bugs; the specimen-first bug ritual absorbed it.
- Observability was missing until the operator demanded it three times.
  **Build the x-ray on day one for AIG.**
