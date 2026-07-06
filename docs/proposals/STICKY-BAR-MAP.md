# STICKY BAR — the obsessive map (map-only, operator-ordered 2026-07-05)

**Status: RESOLVED 2026-07-06 — SHIPPED as `.sticky-bar`**
([sticky-bar.md](../components/sticky-bar.md)). Verdicts: one primitive ×
type compositions (email / button / save) ✓; **the SAMPLER survives as the
GENERAL surface mechanism** (operator loves it — it toggles `.section-dark`
on the bar; sections need no registration) ✓; slim metrics + quiet 1px edge
= canon (both were already operator-ruled in production comments) ✓;
`--chatbot-clear` axis ✓; email→button mobile transform ✓; `--shadow-up`
token approved ✓. Remaining operator action: the /newsletter landing
`landing_html` DB edit at adoption. The census below stays as the harvest
record.

## 1 · Census — SIX instances, THREE visibility engines, FOUR skins

| # | Instance | Brand | Markup lives in | CSS | Engine | Skin |
|---|---|---|---|---|---|---|
| 1 | **/newsletter landing `.lp-sticky`** — THE ORIGINAL | AIF | **HARDCODED in the page's ACF `landing_html` (DB, not PHP)** — verified on the live container | `landing/newsletter.css:717–860` | inline `<script>` in the same `landing_html`: **page-geometry** — show at `scrollY > 0.8×viewport`, hide at `scrollY > docHeight − 1.5×viewport` | DARK: `--lp-ink` fill, **2px accent-blue top stripe**, shadow `.25`, 48px controls, `10px/32` padding; pitch = 15/700 primary + 11px mono uppercase sub; CTA `btn--lg btn--primary-inverted`; form `source="newsletter-sticky"` |
| 2 | **AIF article bar `.lp-sticky`** | AIF | `components/newsletter-sticky.php` via `single.php:485` — **guests only** (`!is_user_logged_in()`) | `article-sticky.css` (scoped `.single`) — "copy of the landing rules… port changes both ways" | `article-sticky.js`: **anchor-geometry, hardcoded** — show past 50% of `.aif-article-body`, hide at its END; `aif-subscribed` localStorage suppression | LIGHT (operator request, logged in the file): white surface, 1px `border-default` top ("quiet edge, not a banner stripe"), shadow `.08`, **SLIM 40px controls + 6px padding** (operator request — fixed header + fixed bar must not eat the viewport); consent-only pitch (14px secondary); mobile ≤900 = full-width 40px anchor button → `#newsletter-signup` (`scroll-margin-top: 104px`); tablet 901–1150 nowrap fix (consent drops to 12px) |
| 3 | **AIG article bar `.lp-sticky`** | AIG | `components/newsletter-sticky.php` via `single.php:211` — guests only; **args API** (`source` / `show_anchor` / `hide_anchor` via `get_template_part` $args) | `article-sticky.css` (unscoped) | `article-sticky.js`: **anchor-geometry, DATA-DRIVEN** — `data-show-anchor` (50% gate; EMPTY = always-on) + `data-hide-anchor` (fallback: show-anchor bottom); pure `shouldShow()` exported for node unit tests (`tests/unit-sticky.js`) | LIGHT, same slim metrics + **100px right padding** (chatbot-bubble clearance); article config: hide at `.kurz-more-courses` (the course promos, not the body end) |
| 4 | **AIG positions bars** | AIG | same component — `page-pozice.php:108` + `landing-pozice.php:101`, guests only | same | same engine, args: `show_anchor: ''` (**always-on from load**), hide at `.footer__newsletter-section` | same light skin; `source="pozice-sticky"` |
| 5 | **AIG course-detail `.sticky-cta`** | AIG | `single-kurz.php:667` + **inline script**; REUSED as a landing primitive (`landing-page-primitives.md §sticky-cta`, toggled by `landing-base.js`) | `components.css:3017–3086` | inline: show when the hero CTA (`.hero-card__actions`) scrolls above the viewport, hide when `#terminy` enters; landing variant (`landing-base.js`): `.page-hero` / `.newsletter-cta` anchors | DARK default + **`--light` AUTO-VARIANT via a luminance sampler** (`elementsFromPoint` → first opaque ancestor bg → luminance > 127; any failure = dark; re-samples only while visible so hide never color-flashes); cohort date + `btn--sm btn--primary`; z-90; 100px chatbot pad; **phantom-shadow fix** (shadow only on `--visible` — an always-on upward shadow bled through while translated off-screen); ≤1279 hides the date; Apple sheet curve `.45s cubic-bezier(0.32,0.72,0,1)` |
| 6 | **AIF preferences `.sticky-bar`** | AIF | `page-newsletter-preferences.php:246` — the SAVE bar (status + submit) | `page.css:2430` — **a bare reusable skeleton already extracted** ("STICKY BAR — reusable fixed-bottom bar. Used by: newsletter landing, preferences") + per-page inline `<style>` | page JS, form-state driven (not scroll) | skeleton only: fixed-bottom + slide transform + inner container; no surface of its own |

Shared DNA (all six): `position:fixed; bottom:0; translateY(100%)` →
`--visible` translates to 0; `aria-hidden` mirrors visibility; rAF-throttled
passive scroll listeners; a max-width inner flex container.

## 2 · Divergence table

