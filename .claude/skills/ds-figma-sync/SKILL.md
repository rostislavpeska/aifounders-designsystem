---
name: ds-figma-sync
description: Propagate a CANON change into the existing AIF/AIG Figma file (roadmap item 2, the sync leg). Use when a token value, type primitive, or component contract changed in the repo and the Figma projection must be brought back into agreement — NOT for the first-time build (that is ds-figma-import). Encodes the concrete file, its structure, the token-delta protocol, and the contribution laws; delegates all Plugin-API mechanics to the bundled figma-use + figma-generate-library skills.
---

# DS → Figma sync (update an existing projection)

The code is the source of truth; the Figma file is a **projection that has
already been built** (ds-figma-import, session 1). This skill updates that
projection when canon moves. It never authors values in Figma and never
invents structure — every value comes from the repo, every node is addressed
by the id already recorded in the ledger.

If the file is empty / no ledger exists → this is not a sync, it is an import:
use **ds-figma-import** instead.

## The concrete target (never re-derive — read the ledger)

**`assets/figma-map.json` is the authoritative address book AND resume
ledger.** It carries the real ids for everything below; address nodes by id
from it, never by re-deriving names.

- **File:** `HHYhpNh2JoxtKSKm7Ecpli` — team **"My Projects"**, Figma
  **Professional**, Full seat. (Professional = **max 10 modes per collection**
  since the Oct-2025 Schema-2025 raise — the architecture uses 8; never
  `addMode` past 10.)
- **4 variable collections** (one axis each — see the laws):
  - **Palette** — modes `AIF · AIG`. 64 named colour values (+ 8 brandType
    non-colour diverged tokens). The ONLY place literal values live.
  - **Semantic** — modes = the **8 CSS scopes** `Light-1/2/3 · Dark-1/2/3 ·
    Brand · Support`. 51 roles, **every value a VARIABLE_ALIAS into Palette**
    (manifest `ref`). Zero raw hex here, ever.
  - **Base** — single `Value` mode. 59 non-colour primitives (spacing, radius,
    strokes, containers, shadows, transitions, field/icon sizes).
  - **Type Primitives** — modes `Desktop · Mobile` (the clamp() answer). 42
    manifest primitives + 6 materialised clamp sizes.
- **19 text styles** (`Display/* · Heading/* · Body/* · UI/*`), **5 effect
  styles** (`Shadow/*`), **55 icon components**, and the component sets keyed
  by ds-row name across `components`, `componentsS2/S3/S4` (Tier 0 atoms →
  organisms). Ids for all of these are in the ledger.

## Preconditions (non-negotiable)

1. Load the bundled **figma-use** skill BEFORE every `use_figma` call
   (hard prerequisite); **figma-generate-library** for quality bars. This
   skill does not repeat the Plugin API — it binds you to those.
2. `whoami` first; work only in **"My Projects"**. Read
   **docs/proposals/FIGMA-IMPORT.md** (the structure canon this skill syncs to)
   and **assets/figma-map.json** (the ids).
3. Source of values: **`assets/tokens-manifest.json`** (generated) —
   `palette[name].{aif,aig}`, `semantic[name].ref`, `scopes`, `base`,
   `typePrimitives`, `brandType.{aif,aig}`. Never read values off Figma.

## What changed → which path

Compute the delta first. The ledger records the git ref it was last synced at
(`syncedRef`; if absent, this is the **bootstrap** — see below):

```
git diff <syncedRef>..HEAD --stat -- tokens/ assets/css/components.css docs/components/
```

- Only `tokens/**` moved → **Path A (variables)** — deterministic.
- `components.css` or a `docs/components/*.md` contract moved → **Path B
  (components)** — manual, per changed set.
- A row set changed (`assets/ds-rows.json`) → also run `node build/rows.mjs`
  and refresh `figma_node_id` backfill; not a Figma write on its own.

**Bootstrap (no `syncedRef` yet):** do a full **value reconcile** — for every
Palette / Base / Type-Primitive variable in the ledger, read its expected
value(s) from `tokens-manifest.json` and compare to Figma; write only the
mismatches. Then stamp `syncedRef: <HEAD sha>` into the ledger. Every later run
is the cheap git-diff delta above.

## Path A — variables (deterministic, by id)

1. `node build/build.mjs` so `tokens.css` + `tokens-manifest.json` are current;
   optionally `node build/compare.mjs <old> <new>` to see the exact changed
   property set per scope.
