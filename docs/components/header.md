# Header

**Type:** site chrome component · **Status:** shipped · **git_path:** `assets/css/components.css#header` + `js/components/menu.js` · **Specimen:** `/?aifds_styleguide=1&item=header`

## Intent

THE SITE CHROME, distilled from **byte-identical twins**
([HEADER-MAP](../proposals/HEADER-MAP.md)): both production themes ship one
skeleton, one shrink engine, one dropdown grammar, one burger, one
dual-path progress bar — the DS keeps that single component and defines
the **anatomy (slots), not the content**. Nav links, CTAs, language rows
are all consumer-filled slots.

## Anatomy

```html
<header class="main-header surface-support">        <!-- or .section-dark; --overlay for hero pages -->
  <div class="main-header__inner">
    <a class="site-logo" href="/"><img class="site-logo__image" …></a>   <!-- or .logo-placeholder -->
    <nav class="site-nav site-nav--desktop" aria-label="Main navigation">
      <a class="nav-item [nav-item--active]" href="…">Item</a> …
      <a class="btn btn--primary" href="…">Action slot</a>               <!-- optional -->
      <div class="nav-item nav-item--has-dropdown">                      <!-- optional; 2 levels MAX -->
        <button class="nav-item__trigger">CZ <span class="nav-item-icon">…chevron…</span></button>
        <div class="nav-dropdown">
          <a class="nav-dropdown-item [nav-dropdown-item--active]" …>…</a> …
        </div>
      </div>
    </nav>
    <nav class="site-nav site-nav--mobile" aria-label="Mobile navigation">
      <button class="burger-toggle" aria-label="Menu" aria-expanded="false">
        <svg viewBox="0 0 24 24"><path class="line-top" d="M3 5h18"/>
          <path class="line-mid" d="M3 12h18"/><path class="line-bot" d="M3 19h18"/></svg>
      </button>
    </nav>
  </div>
  <div class="reading-progress" aria-hidden="true"></div>  <!-- consumer renders on article pages -->
</header>
<div class="mobile-menu-overlay surface-support" id="mobile-menu-overlay">  <!-- scope mirrors header -->
  <div class="mobile-menu-content">
    <a class="mobile-nav-item [--active]" …>Item</a>
    <a class="mobile-nav-item mobile-nav-item--sub" …>…</a>   <!-- flat, always expanded -->
    <div class="mobile-nav-divider"></div>                    <!-- optional -->
    <a class="btn btn--primary btn--md" …>Action slot</a>     <!-- optional; margins are CSS, not inline -->
    <div class="mobile-lang-row">                              <!-- optional; pinned to overlay bottom -->
      <a class="mobile-lang-item [--active]" lang="cs">CZ</a> …
    </div>
  </div>
</div>
```

## Surfaces replace variants

Production's `--light`/`--dark` modifiers **DIE**. The header and overlay
are surface-riding: `.surface-support` = the light brand band (support
blue/yellow — exact harvested values), `.section-dark` = the dark chrome.
Rest bg = `--bg`; scrolled bg = **`--bg-base` (white) on light scopes /
`--raised` on dark** (operator ruling; AIG #1c1c1c = dark raised
exactly); text/controls ride roles.

## The two modes (harvested)

