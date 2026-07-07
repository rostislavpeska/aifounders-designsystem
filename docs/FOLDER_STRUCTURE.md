# AI Founders Design System — Folder Structure

**Version:** 2.0 · **Last updated:** 2026-07-04 (deep-hygiene run)

The repo is a **WordPress plugin** (v2). The v1 static-HTML generation is
archived in `dev/v1-archive/`.

```
aig-desigsystem/
├── aifounders-designsystem.php    # Plugin entry (Stage 1: registered, not enqueued on themes)
├── README.md                # Project overview + doc index
├── AGENT_README.md          # Quick-start for agents
├── REPOSITORY_RULES.md      # Mandatory rules (harvest law, RITUAL, friction)
├── COPY_MANIFEST.json       # What copies to the WP themes at adoption
├── package.json             # scripts: tokens:build, lint:css, test
├── playwright.config.js     # Gate config (targets localhost:8090)
│
├── tokens/                  # ★ TOKEN SOURCE OF TRUTH (JSON, hand-edited)
│   ├── palette.aifounders.json   # Tier 1 — per-brand values only (62 names, parity law)
│   ├── palette.aiguild.json      # Tier 1 — same name set, brand values
│   ├── semantic.json             # Tier 2 — single-hop {palette-ref} (48 names)
│   ├── scopes/                   # Tier 3 — per-surface overrides (no new names)
│   │   ├── brand.json  support.json
│   │   ├── dark-1.json dark-2.json dark-3.json
│   │   └── light-2.json light-3.json
│   ├── base.tokens.json          # spacing · containers · strokes · shadows · field tokens
│   ├── typography.json           # sizes · leading · flow · fonts · weights · transforms
│   ├── type-styles.json          # 18 style bundles (single-hop {primitive} refs)
│   ├── breakpoints.json          # the CLOSED SET: 600/768/1024/1440
│   ├── brand.aifounders.tokens.json  # ratified per-brand diverged props
│   ├── brand.aiguild.tokens.json
│   └── rename-map.json           # v1→v2 names — the theme-adoption mapping (keep)
│
├── build/
│   ├── build.mjs            # tokens/*.json → assets/css/tokens.css + manifest
│   ├── lint-css.mjs         # THE FOUR LAWS (inversion, legacy names, wildcards, breakpoints)
│   ├── compare.mjs          # harvest comparison helper
│   └── migrate.mjs          # one-time v1→v2 migration (historical)
│
├── assets/
│   ├── css/
│   │   ├── tokens.css       # GENERATED — never hand-edit
│   │   ├── components.css   # ★ hand-written components (banner sections)
│   │   ├── fonts.css        # self-hosted font faces
│   │   └── normalize.css    # normalize v8
│   ├── fonts/  img/
│   └── tokens-manifest.json # GENERATED token manifest
│
├── inc/
│   ├── enqueue.php          # asset registration (normalize → fonts → tokens → components)
│   ├── icons.php            # inline SVG icon system (aifds_icon(), taxonomy, stroke law)
│   └── styleguide.php       # /?aifds_styleguide=1 — specimens for every component
│
├── js/
│   ├── main.js              # global only (theme switcher)
│   └── components/          # class-toggling behavior, zero style injection
│       ├── accordion.js  datepicker.js  dropdown.js  menu.js
│
├── icons/
│   ├── custom/              # AI Guild custom art (incl. size-locked colored icons)
│   └── lucide/              # Lucide imports (outline-only)
│
├── tests/
│   └── tokens.spec.js       # THE GATE — 52 assertions × both brands vs localhost:8090
│
├── docs/
│   ├── DESIGN_SYSTEM_GUIDE.md      # architecture & quality guide
│   ├── IMPLEMENTATION_STATUS.md    # ★ component ledger (one row per shipped unit)
│   ├── components/                 # ★ per-component contracts (future VECTOR-DS rows)
│   ├── DECISIONS.md                # authoritative operator decision log
│   ├── HANDOFF.md                  # live handoff brief for incoming agents
│   ├── FOLDER_STRUCTURE.md         # this file
│   ├── HERO_PATTERN.md  PAGE_LAYOUT_ARCHITECTURE.md   # narrative pattern docs
│   ├── proposals/                  # research + proposals (BREAKPOINTS, DATA-TABLES,
│   │                               #   RECORD-LIST, HEADING-RAMP, INDENT-SIGNIFIER,
│   │                               #   VECTOR-DS, AGENTIC-DS-RESEARCH)
│   └── handoff/                    # WP integration guides + pointer README
│
├── dev/
│   └── v1-archive/          # the retired v1 static generation (css, html)
│
└── .claude/skills/          # operating manuals — ds-tokens is the GENERIC
                             #   token entry (routes → ds-colors / ds-typography,
                             #   handles foundations itself) · ds-distill (harvest)
```

## The two generated files

`assets/css/tokens.css` and `assets/tokens-manifest.json` are build outputs of
`node build/build.mjs`. Never hand-edit them — edit `tokens/*.json` and
rebuild. Everything else in `assets/css/` is hand-written.

## What copies to WordPress at adoption

Governed by `COPY_MANIFEST.json` (tokens.css, components.css, fonts,
`js/components/*`, custom icons). Docs, tests, build tooling, and `dev/`
never ship.
