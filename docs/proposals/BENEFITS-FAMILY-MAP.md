# BENEFITS FAMILY — the census (operator-ordered 2026-07-06)

**Status: FULLY RESOLVED 2026-07-06** — info-bar shipped separately
([info-bar.md](../components/info-bar.md)); the rest of the family shipped
as **blurb + stack grid** ([blurb.md](../components/blurb.md)): the
operator's container/blurb model, challenged and refined (no container
component — the box is a consumer recipe; THE PIZZA LAW separators; closed
headline/text rungs; the benefit-display bundle). This map stays as the
harvest record. Original proposals remain on the sandbox
proposals (`/?aifds_sandbox=1`). Scope per operator: benefits + dark blurbs
+ homepage-header mini-benefits + nl-benefits + the AIF /newsletter "Šest
oblastí" section — "semantically this should possibly be one component";
the inconsistency is extreme. This map is the harvest record; the
architecture question is answered in the sandbox, not here.

## 1 · Census — SEVEN dialects of "eyebrow + title + text in rows of N"

| # | Instance (where) | Surface | Eyebrow | Title | Body | Structure |
|---|---|---|---|---|---|---|
| 1 | **AIG editorial cert-card** — homepage benefits row + course `kurz-benefits` (`components.css:2796`) | dark | CSS-counter `01/02/03`, mono 12/700/0.1em uppercase, inverse-link color | Inter `clamp(24px,2.4vw,32px)`/800/**1.15**/-0.01em | Inter **18px/1.55**, inverse-secondary, `max-width: 38ch` | transparent, **2px inverse border-TOP**, left-aligned; icon hidden (`--with-icon` re-enables); `.reveal` stagger via `--reveal-delay` |
| 2 | **nl-benefits skin** — AIG newsletter landing (`landing-base.css:107`) | dark | same counter | **`clamp(18px,1.8vw,22px)`** (smaller) | same 18/1.55 | same card, tighter gaps (16/12) — an OVERRIDE SKIN of #1 |
| 3 | **AIF cert-card (old)** — AIF benefit rows (`components.css:2638`) | dark **filled box** (inverse-tertiary) | none | Inter **22px/800/1.35** | **Space Grotesk 16/1.5** | CENTERED, 64px icon VISIBLE, max-width 480 — the pre-editorial generation |
| 4 | **dark-blurb** — AIG homepage-hero mini-benefits (conjoined row bleeding −80px below the yellow hero) + AIF front-page sections (`components.css:4117` AIG / AIF twin) | dark box (inverse-primary; `--secondary` variant) | none | Inter **18px/800/1.35** (h5) | **Space Grotesk 14px/1.35** | 64px icon LEFT (row layout), CTA/link/info **bottom-pinned**, max-width 483; ≤1023 stacks |
| 5 | **footer-blurb** — AIG footer (`components.css:3177`, "replaces dark-blurb in footer") | dark (footer) | **THE EYEBROW IS THE TITLE**: mono 12/700/0.1em, looser 1.6 leading, inverse-quaternary | — | Inter **14px/1.35**, inverse-primary | no box, lightweight column |
| 6 | **lp-what cell** — AIF /newsletter "Šest oblastí" (**DB `landing_html`** + `landing/newsletter.css:187`) | **LIGHT** | mono 12/700/0.1em label (shared rule with `course-headline__pre`) | Inter **24px/900/1.2**/-0.01em | Inter **15px/1.6** | 3-col grid (2-col ≤ tablet), **2px strong border-top on the GRID** (not the cells) |
| 7 | **info-bar / homepage-infobar** — AIF front-page (`page.css:1724`) + AIG homepage 1:1 mirror (`page.css:1044`) | dark-secondary STRIPE (⚠️ AIG currently experiments with a LIGHT flip "applied at ALL widths to preview — before deciding") | none | none | Space Grotesk **16/1.5** statement | **2px BRAND border-LEFT** per column, rows of 3 |

