# VECTOR-DS — a vectorized design system, GitHub-canonical

**Status: DRAFT — architecture agreed (+ §12 serving verdict 2026-07-06); store/ingest not built (roadmap item 4).** Living
document; expect the ledger (§6) and open questions (§11) to churn. No Supabase
table, no migration, no ingest wiring exists yet. This file is the thing that
must not get lost.

Per the harvest-before-values law: every claim about existing infra below is
read from the live AIF Supabase project or the harvested theme repos, never from
memory.

---

## 1. Purpose

Make the design system **retrievable by intent** so an agent (in any repo, or on
the Figma side) can ask *"which component do I reach for, and what's its
contract?"* and get a pointer to the canonical source — without loading the whole
system into context.

Not the goal: storing exact values in vectors. Not the goal: a second source of
truth. See §3.

## 2. Topology — three surfaces, one direction of truth

```
GitHub (aig-desigsystem)  ──canonical──►  everything
   │  tokens.json, components.css, .ds.yaml
   │
   ├─ build ─► CSS (shipped)
   ├─ sync  ─► Figma variables + Code Connect      (design surface)
   ├─ ingest─► Supabase pgvector rows               (retrieval index)
   └─ gate  ─► Playwright contract layers L0–L4     (verification, §10)
```

- **GitHub is canonical for both tokens and components.** Git is the version
  authority.
- **Figma is the design surface.** Variables are pushed *from* the token JSON,
  never authored as canon.
- **Supabase is a derived, rebuildable index.** It must be 100% regenerable from
  GitHub by CI on every push. One-way sync only. Nothing is ever *authored* in
  Supabase.

## 3. The three laws that keep it from rotting

1. **Supabase is a cache, never a source.** The moment a fact lives in Supabase
   and not in git, there are two truths and the index starts lying. If it can't
   be rebuilt from a clean checkout, it's wrong.

2. **The vector is a router, not a value store.** Embeddings are lossy; token
   values and prop signatures must be exact. So: the vector *finds* the
   component and returns a pointer; the agent then reads the canonical structured
   record verbatim for values. `vector = router · GitHub = truth`.

3. **A component earns a row only if the DS owns or themes it, on a themed
   surface.** Three outcomes at the gate:
   - **Canonical** DS component → row now.
   - **Themed third-party** (e.g. wrapped editor) → one *theming-contract* row
     describing how the DS dresses it, not an atom row.
   - **Drift** (invented in a theme/plugin, not in canon) → **promote into the
     canonical DS with tokens first, then row.** Never index the fork.

**Corollary — vectorization is a drift audit.** You cannot give a row a
`git_path` into `aig-desigsystem` if the component only exists in a theme.
Trying to write the row is what exposes the drift. This is a feature.

## 4. Row-granularity rule

> **Granularity follows intent divergence, not stylesheet classes and not DOM
> tags.** Two things that share CSS but are reached for in different situations →
> two rows. Two things with different CSS you'd never search for separately →
> one row. The embedded prose answers "when do I reach for this," so rows
> partition the *decision space*, not the stylesheet.

Worked example: checkbox, radio, and consent share one CSS system
(`.selection-*`) yet get **three** rows — their *when-to-use* genuinely diverges
(multi-select · single-select · legal consent). The stylesheet unifies them; the
decision space separates them; the rows follow the decision space.

## 5. Tokens vs components

| Layer | Canonical home | Vectorized? | Why |
|---|---|---|---|
| **Tokens** | GitHub `tokens.json` | **No** | Flat name→value. Any agent ingests them whole and reliably. Embedding adds lossiness for zero retrieval benefit — you never want an agent *guessing* a hex from a vector. Ship the file. |
| **Components** | GitHub `components.css` + `.ds.yaml` | **Yes** | The value is retrieval-by-intent — "which component, when, why, what breaks." That's the behavioral knowledge agents fail to absorb, and the one thing embeddings genuinely help with. |

Grounding: AIF's own published articles (in the internal vector store) already
field-tested this — *agents adopt tokens fast; components and behavioral
contracts are the hard part.* This proposal is built on that finding, not
against it.

## 6. Component ledger (forms domain — the pilot)

Derived from the real seams in `assets/css/components.css` (`.form-*`,
`.selection-*`, `.input-pair`) plus the write-article template.

