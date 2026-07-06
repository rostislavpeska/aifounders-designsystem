# FOOTER — the obsessive map (both websites)

**Status: RESOLVED — component SHIPPED 2026-07-06**
([footer.md](../components/footer.md)). §10 verdicts: 1 YES (alias
dropped) · 2 dark-2 KEPT (the band) · 3 optional slot (newsletter-cta =
its own future row) · 4 canon columns, QUATERNARY eyebrow · 5 blessed ·
6 link law wins (underline stays) · 7 formalized (hacks died) · 8 unified
shell + --chatbot-clear axis · 9 ACF imgs kept, DS sizing (+ currentColor
icons compose) · 10 --raised (exact). This map stays as the harvest
record.
Method: full theme-clone harvest (two parallel deep-research passes with
file:line provenance) + live-DOM verification (aifounders.cz + aiguild.cz,
home/article/archive/course). Every value exact and sourced.

---

## 0 · The headline findings

1. **The lower footer is byte-identical twins again** — same skeleton
   (columns grid › footer-blurbs › bottom bar › legal), same geometry,
   same link idioms, same responsive program, ported 1:1. Like the
   header, the work is definition + slots.
2. **The newsletter capture is the big asymmetry**: AIG hosts it INSIDE
   the footer as a full-bleed band (`.footer__newsletter-section`, bg
   `#161616` = inverse-SECONDARY — **the dark-2 scope selector**); AIF's
   footer has NO form at all — its capture is `.newsletter-cta`, a
   brand-blue PAGE-CONTENT band (Lazzer clamp 40→80 headline, glitch
   word) rendered by templates before `get_footer()`.
3. **The scope-census blocker dissolves on harvest evidence**: live
   markup on BOTH sites emits `<footer class="footer section-dark">` —
   the `.footer` alias in the DS dark-1 scope map is REDUNDANT (the
   markup already carries the scope class). Persona precedent applies.
4. **The columns band IS our blurb + stack grid**: `.footer-blurb` =
   mono-atom eyebrow + 14px body (+ link stack); the grid = 3/2/1
   columns with open gaps. The benefits verdict already retired
   footer-blurb into `.blurb` — this map confirms the fit is exact.
5. **Zero nav menus.** Everything is ACF options (link repeaters, logo,
   socials, legal, copyright). AIG's registered `footer-menu` location
   is orphaned. Both themes: no widgets, no footer template variants.

Shared skeleton (live-verified):

```
[AIF only, page content BEFORE footer]  section.newsletter-cta        brand-blue capture band
footer.footer.section-dark [data-footer-type]  bg inverse-primary
  [AIG only] section#newsletter-signup.footer__newsletter-section     full-bleed #161616 stripe
  div.container (1600px / 24px sides / flex-gap 40)
    [AIG only] .footer__partners (label + logo grid) + .footer__divider
    .footer__cards-grid > .footer__row (display:contents) > 3× .footer-blurb
    .footer__bottom-bar (logo 16px → #top | social imgs 16px)
    .footer__legal-section (.footer__divider + .footer__legal right-aligned)
```

---

## 1 · Band inventory (top → bottom)

| # | Band | Who | bg | Key geometry | Separator |
|---|---|---|---|---|---|
| 0 | `.newsletter-cta` (page content) | AIF | `--color-primary-brand` #00b5ff | `clamp(80px,12vw,160px) 0`; headline Lazzer 900 clamp(40→80)/0.95/−0.015em; form max 560 | none — hard color cut |
| 1 | `.footer__newsletter-section` | AIG | **#161616 inverse-secondary (= DS dark-2)** | full-bleed, outside container; pad 48/24 (40 sides ≥768); `margin-top: −120px` = becomes the footer's top edge; row re-caps at 1200 | none — **the surface change IS the separator** |
| 2 | `.footer__partners` | AIG | inherits #070708 | mono label (margin-bottom 56) + flex logo grid; opacity .5 × img .5 = **.25 effective**; `zoom: .5` (!); mobile 44px heights | `.footer__divider` after (only when shown) |
| 3 | `.footer__cards-grid` | both | inherits | 3×1fr, gaps 80·80 → 2 cols 56·80 (768–1023) → 1 col 0·40 (≤767); `.footer__row { display: contents }` | none |
| 4 | `.footer__bottom-bar` | both | inherits | `24px 0 12px`; logo 16px tall `href="#top"` (pure anchor, no smooth scroll anywhere); socials 16px imgs, raw `gap: 20px`, hover opacity .7 | none |
| 5 | `.footer__legal-section` | both | inherits | gap 12; legal SG 14 right-aligned inverse-tertiary; links `!important` underline → hover no-underline + inverse-secondary; literal ` \| ` pipes in markup | **`.footer__divider` before — 2px inverse-tertiary** |

