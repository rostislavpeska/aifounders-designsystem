---
name: ds-typography
description: Operate the design system's typography layers — sizes, leading + flow (paragraph/heading rhythm), fonts + weights, style bundles, mobile sizes, and transforms. Use for ANY type change in the aiguild-design-system repo — "make h3 smaller", "more space before headings", "add a text style", "change the lead font", "tune the mobile size". Shares THE RITUAL with ds-colors (build → gate → never weaken assertions → commit, no push unasked).
---

# DS Typography — agentic operations on the type system

Repo: `C:\Users\TIGO\Desktop\WORKSPACE\aiguild-design-system`. Read
`.claude/skills/ds-colors/SKILL.md` first — THE RITUAL and commit rules are
defined there and apply identically.

## The layers (operator model, 2026-07-03)

| Layer | File | Holds |
|---|---|---|
| 1 · Sizes | `tokens/typography.json` (`size-*`) | pure sizes, line-height-free; the HARVESTED list (12…80 + `size-hero` clamp), not a formula |
| 2 · Spacing | same file (`leading-*`, `flow-*`) | line-heights (none/tight/heading/body/relaxed/normal) + paragraph rhythm (`flow-space` 24, `flow-tight` 16, `flow-before-h2/3/4` 48/40/32, `flow-figure`, `flow-quote`) |
| 3 · Fonts | same file (`font-*`, `weight-*`) | display=Lazzer, primary=Inter, accent=Space Grotesk, mono; weights 400–900 — **NO 300, ever (the Light law)** |
| 5 · Styles | `tokens/type-styles.json` | style bundles (title, heading-xl…xs, lead, body-lg/md/sm, quote, code, description, caption, meta, button, button-small…): size + font + weight + leading (+ `mobile-size`) — every prop a single-hop `{primitive}` ref. `subtitle` retired 2026-07-03 (zero live usage, operator verdict); subheadline/lead-quote collapsed into `lead` earlier |
| 4 · Mobile | inside each bundle | `mobile-size` re-declares the style's OWN `--<style>-size` in the emitter's single media block — never a hand-written media query |
| 6 · Transforms | CSS idioms + brand files | article context (heading slots step one style down), the 7 brand-diverged props in `brand.*.tokens.json` (heading-md font/weight = Lazzer AIG / Inter AIF…), decorations (link hairline, perex border), emphasis (bold/italic — never a new style) |

Emitted vars: `--<style>-size/-font/-weight/-leading`. Classes (`.heading-lg`…)
consume ONLY their own bundle vars. Prose rhythm rules in `components.css`
consume ONLY `flow-*` tokens.

## THE LAWS
0. **MECHANISM LAW (Carbon)** — a style's responsive behavior comes from its CLASS, never the individual style: **display** (hero, title, heading-xl, heading-lg) = FLUID via clamp() · **content ramp** (heading-md, heading-sm, lead) = ONE step at 768px (`mobile-size` in the bundle) · **reading/UI** (everything else) = CONSTANT. Mechanisms NEVER diverge per brand — only values do (AIG clamp(32,3.2vw,44) vs AIF clamp(28,4.7vw,36) is legal; fluid-on-one-brand/stepped-on-the-other is not).
1. **Sizes are pure** — a size token never carries line-height or a media query.
2. **No 300/Light.** The old Light claims were fiction (subheadline renders BOLD, lead-quote MEDIUM) — encoded and gate-asserted; do not reintroduce.
3. **Classes set type only** — no margins in typography classes; rhythm lives in the prose flow rules.
4. **FLOW LAW**: space before a heading ≈ 2× space after (48/24 · 40/24 · 32/16); first heading in a container resets to 0; values reference `spacing-*` primitives.
5. **Mobile = the style's own var re-declared** — adding a mobile size means adding `mobile-size` to the bundle, nothing else.
6. **A new style needs operator sign-off** (vocabulary change); emphasis/decoration variants never become new styles.

## Operations
- **Change a size everywhere it's used**: edit the `size-*` value (blast radius = every bundle referencing it — grep first, list them).
- **Change one style's size**: repoint the bundle's `size` ref to another `size-*`.
- **Tune rhythm**: edit `flow-*` values (still `{spacing-*}` refs). The rhythm specimen on the Typography tab is the review surface; gate asserts 48/40/32/24/16 — update assertions WITH the ruling.
- **Add a style**: operator sign-off → add the bundle → add the public class in `components.css` (type props only) → specimen row appears automatically (tab is generated).
- **Brand-diverge a prop**: remove it from the bundle, define it in BOTH `brand.*.tokens.json` files ({font-display} etc. refs).
- **Retire the old names**: the lint fails the build on any `--text-h*/--font-family-*/--font-weight-*` legacy name.

## Ritual
Same as ds-colors: `node build/build.mjs` → `npm run test:tokens` (40 assertions,
both brands, includes rhythm + fiction-fix + mobile-remap checks) → conventional
commit → push only when asked.
