# REPO-CLEANUP-SPEC — roadmap item 3, executable (written for the next executor)

**Status: SPEC RESOLVED 2026-07-07 (all four §6 verdicts ratified by the
operator this night — see §1). Execution deliberately deferred until the
overnight Figma import agent LANDS** — the rename breaks the styleguide
(`aigds_styleguide` param, PHP prefixes) that the import agent validates
against live; running both in one tree sabotages the import. Executor:
a fresh session (Opus-class is fine — everything below is mechanical;
all judgment already happened).

## 0. Read before touching anything

- `CLAUDE.md` / `AGENT_README.md` — THE RITUAL: `node build/build.mjs` →
  `node build/lint-css.mjs` → `npx playwright test` (92 checks, both
  brands, against `localhost:8090`). Never weaken an assertion.
- `docs/proposals/PUBLIC-REPO-PLAN.md` (§7 bootstrap already executed →
  public repo `rostislavpeska/aifounders-designsystem` @ `efb1442`).
- `docs/DECISIONS.md` — the decision log is HISTORY: never rewrite
  existing rows (they may mention `aigds_*`; that's the record).
- Parallel-chat law: `git status` + `git log` before starting AND before
  every commit; other agents may have landed work.

## 1. The verdicts (operator, 2026-07-07 — final, do not re-ask)

1. **Rename prefixes** → `aifds_` / `AIFDS_` / handles `aifds-`; plugin
   file + slug + text-domain → `aifounders-designsystem`; display name
   "AI Founders Design System".
2. **LICENSE holder** → "AI Founders" (MIT stays).
3. **`docs/proposals/` → ALL ship publicly** (the research trail is the
   documentation soul).
4. **Version → `2.0.0-rc.1`** (final 2.0.0 cuts at theme adoption,
   roadmap 5).

## 2. Rename map (census 2026-07-07, ~512 occurrences / 64 files)

| from | to | where (verify counts at run time) |
|---|---|---|
| `aigds_` (fns, hooks, options, the `aigds_styleguide` + `aigds_sandbox` query params) | `aifds_` | ~471 case-insensitive hits incl. handles: PHP (`inc/*.php`, `aig-design-system.php`), `js/`, `build/rows.mjs`, `build/shots.mjs`, `tests/*.spec.js` (47× `aigds_styleguide`), `docs/components/*.md` specimen URLs, `.claude/skills/*` (ds-colors, ds-distill, ds-figma-import, ds-lookup), `assets/ds-rows.json` (regenerates — do NOT hand-edit) |
| `AIGDS_` (constants) | `AIFDS_` | 41 hits, PHP |
| `aigds-` (style/script handles) | `aifds-` | `inc/enqueue.php` |
| file `aig-design-system.php` | `aifounders-designsystem.php` | `git mv`; then header: Plugin Name "AI Founders Design System" · Plugin URI → `https://github.com/rostislavpeska/aifounders-designsystem` · Text Domain `aifounders-designsystem` · Version `2.0.0-rc.1` |
| text-domain `aig-design-system` | `aifounders-designsystem` | header + any `__()`/`load_plugin_textdomain` uses |
| `package.json` name/version | `aifounders-designsystem` / `2.0.0-rc.1` | factory + public |

**Exclusions (leave every `aigds` there untouched):** `docs/DECISIONS.md`
(history), this spec's §2 (self-reference), `.git/`, `_shots/`,
`assets/figma-map.json` ONLY if the Figma agent wrote row-name keys — its
artifact; regenerate-don't-edit files (`assets/ds-rows.json`,
`assets/tokens-manifest.json`, `assets/css/tokens.css` regenerate via the
build). After the sweep: `grep -ri aigds --include=... .` must list ONLY
DECISIONS.md + this spec.

## 3. Ordered execution (gate checkpoint after every phase)

**R0 — preconditions.** Figma import agent LANDED (its session finished;
`assets/figma-map.json` committed or explicitly handed over — if in doubt
ask the operator, do not guess). `git status` clean. Docker stack up
(styleguide answers on :8090). Baseline: full ritual GREEN before any
edit; record the baseline commit hash.

**⚠️ Dry-run findings (2026-07-07, a sweep was test-run on a scratch
branch and rolled back — bake these in):**
- The sweep script is ALREADY WRITTEN AND TESTED: `build/rename-sweep.mjs`
  (459 replacements / 71 files on the dry run). It now includes the
  camelCase pair **`aigdsModal` → `aifdsModal`** (no underscore — the
  prefix patterns miss it; lives in `js/components/modal.js`,
  `inc/styleguide.php`, `inc/sandbox.php`, `docs/components/modal.md`)
  and the history allowlist (`DECISIONS.md`, `REPO-CLEANUP-SPEC.md`,
  `AGENTIC-DS-RESEARCH.md`, the script itself, regenerated artifacts,
  `figma-map.json`).
- **Run in the MAIN working tree, never a fresh worktree**: a fresh
  Windows checkout materializes CRLF, and `build/rows.mjs`'s ledger row
  regex (`\|$`) silently matches 0 rows against `|\r` — the dry run
  produced an empty ds-rows.json this way. The main tree's files are LF
  and parse fine. (If rows.mjs ever reports 0 rows: check line endings
  first.)
- Residue expectation after the sweep: `grep -ri aigds` hits ONLY the
  §2 exclusion list. `2` uppercase `AIGDS` hits in inc/ came from
  aigdsModal contexts — covered by the new pair; verify 0 outside the
  allowlist.