- **Default: fixed + THE SHRINK.** `position: fixed`, body compensates
  `padding-top: var(--header-height)`. Engine (`menu.js`): 50px threshold
  (CALIBRATED) toggles `.main-header--scrolled` → height 80→56 (64→52
  mobile); scrolled surface = **WHITE on light scopes** (`--bg-base`, the
  pop-to-page-surface role — operator ruling, supersedes the raised GM)
  and **`--raised` on dark** (the harvested #1c1c1c lift); `--shadow-xl`
  on BOTH (GM from the harvested `0 0 60px rgba(7,7,8,.2/.3)` glow); nav
  rung 36→34, logo 21→17, the dropdown panel follows the scrolled
  surface. Never hides on scroll.
- **`--overlay`:** hero pages (AIG kurz/landing, AIF landing precedent) —
  `position: absolute`, scrolls away, the engine bails.

## Component knobs

Declared on BOTH `.main-header` and `.mobile-menu-overlay` (siblings):
`--header-height` 80 (64 ≤1023) · `--header-height-scrolled` 56 (52) ·
`--header-control` 36 — the header rung. The overlay's `padding-top`
auto-syncs to the knob (production hardcoded 64).

## The header rung (bar precedent)

`.main-header .btn` composes any DS button at the rung: `min-height:
var(--header-control)`, `padding: 0 16`, `font-size: --caption-size`.
Production's bespoke `.nav-item--cta` **dies** — the action slot is a
real button.

## Nav grammar

`.nav-item` — Space Grotesk (`--font-accent`) 16 (`--body-md-size`) /
medium, 36px rung, padding 4/12 (CALIBRATED). The underline mechanism:
`::after` 2px (`--stroke-2`), inset 12px, grows on hover in `--brand`
(`.22s cubic-bezier(.25,.1,.25,1)` CALIBRATED). **Active = persistent
`--nav-active`** (the NEW role — dimmed brand; kills the #98C3D9/#63531B
hardcodes ×7), waking to live `--brand` on hover.

## Dropdown axis

2 levels MAX (the harvested ceiling; no flyouts). Panel: `top: 100%;
right: 0; min-width: 200px` (CALIBRATED), bg = scope `--bg` (scrolled:
follows the scrolled surface — `--bg-base` light / `--raised` dark),
`--shadow-xl`, no border/radius/transition (harvested).
Opens on **hover** (production) + **`:focus-within`** (a11y GM — the
mapped keyboard gap, fixed) + **`.nav-item--open`** (engine toggle for
button triggers, with `aria-haspopup`/`aria-expanded`). Non-link triggers
are `<button class="nav-item__trigger">`. `.nav-dropdown-item--active`
got a real rule (`--nav-active` underline — production emitted it inert).

## Burger

24px (`--icon-size-default`) button, currentColor, stroke `--stroke-1_5`
(stepped-stroke law). The perfect cross: top/bottom translate ±7px +
rotate ±45°, mid fades; `.3s cubic-bezier(.4,0,.2,1)` (CALIBRATED).
AIG's wider path (`M3 5h18`) wins over AIF's `M4 5h16` (twin drift).

## Mobile overlay

≤1023 (harvested switch; the duplicate 767 blocks were dead weight — not
ported). Full-screen fixed at **z-99, deliberately UNDER the z-100
header** (the opaque header covers the overlap — harvested design).
Plain display toggle (no animation, harvested). Items: SG **16 /
medium** (`--body-md-size` + `--weight-medium` — operator rulings: AIF's
16 wins; SAME weight as desktop), **primary text color only**, underline
grammar, `--active` = `--nav-active`. Nesting stays **flat, always
expanded** (`--sub` with the arrow prefix; the harvested parent chevron
DIED — with no accordion it signaled an interaction that doesn't exist,
operator). Divider = `--nav-active` 2px. The action slot's harvested inline margin
violation is homed in CSS (`.mobile-menu-content > .btn`). Lang row pins
to the overlay bottom (`margin-top: auto`). **Scroll-lock is canon**:
`body.menu-open { overflow: hidden }` (AIG's was declared-but-inert —
mapped bug, fixed).

## Reading progress

`.reading-progress` — 2px at the header's bottom edge. **An AXIS, not
part of the scrolled state** (operator): ONLY article pages render the
element (production's `body.single-post` CSS coupling dies — the consumer
decides by rendering it); every other page shrinks bare. Visible only in
the scrolled state. Track =
`--border` (GM from bg-tertiary/border-default). Fill = **`--progress-fill`**
(NEW role — per-brand by operator ruling: AIF lime / AIG magenta, both
values already lived in both palettes; the palette gains the `progress`
name). Dual engine (harvested): `animation-timeline: scroll(root)`
zero-JS where supported; rAF-throttled `--reading-progress` custom-prop
fallback in `menu.js`. Whole-document basis (harvested).

## Engine (`js/components/menu.js`)

Shrink (50px, boolean-guarded, passive, bails on `--overlay`) · mobile
menu (burger ↔ overlay, `aria-expanded`, Escape / backdrop / nav-away
close, scroll-lock page-mode only) · dropdown toggle (button triggers
only — link triggers ride hover/:focus-within) · progress fallback.
Demo-aware: inside `.header-demo` frames it binds locally and never
touches body state.

## Tokens referenced

`--bg` `--raised` `--text` `--text-secondary` `--brand` **`--nav-active`**
**`--progress-fill`** `--border` `--shadow-xl` `--font-accent`
`--body-md-size` `--caption-size` `--weight-medium` `--stroke-1_5`
`--stroke-2` `--icon-size-default` `--spacing-4/8/12/16/24`

New (operator 2026-07-06): palette `brand-muted` (#98C3D9 AIF / #63531B
AIG) + palette `progress` (#a0c92f / #d72da4) + semantic `nav-active` +
`progress-fill`. 64 palette names/brand, 51 roles.

## Known friction / adoption

- Markup swaps at adoption: `--light`→`.surface-support`,
  `--dark`→`.section-dark`, `.nav-item--cta`→`.btn .btn--primary`,
  hardcodes→roles; the overlay scope class mirrors the header's.
- AIG's 3 light-header templates (archive-kurz/index/page-sidebar) looked
  accidental in the map — flag at adoption.
- AIF logged-in mobile `href="#"` stubs are THEME bugs, not DS.
- Avatar/account = the actions slot + dropdown grammar (avatar sizing
  36→30 scrolled stays a theme concern until the account header ships).
- Dead code NOT ported: `.mobile-menu-close`, `.nav-lang-row`,
  `.main-header--mobile`, 767px duplicates, `.burger-preview`.
- No focus trap in the overlay (tracked; Escape + lock shipped).
- AIG menus bind by NAME (`'Main menu CZ'`) bypassing the registered
  location — theme fix at adoption.
