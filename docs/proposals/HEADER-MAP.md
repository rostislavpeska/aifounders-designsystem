# HEADER — the obsessive map (both websites)

**Status: RESOLVED — component SHIPPED 2026-07-06**
([header.md](../components/header.md)). §12 verdicts: 1 `--nav-active`
new role · 2 `--progress-fill` per-brand role · 3 flagged at adoption ·
4 two levels · 5 YES (focus-within + aria) · 6 flat (harvested) · 7 16px
(AIF twin) · 8 YES (canon) · 9 confirmed two modes · 10 YES (optional
slot). This map stays as the harvest record.
Method: full theme-clone harvest (`_harvest\aif-theme`, `_harvest\aiguild`,
two parallel deep-research passes with file:line provenance) + live-DOM
verification of aifounders.cz (home, /en/, /clanky/, article) and
aiguild.cz (home, /clanky/, article, /kurz/ai-designer/). Every value
below is exact and sourced; nothing is interpolated.

---

## 0 · The headline finding

**The two headers are the SAME component already** — one skeleton, one
geometry, one behavior engine, ported 1:1 between themes. The divergences
are: (a) the brand skin (2 hardcoded "dimmed brand" hexes, 2 progress-bar
fills), (b) AIF-only slots (CTA, avatar, language switcher), (c) the
default variant choice (AIF light everywhere / AIG dark everywhere), and
(d) a handful of drift bugs (AIG's broken scroll-lock, one font-size
mismatch). This is the easiest "unification" of the whole census — the
work is definition + slots, not reconciliation.

Shared skeleton (verified in live DOM on every page type, both sites):

```
header.main-header.main-header--light|--dark          fixed, 80px, z-100
  div.main-header__inner                              flex, max-width 1600, pad 0 16
    a.site-logo > img.site-logo__image                ACF option, 21px tall (17 scrolled)
    nav.site-nav.site-nav--desktop                    flat nav-items + optional slots
    nav.site-nav.site-nav--mobile > button.burger-toggle
  div.reading-progress[aria-hidden]                   absolute bottom 0 (articles only)
div.mobile-menu-overlay--light|--dark#mobile-menu-overlay   fixed full-screen, z-99
  div.mobile-menu-content > a.mobile-nav-item …       flat list (+ AIF: divider, CTA, lang row)
```

---

## 1 · Axis: default composition — light / dark

| | AIF | AIG |
|---|---|---|
| Emitted variant | `--light` on EVERY page (header.php:48) | `--dark` on effectively every page — `header-dark.php` sets `$aiguild_header_variant='dark'`; `is_front_page()` forces dark even via plain `get_header()` (header.php:24-25). Light only on archive-kurz, index.php, page-sidebar (all `get_header()` callers) |
| `--light` bg | `--color-primary-support` **#c9f1ff** (components.css:1253) | `--color-primary-support` **#fff3b0** (:1210) — same ROLE, brand value |
| `--dark` bg | `--color-bg-inverse-primary` #05070a (in CSS, never emitted) | `--color-bg-inverse-primary` #070708 (:1209) |
| Logo source | ACF option `hd_logo` (one logo) | ACF `hd_logo` (dark header) / `hd_logo_inverse` (light header) — header.php:47-53 |

Both themes carry BOTH variant CSS blocks — the variant axis is already a
class switch, i.e. **exactly our surface-scope grammar**. The light
header = the brand-support surface, not white.

