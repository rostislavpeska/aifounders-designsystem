# Button

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#buttons` · **Specimen:** `/?aifds_styleguide=1&item=buttons`

## Intent

The action trigger. Reach for a button when the user *does* something —
submits, reserves, confirms, deletes. Hierarchy carries meaning: **primary**
(brand fill) is the single most important action in a view; **secondary**
(transparent, brand border) is the alternative path; **tertiary** (transparent,
neutral border) is the quiet utility action. NOT for navigation inside prose —
that is a link (see `links.md`); the `.btn--link` variant exists only for
action-shaped text triggers (e.g. "remove", destructive text actions). Buttons
are square-cornered (no radius, per the radius retirement) and read ONLY
component/semantic tokens, so the same markup resolves correctly on every
surface — the AIF newsletter button on the brand hero goes dark automatically
(the newsletter law; no special class).

## Anatomy

```html
<button class="btn btn--md btn--primary">Reserve</button>
<a class="btn btn--lg btn--secondary" href="…">
  <?php echo aifds_icon( 'arrow-right' ); ?> With icon
</a>
<div class="button-group">…buttons…</div>
```

- `.btn` — base (inline-flex, `--stroke-2` transparent border, no radius,
  `--transition-button`, nowrap).
- One size class + one hierarchy class, always.
- `.button-group` — LAYOUT-ONLY wrapper (operator ruling): spacing between
  buttons, no visual styling of its own.
- Icons inside buttons: `--icon-size-md` (20px); `.btn--sm` drops to 16px
  (calibrated, no token).

## Variants

| Class | Role |
|---|---|
| `.btn--primary` | brand fill — one per view; surface-aware via `--button-bg/text/border` |
| `.btn--secondary` | transparent, brand border; hover fills brand |
| `.btn--tertiary` | transparent, neutral border; hover raises |
| `.btn--link` | text-shaped action; follows the global link idiom (1px/4px, hover 2px — GM exception vs live) |
| `.btn--link.btn--destructive` | destructive text action — `--status-error` |
| `.btn--lg` / `--md` / `--sm` | CALIBRATED ladder: 60/52/38px heights, 20/16/10px x-padding (harvested constants, off the spacing scale); `--sm` uses the `button-small` type bundle |
| `.smart-btn` | AIG AI-driven CTA wrapper — IS the support surface scope; reads its own surface roles |

Deprecated (markup-compat only, rationalization queue — see
`IMPLEMENTATION_STATUS.md`): `.btn--outline` (≡ secondary),
`.btn--primary-inverted` (reclassified: the legacy newsletter CTA, not a
hierarchy member), `.btn--secondary-inverted` / `.btn--tertiary-inverted`
(superseded by surface-aware tokens).

## States

- **hover** — per hierarchy (`--button-bg-hover`, secondary fills +
  `--button-secondary-text-hover`, tertiary `--button-tertiary-bg-hover`).
  Icon strokes follow the hover TEXT role, not the surface text.
- **disabled** — surface-aware tier-3 tokens (operator 2026-07-02):
  `--disabled-bg/text/border`; primary fills gray, secondary keeps border,
  tertiary goes borderless; `cursor: not-allowed`.
- **focus** — inherits the global focus treatment.

## Responsive

`.btn--sm` height rises 38 → 44px under 768px (touch target). Other sizes
constant.

## Tokens referenced

`--button-font` `--button-weight` `--button-size` `--button-small-font/size/weight`
`--button-bg` `--button-bg-hover` `--button-text` `--button-border`
`--button-border-hover` `--button-secondary-text/border/bg-hover/text-hover`
`--button-tertiary-text/border/bg-hover` `--disabled-bg/text/border`
`--stroke-2` `--transition-button` `--spacing-6/8` `--icon-size-md`
`--text-on-brand` `--status-error` (destructive) `--font-accent` `--weight-bold`
(link variant). Inverted aliases additionally touch `--black/--paper/--dark-*`
(their deprecation reason).

## Surfaces

The three-tier repoint (Batch 1.5 step 2) means buttons never know their
surface: on the **brand** surface primary resolves dark automatically (the
newsletter law); on **dark** surfaces tertiary reads dark-scope tokens.
Newsletter CTAs are PLAIN hierarchy classes on the right surface —
`.btn--newsletter-*` never shipped and was deleted (operator 2026-07-02).

## Known friction

- One primary per view is a convention the DS cannot enforce — review surface.
- `.btn--link` deliberately diverges from the harvested live state (live
  removed the underline on hover — "feedback by subtraction", outlawed);
  GM exception documented in the CSS.
- The size ladder is calibrated raw px — do not "fix" it onto the spacing
  scale.

## Reserved: button-group alignment axis

`.button-group--center` / `.button-group--right` — harvested alignment
variants of the layout primitive; no DS specimen composes them yet
(reserved API, audit 2026-07-06). Retire-or-demo at adoption review.
