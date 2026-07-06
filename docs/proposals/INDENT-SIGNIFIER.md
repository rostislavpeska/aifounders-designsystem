# PROPOSAL — the left-border-indent signifier: tighter + type-relative

Status: **awaiting operator sign-off.** Raised by the operator: the border-left
+ padding pattern (perex, blockquote, info-box) and the list indent (ul/ol) are
"too luxury everywhere," should align across blockquote/ul/ol, and the gap needs
to grow as type grows.

## The signifier today (all fixed px, inconsistent)

| element | border | indent (at 16px body) | in em |
|---|---|---|---|
| perex | 4px | `spacing-32` = 32px | 2.0em |
| blockquote | 4px | `spacing-32` = 32px (24 mobile) | 2.0em |
| info-box | 4px | `spacing-24` = 24px | 1.5em |
| ul / ol | — | `spacing-40` = 40px | 2.5em |

Three different indents for one visual idea, none aligned, none scaling with type.

## What the field uses (em-based → auto-scales)

| system | blockquote | lists | border |
|---|---|---|---|
| Tailwind Prose | **1em** | **1.625em** | 0.25rem (~4px) |
| GitHub markdown | **1em** (`padding: 0 1em`) | **2em** | 0.25em (~4px) |
| Apple HIG | (Dynamic Type — proportional/relative spacing, no fixed px) | — | — |

Prose's own size ramp is the proof of the operator's instinct: the blockquote
indent stays ~1em, so in px it grows 20 → 24 → 32 → 40 as the type grows
20 → 24 → 30 → 36. The gap scales because it's **relative**, not because anyone
maintains four px values.

Two findings: (1) the DS runs ~2× the industry blockquote indent — "too luxury"
is real; (2) everyone credible expresses the indent in **em**, which is the only
thing that makes the gap track the type size automatically.

## Recommendation — ONE relative indent, aligned, tighter

Replace the fixed px indents with a single relative token used everywhere the
signifier appears:

```
--flow-indent: 1.5em;   /* per-element em → scales with that element's size */
```

- **perex / blockquote / info-box**: `padding-left: var(--flow-indent)`
- **ul / ol**: list indent = `var(--flow-indent)` (the arrow bullet sits inside it)
- **border stays a constant 4px** — a signifier bar should read the same weight
  at every size; 0.25em would over-thicken big pull-quotes. (Both refs land ~4px
  at body anyway.)

Result:

| | today (16px) | proposed 1.5em | at perex 24px | at body-lg 18px |
|---|---|---|---|---|
| blockquote / perex | 32px | **24px** | 36px | 27px |
| lists | 40px | **24px** | 36px | 27px |
| info-box | 24px | **24px** (16px @ small-14) | — | 27px @ article-18 |

- **Tighter**: blockquote/perex 32 → 24 (−25%), lists 40 → 24 (−40%).
- **Aligned**: blockquote, ul, ol now share ONE indent → their text starts on
  the same line (the operator's ask), at every size.
- **Scales**: bump a block to perex/lead and the gap grows on its own — no new
  token, no per-size rule.

**Why 1.5em and not 1em (industry blockquote):** the shared value must also seat
the 14px arrow bullet for lists. 1.5em = 24px at body leaves the arrow a ~10px
gap; 1.25em (20px) leaves ~6px (workable but tight); 1em (16px) can't fit a 14px
arrow + gap. 1.5em is the tightest value that keeps blockquote and lists on one
line with the current bullet. Going tighter means shrinking the arrow to ~11px.

## The one real cost — sign-off needed

This puts an **em value into a px-token system**. Today every space is a fixed
`spacing-*` primitive; `--flow-indent: 1.5em` is a new *relative* kind of token.
That's the decision: it's the only way to get auto-scaling, and it's what the
whole industry does for prose — but it's a genuine departure from "everything is
a fixed primitive," so it's the operator's call.

Alternative if we want to stay all-fixed-px (Model B): keep px tokens but pick a
tighter shared value (e.g. `spacing-24` = 24px for blockquote/perex/lists) and
add a second, larger token for perex-size contexts by hand. Aligned and tighter,
but does NOT auto-scale — every new size needs a new rule. Not recommended;
it re-creates the maintenance the em model removes.

## If approved (Model A)

Add `--flow-indent` (or inline `1.5em`), repoint perex + blockquote + info-box
`padding-left` and the ul/ol indent to it, drop the mobile blockquote px
override (em handles it), retune the arrow-bullet offset math, update the gate
indent assertions to the new values. Ritual as always. ~30 min, one commit.

Pick a value (1.25em tight / 1.5em recommended / 2em = today) and I'll implement.