**Atoms**
1. **Input** — text field (`.form-group` + `.form-control` + label/helper/error/
   mandatory). **Textarea is a variant here, not a sibling** — it shares the
   entire wrapper/label/helper/error/disabled system.
2. **Select** — pick-from-known-set (`.form-select-*` popover).
3. **Datepicker** — pick-a-date. Shares the popover mechanic with Select
   (`metadata.shares_pattern: popover-panel`); rows stay separate (intent
   differs).
4. **Checkbox** — multi-select / boolean opt-in.
5. **Radio** — single-select, mutually exclusive.
6. **Consent (GDPR)** — `.selection-item--consent`; distinct legal intent
   (mandatory, quiet voice) justifies its own row despite shared CSS.
7. **SegmentedControl** ✓ — pill toggle. **PROMOTED 2026-07-04** (branch
   `forms-split`) into canonical `assets/css/components.css` as `.segmented` /
   `.segmented-option` / `.segmented-option--active`; theme-local
   `.aif-form__toggle` is now the drift to be retired at adoption. Single-select,
   drives disclosure. *None/Podcast/Video is one instance; the atom is generic.*
8. **FileDropzone** ✓ — drag&drop / click-to-browse / preview / remove.
   **PROMOTED 2026-07-04** (branch `forms-split`) as `.dropzone` /
   `.dropzone--dragover` / `.dropzone-preview` / `.dropzone-remove`; theme-local
   `.aif-publish__image-dropzone` is the drift to retire. Proven reusable —
   served both image and audio on the same template.

**Patterns**
- **Forms-composition** — the highest-value row. Holds the cross-cutting laws:
  field-scale (`.form-scale-small`/large), `input-pair` layout primitive,
  label/helper/error/mandatory conventions, validation timing, `focus-within`
  behavior, and the **disclosure/reveal** pattern (a pill reveals its panel).

**Theming contracts**
- **Markdown editor (Toast UI)** — third-party lib mounted in
  `aif-publisher/admin-editor.php` (replaces Gutenberg). DS owns only the toolbar
  theming, not the component. One theming-contract row, sits beside the
  ecomail-form harvest context, not beside Input.

**Excluded**
- `aif-publisher/review.php` — wp-admin diff screen, hardcoded hex, un-tokenized.
  Admin chrome. Never enters the store.

✓ **PROMOTION DONE (2026-07-04, branch `forms-split`).** `SegmentedControl` and
`FileDropzone` were canonicalised into `assets/css/components.css` using only
existing field tokens (zero new tokens → no token-file churn), documented as
their own Forms styleguide blocks (7 · and 8 ·), and shipped green through the
gate (css lint LAWS 1–4 ✓, `playwright tokens.spec.js` 48/48 ✓). The theme-local
`.aif-form__*` / `.aif-publish__*` markup is now the drift to retire at adoption.
Both are therefore row-eligible: `git_path: assets/css/components.css#segmented`
and `#dropzone`.

## 7. Supabase-ready row shape

Mirror the existing AIF `documents` shape exactly so the proven ingest + MCP
search cover it for free (see §9). One row per component (or per section for big
ones). `content` is embedded; `metadata` is fetched verbatim.

```jsonc
{
  "id": "uuid",
  // EMBEDDED — similarity ranks on this. Write it for recall: intent, not API.
  "content": "Button — the primary action trigger. Use for the single most \
important action in a view. Variants: primary (one per view), secondary, ghost, \
destructive (irreversible only, always paired with confirm). NOT for navigation \
(use Link). NOT for toggles (use Switch). Contract: disabled means 'not yet', \
never 'not allowed'. Loading replaces label, keeps width. A11y: visible focus …",

  // NOT embedded — the truth payload, fetched verbatim
  "metadata": {
    "name": "Button",
    "git_path": "assets/css/components.css#button",   // canonical pointer
    "git_ref": "060d1bc",                              // commit sha → staleness
    "variants": ["primary","secondary","ghost","destructive"],
    "states": ["default","hover","focus","disabled","loading"],
    "token_refs": ["--color-brand","--radius-md","--space-3"], // NAMES, not values
    "shares_pattern": null,
    "figma_node_id": "1:2345",
    "code_connect": "src/components/Button.tsx",
    "depends_on": ["Icon","Spinner"],
    "type": "component",
    "visibility": "public"
  },
  "embedding": "vector(1536)"   // text-embedding-3-small, matches the corpus
}
```