| Property | #1 landing | #2 AIF article | #3/4 AIG bars | #5 course CTA | #6 save bar |
|---|---|---|---|---|---|
| Surface | dark (`--lp-ink`) | white | white | dark + auto-light | none |
| Top edge | 2px accent stripe | 1px border | 1px border | 1px inverse border | none |
| Shadow | .25 always | .08 always | .08 always | .3/.08 only-when-visible | none |
| Controls | 48px (form 60 on landing skin) | 40px slim | 40px slim | btn--sm | btn--md |
| Inner pad | 10/32 | 6/32 | 6/32→**100 right** | 12/24→100 right | 10/32 |
| Slide | .35s apple-ease | .35s apple-ease | .35s apple-ease | **.45s sheet curve** | .35s apple-ease |
| z-index | 40 | 40 | 40 | **90** | 40 |
| Trigger | page fractions (0.8h / docH−1.5h) | 50% body → body end | data-anchors (50% / top; always-on mode) | hero-out → #terminy-in | form state |
| Mobile | keeps form | ≤900: anchor button 40px | ≤900: anchor button | ≤1279: date hides | wraps |
| Suppression | none | guest-only + localStorage | guest-only + localStorage | none | n/a |

Off-token values in the skins: `#FFFDF6` (lp-paper), `#8B919E` (mono sub),
`#363B46` (dark field border), `#00A3E6` (button hover), the `--lp-*`
variable layer, landing shadow pair, 900/1150/1279 breakpoints (all
off the closed set), 100px chatbot clearance, 104px scroll-margin.

## 3 · What production already concluded

- AIF **already extracted the primitive** (`.sticky-bar` skeleton, page.css:
  "reusable fixed-bottom bar") — the decomposition is: BAR PRIMITIVE
  (fixed/slide/aria/inner) × CONTENT (form / CTA / save) × SURFACE.
- AIG **already generalized the engine** (data-anchors, pure tested
  `shouldShow()`, always-on mode) — engine and bar are separable.
- The AIF article file carries a standing maintenance debt: "if the landing
  bar design changes, port the change here too — and vice versa" — the exact
  drift the DS exists to kill.

## 4 · Proposed decomposition (for the eventual distill)

1. **`.sticky-bar` primitive** — fixed-bottom slide-up shell: transform
   engine, `--visible` + `aria-hidden` contract, inner container, top
   hairline, only-when-visible shadow (the course bar's phantom fix wins),
   chatbot clearance knob. Surface from SCOPE CLASSES
   (surfaces-replace-variants: `.sticky-bar.section-dark` = the landing/
   course look) — the `--lp-*` skin layer and the light/dark forks die.
2. **`js/components/sticky-bar.js`** — the AIG data-anchor engine as canon
   (superset; unit-tested), extended with the landing's page-fraction mode
   (e.g. `data-show-fraction="0.8" data-hide-fraction="1.5"`) so the
   hardcoded inline script can die; localStorage suppression stays a
   data-attr option (`data-suppress-key="aif-subscribed"`).
3. **Compositions** (markup recipes, not variants): newsletter-form bar
   (pitch + DS ecomail form + mobile anchor button), CTA bar (meta text +
   button), save bar (status + submit).

## 5 · OPERATOR VERDICTS NEEDED (build blocked)

- [ ] **Decomposition** — one `.sticky-bar` primitive + three compositions
  (recommended, mirrors production's own extraction) vs separate components?
- [ ] **Surface** — scope classes per surfaces-replace-variants
  (recommended). What happens to the course bar's **luminance auto-sampler**:
  (a) dies — course detail declares its surface explicitly per section, or
  (b) survives as opt-in behavior (`data-sticky-sample`) for pages where the
  bar floats over alternating sections? It is genuinely clever and genuinely
  exotic.
- [ ] **Metrics** — unify on the SLIM article bar (40px controls / 6px pad —
  already an operator request once) and let the landing's 48/60px tall skin
  die? Or keep tall as the landing composition's right?
- [ ] **Top edge** — the landing's 2px accent stripe vs the quiet 1px
  hairline (the article file explicitly calls the stripe out as rejected:
  "quiet edge, not a banner stripe"). Kill the stripe everywhere?
- [ ] **The hardcoded landing instance** — adoption REQUIRES a DB edit
  (`landing_html` on /newsletter): swap to DS classes + drop the inline
  script for the DS engine. Operator action, not code.
- [ ] Breakpoint snaps: 900→1023 or 767? 1279→1439 or 1023? (all off the
  closed set); z-index 40 vs 90 unification.

## 6 · Sources (every claim, file:line)

Instance 1: live container `/newsletter/` (ACF `landing_html`) +
`aif-theme/assets/css/landing/newsletter.css:717`. Instance 2:
`aif-theme/components/newsletter-sticky.php` + `assets/css/article-sticky.css`
+ `assets/js/article-sticky.js` + `single.php:478-486`. Instances 3/4:
`aiguild/components/newsletter-sticky.php` + same-named css/js +
`single.php:211` + `page-pozice.php:105-113` + `landing-pozice.php:101`.
Instance 5: `aiguild/single-kurz.php:626-740` + `components.css:3017-3086` +
`assets/js/landing-base.js` + `docs/landing-page-primitives.md:128`.
Instance 6: `aif-theme/page-newsletter-preferences.php:246-260` +
`assets/css/page.css:2430-2458`.
