# ADOPTION-FEEDBACK — the upstream protocol + queue (roadmap 5 companion)

**Status: PROTOCOL 2026-07-07.** How project-side findings flow back into
the DS during theme adoption — written BEFORE the first refactor agent
launches, because adoption WILL surface DS issues (responsivity in real
compositions, contexts the styleguide never exercised) and the DS must
absorb them without eroding its laws.

## The four categories (classify FIRST, then act)

Every mismatch a refactor agent hits is exactly one of these:

**F1 — THEME COMPOSITION issue.** The page composes DS parts wrongly
(wrong wrapper, missing scope class, stale markup). → Fix in the THEME.
No DS change, no queue entry. This is the default assumption.

**F2 — DS BUG.** A component provably violates its own contract
(`docs/components/*.md`) or breaks in a LEGITIMATE composition — e.g.,
the preview-card grid collapses at a width the archive page really has.
→ The refactor agent MAY fix the DS itself, under the bug ritual:
1. **Specimen first**: reproduce the failure as a styleguide specimen in
   the DS repo (the failing composition becomes permanent);
2. fix the canon CSS/JS (tokens only; all DS laws apply);
3. gate — the full 92+ suite, both brands, PLUS the new assertion the
   specimen enables (the gate must GROW by the bug);
4. conventional commit in the DS repo + **mirror-sync the changed files
   to the public repo** (see "Where the DS lives" below);
5. log one line in the queue table (§Queue) with resolution = fixed;
6. resume the theme sweep.

**F3 — DS GAP.** The project needs something the DS deliberately lacks —
a new variant, knob, token, breakpoint behavior. → **NOT the agent's
call, ever.** The one-offs law (≥2 usages or operator ruling) and the
HARDENED TOKEN LAW (new tokens only via operator verdict) exist precisely
to survive adoption pressure. The agent:
1. files a queue row (§Queue) with EVIDENCE — page URL, screenshot path,
   which contract falls short, whether the need exists on BOTH themes;
2. **parks that component's sweep** (reverts to the pre-sweep state for
   that family if mid-way) and CONTINUES with other families — never
   blocks the whole phase on a verdict;
3. the operator rules at the next checkpoint (AskUserQuestion batch);
   approved gaps are implemented by a DS session (ds-distill / ds-colors
   skills carry the how), then the parked sweep resumes.

**F4 — DELIBERATE DELTA.** Production looked different because
production was ruled WRONG (consent-note cream, mandatory-marker red,
1px footer field border…). DECISIONS.md is the authority. → Theme adopts
the DS rendering as-is; one line in the theme commit naming the ruling.
When unsure whether a difference is F2 or F4: search DECISIONS.md first,
then ask.

## Where the DS lives during adoption (ruling)

The FACTORY repo (`aig-desigsystem`) stays the WORKING tree until both
themes are adopted: the docker mount, the 92-gate, and the styleguide all
run there, and re-plumbing infrastructure mid-refactor is worse than a
sync step. Every DS change therefore lands twice, mechanically:
commit in factory (the ritual) → copy the changed files into the public
clone (`WORKSPACE\aifounders-designsystem-sync`) → commit + push there
(CI validates). The vector store ingests from the PUBLIC repo — re-kick
the ingest webhook after public pushes that touch rows/docs. Factory gets
archived only after adoption completes.

## Human-in-the-loop checkpoints (both theme agents)

- **C0** after P0: operator eyeballs the parity baselines (are these the
  right pages?) + approves the DS-side JS-enqueue change.
- **C1** after P1 (foundations swap): operator loads both local sites,
  30-second sanity look. Highest-risk moment (token swap).
- **C2** during P2: NO stop per family — the parity gate is the reviewer.
  Operator is pinged only for F3 queue rows (batched, AskUserQuestion).
- **C3** after P3 (JS engines): operator clicks through the behavior
  inventory (menu, modal, aha, sticky) on one long article + homepage.
- **C4** after P5: full-inventory parity run + operator eyes on every
  page in the inventory → sign-off → production deploy is the operator's
  own rsync/git step, per site, at their pace.

## Queue

| date | theme | page/context | category | finding (evidence) | resolution |
|---|---|---|---|---|---|
| 2026-07-07 | AIF | homepage hero + newsletter band (all pages) | F1 | Blue sections carried no DS surface scope → consent-note brand flip never fired (rendered light-scope gray on blue; operator-spotted + gate) | fixed in theme: `section-brand` added to `.hero-aif` + `.newsletter-cta` wrappers; note = black on brand, identical to production |
| 2026-07-07 | AIF | every newsletter capture | F4 | Consent-note voice (tertiary color, leading 1.7→1.5, margin 2→8) shifts pages 6–13px — "THE NOTE VOICE" ruling 2026-07-07 in components.css | adopted; logged in theme `theme-parity/DELTAS.md` #1 |
| 2026-07-07 | AIF | footer, all pages | F4* | AIF `--paper` #F6FDFF vs production #FFFDF6 — digit-transposed hexes, NO DECISIONS entry; deliberate-vs-typo unconfirmed | adopted BY DEFAULT (operator: no per-color stops); **flagged for C4 review** — DELTAS.md #2; revert = one palette line |
| 2026-07-07 | AIF | P1 structural | — | Spec's "harmless duplication" assumption false: DS-native markup means DS components.css wins wherever specificity ≥ theme → F4s assert at P1. Baseline rolls forward at blessed phase closes. | spec + process updated (operator policy 2026-07-07) |
| 2026-07-07 | AIF | buttons/badges everywhere (P2 sweep 1) | F2 | `.btn`/`.badge` base blocks omitted `box-sizing` (against the DS's own per-component convention — 34 siblings). Without a global reset the 2px border rendered OUTSIDE the 60/52/38 height ladder → styleguide drew 64/56/42, violating button.md. Surfaced when sweep 1 removed the theme's reset crutch. | **FIXED** in DS (factory `c4e9c42` + public `1458442`): `box-sizing: border-box` on both base blocks; gate GREW 92→94 (new contract-height test/brand, failed pre-fix). Restores parity — buttons back to spec heights. |
