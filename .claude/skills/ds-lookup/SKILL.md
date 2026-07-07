---
name: ds-lookup
description: Semantic search over the AIF design-system component index. Use when you need to find WHICH DS component/pattern serves a need ("dark newsletter form", "threaded comments", "sticky CTA bar") before reading code — returns the component's classes, token refs, doc path, git path, specimen URL. The vector store is a ROUTER; the repo is the truth.
---

# DS component lookup

Query the design-system retrieval index (Supabase `ds_component_vectors`
via the n8n RAG search) to route yourself to the right component, then
READ THE REAL SOURCE the row points to. Never style from memory of the
search snippet — the row's `git_path` + `doc_path` are the truth.

## Query (zero config — via the n8n MCP)

Workflows (production n8n): search = `yfXHlHY4IaoVaQYF`
(`AIF DS Components RAG Search (MCP)`), ingest = `meX2IgYov6Pyn23r`
(`AIF Sub: DS Vector Ingest (components)`).

1. Self-serve the API key (never committed anywhere): read it off the
   workflow itself — `n8n_get_workflow` id `yfXHlHY4IaoVaQYF`, mode
   `filtered`, nodeNames `["Check API Key"]` → the condition's
   `rightValue`.
2. `n8n_test_workflow` → workflowId `yfXHlHY4IaoVaQYF`, POST, headers
   `{"x-api-key": <key>}`, data:

```
{
  "query": "natural-language need, e.g. dark footer email capture",
  "match_count": 8,          // CHUNK-level (a component = 1-3 chunks);
                             // over-fetch ~3x the components you want —
                             // the response dedupes to whole components
  "type": "component",       // optional containment filters:
  "status": "shipped",       //   type/status/visibility
  "_class_like": "btn",      // optional ilike over the classes string
  "_token_like": "--text",   // optional ilike over token_refs
  "_min_similarity": "0.3"   // optional floor
}
→ { results: [{ name, classes[], token_refs[], repo, git_path, git_ref,
    doc_path, specimen, figma_node_id, code_connect, status, similarity,
    contentPreview }], count }
```

External consumers (no n8n MCP): `POST <n8n-base>/webhook/rag-search-ds-components`
with the same body + `x-api-key` header — the operator wires those
(house pattern, same as the positions RAG search).

## The router contract

1. Search → pick the component(s).
2. Read `doc_path` (the component's full contract: intent, anatomy,
   variants, laws, tokens) and `git_path` (the canon CSS section) in the
   repo named by `repo` at/after `git_ref`.
3. Verify against the live specimen (`specimen` URL on the styleguide,
   both `theme=aifounders|aiguild`).
4. Compose EXISTING classes/tokens; never invent variants (one-offs are
   not variants — operator law). If nothing fits, that is a finding to
   report, not a license to freestyle.

## Freshness / rebuild

PULL model: the ingest workflow fetches `assets/ds-rows.json` straight
from GitHub (n8n "GitHub account" credential) and wipe-rebuilds the
store. Rebuild after rows change: `n8n_test_workflow` → workflowId
`meX2IgYov6Pyn23r`, POST, same key, data `{}` (optional body
`{owner, repo_name}` overrides the source repo — default is the factory
repo; flips to `aifounders-designsystem` at the public flip). Every row
carries the `git_ref` it was generated at — if repo HEAD moved far past
it, rebuild before trusting fine details.
