# PLAYWRIGHT-AGENTIC — a meaningful gate for an agent-built design system

**Status: BUILT 2026-07-06 (audit Phases 0+D)** — L2 `layout.spec.js`, L3 `a11y.spec.js` (baseline mode, 180 entries committed), L4 `interactions.spec.js` (modal + accordion pilot), `npm run shots`. Rollout steps 0–4 done; step 5 (L3 ratchet burn-down) ongoing — see [AUDIT-2026-07](AUDIT-2026-07.md). Deep-research
harness run 2026-07-06: 5 angles · 23 sources fetched · 109 claims extracted ·
25 adversarially verified (3-vote) → **23 confirmed, 2 refuted**. Fused with
the AIF internal vector store (our own field-tested articles) and the measured
state of this repo. Per the harvest-before-values law: every load-bearing claim
below carries its source and its verification vote.

---

## 1. The problem, stated honestly

The suite named "Visual Regression Testing" does **zero visual testing**:

| measured fact (this repo, 2026-07-06) | value |
|---|---|
| `toHaveCSS` / `getComputedStyle` assertions | 78 / 183 |
| `toHaveScreenshot` / `.screenshot()` calls | **0 / 0** |
| committed baselines | none (orphaned empty `tests/visual.spec.js-snapshots/`) |
| pages the gate must guard | **38 tabs × 2 brands = 76** |
| suite size | `tests/tokens.spec.js`, 1,693 lines, 48 tests |
| a11y engine | none (`devDependencies`: only `@playwright/test`, `http-server`) |
| CI | headless only (lint + tokens-sync); **no WP server in CI** |

Git history: `visual.spec.js` (real screenshots) was scaffolded, then deleted
in the pivot to computed-style assertions (`0bdd944`), leaving the name behind.
So the operator's observation is structurally guaranteed: *a screen can be
broken — overlapping, overflowing, occluded, font-404'd — while every computed
value it asserts is correct.* The gate reads values; nobody reads the screen.

This matters more here than in a human shop because of who commits. Our own
published finding (aiguild.blue, *Agent Deployment Is a Design System
Decision*): incidents that were detectable one-at-a-time in human review
**accumulate faster than review at agent velocity**. And (*The Finger Palm*):
*"Visual regression on every change. Accessibility checked automatically.
Contracts a generated artifact either passes or fails… the runtime enforcement
layer for AI output."* The gate IS the reviewer here — currently it reviews
only one failure class.

## 2. The failure taxonomy the gate must cover

From our own field data (*Who Owns the UI When the Agent Wrote It?*,
aiguild.blue) — production incidents from AI-generated UI cluster in three
patterns, and each maps to a different test type:

| failure mode (field-observed) | what catches it | current gate |
|---|---|---|
| **Right-looking wrong thing** — visually matches, behavior off (disabled state enabled, wrong transition, label wraps instead of truncating) | interaction/state tests against a behavior contract | ✗ (few) |
| **Broken screen** — overlap, overflow, occlusion, zero-size, dead asset | layout-sanity invariants + runtime error capture | ✗ |
| **Token/contract drift** — right drawing, wrong value on wrong surface | computed-style contracts | ✓ (strong — keep) |
| **Machine-checkable a11y** — contrast, names/roles, labels | axe-style engine | ✗ |

## 3. What the research established (verified claims only)

