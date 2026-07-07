# Footer

**Type:** site chrome component · **Status:** shipped · **git_path:** `assets/css/components.css#footer` · **Specimen:** `/?aifds_styleguide=1&item=footer`

## Intent

THE CLOSING CHROME, distilled from **byte-identical twins**
([FOOTER-MAP](../proposals/FOOTER-MAP.md)): a stack of BANDS, every one
optional except legal. The DS defines the anatomy — all content (links,
logos, socials, legal) is consumer-filled (production: ACF options, zero
nav menus). No JS.

## Anatomy — the band stack

```html
<footer class="footer [footer--chatbot-clear] section-dark">  <!-- the shell paints NOTHING -->
  <section class="footer__newsletter-section" aria-label="Newsletter">  <!-- OPTIONAL; dark-2 -->
    <div class="footer__newsletter-row">
      <p class="footer__newsletter-headline">…pitch…</p>
      <div class="footer__newsletter-form-wrap">…the shipped aif-ecomail-form--footer-dark
        (incl. its STANDARD .mc4wp-consent-note slot — operator 2026-07-07)…</div>
    </div>
  </section>
  <div class="footer__inner">
    <div class="footer__partners">                             <!-- OPTIONAL -->
      <p class="footer__label">…</p>
      <div class="footer__partners-grid">…44px logos…</div>
    </div>
    <div class="footer__divider"></div>                        <!-- only when partners shown -->
    <div class="stack-grid" style="--stack-cols: 3;">           <!-- columns = THE CANON -->
      <div class="blurb"><p class="blurb__eyebrow">…</p><p class="blurb__text blurb__text--sm">…</p></div>
      <div class="blurb"><p class="blurb__eyebrow">…</p>
        <a class="footer__subtle-link" href="…"><span>Label</span></a> …</div>
    </div>
    <div class="footer__bottom-bar">
      <a class="footer__logo-link" href="#top">…16px logo…</a>
      <div class="footer__social-icons"><a class="footer__social-link" aria-label="…">…16px icon…</a>…</div>
    </div>
    <div class="footer__legal-section">
      <div class="footer__divider"></div>
      <p class="footer__legal">©… | <a class="footer__legal-link" …>…</a> …</p>
    </div>
  </div>
</footer>
```

## Surface

The shell paints nothing — **markup carries `.footer.section-dark`**
(live on both production sites; the dark-1 `.footer` scope alias was
DROPPED, operator 2026-07-06, persona precedent — a bare `.footer` stays
on its host surface). The newsletter band's class is a **dark-2 scope
selector** (operator: dark-2 LIVES) — its `--bg` (dark-900) IS the band;
the surface change is the only separator, per harvest.

## Knobs & the chatbot axis

`--footer-pad-top` 80 → 48 (≤1023) → 40 (≤767) ·
`--footer-pad-bottom` 24 → 24 → 16. **`--chatbot-clear`** (sticky-bar
precedent): bottom 56 desktop / 80 mobile — AIG's harvested bubble
clearance, opt-in. The newsletter band's negative pull is
`calc(-1 * var(--footer-pad-top))` — **knob-synced at every breakpoint**
(production hardcoded −120/−48/−40 three times).

## The bands

- **Newsletter (optional):** full-bleed dark-2 stripe, pad 48/24 (40
  sides ≥768), `margin-bottom: 80`; row re-caps at `--container-max`;
  headline = `--font-accent` / `--lead-size` (24→20 token-stepped) /
  bold / `--leading-heading` (GM from 1.28) / `--text` (kills the
  harvested #fffdf6 hardcode). Hosts the SHIPPED capture composition
  (`aif-ecomail-form--footer-dark`, submit = `.btn--md .btn--tertiary`
  per the standing ruling). AIG renders it; AIF's capture stays page
  content (the blue `.newsletter-cta` = its own future distillation
  row — operator). Production's landing kill-switch stays theme-side.
- **Partners (optional):** centered mono label + logo row. Production's
  hacks DIED: one `opacity: 0.5` (was .5×.5=.25) and uniform **44px**
  logo heights (was nonstandard `zoom: .5`); gap 80 (GM from raw
  120×100), 40×56 tablet/mobile.
- **Columns = THE CANON:** `.stack-grid` (footer rhythm `gap: 80`;
  `--stack-cols: 2` at 768–1023; 40 mobile) › `.blurb` — eyebrow +
  `__text--sm` (contact) or a `.footer__subtle-link` stack.
  **Footer eyebrow voice (operator): QUATERNARY** — palette-direct
  `var(--dark-400)` GM (harvested inverse-quaternary, exact both brands;
  highlight-chip precedent — no light quaternary exists to mint a role
  from) + `--leading-body` (titles wrap; the atom is leading-none).
  `.footer-blurb/__title/__body/__description` DIE at adoption.
- **Bottom bar:** 16px logo (CALIBRATED chrome pair) in `#top` back-to-top
  (instant jump — no smooth scroll in production); 16px social icons,
  `gap: 16` (GM from raw 20), **QUATERNARY at rest** (`--dark-400`
  palette-direct like the eyebrows — operator; browser-verified: live
  production renders the white-fill imgs at ~rgb(106,106,106)) waking to
  `--text` on hover — the harvested opacity fade died with the img era.
  DS icons ride currentColor; production's uploaded imgs still compose
  (`.footer__social-icon` sizes them without the harvested `!important`).
- **Legal:** THE divider — 2px `--raised` (dark-850 = the harvested
  divider color verbatim, both brands) + right-aligned caption in
  `--text-tertiary`; `.footer__legal-link` underlined, hover =
  `--text-secondary` **with the underline KEPT** (production removed
  it — the link law outlaws that; GM). The `!important` pair died — the
  link-exclusion chains (both `main` and `.section-dark`) now know all
  four footer idioms.

## The arrow-link idiom (operator-blessed)

`.footer__subtle-link` — Inter `--caption-size`, `--leading-heading`
(1.35 value-exact), `--link` → `--link-hover`; the `→ ` prefix
(`::before`) never underlines, the `<span>` label does.

## Tokens referenced

`--bg` `--text` `--text-secondary` `--text-tertiary` `--link`
`--link-hover` `--raised` `--font-primary` `--font-accent` `--font-mono`
`--caption-size` `--lead-size` `--meta-size` `--weight-bold`
`--leading-none` `--leading-body` `--leading-heading` `--tracking-label`
`--case-upper` `--stroke-2` `--transition-normal` `--container-max`
`--spacing-8…80` + GM palette-direct `--dark-400` (×3, commented).
0 new tokens.

## Known friction / adoption

- Markup swaps: `.footer > .container` → `.footer__inner`;
  `footer-blurb*` → the canon; AIG's `btn--lg btn--primary-inverted`
  submit → `btn--md btn--tertiary`; the `!important`s and hacks die.
- The scope-map drop means any theme footer NOT carrying `.section-dark`
  renders light — both live sites already carry it.
- AIG footer types (default/requal) = ACF data only; ONE DS footer.
- Faux-bold mono (Spline Sans Mono 700 synthesized — the loadout has
  400;500 only, both themes): extend the font enqueue at adoption.
- `footer-menu` location (AIG) stays orphaned — content is ACF by design.
- AIF's `.newsletter-cta` blue band: tracked as its own distillation row.
