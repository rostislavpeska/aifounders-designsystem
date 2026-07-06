# Engagement

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#engagement` · **Specimen:** `/?aigds_styleguide=1&item=engagement`

## Intent

The article engagement row — the reader's appreciation + amplification
moment below the article body: an **Aha!** pill (the "this helped" action;
the lightbulb carries the clicked state, not the button) and a **Share**
pill, framed by hairlines, with a compact **toast** that slides down after
either action carrying cloned share buttons so the reader can share without
scrolling. Reach for it on any long-read content page. Do NOT reach for it
for comment threads (comments row) or persistent CTAs (sticky footer bar,
queued). Both themes ship it **byte-identically** (AIF
`components.css:5556-5842` / AIG `blog.css:3055-3334`, same markup, same
JS) — a verbatim cross-brand twin with zero divergences; production class
names are kept (the `aif-` prefix is already the cross-brand namespace,
newsletter-capture precedent).

## Anatomy

```html
<div class="aif-engagement"
     data-post-id="…" data-nonce="…" data-ajax-url="…"  <!-- ajax-url optional -->
     data-i18n='{"ahaLabel":"Aha!","ahaThanks":"…","shareLabel":"…"}'>
  <div class="aif-engagement__row">
    <button type="button" class="aif-aha" aria-pressed="false" aria-label="Aha!">
      <span class="aif-aha__icon-wrap"><?php echo aigds_icon( 'lightbulb', array( 'size' => 24 ) ); ?><?php echo aigds_icon( 'lightbulb-filled', array( 'size' => 24 ) ); ?></span>
      <span class="aif-aha__label">Aha!</span>
      <span class="aif-aha__count">12</span>
    </button>
    <button type="button" class="aif-share" aria-label="Share article">
      <span class="aif-share__icon-wrap"><?php echo aigds_icon( 'share', array( 'size' => 24 ) ); ?></span>
      <span class="aif-share__label">Share article</span>
      <span class="aif-share__count" hidden>0</span>
    </button>
  </div>
  <div class="aif-engagement-toast" hidden>
    <div class="aif-engagement-toast__title">Share with someone…</div>
    <div class="aif-engagement-toast__buttons"></div>  <!-- JS clones .a2a_kit here -->
    <button type="button" class="aif-engagement-toast__close" aria-label="Close">&times;</button>
  </div>
</div>
```

- `.aif-engagement` — hairline frame (`--stroke-1` `--border` top+bottom),
  vertical rhythm `--spacing-32`/`--spacing-24`.
- Pills — ghost buttons: accent font (`--font-accent`), `--body-md-size`,
  bold, `--leading-heading`; `--text-tertiary` at rest,
  `--button-tertiary-bg-hover` fill on hover; counts are `tabular-nums`.
- Aha! clicked (`.aif-aha--clicked`) — the outline bulb swaps to the
  **filled** one (colored shape icon, baked yellow + dark stroke) with the
  harvested 0.6s pulse; text jumps to `--text`; background stays
  transparent. `aria-pressed` mirrors the state.
- Toast — `--raised` pill with `--shadow-sm`; slide-down via the harvested
  max-height engine (0 → 200px ceiling); title = accent `--caption-size`
  regular; close = `--icon-size-default` ghost button; stacks to a column
  ≤599px.
- In-toast kit — squared (`border-radius: 0`), **desaturated at rest**
  (`grayscale(0.7) opacity(0.7)`, full color + 1px lift on hover); the
  AddToAny "+" overflow is hidden.

## Behavior (`js/components/engagement.js` — the production engine, ported)

Aha! click → optimistic +1 + `--clicked` + localStorage → AJAX `aif_aha` →
server count reconciles → toast opens (label swaps to the thanks message).
Share click → toast opens with no Aha! side effects. The toast **clones the
first `.a2a_kit`** on the page and rewrites AddToAny's placeholder hrefs
into real share URLs (facebook/x/linkedin/email/mastodon/reddit/whatsapp/
telegram/copy-link — the plugin's own JS does not bind on clones);
auto-hides after 8s, hover pauses the timer. Kit clicks fire AJAX
`aif_share` + platform. localStorage is UX-only; the server transient is
the dedup source of truth. **Without `data-ajax-url` the engine degrades
gracefully** to optimistic UI (the specimen runs this way).

## THE ADDTOANY BOUNDARY

The DS owns: the widget, the toast, the in-toast kit look, the clone+rewrite
engine. Theme/plugin territory: the AddToAny plugin itself (renders the
source kit), the AJAX counting endpoints (`aif_aha`/`aif_share`, nonce,
5-min dedup), and the `.single` rules that HIDE the standalone plugin
output in the article column.

## Variants

None. States: rest / hover / `--clicked` / toast `--open` / share count
`[hidden]` at zero.

## Responsive

Toast stacks to a column ≤599px (GM: harvested 600 snapped to the closed
set). Pills wrap via the row's `flex-wrap`.

## Tokens referenced

`--spacing-8` `--spacing-12` `--spacing-16` `--spacing-24` `--spacing-32`
`--spacing-48` `--stroke-1` `--border` `--font-accent` `--body-md-size`
`--caption-size` `--weight-bold` `--weight-regular` `--leading-heading`
`--leading-none` `--size-20` `--text` `--text-tertiary`
`--button-tertiary-bg-hover` `--raised` `--shadow-sm` `--icon-size-default`
`--transition-fast` `--transition-normal`

## Surfaces

Roles re-resolve (hairlines, tertiary text, hover fill, raised toast) —
but production only renders it on the light article column; **unharvested
on dark**. The filled bulb is a colored shape icon (baked yellow) — exempt
from currentColor, identical on both brands per the harvest.

## Known friction

- GM exceptions: toast shadow (harvested two-layer 4%/3%) → `--shadow-sm`;
  toast slide 250ms → `--transition-normal` (the JS hide-timer stays 250ms,
  safely ≥ the transition); breakpoint 600 → 599.
- Harvested constants: the aha-pulse keyframes (0.6s scale+wiggle), the
  200px toast slide ceiling, the kit desaturation pair.
- The sweep still tracks `.aif-engagement-toast` as a candidate GENERIC
  feedback primitive — unchanged by this row.
- At adoption both themes' engagement CSS blocks and `engagement.js` are
  deleted in favor of the DS copies (identical anyway); the theme keeps its
  AJAX handlers + the AddToAny-hiding rules.
