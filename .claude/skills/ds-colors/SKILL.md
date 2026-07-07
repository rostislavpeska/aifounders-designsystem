---
name: ds-colors
description: Operate the design system's 3-layer color architecture — add/edit palette colors (tier 1), semantic tokens (tier 2), and on-background transforms (tier 3), plus rename/retire operations. Use for ANY color change in the aiguild-design-system repo — "this border is too dark", "add a new color", "make X lighter on dark", "the button on brand should be...", "add a colored section background". Every operation ends with the same ritual — build, gate, commit — and the skill encodes the laws that keep the system from collapsing (values only in the palette, single-hop refs, scopes add no names).
---

# DS Colors — agentic operations on the 3-layer color system

Repo: `C:\Users\TIGO\Desktop\WORKSPACE\aiguild-design-system`

## The architecture (30 seconds)

| Layer | File(s) | Holds |
|---|---|---|
| 1 · Palette | `tokens/palette.aiguild.json` + `tokens/palette.aifounders.json` | NAMED unique colors — the ONLY place values live. Same names, per-brand values. |
| 2 · Semantic | `tokens/semantic.json` | ONE vocabulary (~45 tokens: text, border, button-bg, field-bg…). Each is a single-hop `{palette-name}` reference. One file serves both brands. |
| 3 · Transforms | `tokens/scopes/{light-2,light-3,dark-1,dark-2,dark-3,brand,support}.json` | Per-background DELTAS re-declaring the SAME semantic names with different palette refs. |

Emitter: `build/build.mjs` (plain script, no framework). Output: `assets/css/tokens.css` + `assets/tokens-manifest.json` (the styleguide renders from the manifest — pages can never lie).

## THE LAWS (violating these collapses single-source-of-truth)

1. **Values live ONLY in the palette.** A semantic token or scope delta NEVER holds a hex — only `{palette-name}`.
2. **Single hop.** Semantic tokens reference palette names, never other semantic tokens. (Kills the CSS var() resolution gotcha; keeps every chain readable.)
3. **Scopes add no names.** A transform re-declares an EXISTING semantic token. If you think a scope needs a new name, that's a tier-2 addition first — operator sign-off.
4. **No speculative scopes.** A new background scope file is created when a real section exists on a live site, never before.
5. **Growth budget:** new element → 0 tokens · new background → 1 scope file · new palette color or semantic token → operator sign-off.
6. **Temperature law:** AIF (blue brand) neutrals are COLD-tinted, AIG warm/neutral. Never copy an AIG value into the AIF palette file unchanged if it has a warm tint (R>B). Mirror law: `secondary-*` palette entries are the exact values of the sibling brand's primary family.

## The decision guide — "this color looks wrong"

Ask which of two edits is meant, they have different blast radii:
- **The role points at the wrong color** → repoint the ref (tier 2 or 3 edit). Only that role changes.
- **The color itself is wrong everywhere** → edit the palette value (tier 1 edit). Everything referencing it follows.
State the blast radius to the operator before committing.

## Operations

### 1 · Add a palette color (tier 1)
1. Pick the name by convention: neutrals = `gray-NNN`/`dark-NNN` (numbered by lightness, higher = darker), brand family = words (`brand`, `deep`, `support-strong`…), accents = color words (`magenta-*`, `lime-*`).
2. Add the SAME key to **BOTH** `palette.aiguild.json` and `palette.aifounders.json` with per-brand values (temperature law!). A key existing in only one file = broken AIF/AIG rendering; the gate's no-empty-chips assertion catches it.
3. Ritual (below).

### 2 · Edit a palette color (tier 1)
1. Change `$value` in one or both palette files. Blast radius = every token referencing the name — grep `{name}` in `tokens/semantic.json` + `tokens/scopes/` and LIST the affected tokens in the report.
2. Ritual. Expect gate assertions holding hex literals (`tests/tokens.spec.js` BRANDS table) to need the new value — update them as part of the same commit, never loosen them.

### 3 · Add a semantic token (tier 2) — requires operator sign-off
1. Confirm no existing token covers the role (read `semantic.json` first — 45 names; duplicating a role is the collapse he fears).
2. Add `"name": { "$value": "{palette-name}" }` to `tokens/semantic.json`. Name = role words (`field-bg`, `button-border`), never a color word.
3. Consumers use `var(--name)`. Ritual.

### 4 · Edit a semantic token (tier 2)
Repoint its `$value` to a different `{palette-name}`. Never a raw value. Ritual.

### 5 · Add a transform (tier 3)
1. The token must already exist in `semantic.json` (Law 3).
2. Add `"token": { "$value": "{palette-name}" }` to the right `tokens/scopes/*.json`. The matrix column updates by itself.
3. Ritual + add/adjust a gate assertion if this is a design ruling (rulings live in the gate, not in memory).

### 6 · Edit a transform (tier 3)
Change the ref in the scope file. Example (operator ruling 2026-07-03): perex border on brand yellow → support tint = one line in `scopes/brand.json`: `"perex-border": { "$value": "{support}" }` + updating the perex-map assertion.

### Also covered (don't create separate skills for these)
- **Rename a token**: update `semantic.json` + all `scopes/*.json` + grep-replace `var(--old)` in `assets/css/components.css` and `inc/*.php` — the lint fails the build on any leftover legacy name.
- **Retire a palette color**: repoint all referencing tokens first, then delete from BOTH palette files.
- **Add a background scope**: only for a section that exists live (Law 4). New file in `tokens/scopes/`, register its selector in the `SCOPES` list in `build/build.mjs`, and add the label in `aifds_sg_item_colors()`'s `$scope_cols`.
- **Audit**: the Colors tab (`http://localhost:8090/design-system/` → Colors) is generated and complete by construction — if something isn't there, it doesn't exist.

## THE RITUAL — every operation ends exactly like this

```bash
cd C:\Users\TIGO\Desktop\WORKSPACE\aiguild-design-system
node build/build.mjs        # regenerate tokens.css + manifest
npm run test:tokens         # lint (3-layer laws) + 40-assertion Playwright gate, BOTH brands
```
- Gate red → fix the cause; NEVER weaken an assertion to pass. If the change IS a new ruling, update the assertion to the new expected value in the same commit.
- Show the operator the diff + blast radius, then conventional commit (`feat(tokens):` / `fix(tokens):`). **No `git push` unless the operator asked.**
- Verify visually: the Colors tab + the component pages that consume the changed token.

## Gotchas (paid for in operator hours)
- Windows bash heredocs can smuggle a literal BACKSPACE (0x08) into files via `\b` — write regexes with the Edit tool, diagnose ghosts with `od -c`.
- Playwright reports computed stroke/calc values as strings like `calc(1.41px)` — regex-parse before comparing.
- The styleguide runs on the AIF docker stack at `:8090` (`?aifds_styleguide=1&item=colors&theme=aiguild|aifounders`); docker must be up.