2. For each changed token, look up its **VariableID in the ledger** and write
   with the Plugin API (figma-use):
   - **Palette** value change → `setValueForMode` on **both AIF and AIG** modes
     from `manifest.palette[name]`.
   - **Base** → the single `Value` mode from `manifest.base[name]`.
   - **Type Primitives** → `Desktop` and `Mobile` modes (only the 6 materialised
     sizes differ per mode; the rest are equal).
   - **Semantic** → almost never a *value* write: a semantic change is an
     **alias re-point** (`ref` moved to a different palette name). Re-bind the
     alias; never resolve it to a hex.
3. **Never** create a new variable, mode, or collection here. A new token in the
   repo is a **structure** change that needs operator approval (hardened token
   law) and belongs to an import/extend session, not a value sync.
4. Verify: swatch/foundation boards render per mode; **zero raw-hex on any
   Semantic variable** (alias integrity); variable counts still 64+8 / 51 / 59 /
   42(+6).

## Path B — components (manual, per changed set)

Components were hand-built with Auto Layout and have **no Code Connect**
(Professional plan) — there is no automated code→component update. For each
changed component:

1. Re-read its contract (`docs/components/<name>.md`) and the live specimen
   `http://localhost:8090/?aifds_styleguide=1&item=<tab>&theme=<brand>`.
2. Update **only** the changed component set (id in the ledger), re-binding any
   variables that moved. Watch the recorded gotchas (below).
3. `get_screenshot` the set against the specimen on **both brands** and the
   scopes it rides; on mismatch STOP → inspect → fix → re-run.
4. Update the ledger; if the row content moved, `node build/rows.mjs`.

## The contribution laws (what you may and may not do in Figma)

1. **Code is canon; Figma is downstream.** A wrong value in Figma is fixed by
   editing the token JSON and re-syncing — **never** by typing a value in Figma.
2. **One axis per collection, never duplicated:** brand lives only in Palette,
   scope only in Semantic, breakpoint only in Type Primitives. Never add a mode
   that reintroduces another axis.
3. **Idempotent, id-addressed writes.** Match by the ledger id (and stable
   `setSharedPluginData` key); check-before-write; never create a second node
   with an existing name.
4. **codeSyntax WEB is sacred** — it is the exact `var(--…)` name and carries
   token fidelity regardless of plan. Never edit or derive it from a Figma name.
5. **Semantic = alias, not value.** Zero raw hex on a role. Palette is the only
   literal-value collection.
6. **New token / mode / component / variant → operator approval, always**
   (hardened token law). Sync propagates existing structure; it does not grow it.
7. **THE RITUAL after any write:** `node build/build.mjs` → `node
   build/lint-css.mjs` → (`node build/rows.mjs` if a component/contract moved) →
   update `assets/figma-map.json` **incl. `syncedRef`** → conventional commit →
   report to operator with before/after screenshots. Never weaken a gate.

## Known live-file carve-outs (from the ledger `notes` — do not "fix")

- **Lazzer is not installed in Figma** → display styles (Hero/Title/XL/LG,
  MD-aig) render Inter (next in the font-display stack). Expected until Lazzer
  is uploaded; do not rebind fonts to "fix" it.
- **lineHeight variable binding reads FLOAT as PX** → leadings are static
  PERCENT on the text styles; the AIG per-mode leading divergence
  (`heading-sm-leading`, `lead-leading`) is **not representable per-mode** —
  AIF values carry. Documented limitation, not drift.
- **Icon component root fills must be transparent** (createNodeFromSvg emits
  opaque white; cleared S1). After any icon touch, re-check the root fill.
- **INSTANCE_SWAP drops per-vector stroke bindings** when the swapped icon's
  structure differs — after swapping an icon inside a Button/Badge/Input
  instance, re-bind the vector strokes to the consuming text role.

## Optional: deterministic delta plan

`build/figma-sync-plan.mjs` (spec — build on request): reads `figma-map.json`
`syncedRef`, `git diff`s `tokens/**` to HEAD, intersects changed token names
with the ledger variable map, and emits `{name, variableId, collection,
modes, expected}` per change (values from `tokens-manifest.json`). Pure Node,
no Figma — makes Path A a checklist the agent executes rather than a re-walk.
Mirrors the `git_ref`-driven discipline of `build/rows.mjs`.