Discipline:
- `token_refs` are **names only** — values live in `tokens.json` and reach the
  agent whole.
- `git_ref` lets CI detect stale rows (row sha ≠ HEAD → re-ingest).
- One intent search returns code path **and** Figma node **and** Code Connect map
  in a single hit — that's the "ultra-fast dev" payoff and how the Figma leg is
  wired in.

## 8. Sync mechanism (proposed, not built)

- Source of change: `.ds.yaml` per component + `tokens.json`, committed to GitHub.
- On push → CI → n8n `vector-ingest` webhook (the existing AIF pattern, §9),
  **changed components only** (diff against `git_ref` stored on each row).
- `url`/stable key is the dedup key → re-ingest replaces, never duplicates.
- Note: a raw SQL edit does **not** re-embed — only re-ingesting recomputes the
  embedding. SQL-only edits are invisible to search.

## 9. Reference infra (already exists — reuse, don't invent)

AIF Supabase project `vxhhfbrxpvapxvjwgnzr` (eu-west-1, Postgres 17, `pgvector`
0.8.0, ivfflat+hnsw). Every vector store on it is the **same shape on purpose**:

| table | rows | role |
|---|---|---|
| `documents` | 89,420 | external scraped corpus |
| `aif_internal_article_vectors` | 4,301 | AIF's own articles |
| `faq_vectors` | 95 | FAQ |
| `aig_position_vectors` | 1,276 | job positions |

Table comment, verbatim: *"Standard documents shape (id, content, metadata,
embedding) … so the same n8n ingest pattern + LangChain Vector Store nodes can be
reused."* Embed model `text-embedding-3-small` (1536-dim). Ingest via the
`vector-ingest` n8n webhook. One `match_*` RPC per table. A DS store would be a
5th table in this exact mold + a `match_design_components` RPC.

## 10. Verification layer — the gate is part of the architecture

The agentic DS has a fourth surface the topology in §2 implies but never
named: **the gate**. Agents are both the main committers and the main
consumers of test feedback, so the gate is the reviewer — and our own field
finding applies (*Agent Deployment Is a Design System Decision*): incidents
that were review-detectable at human speed accumulate faster than review at
agent speed. Full design, evidence and rollout live in
**[PLAYWRIGHT-AGENTIC.md](PLAYWRIGHT-AGENTIC.md)** (researched 2026-07-06:
23 verified claims, 2 refuted); this section is the architectural summary.

**Layers** (all local, against the live :8090 stack; CI runs only L0 — it has
no WP server):

| layer | file | guards |
|---|---|---|
| L0 | `build/lint-css.mjs` + tokens-in-sync | the 3-layer/breakpoint laws, headless (CI ✓) |
| L1 | `tests/tokens.spec.js` *(exists)* | token contracts — value × surface × scale |
| L2 | `tests/layout.spec.js` *(planned)* | broken screens: overflow, occlusion (`elementFromPoint` — Playwright can't see overlap natively, won't-fix), zero-size, console errors, failed requests |
| L3 | `tests/a11y.spec.js` *(planned)* | axe, graduated Carbon-style: baseline → no-new-violations → per-tab zero ratchet |
| L4 | `tests/interactions.spec.js` *(planned)* | behavior contracts — the "right-looking wrong thing" |

**Laws** (mirror the laws in §3):

1. **Assertion-first; screenshots are for humans.** Named structured failures
   are what agents act on (Playwright MCP feeds agents accessibility trees,
   not pixels — by design). `toHaveScreenshot` as a gate was rejected on
   evidence: baselines are OS-bound, Docker determinism was refuted 0-3, and
   under agent commit velocity baseline churn turns the gate into ceremony.
   Screenshots stay an operator-judgment artifact (they caught the
   hallucinated segmented control; that is their job).
2. **Auto-discovery, never per-tab registration.** Sweeps crawl the styleguide
   sidebar at runtime — a tab added by any agent is covered the moment it
   exists. A gate that must be told about new work will miss exactly the
   newest (buggiest) work.
