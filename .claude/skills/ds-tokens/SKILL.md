---
name: ds-tokens
description: THE GENERIC ENTRY POINT for ANY design-token operation in the aig-desigsystem repo — add/change/retire/rename any token, "make this smaller", "new shadow", "different stroke", "add a spacing step", "this color is wrong", "bigger heading". It ROUTES BY FAMILY (nested-skill architecture, operator 2026-07-04): color tokens → delegate to ds-colors, typography tokens → delegate to ds-typography, and ALL FOUNDATION tokens (spacing, containers, strokes + styles, shadows, icon/illustration sizes, transitions/easings, field component tokens, breakpoints) are handled INSIDE this skill. Shares THE RITUAL and the hardened token law (new tokens NEVER without operator approval). When unsure which skill a token request belongs to, start HERE.
---

# DS Tokens — the generic token wrapper (router + foundations manual)

Repo: `C:\Users\rosti\Documents\WORKSPACE\aig-desigsystem`.

**Architecture (operator 2026-07-04): nested skills.** This file is the ONE
front door for token work. Step 1 is always routing; the specialized skills
stay the deep manuals for their families.

## STEP 1 — ROUTE BY FAMILY

Classify what the requested value actually IS, then:

| The value is… | Route | Mechanism |
|---|---|---|
| a COLOR (palette value, semantic role, scope override, alpha/tint, status, surface) | **ds-colors** | READ `.claude/skills/ds-colors/SKILL.md` and follow it fully — its 3-layer laws govern |
| TYPE (size, leading, flow rhythm, font, weight, style bundle, mobile-size, tracking, case) | **ds-typography** | READ `.claude/skills/ds-typography/SKILL.md` and follow it fully — MECHANISM LAW governs |
| anything else — spacing, container, stroke width/style, shadow, icon/illustration size, transition/easing, radius, field/selection component token, breakpoint | **THIS skill** | continue below |
| a COMPONENT to bring in from the themes (not a single token) | **ds-distill** | this is a harvest, not a token op |

Routing rules for the messy cases:
- **Route by the VALUE's family, not the component that consumes it.** "The
  info-box tint is wrong" = a color op (ds-colors) even though info-box is a
  component. "The field padding is cramped" = foundations (here).
- **Mixed operations sequence through the skills** (e.g. a new component
  token needing a color ref AND a spacing ref): do each family's edit under
  its own skill's laws, then run ONE ritual at the end for the combined
  change — one build, one gate, one commit.
- **Component-token vs design-token:** knobs living in `components.css`
  (`--dt-pad-*`, `--record-columns`) are layout vars — edit them with the
  component, not here; but their VALUES must reference design tokens, and if
  a needed primitive is missing, THAT decision routes here.

## THE LAWS (apply to every route; the specialist skills add their own)

1. **HARDENED TOKEN LAW (operator 2026-07-04):** a new token is NEVER defined
   without explicit operator approval — even when a harvest "needs" it.
   Present: the value, the evidence (theme file:line or measured), the
   proposed name + family + tier, and the nearest existing token with its
   delta. Wait for the ruling. (Precedent: `spacing-6` was ADDED only after
   showing the 4→8 gap was real across small-button gaps + smart-button
   internals.)
2. **Hardcoded values are strictly forbidden** in component CSS. A value with
   no token match = FULL STOP → the three-option question (nearest token /
   new-token proposal / operator-approved CALIBRATED constant — approval
   required for the calibrated idiom too).
3. **Scales are harvested, not generated.** No formula-filling ("add 10 and
   20 for symmetry") — the scales deliberately have holes; the old
   `--spacing-20` info-box bug came from assuming a step exists.
4. **Retirement needs a usage sweep** across: plugin CSS/PHP/JS, `{refs}` in
   all `tokens/*.json`, `tests/` (gate assertions are contract), `docs/`
   (incl. `docs/components/*`), the styleguide's DYNAMIC names
   (`var(--spacing-<?php`-style construction), and the THEME clones in
   `_harvest` (production consumers). Unused-in-plugin ≠ dead: the ledger's
   "Reserved token API" section (`docs/IMPLEMENTATION_STATUS.md`) lists
   deliberate Stage-2 exports — check it before calling anything an orphan.
5. **Every token carries `$description`** (+ `$evidence` where harvested) —
   the styleguide renders them; a token without a description is a defect.

## THE FOUNDATIONS TERRITORY (handled inside this skill)

| Family | File | Notes |
|---|---|---|
| `spacing-*` (2,4,6,8,12,16,24,32,40,48,56,80,120) | `tokens/base.tokens.json` | rem-based, px-named; the HARVESTED scale — not a formula |
| `space-*` (tight/stack/block/section) | same | semantic spacing aliases |
| `container-*` (narrow/article/wide/max/ultra; `container-wider` is BRAND-DIVERGED → `brand.*.tokens.json`) | same | |
| `stroke-1/1_5/2/3/4/6` + `stroke-style-solid/dashed` | same | `--stroke-4` couples to `--flow-indent` (change the stroke, the indent follows) |
| `shadow-sm/md/lg/xl` | same | reserved API (unused until theme adoption) |
| `icon-size-sm/md/default/xl` (14/20/24/32) | same | pairs with the STEPPED STROKE law (<16→1 · 16–32→1.5 · >32→3; `docs/components/icons.md`) |
| `illustration-size-*`, `transition-*`, `ease-*` | same | theme-consumed reserved API |
| `field-*`, `selection-*` (component tokens) | same | values are `{refs}` to primitives — keep single-hop; no raw value a primitive could express |
| `radius-full` | same | THE ONLY RADIUS (operator ruling — all others retired) |
| breakpoints (600/768/1024/1440) | `tokens/breakpoints.json` | THE CLOSED SET — lint LAW 4, BOUNDARY LAW max = value−1 |

**Coupled values:** `--flow-indent` derives from `--stroke-4`
(indent-signifier law); icon sizes pair with stroke steps. Changing one side
of a coupling requires updating the other + its gate assertion.

**Breakpoints:** adding/moving a cut = operator ruling +
`tokens/breakpoints.json` + `build/lint-css.mjs` expectations + the gate's
boundary test, all in ONE change. Never a one-off px in CSS (escape hatch =
same-line `bp-exception:` comment, local only).

## Operations (foundations)

- **Add a step/width/size:** evidence → operator approval (LAW 1) → add with
  `$description` (+ `$evidence`) → build → check the styleguide tab renders
  it → gate → commit.
- **Change a value:** grep the blast radius first (every consumer incl.
  component-token refs and coupled values); list it in the commit body.
- **Retire:** LAW 4 sweep → operator approval → remove → build (manifest +
  styleguide tabs regenerate) → gate → commit. Log in `docs/DECISIONS.md`.
- **Rename:** update every `var()` + `{ref}` + test + doc in one commit; add
  the old→new pair to `tokens/rename-map.json` ONLY if themes use the old
  name (it is the adoption mapping, not a graveyard).

## Ritual (identical for every route — run ONCE per combined change)

`node build/build.mjs` → `node build/lint-css.mjs` →
`npx playwright test tests/tokens.spec.js` (both brands, live container on
`localhost:8090`) → conventional commit; push per the session's standing
instruction. **Never weaken a gate assertion without an operator ruling.**