**The dividers are interior-only by production's own law** (AIF comment:
"Single divider above legal section only"; AIG: "after partners + above
legal only") — the footer already obeys the pizza grammar at band scale.

Footer padding (the shell): AIF `80/0/24` → `48/0/24` (≤1023) → `40/0/16`
(≤767). AIG `120/0/56` → `48/0/24` → `40/0/80` — the 56/80 bottoms are
**chatbot-bubble clearance** (comments in CSS; the bubble is the aigb-chat
mu-plugin, not a DS component per standing ruling).

---

## 2 · The footer-blurb (columns) — maps onto `.blurb`

Identical both (AIF components.css:2913-2964 · AIG :3177-3228):
- `__title` eyebrow: Spline Sans Mono **12/700/0.1em/uppercase**,
  `line-height: 1.6` (looser than other eyebrows — titles wrap),
  color **inverse-QUATERNARY** (#69707a AIF / #797b78 AIG),
  margin-bottom 16.
- `__body`: column gap 12. Contact column = `__description` rich text
  (Inter **14**/1.35, inverse-primary; links = inverse-link underline →
  support hover). Link columns = a stack of `.footer__subtle-link`.
- Card: borderless, no icons, `gap: 12`, `flex: 1 1 0` + `min-width: 0`.

**Blurb mapping**: eyebrow = THE MONO ATOM (color divergence: footer uses
QUATERNARY vs the blurb's tertiary — verdict), text = `__text--sm` (14).
The grid = `.stack-grid` (open, 3/2/1). The 2-col tablet step (768–1023)
is a footer-specific column count the stack grid handles via
`--stack-cols` + one media override.

**Font anomaly (both themes)**: Spline Sans Mono is loaded at
`wght@400;500` only — the 700 in eyebrows is browser-synthesized
faux-bold. (DS `--font-mono` + `--weight-bold` will render the same way
unless the loadout changes at adoption — flag, not a blocker.)

## 3 · The footer link idioms

- **`.footer__subtle-link`** (identical both): Inter 14/1.35, colored
  `--color-primary-inverse-link` (brand-bright on dark), NO underline on
  the `→ ` prefix (`::before`, content `"\2192\00a0"`), label `<span>`
  underlined, hover → primary-support. Self-exempted from the dark link
  idiom via the `:not()` exclusion chain (already mirrored in DS CSS).
- **`.footer__legal-link`**: inverse-tertiary `!important`, underlined,
  hover = inverse-secondary + underline REMOVED (production; note the DS
  link law outlawed hover-removes-underline elsewhere — verdict).
- **`.footer__logo-link` / `.footer__social-link`**: excluded from the
  idiom; social hover = opacity .7 fade only.

## 4 · The newsletter capture (the asymmetry, in detail)

**AIG (in-footer band):** hosts the SHIPPED DS composition —
`aif-ecomail-form--footer-dark` (mu-plugin shortcode, `source=
"guild-footer"`): field = a **well** (#070708 on the #161616 band, 1px
`border-inverse-default` #2a2a2a, 52px, focus → #3c3c3c) + submit =
**subtle-filled** (#2a2a2a fill, 52px, hover #3c3c3c) — the exact
production pair behind the standing DS rulings (border 1px→2px GM; AIG
subtle → tertiary-on-dark). Headline: SG 24/700/1.28, **hardcoded
#fffdf6**; consent note `--color-primary-neutral-light` #fffce3, link
inverse-link. Row = 2 equal flex columns, stacks ≤1023. The band is the
sticky-bar's `data-hide-anchor` on pozice pages and the mobile
`#newsletter-signup` jump target (`scroll-margin-top: 104px`).
**Landing pages kill the band** (`body.aiguild-landing
.footer__newsletter-section { display: none }`) to avoid duplicate
capture. Band renders only if the shortcode exists.

**AIF (page-content band):** `.newsletter-cta` — brand-blue, Lazzer
clamp(40→80)/900 headline + `.hero-glitch` word, perex-voice subtitle,
the same Ecomail composition in its light skin (field bg-secondary, 2px
text-primary border, 60px controls), 2-step consent modal (main.js
re-parents the modal to `<body>` — its comment literally cites escaping
the footer transform trap). Rendered per-template before `get_footer()`;
NOT site chrome (missing on recipes, author flows, landings).

## 5 · Footer types (AIG) — data, not DOM

`default` vs `requalification` = the SAME markup; only the ACF option
prefix (`ft_`/`ftr_`) + `data-footer-type` attr change (chosen per kurz
post / produkt_kategorie term). Two parallel ACF option pages. The
newsletter band does NOT vary with type. → DS impact: zero (content
plumbing); the DS defines one footer.

## 6 · Behavior

No footer JS exists (both). Back-to-top = pure `#top` anchor, instant
jump (no smooth-scroll anywhere). Newsletter submit = the aif-ecomail
mu-plugin's inline engine (shake/spinner/success + `localStorage
aif-subscribed` — which also suppresses the sticky bar). AIF's 2-step
consent modal lives in theme main.js. Contact links get Umami tracking
(AIG contact-tracker.js).

## 7 · Twin-diff summary

**Identical:** the whole lower skeleton (§0.1); container 1600/24/gap-40
(both override their 1200 site container — and BOTH carry a stale
"1200px max" comment: fiction ×2); cards grid + breakpoints; blurb
recipe; subtle-link idiom; bottom bar (16px logo, 16px socials `!important`,
gap 20 raw); divider 2px inverse-tertiary; legal band incl. the
line-height-only mobile "size drop" (`--text-small-size` ==
`--text-caption-size` — both 14px, both themes).

**Brand-valued:** inverse-link (blue #00b5ff / yellow #ffd84d), support
hover, inverse-tertiary/quaternary grays (AIF cool #8b919e/#69707a, AIG
warm #b7bbb4/#797b78), divider #1f2126/#1c1c1c.

**Structural:** AIG adds newsletter band (dark-2 stripe) + partners band;
AIF adds nothing (its capture is page content). AIG shell pads 120/56 vs
AIF 80/24 (+ mobile 80 vs 16 bottom — chatbot clearance).

**Dead/orphaned:** AIF carries the full partners CSS unused; AIG's
`footer-menu` location + group-level `ft_social_label` field are
orphaned; AIF's modal-registration include is dead; `.footer__section`
legacy wrapper unemitted (both).

## 8 · Hardcode / fiction inventory

- `gap: 20px` social icons (raw, both) · logo/social **16px** magic pair
  (+ `!important` sizing) · `zoom: 0.5` partners (non-standard property)
  · opacity stacking .5×.5 · `#fffdf6` hardcoded in AIG's newsletter
  headline · AIG band paddings 48/24/40 + −120 pull (knob candidates) ·
  "1200px max" comments vs 1600 code (both) · faux-bold mono 700 (§2) ·
  ` | ` literal pipes in legal markup · `scroll-margin-top: 104px`
  (sticky-bar file, anchors into the footer).

## 9 · A11y notes

Socials carry aria-label + title ✓; back-to-top has aria-label ✓; legal
pipes are text nodes (read aloud — minor); the AIG newsletter band has
`aria-label="Newsletter"` ✓; no landmarks issues found. The instant
`#top` jump is production behavior (no smooth scroll to port).

## 10 · OPEN VERDICTS (operator) — before any build

1. **Scope map cleanup** — drop `.footer` from the dark-1 scope selectors
   (markup already emits `.section-dark` alongside; persona precedent)?
   Harvest evidence says YES.
2. **The dark-2 question (the census blocker)** — the AIG newsletter band
   (#161616 inverse-secondary) is dark-2's only footer consumer. Express
   it as: (a) keep a dark-2 scope class on the band, or (b) retire dark-2
   here and paint the band with a ROLE on dark-1 (nearest: a raised-family
   step — but #161616 ≠ dark raised #1c1c1c; would need a GM snap or a
   new role). This decides dark-2's fate.
3. **Newsletter slot symmetry** — define the in-footer capture band as an
   OPTIONAL footer slot (AIG on, AIF off), and keep AIF's blue
   `.newsletter-cta` as page content (its own distillation row — it's a
   hero-scale band with display type + glitch, not footer anatomy)?
4. **Columns = blurb + stack grid** — compose the columns band from the
   shipped canon (`.stack-grid` + `.blurb`: eyebrow + `__text--sm` /
   link stack). Sub-verdict: eyebrow color — footer's inverse-QUATERNARY
   vs the blurb mono atom's tertiary (keep quaternary as a footer-scope
   override, or unify on the atom?).
5. **`.footer__subtle-link`** — bless as the DS arrow-link idiom (prefix
   un-underlined, label underlined)? It's a strong, shipped pattern; the
   exclusion chain already knows it.
6. **Legal-link hover** — production removes the underline on hover; the
   DS link law outlawed that elsewhere. Align to the law (keep underline,
   color shift only) or GM-except the legal row?
7. **Partners band** — formalize as an optional footer slot (fixing the
   opacity-stacking and the non-standard `zoom` → proper sizing), or
   leave it AIG-theme territory?
8. **Shell paddings** — unify the footer paddings (AIF 80/24 vs AIG
   120/56) or keep per-brand? And is chatbot clearance (AIG's 56/80
   bottoms) a consumer/theme concern per the chatbot ruling?
9. **Social icons** — keep ACF-uploaded img SVGs (content-managed) with
   DS-defined 16px sizing, or migrate to the DS icon catalog
   (currentColor, hover recolor instead of opacity fade)?
10. **Divider mapping** — `.footer__divider` 2px inverse-tertiary → the
    dark scope's `--border`-family role (GM snap) or a footer knob?
