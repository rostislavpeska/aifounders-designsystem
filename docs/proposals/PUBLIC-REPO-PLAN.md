# PUBLIC REPO — `aifounders-designsystem` (roadmap item 3)

**Status: BOOTSTRAPPED 2026-07-06** — repo live at
<https://github.com/rostislavpeska/aifounders-designsystem> (private),
initial commit `efb1442`: curated tree (2,064 files), fresh public
README, headless CI (build-sync + L0 lint), MIT, `package.json` renamed
+ fixed (ISC→MIT mismatch died). Token build verified **byte-identical**
to the dev repo before pushing. Dev continues HERE until the audit +
cleanup pass; then final sync → §6 verdicts → public flip → archive this
repo. The §6 verdicts remain OPEN (plugin rename, LICENSE holder,
proposals list, version).

## 0. Identity (operator ruling 2026-07-06)

**AI Founders is the open-source platform; AI Guild is a commercial
project built on it.** The system ships publicly as the **AI Founders
design system** with two brand themes (aifounders blue · aiguild yellow —
mirror-law twins). The repo name follows: `aifounders-designsystem`.
The old working repo (`aig-desigsystem`, name-typo included) stays as the
private dev archive and is archived after cutover.

## 1. Repo shape

- **Fresh history** (operator): ONE initial commit of the curated tree.
  Dev provenance stays readable in the old repo; DECISIONS.md carries the
  design provenance forward — that file IS the public history of rulings.
- **Visibility:** create private → flip public when the cleanup pass
  (below) is green.
- **Default branch `main`**; conventional commits; the RITUAL unchanged
  (build → lint → gate → commit).

## 2. Curated tree (in / out)

**IN (the plugin + its truth):**
`aifounders-designsystem.php` · `assets/` · `inc/` · `js/` · `icons/` ·
`tokens/` · `build/` · `tests/` + `playwright.config.js` · `docs/`
(curated — see §3) · `LICENSE` (MIT — copyright line updated per §0
verdict) · new public `README.md` · `.github/workflows/` (CI, §5) ·
`.gitignore`.

**OUT (dev residue):** `dev/` · `node_modules/` · `playwright-report/` ·
`test-results/` · `COPY_MANIFEST.json` · `AGENT_README.md` +
`REPOSITORY_RULES.md` + `Readme_Public.md` (superseded — their surviving
content merges into README/CONTRIBUTING) · any scratch/artifacts.

## 3. Docs curation

- `docs/components/*.md` — ALL IN (the vector rows; the public value).
- `docs/DECISIONS.md`, `docs/IMPLEMENTATION_STATUS.md`,
  `docs/DESIGN_SYSTEM.md` — IN (sweep for internal-only phrasing).
- `docs/proposals/` — IN as the research record, minus anything
  operator-flags at cleanup (review pass required; e.g. live-site
  credentials never existed there, but tone-check each map).

## 4. README outline (public voice, §0 framing)

1. What this is — a WordPress-plugin design system where CODE is the
   source of truth (tokens → CSS, real-markup styleguide, Playwright
   contract gate); Figma is a projection.
2. The two brand themes + surface-scope architecture (3-layer color,
   48+ roles, 7 scopes).
3. Quickstart: install as plugin → `/?aifds_styleguide=1` (Stage-1: no
   frontend impact until theme adoption).
4. Architecture map: tokens/ · build/ · components ledger · the gate.
5. The agentic layer: component docs = retrieval rows, the ds-lookup
   skill (ships here, roadmap item 4), VECTOR-DS pointer.
6. Status/roadmap + contributing + license.

## 5. CI (GitHub Actions, headless-only)

On push/PR: `npm ci` → `node build/build.mjs` → assert `git diff --quiet`
(tokens.css in sync) → `node build/lint-css.mjs` (L0 laws; the WP-bound
L1 gate stays local per PLAYWRIGHT-AGENTIC). Later (roadmap item 4): the
changed-rows ingest hook → n8n webhook.

## 6. Open verdicts at cleanup — ✅ ALL RESOLVED 2026-07-07 (operator)

1. **Plugin rename: FULL AIFDS identity** — `aifds_`/`AIFDS_`/`aifds-`
   prefixes, file+slug+text-domain `aifounders-designsystem`, display
   name "AI Founders Design System". 2. LICENSE holder → **AI Founders**.
   3. `docs/proposals/` → **all in**. 4. Version → **`2.0.0-rc.1`**.

Execution spec (phases, census, rename map, verification, rollback,
executor kickoff prompt): **[REPO-CLEANUP-SPEC](REPO-CLEANUP-SPEC.md)** —
deferred until the Figma import agent lands (the rename breaks the live
styleguide it validates against).

## 7. Bootstrap procedure (I execute when the repo URL exists)

```
1. git archive the curated tree from aig-desigsystem HEAD → staging dir
2. add public README + CI workflow + .gitignore; apply §6 verdicts
3. git init → single commit "feat: AI Founders design system v2.0.0 — initial public release"
4. git remote add origin <URL> → push main
5. run the RITUAL against the local stack from the new clone (parity check)
6. old repo: add a README banner pointing here; archive after cutover
```

**Needed from the operator:** create `aifounders-designsystem` on GitHub
(private; no README/license/gitignore — fully empty so the bootstrap push
is clean), attach the Project board, paste the repo URL here.