1. **Playwright cannot see overlap natively.** `isVisible`/`toBeVisible`
   ignore stacking order — a fully covered element still reports visible.
   Closed **won't-fix Nov 2021** (microsoft/playwright#9923); the 2025 successor
   (#34778) still open/unimplemented. Overlap detection must be hand-built
   (`document.elementFromPoint` sampling). *(3-0, 3-0)*
2. **Assertion-based layout invariants are a proven discipline** — Galen
   Framework built layout testing on declarative relative-position rules;
   pixel diffing was the add-on, not the core. Galen is dead (~2017): a
   precedent to re-implement, not a dependency. *(3-0)*
3. **Screenshot baselines are not portable, and Docker does NOT fix it.**
   Playwright's own docs mandate per-OS baselines; diffs occur even between
   same-spec machines (#20097). The claim "official Docker image ⇒
   deterministic shared baselines" was **REFUTED 0-3** — even in Docker you
   need `maxDiffPixels` tolerance and baseline-update discipline. *(3-0 ×3 +
   0-3 refutation)*
4. **IBM Carbon — the strongest verified precedent** — gates every PR with
   sharded Playwright AVT (`--grep @avt`, IBM Equal Access engine), graduated
   per-component categories (default · advanced states · **keyboard** ·
   screen-reader), honest "partially tested" statuses. *(3-0 ×3; verified live
   against carbondesignsystem.com + carbon ci.yml)*
5. **An axe gate is near-zero infrastructure.** `@axe-core/playwright` drops
   into existing tests; `expect(violations).toEqual([])` yields **named rule
   ids + failing selectors + HTML snippets**. Catches *up to* 57% of issues by
   volume (Deque self-study; criteria-based estimates 20–40% — treat as upper
   bound; manual testing still required). *(3-0 ×3; 57% figure medium
   confidence)*
6. **Graduated gating is the mature rollout model.** Storybook ships
   per-story `a11y.test: 'off' | 'todo' | 'error'` — warn first, ratchet to
   hard-fail. Directly transferable via test tags / per-tab config. *(3-0,
   2-1)*
7. **The agent ecosystem has converged on structured named feedback, not
   pixels.** Playwright MCP deliberately feeds agents **accessibility-tree
   snapshots** ("operates purely on structured data"; screenshots are opt-in
   and non-actionable); GitHub Copilot's coding agent closes its
   generate→run→verify loop the same way. And: MCP self-verification runs at
   agent discretion — **only a committed suite is a deterministic gate**.
   *(3-0 ×3)*
8. Refuted en route: the "GitHub/Adobe/Square use Chromatic" customer-list
   claim *(0-3)* — do not argue "mature systems buy hosted VRT" from vendor
   pages. Chromatic's own bundle (visual + interaction + a11y, never
   screenshots alone) stands as vendor positioning only.

Grounding from our own store, consistent with all of the above: agents adopt
tokens fast; **components and behavioral contracts are where they fail** — and
the spec must become *testable acceptance criteria the agent can verify
against, not just match visually*.

## 4. The design judgment

**Assertion-first, screenshots-for-humans.** Two reasons this is right *for
this repo specifically*:

- **The consumer of failures is an agent.** A named assertion
  (`overlap: .segmented-option obscured by .modal on input@aif, 375px`) or an
  axe rule id is directly actionable; a pixel diff is not (verified: the
  entire agentic tooling ecosystem is built on this premise, §3.7).
- **The committer is an agent.** With `toHaveScreenshot` as a gate, every
  intentional UI change — which is *most* commits here — churns baselines; the
  agent "fixes" the gate by regenerating them, mechanically. A gate whose
  failure is routinely resolved by overwriting the expectation is not a gate.
  Add non-determinism even in Docker (§3.3) and screenshot VRT here would be
  noise with ceremony. What screenshots ARE for: **operator judgment**. The
  hallucinated-segmented-control incident was caught by the operator from a
  screenshot in chat — formalize that, don't automate it into a lie.

One law carried over from the whole architecture: **auto-discovery**. Agents
add tabs weekly; any check that requires per-tab registration will silently
not cover the newest work — the exact place bugs live. Every sweep below
crawls the styleguide sidebar at runtime: a new tab is covered the moment it
exists, zero test code.

## 5. The proposed architecture — four layers, one ritual

```
                       ┌──────────────────────────────────────────────┐
                       │ LOCAL GATE (Docker up · npm run test:tokens) │
  L0 lint + build      │  css laws · tokens-in-sync        (exists)   │
  L1 token contracts   │  tokens.spec.js                   (exists)   │
  L2 layout sanity     │  layout.spec.js   — NEW, auto-discovering    │
  L3 a11y              │  a11y.spec.js     — NEW, graduated           │
  L4 behavior contracts│  interactions.spec.js — NEW, per component   │
                       └──────────────────────────────────────────────┘
  CI (no WP server)    │  L0 only (already shipped 2026-07-06)        │
  Operator             │  screenshots — for eyes, never a gate        │
```

### L2 · `tests/layout.spec.js` — the broken-screen detector (the big win)

Crawls all tabs × both brands × 2 viewports (1280 / 375). Per page, **named**
invariants:

| invariant | mechanism | catches |
|---|---|---|
| no horizontal page overflow | `documentElement.scrollWidth <= clientWidth` | the classic broken-mobile screen |
| no element wider than viewport | `el.offsetWidth > doc.offsetWidth` scan (Fenton pattern) | overflowing cards/tables |
| no obscured interactive control | `elementFromPoint` at center of every visible `a, button, input, select, textarea, [role=button]` — hit must be the element or a descendant/ancestor | overlap — the thing Playwright can't see (§3.1) |
| no zero-size rendered component | demo-region elements with `offsetParent` but 0×0 box | collapsed/failed renders |
| zero console errors + pageerrors | `page.on('console'/'pageerror')` | JS death that style checks never see |
| zero failed requests | `page.on('response')` ≥400 + `img.naturalWidth>0` | 404 fonts, dead images |

False-positive discipline (research open-question #4, answered our way): an
explicit **opt-out attribute** (`data-sg-overlap-ok`) on intentional overlaps
(open popovers, dropdown demos, sticky bars) — visible in markup, greppable,
never a hidden config. Expected: ~a dozen annotations across 38 tabs.

### L3 · `tests/a11y.spec.js` — axe, graduated like Carbon/Storybook

- `@axe-core/playwright` (one dev dependency, §3.5), same crawl loop.
- **Phase A (todo-mode):** run everywhere, write violations to a committed
  `a11y-baseline.json` (rule id + selector per tab) — the IBM Equal Access
  baseline idea in its diffable, agent-readable form. Gate = **no NEW
  violations** vs baseline.
- **Phase B (ratchet):** burn the baseline down per tab to
  `violations == []`; a clean tab flips to hard-fail and can never regress.
  Graduated statuses, honest partials — the Carbon model (§3.4, §3.6).
- Scope per tab to the demo region (exclude the sg chrome) so failures always
  point at DS components, not the styleguide shell.

### L4 · `tests/interactions.spec.js` — behavior contracts (the "right-looking wrong thing")

Not a sweep — deliberate contracts for the interactive components, per our own
doctrine (*acceptance criteria + full state machine*) and Carbon's keyboard
category: modal (focus trap · Esc · scroll lock · focus restore), select/
datepicker (open/close/selection), segmented (exactly-one-active, disabled
inert — the hover bug we shipped 2026-07-05 is precisely this class), checkbox/
radio groups (keyboard, `role=group` naming), dropzone (`.is-dragover` toggle).
Grow it one component per distill; the `.ds.yaml` behavior notes from
VECTOR-DS §7 and these contracts should be the same sentences.

### Screenshots — formalized as an operator artifact, not a gate

`npm run shots -- <tab|all>` → `_shots/<tab>-<brand>.png`, full-page, both
brands. Used in chat/PRs for operator judgment (it already caught a real
fake). Never asserted against. If pixel-gating is ever revisited: Docker-only,
`maxDiffPixels` tolerance, masked dynamic regions — with §3.3's refutation on
the record.

### Naming honesty (cheap, do first)

Suite is a **contract gate**, not VRT: delete the orphaned
`tests/visual.spec.js-snapshots/`, rename the npm script story
(`test:tokens` → keep; add `test:layout`, `test:a11y`, `test:all`), and the
CI workflow name already says "Design System Checks" (fixed 2026-07-06).

## 6. What we deliberately do NOT do

- **No `toHaveScreenshot` gate** — non-determinism even in Docker (refuted
  0-3), baseline churn under agent commit velocity, non-actionable failures.
- **No Chromatic/hosted VRT** — WP-plugin styleguide, not Storybook; local-only
  server; and the "mature systems all use it" evidence failed verification.
- **No trusting axe as "a11y done"** — 57% is a vendor upper bound by volume;
  keyboard/focus contracts live in L4, manual review stays.
- **No per-tab test registration** — auto-discovery or it doesn't ship.

## 7. Rollout

| step | contents | effort |
|---|---|---|
| 0 | delete orphan snapshots dir · script names | trivial |
| 1 | L2 `layout.spec.js` + `data-sg-overlap-ok` annotations until green | the main build; catches the operator's "broken screens" from day one |
| 2 | L3 axe in baseline (todo) mode | small |
| 3 | L4 contracts: modal + segmented + select first | incremental, per component |
| 4 | `npm run shots` operator script | trivial |
| 5 | ratchet L3 tabs to zero-violation hard-fail | ongoing |

Every step lands via the standing ritual: worktree branch → build → full local
gate → mount-swap verify on :8090 → screenshots to operator → ff-merge.

## 8. Open questions (from the verified research)

- Does #34778 (native occlusion API) ship and obsolete our `elementFromPoint`
  layer? Watch it.
- No published data yet on baseline churn in agent-committed repos — our
  screenshots-for-humans stance is reasoned, not measured; revisit if evidence
  appears.
- Primer's and Polaris's real gate mixes failed verification — Carbon is the
  only mature-system precedent we may cite.
- Heuristics for overlap false positives at scale: start with the opt-out
  attribute; revisit if annotations exceed ~20.

## 9. Sources

Verified primary: playwright.dev (test-snapshots, actionability,
accessibility-testing, getting-started-mcp) · microsoft/playwright#9923,
#34778, #20097 · carbondesignsystem.com accessibility-status + carbon ci.yml ·
IBMa/equal-access · storybook.js.org accessibility-testing · deque.com 57%
study · developer.microsoft.com Playwright E2E story · github.blog Copilot
coding-agent changelog · galenframework.com. Practitioner (corroborated):
adequatica (Docker screenshots), stevefenton.co.uk (overflow scan), checklyhq
(pageerror capture). Internal (field-tested): aiguild.blue — *Who Owns the UI
When the Agent Wrote It* · *Agent Deployment Is a Design System Decision* ·
*The Finger Palm* · *What Agents Actually Adopt* · Medium — *A Constrained
Test*. Refuted and unusable: Chromatic customer list (0-3), Docker pixel
determinism (0-3).