3. **One behavioral truth, two consumers.** The behavioral-contract prose in a
   component's vector row `content` (§7) and the assertions in its L4
   interaction test must be the same sentences. The row tells the agent what
   the component promises; the gate proves the promise still holds. If they
   diverge, one of them is lying — same corollary as §3's drift audit.

## 11. Open questions / next steps

- [x] **Promote `SegmentedControl` + `FileDropzone`** from the theme into
      canonical `components.css` (build + gate). *Done 2026-07-04, branch
      `forms-split` — see §6.*
- [x] **Split the Forms tab into per-element atom blocks** (Input → Dropzone,
      one documented block each) + a Composition section for the cross-cutting
      laws. *Done same branch; gate 48/48.*
- [ ] Retire the theme-local `.aif-form__*` / `.aif-publish__*` markup at
      adoption (point `page-author-publish.php` at the canonical classes).
- [ ] Decide `.ds.yaml` schema (fields that become `metadata`).
- [ ] Confirm: is the win big enough yet? For one repo / one maintainer, a good
      `DESIGN.md` + `tokens.json` may give ~90% of the value at ~5% of the infra.
      Vectors earn their keep when **many** agents/repos query by intent, or the
      DS is too big to hold in context. Name the concrete consumer before
      building the table.
- [ ] Draft the migration for the 5th table + `match_design_components` RPC.
- [ ] Draft the CI → n8n changed-components-only sync.
- [ ] Textarea: keep folded into Input, or split? (currently: folded — the weak
      split.)
- [ ] **Build the gate layers L2–L4** per the PLAYWRIGHT-AGENTIC rollout (§10;
      layout sweep first — it answers the operator's "broken screens pass
      undetected"). Deferred by operator 2026-07-06: document now, build later.
- [ ] When authoring `.ds.yaml` rows, source each component's behavioral prose
      and its L4 interaction assertions from the same contract text (§10 law 3).

## 12. Serving architecture — HYBRID, skill-first (researched 2026-07-06)

Question ruled on: dedicated MCP server vs "webhook skill" for consuming the
store. Answer: **neither alone — one data plane, two thin adapters**, matching
the 2026 consensus (MCP = the transport/nervous system, skills = the playbook;
you want both, each where it's cheap).

**Data plane (build once):** the 5th Supabase table + `match_design_components`
RPC (§7–9), ingested by the existing n8n `vector-ingest` webhook from CI
(changed rows only, keyed on `git_ref`). Read-only from everywhere; Supabase
stays a rebuildable cache (§3 law 1).

**Adapter 1 — the `ds-lookup` SKILL (primary; ships IN the public repo):**
a consumer-facing skill whose body carries the query contract: when to reach
for the store, how to call the search endpoint (n8n search webhook wrapping
embed+match), and the router discipline — the row returns a POINTER
(`git_path` + token NAMES); the agent then reads canon verbatim and ingests
`tokens.css`/manifest whole. Progressive disclosure = ~1 description line of
standing context. This natively reaches the ACTUAL next consumers: the two
theme-refactor agents (roadmap item 5) working inside repos, any repo that
copies the skill, and Managed Agents via the Skills API. This is the
"plugin architecture" instinct, made concrete.

**Adapter 2 — a tool on the EXISTING production MCP (secondary):** the AIF MCP
server already exposes vector-search tools over sibling tables (e.g. internal
articles); adding `ds_search_components` beside them is near-zero marginal
infra and buys the surfaces skills can't reach (claude.ai web/mobile/Desktop
connectors; `mcp_servers` on Managed Agents). NOT a new dedicated DS MCP
server — one more tool on the server that exists. Operator owns MCP config;
server-side addition needs no client JSON.

**Explicitly rejected:** a standalone DS MCP server (registration + auth +
maintenance for ~1 tool over a ~40–60-row corpus); embedding token VALUES
(§3 law 2); any write path outside CI ingest.

**Scale honesty (per §11's own question):** at ~40–60 rows the whole
decision-space index fits in one file — the embeddings' payoff here is
intent-matching (fuzzy/bilingual queries) and cross-repo reach, not scale.
The skill should therefore also name the degraded path: if the endpoint is
unreachable, read `IMPLEMENTATION_STATUS.md` (the ledger IS the row list).