- Two hand-edits the script does not do (already spec'd): `git mv
  aig-design-system.php aifounders-designsystem.php` + header Plugin
  URI/Version lines, and `package.json` (dry run confirmed it still said
  `aiguild-design-system@1.0.0` → `aifounders-designsystem@2.0.0-rc.1`).

**R1 — plugin identity.** `git mv aig-design-system.php
aifounders-designsystem.php`; rewrite the header block per §2.
⚠️ WordPress consequence: the mounted plugin's slug changes → WP treats
it as a NEW plugin. The docker compose override (lives in the
`aifounders_web` project — operator's file) maps this host folder into
`wp-content/plugins/<target>`; hand the operator the one-line change of
the container TARGET path to `aifounders-designsystem` (host folder name
stays `aig-desigsystem` — nothing else moves), then reactivate the plugin
(wp-admin or `wp plugin activate aifounders-designsystem` in the
container). The styleguide must render again on :8090 (now at
`?aifds_styleguide=1`) BEFORE R2's gate run.

**R2 — the sweep.** Three case-sensitive passes (`aigds_`→`aifds_`,
`AIGDS_`→`AIFDS_`, `aigds-`→`aifds-`) + text-domain string, over the §2
file set, honoring exclusions. Then `node build/build.mjs` + `node
build/rows.mjs` (regenerates manifest/tokens.css/ds-rows with the new
specimen URLs) + `node build/lint-css.mjs` + **full playwright gate = 92
green** (tests were renamed in the same sweep, so they now assert the new
param — that is the point, not a weakening). Residue grep per §2.

**R3 — factory commit.** Single conventional commit, e.g.
`refactor(identity)!: AIFDS — the AI Founders Design System rename
(operator verdicts 2026-07-07)`. Push.

**R4 — vector store rebuild.** Via the n8n MCP: workflow
`meX2IgYov6Pyn23r` (`n8n_test_workflow`, POST, empty `{}` body, header
`x-api-key` — self-serve the key by reading workflow `yfXHlHY4IaoVaQYF`
mode=filtered node "Check API Key" → condition rightValue). Expect
`{ok:true, ingested:40}`; then one search smoke via `yfXHlHY4IaoVaQYF`
(query "dark footer email capture" → Newsletter capture top). The rows
now carry `aifds_styleguide` specimen URLs.

**R5 — curated sync → `aifounders-designsystem`.** In a fresh clone of
the public repo: delete everything except `.git`; `git archive` the
factory HEAD curated tree (same path set as the `efb1442` bootstrap) and
extract; restore/refresh the public-only files: public-voice `README.md`,
`LICENSE` (MIT, **AI Founders**), `.github/workflows/ci.yml`,
`.gitignore`; ensure `docs/proposals/` is ALL-IN (verdict 3) and
`package.json` says `2.0.0-rc.1`. One commit: `feat: AI Founders Design
System 2.0.0-rc.1 — AIFDS identity`. Push; CI must go green (CI = build +
`git diff --exit-code` tokens sync + lint).

**R6 — parity check.** Byte-diff every synced path factory↔public
(expect empty). Full ritual once more against the local stack.

**R7 — the public flip (OPERATOR actions, hand them this list):**
repo Settings → visibility → Public; n8n ingest source flip — patch
workflow `meX2IgYov6Pyn23r` node "Repo Params" default `repo_name` →
`aifounders-designsystem` (one `patchNodeField` via the n8n MCP is fine)
→ rebuild + smoke (public repo needs no PAT once public); factory repo:
add a README banner pointing to the public repo, then GitHub → Archive.
(The n8n PAT keeps `aig-desigsystem` access until archive day.)

**R8 — aftercare.** `docs/IMPLEMENTATION_STATUS.md` roadmap 3 → ✅ with
date; new `docs/DECISIONS.md` row recording the rename ruling + counts;
update memory (`road-to-deployment`, `brand-architecture-aif-platform` —
plugin rename verdict now executed; `local-wp-stack-setup` — new compose
target/slug).

## 4. Verification table (all must hold before calling it done)

| check | expected |
|---|---|
| residue `grep -ri aigds` (excl. DECISIONS.md, this spec) | 0 hits |
| full gate | 92/92 both brands, against `?aifds_styleguide=1` |
| styleguide manual load | renders both brands on :8090 |
| `assets/ds-rows.json` | 40 rows, specimen URLs carry `aifds_` |
| vector store | `{ingested:40}`, smoke top-hit correct |
| public repo CI | green |
| parity diff factory↔public synced paths | empty |
| WP plugins screen | "AI Founders Design System 2.0.0-rc.1" active |

## 5. Rollback

Factory: `git revert` the rename commit (single commit = single revert);
compose target line back; reactivate old slug; vector rebuild (store
follows the repo — rows carry `git_ref`). Public: `git reset --hard
efb1442 && git push --force` (pre-flip only; after the flip, revert
forward instead — never force-push a public repo).

## 6. Kickoff prompt for the executor session

```
Execute docs/proposals/REPO-CLEANUP-SPEC.md — roadmap item 3, the AIFDS
rename + curated sync + public-flip prep. All operator verdicts are
already ratified inside the spec (§1); do not re-ask them. Follow the
phase order R0→R8 exactly; run the full ritual at every gate checkpoint
and never weaken an assertion. Preconditions in R0 are hard — especially
"the Figma import agent has landed"; if unsure, stop and ask. The two
compose/WordPress touches and the R7 flip list are operator handoffs —
prepare them, don't attempt them yourself. Report with the §4
verification table filled in.
```