Adjacent, deliberately OUT: the mission quote (perex idiom), logo grid,
section headings.

## 2 · The inconsistency, quantified

- **Titles: 5 voices** — 18/1.35 · 22/1.35 · 24/900/1.2 · clamp(18→22) ·
  clamp(24→32)/1.15. Two weights (800/900), three leadings, two tracking
  values, fluid vs constant.
- **Bodies: 5 voices** — SG 14/1.35 · SG 16/1.5 · Inter 14/1.35 ·
  Inter 15/1.6 · Inter 18/1.55. TWO FONTS for the same semantic slot.
- **Eyebrows: 1 recipe, 4 costumes** — the mono-label recipe (12/700/0.1em
  uppercase — ALREADY a DS recipe, `--tracking-label`) appears as a
  counter, a label, a title-replacement, and (adjacent) the course
  eyebrow. The only consistent atom in the family.
- **Accent structure: 3 grammars** — border-top (cert), border-left
  (info-bar), none (blurbs/cells).
- **Surfaces** — five dark treatments (transparent-on-dark, filled box,
  darker box, stripe, footer) + one light; AIG's info-bar light flip is an
  UNRESOLVED live experiment.
- **Icons** — hidden (AIG cert), visible-centered (AIF cert), visible-left
  (blurb), absent (rest).

## 3 · The semantic core

Every instance is: **optional eyebrow (mono recipe; number / label /
nothing) + optional icon + optional title + body + optional action,
arranged in rows of N on a surface.** The differences are DENSITY (display
/ default / compact / statement), ACCENT (rule-top / rule-left / none) and
SURFACE — which is exactly a variant-axes question, not seven components.

## 4 · The three sandbox proposals (`/?aifds_sandbox=1`)

All three render the SAME seven replica contexts, differing in
architecture + unification appetite:

- **A — ONE PRIMITIVE, aggressive** (`benefits-a`): `.benefit` with slots
  (eyebrow/icon/title/body/action) × density axis (`--display` /
  `--compact` / `--statement`) × accent axis (`--rule-top` /
  `--rule-left`). Voices collapse to TWO title voices (display clamp +
  heading-xs) and TWO body voices (body-lg + caption); the SG/Inter body
  split dies. Maximum consistency, biggest visual delta vs production.
- **B — TWO ROWS, middle** (`benefits-b`): `.benefit-card` (the loud
  editorial: eyebrow + display title + reading body — cert/lp-what/nl
  lineage) + `.blurb` (the quiet utility: icon + small title + caption
  body + pinned action — dark-blurb/footer lineage; `--statement` covers
  the info-bar). Two mental models matching the two real jobs (SELLING vs
  ORIENTING); moderate visual delta.
- **C — CONSERVATIVE** (`benefits-c`): three components (`benefit-card`,
  `blurb`, `info-stripe`) each kept closest to its production look; only
  the eyebrow atom + token mapping unified. Smallest delta, least
  rationalization.

Open sub-verdicts the sandbox pages surface inline: the display-title
clamp (MECHANISM LAW: fluid = display class — needs a ruling or a bundle),
the info-bar light-vs-dark experiment, icon fate (per-axis or dead), the
`.reveal` primitive (separate behavior row?), footer-blurb membership.

## 5 · Sources

cert-card AIG `components.css:2796–2873` + `homepage.php:357` +
`single-kurz.php:354–370`; nl skin `landing-base.css:107–131`; cert-card
AIF `components.css:2638–2680`; dark-blurb AIG `components.css:4117–4216`
+ `homepage.php:73–110` (hero) + AIF `front-page.php:117` + twin CSS;
footer-blurb AIG `components.css:3177–3230`; lp-what `landing/
newsletter.css:156–216,887` + live DB markup (localhost:8090/newsletter);
info-bar AIF `page.css:1724+` / AIG `page.css:1004–1105` (incl. the light
flip experiment); reveal `components.css:4871+` + `scroll-reveal.js`.
