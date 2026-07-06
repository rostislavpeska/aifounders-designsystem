# PROPOSAL — one heading ramp (article-size) for pages too

Status: **awaiting operator sign-off.** Raised by the operator during B2:
*"article has special styling with smaller headlines. And btw, maybe we can
use the same also for pages…"*

This is a decision, not an autonomous change — it alters how **every standard
page** renders. Nothing implemented; this doc is for the verdict.

## Today: two ramps

The DS runs two heading ramps. The article context deliberately steps the
whole ramp **one style down** (already a DS ruling + GM exception vs live AIG,
which had a 22px-Lazzer orphan — see components.css "HEADING CONTEXTS").

| slot | STANDARD PAGE (`main h*`) | ARTICLE (`.article-layout__content h*`) |
|---|---|---|
| h1 | heading-xl — `clamp(36,4.5vw,64)` | heading-xl (same) |
| h2 | **heading-lg** — AIG `clamp(32,3.2vw,44)` · AIF `clamp(28,4.7vw,36)` | heading-md — **28** (→22 mobile) |
| h3 | heading-md — **28** (→22 mobile) | heading-sm — **22** (→20 mobile) |
| h4 | heading-sm — **22** (→20 mobile) | heading-xs — **18** |

The one real gap between them is **h2**: a page h2 is the big brand-diverged
`heading-lg` (36–44px, fluid, Lazzer on AIG); an article h2 is `heading-md`
(28px). Below h2 the page ramp is just the article ramp shifted by one slot.

## The proposal: collapse to the article ramp

Point `main h2/h3/h4` at `heading-md / heading-sm / heading-xs` — i.e. make the
page ramp identical to today's article ramp. The `.article-layout__content`
override then becomes redundant and is **deleted**: one ramp, the page/article
distinction dissolves. H1/hero/eyebrow display styles are untouched.

Net visual change: **page h2 drops from 36–44px → 28px**; page h3 22px→ (stays
via heading-sm) — actually h3 28→22, h4 22→18. Pages get calmer, more
editorial; the article and page voices become one.

## Options

- **A — Unify DOWN to the article ramp (the operator's idea).** One ramp
  everywhere; delete the article override. Pro: one mental model, calmer pages,
  less to maintain, matches the DS's de-duplication instinct. Con: landing
  pages lose the big `heading-lg` h2 presence; `heading-lg` leaves prose (see
  risk below). **Recommended.**
- **B — Keep two ramps (status quo).** Pro: marketing pages keep impact. Con:
  the step-down stays a special case to remember; two ramps to reason about.
- **C — Unify UP (page ramp everywhere).** Rejected: this reintroduces the
  original bug — loud headings in article reading columns.

## Risk / verify before shipping A

1. **Does any standard page rely on a 36–44px bare `main h2`?** Hero and
   section headings on live pages are usually component classes
   (`.section-heading`, hero blocks), not bare prose `h2` — so bare-prose
   unification is likely safe. Measure the live pages first (Playwright over
   the real AIF/AIG pages) before deleting the override.
2. **`heading-lg` consumers.** After A, `heading-lg` survives only if a
   hero/section component still consumes it. If nothing does, it either stays
   as an API reserve or gets retired (a separate verdict, like `subtitle`).
3. **FLOW LAW is unaffected** — the 48/24 · 40/24 · 32/16 rhythm is tied to the
   heading slot, not its size, so spacing holds when h2 shrinks.

## If approved

Mechanically tiny: repoint `main h2/h3/h4` to the article ramp styles, delete
the `.article-layout__content h2/h3/h4` block, update the two "page ramp" gate
assertions (page h2 becomes 28, not heading-lg), refresh the Prose specimen
note. Ritual (build → gate → commit) as always. Also decide `heading-lg`'s fate
per risk #2.