**Identical core** (byte-level, both `components.css`):
`.main-header` fixed/top 0/100%/**80px**/z-100, transition `height .25s
ease, box-shadow .25s ease, background-color .25s ease` (AIF :1214, AIG
:1165). `__inner` flex space-between, **max-width 1600px**, `padding: 0
var(--spacing-16)` (AIF :1226, AIG :1177; AIF comment says "caps at
1920px" — value says 1600, comment is fiction). Body compensates
`padding-top: 80px` (64 ≤1023) — both page.css.

**Nav item** (identical both): flex, gap 8, `padding: 4px 12px`
(CALIBRATED comment), **Space Grotesk 1rem / weight 500**, height
**36px**, no case/tracking, `transition: height .25s ease`. Hover =
animated underline `::after` — 2px tall, `bottom: 2px`, inset 12px each
side, grows width 0 → `calc(100% − 24px)`, `transition: width .22s
cubic-bezier(.25,.1,.25,1)`, color `--color-primary` (brand). AIG's own
file-header comment cites Figma "18px Inter Regular" — shipped is 16px
SG Medium: **rendered reality wins, Figma comment is stale**.

**Active state — the twin hardcode:** `.nav-item--active::after` keeps
the full-width underline in a *dimmed brand* hex — AIF **#98C3D9**
(components.css:1299, repeated :1498, :1558, and as the mobile divider
:1204), AIG **#63531B** (:1253, :1439, :1499). Same role, two orphan
hexes; on hover both flip to live brand. → token candidate (§10).

**CTA (AIF only):** `.nav-item--cta` — primary-button bg, text-primary,
**700 / 0.875rem**, `padding: 6px 16px`, radius 0, hover
`--color-primary-button-hover`, underline suppressed (:1180-1199).
Logged-in swaps it for `.nav-avatar` — 36px brand circle (initials or
img), shrinks to 30px scrolled, opens the same dropdown panel with 5
account items (:1151-1177; header.php:98-112).

---

## 2 · Axis: mobile transform & behaviour

**Switch point: ≤1023px** (both; components.css `@media (max-width:
1023px)`: desktop nav `display:none`, mobile nav `flex`, header height
**64px**). Both themes ALSO duplicate the same rules at ≤767px in
page.css — redundant dead weight, 1023 wins first. Closed-set friendly ✓.

**The panel** (identical both): `.mobile-menu-overlay` — `position:
fixed; inset 0; padding-top: 64px; z-index: 99` — deliberately UNDER the
z-100 header (the opaque header covers the overlap; design comment in
both files). Open = `.mobile-menu-overlay--open { display: flex }`.
**No animation** — plain display toggle. Variant classes mirror the
header (`--light`/`--dark`, same bg/text pairs).

**Items:** `.mobile-nav-item` — `padding: 24px 24px`, Space Grotesk,
underline grammar (transparent → brand, 2px, offset 10px, .22s cubic).
**DIVERGENCE:** AIF font-size **1rem** (:1481) vs AIG
**`--text-article-size` 1.125rem/18px** (:1424). Active underline = the
dimmed-brand hex (#98C3D9 / #63531B).

**Mobile extras (AIF only):** `.mobile-nav-divider` (2px, #98C3D9,
margin 16/24) → CTA as real `a.btn.btn--primary.btn--md` **with inline
`style="margin: 0 var(--spacing-24)"`** (header.php:191 — violation) →
`.mobile-lang-row` pinned to overlay bottom via `margin-top: auto`.
Logged-in: 4 `.mobile-nav-item` account links, two of which are
**`href="#"` stubs** (header.php:182,184).

**Scroll lock:** AIF `body.menu-open { overflow: hidden }`
(components.css:1362) ✓. **AIG BUG:** menu.js sets `body.menu-open` but
NO CSS rule for it exists anywhere in the theme — the lock is
declared-but-inert; the page scrolls behind the open menu.

**Close affordances** (menu.js, identical logic both): burger toggles;
Escape; click on overlay background (`e.target === overlay`); click any
nav item. `aria-expanded` is set true/false by JS; AIG's button ships
initial `aria-expanded="false"`, **AIF's ships none**.

**Nested items on mobile:** FLAT, always expanded. Walker renders
children inline as `.mobile-nav-item--sub` (arrow-right `::before` mask,
14px, disabled-color); parents get a decorative `.mobile-nav-arrow`
chevron (20px, opacity .5). **No accordion, no back button, no JS.**

---

## 3 · Axis: multi-layer dropdown nav

**Production reality: exactly 2 levels, one panel, no flyouts — and on
the live sites, ZERO nav items with children exist today** (live DOM has
no `.nav-item--has-dropdown` except AIF's language switcher and avatar).
The dropdown grammar is real and shipped; the "multi-layer" part of the
brief is definition territory, not harvest.

The grammar (identical both, components.css):
- Parent: `.nav-item--has-dropdown { position: relative }` — walker emits
  `div.nav-item` wrapping `a` + `span.nav-item-icon` (24px data-URI
  chevron `M6 9L12 15L18 9`, stroke currentColor 1.5; dark header
  override: white at .6 opacity).
- Panel: `.nav-dropdown` — `absolute; top: 100%; right: 0; min-width:
  200px`, bg `--color-bg-inverse-primary` (dark even on light header —
  no wait: light header recolors it to `--color-primary-support`, and
  scrolled recolors again to the scrolled header bg — 3-state panel),
  `z-index: 200`, `box-shadow: --shadow-xl`, **no border, no radius, no
  panel padding, no transition**.
- Open mechanic: **pure CSS `:hover` → `display: flex`**. No JS, no
  click, no `:focus-within`, no `aria-haspopup`/`aria-expanded` —
  **keyboard users cannot open any dropdown on either site**.
- Item: `.nav-dropdown-item` — `padding: 16px 20px`, SG 1rem, nowrap,
  underline grammar (offset 8px). `--active` class is emitted (AIF lang)
  but has **no CSS rule** — inert.
- Depth 2+: walkers flatten everything below depth 0 into the same
  panel; a nested `.nav-dropdown` div would be emitted with no flyout
  CSS. **Two levels is the real ceiling.**

---

## 4 · Axis: language switchers

**AIF-only.** Mechanism = **Polylang** (guarded `pll_*` calls,
header.php:74,124,133-145). Polylang's own menu items (`lang-item`) are
suppressed by the walkers and re-rendered custom:
- Desktop: last nav slot — `div.nav-item--has-dropdown` › current lang
  as `span` ("CZ"/strtoupper slug) + caret + `.nav-dropdown` of
  `a.nav-dropdown-item[lang]` (+ inert `--active`). Pure dropdown-grammar
  reuse; zero switcher-specific CSS.
- Mobile: `.mobile-lang-row` — flex row, gap/padding 24, `margin-top:
  auto` (pinned to overlay bottom); `.mobile-lang-item` underline
  grammar; `--active` #98C3D9.

**AIG: none, anywhere.** No language plugin installed (full plugins dir
listed); theme is Czech-only by design (inc/feed.php:12 "no Polylang
branch"). **But the switcher CSS survives in AIG as orphans** —
`.nav-lang-row`, `.mobile-lang-row`, `.mobile-lang-item` all styled
(components.css:1139,1472-1500), zero markup emits them, and the
link-exclusion chain still lists them. No aiguild↔blue variant links in
any header.

---

## 5 · Axis: menu button (burger)

Identical mechanics both (components.css): 24×24 button, transparent, no
border, `color` per variant; SVG 3 paths, `stroke: currentColor; 1.5;
round`. Open → "perfect cross": top `translateY(7px) rotate(45deg)`,
mid `opacity 0`, bottom `translateY(-7px) rotate(-45deg)`; origins
`12px 5px / 12px 12px / 12px 19px`; `transition: transform .3s
cubic-bezier(.4,0,.2,1), opacity .3s ease`.

Micro-drift: path data AIF `M4 5h16` (stroke attrs in CSS) vs AIG
`M3 5h18` (stroke attrs inline in SVG) — same 24-box, AIG lines 2px
wider. One of the two dies at adoption.

---

## 6 · Axis: reading progress bar

**Ported 1:1 between themes** (AIG's own CSS comment says so). Anatomy:
`div.reading-progress[aria-hidden]` INSIDE the header, `position:
absolute; bottom: 0; height: 2px; width: 100%`; fill = `::after` with
`transform: scaleX(var(--reading-progress, 0)); transform-origin: left`
(GPU, no reflow).

**Dual engine, identical both:**
1. Modern: `@supports (animation-timeline: scroll())` → pure-CSS
   `animation-timeline: scroll(root)`, keyframes scaleX 0→1 — zero JS.
2. Fallback: rAF-throttled passive scroll listener writing
   `--reading-progress` = `scrollTop / (scrollHeight − innerHeight)`.

**Gating — identical behavior, different plumbing:** visible ONLY on
single posts AND only once the header is `--scrolled` (CSS gate
`.single-post .main-header--scrolled .reading-progress`). AIF renders
the div on every page (CSS hides it); AIG additionally gates markup with
`is_singular('post')` and enqueues the JS on posts only. Progress basis
= whole document, not article extent (both).

**The one real divergence — the fill color:**
| | track | fill |
|---|---|---|
| AIF | `--color-bg-tertiary` #eef2f6 | `--color-quaternary-core` **#a0c92f** (lime) |
| AIG | `--color-border-default` #e5e5e5 | `--color-tertiary-core` **#d72da4** (magenta — comment says "purple", value says magenta) |

Different accent RANKS too (quaternary vs tertiary) — needs a ruling.

---

## 7 · Axis: sticky / non-sticky + the sticky transform

**Default: always-fixed + SHRINK (never hides).** menu.js, identical
engine both ("à la davidkoci.cz" comment): threshold **50px**,
boolean-guarded toggle of `.main-header--scrolled`, passive listener,
initial call on load. No hide-on-scroll-down anywhere, no
IntersectionObserver.

**THE SHRINK** (all animated by the base .25s ease transitions):
| property | rest | scrolled |
|---|---|---|
| header height | 80px (64 mobile) | **56px** (52 mobile) |
| shadow | none | `0 0 60px 0 rgba(7,7,8,.2)` AIF / **`.3`** AIG |
| bg (light) | primary-support | `--color-bg-secondary` |
| bg (dark, AIG) | #070708 | `--color-bg-inverse-tertiary` #1c1c1c |
| nav-item height | 36px | 34px (comment claims "48→34" — fiction) |
| logo height | 21px | 17px |
| avatar (AIF) | 36px / .875rem | 30px / .75rem |
| dropdown panel bg | variant bg | follows scrolled bg |
| progress bar | hidden | **visible** (articles) |

**Non-sticky exceptions** (both themes carve out hero-overlap pages):
- AIF: `body.aif-landing` → header `position: relative`, body
  padding-top 0 (scrolls away with the page).
- AIG: `body.single-kurz, body.aiguild-landing` → header `position:
  absolute` (sits over the hero, scrolls away) AND menu.js **bails
  entirely** (no shrink class ever). Anchor offsets zeroed accordingly.

So production has TWO header modes: **fixed+shrink** (default) and
**overlay+scroll-away** (landing/course-hero pages).

---

## 8 · Twin-diff summary

**Byte-identical (the component):** skeleton; 80/64 geometry; z-ladder
100/99/200; nav-item bundle + underline grammar (.22s cubic); dropdown
grammar; burger mechanics (.3s cubic); overlay mechanics; shrink engine
(50px, class, transitions); progress-bar engine (dual-path); back-to-top
anchor `#top`; ACF logo option; max-width 1600.

**Brand-valued (same role, different value):** light bg (support
blue/yellow); dimmed-active hex (#98C3D9/#63531B); shrink shadow alpha
(.2/.3); progress fill (lime quaternary / magenta tertiary); progress
track (bg-tertiary / border-default).

**Structural (slots):** AIF adds CTA / avatar-account dropdown /
language switcher / mobile divider+CTA+lang-row. AIG adds nothing.

**Drift bugs:** AIG scroll-lock inert (no `.menu-open` CSS); AIF burger
missing initial `aria-expanded`; AIF logged-in mobile `href="#"` stubs;
mobile item font 16 vs 18; burger path 16 vs 18 wide; AIG menu bound by
NAME `'Main menu CZ'` bypassing its registered `main-menu` location.

---

## 9 · A11y gaps (both sites, shipped today)

1. Desktop dropdowns are hover-only — no keyboard path at all (no
   `:focus-within`, no click, no aria). Lang switching on AIF is
   mouse-only.
2. No `aria-haspopup`/`aria-expanded` on dropdown parents.
3. No focus trap in the mobile overlay.
4. `--active` on dropdown items is visually inert.

## 10 · Hardcode / fiction inventory (token-mapping candidates)

- **#98C3D9** (AIF ×4: active underline desktop/mobile/lang + divider) /
  **#63531B** (AIG ×3) — the "dimmed brand active" role. Candidate:
  semantic role or `color-mix(in srgb, var(--brand) X%, var(--bg))`.
- `rgba(7,7,8,.2)` / `rgba(7,7,8,.3)` — scrolled shadow (near-twin).
- Heights 80/64/56/52 (header) · 36/34 (item) · 21/17 (logo) · 30/36
  (avatar) — component calibrations, need named knobs.
- Threshold 50px; underline `.22s cubic-bezier(.25,.1,.25,1)`; burger
  `.3s cubic-bezier(.4,0,.2,1)`; base `.25s ease` — motion constants.
- `#fff` avatar text (AIF); inline mobile-CTA margin (AIF header.php:191).
- Fictions: "caps at 1920px" (=1600), "48px → 34px" (=36), Figma "18px
  Inter" nav (=16 SG), AIG "purple" fill (=magenta #d72da4).

## 11 · Dead code (both, unless noted)

`.mobile-menu-close` (styled + JS-wired, never rendered) ·
`.nav-lang-row` · AIG's whole `.mobile-lang-row`/`.mobile-lang-item`
block (orphaned AIF inheritance) · AIF `.main-header--mobile` ·
`.burger-preview` (styleguide demo) · redundant 767px media blocks ·
AIF unenqueued `*.starter-pack.*` CSS backups.

## 12 · OPEN VERDICTS (operator) — before any build

1. **Dimmed-active color** — mint a semantic role (e.g. `--nav-active`),
   or derive via `color-mix(brand, surface)`? (Kills #98C3D9/#63531B.)
2. **Progress-bar fill** — keep per-brand accents (lime vs magenta), and
   if so which RANK (AIF quaternary vs AIG tertiary is inconsistent)?
   Or unify on one role?
3. **Default variants** — confirm: AIF header = light (brand-support
   surface) everywhere; AIG = dark everywhere incl. light pages? (AIG's
   3 light-header templates look accidental — archive-kurz/index/
   page-sidebar.)
4. **Multi-layer dropdown** — production ceiling is 2 levels and zero
   real submenus exist today. Define the DS dropdown at 2 levels (panel
   only, no flyouts) — or do you want true multi-level flyouts designed?
5. **Keyboard/a11y upgrade** — dropdowns get focus/click+aria support at
   distillation (deviation from production, GM)? Recommended.
6. **Mobile nesting** — keep production's flat always-expanded list, or
   accordion?
7. **Mobile item size** — 16 (AIF) vs 18 (AIG): which twin wins?
8. **Scroll-lock** — adopt AIF's `body.menu-open { overflow: hidden }`
   as canon (fixes AIG's inert lock)?
9. **Sticky modes** — confirm the two-mode model: fixed+shrink default,
   overlay+scroll-away for hero/landing pages (opt-out class), never
   hide-on-scroll.
10. **Language switcher** — define as an optional header slot (dropdown
    grammar) so AIG can adopt it later, with the mobile row pinned to
    overlay bottom?
