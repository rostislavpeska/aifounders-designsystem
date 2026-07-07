# Text styles (typography public API)

**Type:** system · **Status:** shipped · **git_path:** `assets/css/components.css#typography-public-api` · **Specimen:** `/?aifds_styleguide=1&item=text-classes`

## Intent
The public type classes are the design system's entire typographic vocabulary: `.title`, `.heading-xl/lg/md/sm/xs`, `.lead`, `.body-lg/md/sm`, `.caption`, `.meta` (plus `.ui-label-sm` for visually-small non-structural headings). Reach for a text class whenever an element's visual scale must be chosen independently of its semantic tag — the operating rule is "classes own the LOOK, tags own the OUTLINE", so `<h2 class="heading-sm">` is correct usage, not a hack. Do NOT reach for these classes to add rhythm or color: they set type only (family/size/weight/leading) — no color, no margins; spacing comes from the prose flow rules and color from the surface. Do not invent new styles or emphasis variants (bold/italic within a style never becomes a new style); a new style needs operator sign-off. The contract in one breath: each class consumes ONLY its own `--<style>-*` bundle variables, its responsive behavior is fixed by which mechanism class it belongs to, and seven properties diverge per brand through the brand token files — never through per-brand CSS in the class itself.

## Anatomy
```html
<!-- class picks the look; the tag stays semantic -->
<h2 class="heading-sm">A small heading on an h2 outline slot</h2>
<p class="lead">Lead / intro voice — Space Grotesk Bold.</p>
<p class="body-md">Default 16px reading text.</p>
<span class="meta">12px UI metadata</span>
```
Each class is a pure bundle read, e.g.:
```css
.heading-md {
    font-family: var(--heading-md-font);   /* brand-resolved: Lazzer AIG / Inter AIF */
    font-size: var(--heading-md-size);
    font-weight: var(--heading-md-weight);
    line-height: var(--heading-md-leading);
}
```
Bundles are emitted by the build from `tokens/type-styles.json` (each style = size + font + weight + leading, optionally mobile-size and case). The `eyebrow` style exists as a token bundle (`--eyebrow-*`: mono, 12, bold, uppercase via `case-upper`) but has **no public class** in `components.css` — it is currently consumed only by `.logo-placeholder`.

## Variants
- Display: `.title` (`size-title` clamp), `.heading-xl` (`size-hero` clamp). The `hero` style (`size-display`) is a token bundle without a public class.
- Headings: `.heading-lg` (brand-diverged size), `.heading-md`, `.heading-sm`, `.heading-xs`.
- Lead: `.lead` — rendered reality is `--font-accent` (Space Grotesk) at `--weight-bold`; the old perex tokens' Inter/Regular claim was fiction. Leading is brand-resolved via `--lead-leading` (1.5 AIG / 1.6 AIF).
- Reading: `.body-lg` (18, article body), `.body-md` (16), `.body-sm` (14).
- UI: `.caption` (14, accent font, brand-resolved weight), `.meta` (12, medium).
- Legacy aliases (deprecate on rationalization): `.text--perex` (= legacy `.lead` with prose-scope duties), `h2.text-h3` → heading-md, `h3.text-h4` → heading-sm, `.ui-label-sm` (heading-xs look + bottom margin — the one class that carries a margin, because it is a UI label, not a type-only style).
- Retired: `subtitle` (2026-07-03, zero live usage); `subheadline`/`lead-quote` collapsed into `lead` (no 24px-medium exists live).

## States
None — type classes carry no interactive states. Emphasis inside any style: `<strong>` = `weight-bold`, `<em>` = browser italic; neither is a state or a new style.

## Responsive
THE MECHANISM LAW (operator + Carbon, 2026-07-03): a style's responsive behavior comes from its CLASS, never ad-hoc overrides —
- **Display styles** (`hero`, `title`, `heading-xl`, `heading-lg`) are **FLUID**: size is a `clamp()` primitive (`size-display`, `size-title`, `size-hero`; `heading-lg-size` is a brand-file clamp).
- **Content ramp** (`heading-md`, `heading-sm`, `lead`) takes **ONE step at the 768 cut**: under `max-width: 767px` (`bp-md` minus one, BOUNDARY LAW) the build re-declares the style's own size var (`heading-md-size` → `size-22`, `heading-sm-size` → `size-20`, `lead-size` → `size-20`).
- **Reading/UI styles** (body-*, caption, meta, button, eyebrow…) are **CONSTANT**.
Mechanisms never diverge per brand — only values do (AIG `heading-lg` clamp(32,3.2vw,44) vs AIF clamp(28,4.7vw,36) is legal; fluid-on-one-brand/stepped-on-the-other is not).

## Tokens referenced
Per style, only its own bundle: `--title-*`, `--heading-xl-*`, `--heading-lg-*`, `--heading-md-*`, `--heading-sm-*`, `--heading-xs-*`, `--lead-*`, `--body-lg-*`, `--body-md-*`, `--body-sm-*`, `--caption-*`, `--meta-*` (each `-font`, `-size`, `-weight`, `-leading`). `.lead`/`.text--perex` read `--font-accent` and `--weight-bold` directly (class truth). `.ui-label-sm` additionally reads `--spacing-16`.

## Surfaces
Type classes set no color; text color is a surface role (`--text` etc.) supplied by the section-context scopes through inheritance. The same class renders identically on light/dark/brand except color.

## Known friction
- **Brand divergence — exactly 7 diverged props**, living in `tokens/brand.aiguild.tokens.json` / `brand.aifounders.tokens.json`: `heading-lg-size` (AIG clamp(32,3.2vw,44) / AIF clamp(28,4.7vw,36)), `heading-md-font` (Lazzer / Inter), `heading-md-weight` (900 / 800), `heading-sm-leading` (1.35 / 1.2), `lead-leading` (1.5 / 1.6), `description-weight` (400 / 500), `caption-weight` (400 / 500). Never scope a class per brand — repoint the brand file.
- **The no-300 law**: `font-weight: 300` (Light) is BANNED. Every historical Light claim was fiction (subheadline renders BOLD, lead-quote MEDIUM live); 300 becomes Regular at migration and is dropped from font loading at adoption. Gate-asserted.
- `.lead` line-height is brand-resolved — do not hardcode 1.5 or 1.6 anywhere.
- A pending proposal (`docs/proposals/HEADING-RAMP.md`, awaiting sign-off) would collapse the page heading ramp onto the article ramp, changing which bundle `main h2` reads; the classes themselves are unaffected.
