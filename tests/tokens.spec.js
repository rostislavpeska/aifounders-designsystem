/**
 * Token-tier computed-style assertions — the automated gate the surface bug
 * demanded: real browser, real cascade, per brand. If a component token
 * fails to re-resolve on a surface, these fail loudly.
 */
const { test, expect } = require('@playwright/test');

const BRANDS = {
  aiguild: {
    brandColor: 'rgb(245, 196, 0)',      // --brand
    darkBg: 'rgb(7, 7, 8)',              // --black
    textPrimary: 'rgb(7, 7, 8)',         // --text
    inverseText: 'rgb(255, 253, 246)',   // --paper
    inverseLink: 'rgb(255, 216, 77)',    // --brand-bright
    badgeDarkBg: 'rgb(60, 60, 60)',      // --dark-600
    lightLink: 'rgb(138, 106, 0)',       // --brand-ink
    support: 'rgb(255, 243, 176)',       // --support
    supportStrong: 'rgb(255, 230, 128)', // --support-strong
    borderStrong: 'rgb(206, 206, 206)',  // --border-strong
    deepMuted: 'rgb(85, 67, 9)',         // --deep-muted (#554309, operator-approved twin)
    tileInk: 'rgb(7, 7, 8)',             // --text-on-brand (dark digit on the number tile)
    textSecondary: 'rgb(66, 66, 66)',    // --text-secondary → gray-700 (info-box neutral accent)
  },
  aifounders: {
    brandColor: 'rgb(0, 181, 255)',
    darkBg: 'rgb(5, 7, 10)',
    textPrimary: 'rgb(5, 7, 10)',
    inverseText: 'rgb(246, 253, 255)', // COLD near-white (operator: blue theme = colder tone; was the warm AIG copy)
    inverseLink: 'rgb(0, 181, 255)',
    badgeDarkBg: 'rgb(72, 79, 89)',
    lightLink: 'rgb(0, 112, 243)',
    support: 'rgb(201, 241, 255)',
    supportStrong: 'rgb(100, 213, 255)',
    borderStrong: 'rgb(208, 213, 221)',
    deepMuted: 'rgb(9, 69, 110)',        // --deep-muted (#09456e, harvested)
    tileInk: 'rgb(11, 15, 20)',          // --text-on-brand (dark digit on the number tile)
    textSecondary: 'rgb(84, 90, 101)',   // --text-secondary → gray-700 (info-box neutral accent)
  },
};

for (const [brand, c] of Object.entries(BRANDS)) {
  test.describe(`surface tiers — ${brand}`, () => {
    test.beforeEach(async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=surfaces&theme=${brand}`);
    });

    const cell = (page, surface) => page.locator(`[data-surface="${surface}"]`);

    test('primary button: brand color on light, DARK on brand surface (newsletter law)', async ({ page }) => {
      await expect(cell(page, 'light-1').locator('.btn--primary')).toHaveCSS('background-color', c.brandColor);
      await expect(cell(page, 'brand').locator('.btn--primary')).toHaveCSS('background-color', c.darkBg);
      await expect(cell(page, 'brand').locator('.btn--primary')).toHaveCSS('color', c.inverseText);
    });

    test('secondary/tertiary labels are READABLE on dark surfaces (the regression)', async ({ page }) => {
      for (const s of ['dark-1', 'dark-2', 'dark-3']) {
        await expect(cell(page, s).locator('.btn--secondary')).toHaveCSS('color', c.inverseText);
        await expect(cell(page, s).locator('.btn--tertiary')).toHaveCSS('color', c.inverseText);
      }
    });

    test('badge is visible on every surface incl. dark (new definition) and brand (harvested)', async ({ page }) => {
      await expect(cell(page, 'dark-1').locator('.badge--default')).toHaveCSS('background-color', c.badgeDarkBg);
      await expect(cell(page, 'dark-1').locator('.badge--default')).toHaveCSS('color', c.inverseText);
      await expect(cell(page, 'brand').locator('.badge--default')).toHaveCSS('background-color', c.darkBg);
    });

    test('links re-resolve per surface', async ({ page }) => {
      await expect(cell(page, 'light-1').locator('a:not(.btn):not(.badge)')).toHaveCSS('color', c.lightLink);
      await expect(cell(page, 'dark-1').locator('a:not(.btn):not(.badge)')).toHaveCSS('color', c.inverseLink);
      // HOVER reads the link-hover ROLE (audit 2026-07-03: was a hardcoded
      // tint-dark that froze dark scopes inside main) — dark hover = support tint
      const darkLink = cell(page, 'dark-1').locator('a:not(.btn):not(.badge)');
      await darkLink.hover();
      await expect(darkLink).toHaveCSS('color', c.support);
      await page.mouse.move(0, 0);
    });

    test('link idiom: resting = 1px hairline at 4px offset, everywhere', async ({ page }) => {
      for (const s of ['light-1', 'dark-1', 'brand']) {
        const link = cell(page, s).locator('a:not(.btn):not(.badge)');
        await expect(link).toHaveCSS('text-decoration-thickness', '1px');
        await expect(link).toHaveCSS('text-underline-offset', '4px');
      }
    });

    test('brand link hover: underline STAYS, thickens to 2px, offset holds at 4px', async ({ page }) => {
      const link = cell(page, 'brand').locator('a:not(.btn):not(.badge)');
      await link.hover();
      await expect(link).toHaveCSS('text-decoration-line', 'underline');
      await expect(link).toHaveCSS('text-decoration-thickness', '2px');
      await expect(link).toHaveCSS('text-underline-offset', '4px');
      await page.mouse.move(0, 0);
    });

    test('BRAND surface rulings: secondary dark+white, tertiary support, badge inverted', async ({ page }) => {
      const brandCell = cell(page, 'brand');
      await expect(brandCell.locator('.btn--secondary')).toHaveCSS('border-color', c.darkBg);
      // dark text by default (contrast on brand bg); white only on dark hover
      await expect(brandCell.locator('.btn--secondary')).toHaveCSS('color', c.textPrimary);
      await brandCell.locator('.btn--secondary').hover();
      await expect(brandCell.locator('.btn--secondary')).toHaveCSS('background-color', c.darkBg);
      await expect(brandCell.locator('.btn--secondary')).toHaveCSS('color', c.inverseText);
      await page.mouse.move(0, 0); // un-hover
      // unified with the support surface (minimalism ruling): support-strong band
      await expect(brandCell.locator('.btn--tertiary')).toHaveCSS('border-color', c.supportStrong);
      await expect(brandCell.locator('.btn--tertiary')).toHaveCSS('color', c.textPrimary);
      await expect(brandCell.locator('.badge--default')).toHaveCSS('background-color', c.darkBg);
      await expect(brandCell.locator('.badge--default')).toHaveCSS('color', c.borderStrong); // former bg → text
      // badge hover glow on brand = LIGHT overlay via the overlay-hover role
      // (audit 2026-07-03: brand/support scopes now remap it like dark scopes).
      // Poll: the ::after fades in over transition-fast — a one-shot read races it.
      await brandCell.locator('.badge--default').hover();
      await expect.poll(() =>
        brandCell.locator('.badge--default').evaluate((el) => getComputedStyle(el, '::after').backgroundColor)
      ).toBe('rgba(255, 255, 255, 0.1)');
      await page.mouse.move(0, 0);
    });

    test('SUPPORT surface rulings: tertiary uses support-strong band, badge as on brand', async ({ page }) => {
      const s = cell(page, 'support');
      await expect(s.locator('.btn--tertiary')).toHaveCSS('border-color', c.supportStrong);
      await expect(s.locator('.btn--tertiary')).toHaveCSS('color', c.textPrimary);
      await expect(s.locator('.badge--default')).toHaveCSS('background-color', c.darkBg);
      await expect(s.locator('.badge--default')).toHaveCSS('color', c.borderStrong);
    });

    test('newsletter capture uses PLAIN system buttons — no special styles', async ({ page }) => {
      // newsletter capture folded into the Form composition tab (it IS the input-pair in production)
      await page.goto(`/?aifds_styleguide=1&item=form-composition&theme=${brand}`);
      // AIF variant: plain primary on the brand surface → auto-dark
      const aifBtn = page.locator('.hero-aif__form .btn--primary');
      await expect(aifBtn).toHaveCSS('background-color', c.darkBg);
      await expect(aifBtn).toHaveCSS('color', c.inverseText);
      // field on the BRAND surface sits on clean WHITE (gray field bg looks cheap on saturated brand)
      await expect(page.locator('.hero-aif__form .form-control-wrapper')).toHaveCSS('background-color', 'rgb(255, 255, 255)');
      // AIG variant: plain tertiary on the dark surface → inverse border, transparent bg
      const aigBtn = page.locator('.aif-ecomail-form--footer-dark .btn--tertiary');
      await expect(aigBtn).toHaveCSS('border-color', c.badgeDarkBg); // = border-inverse-strong
      await expect(aigBtn).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
      await expect(aigBtn).toHaveCSS('color', c.inverseText);
    });

    test('smart button bulb = BRAND fill; chatbot bubble is NOT in the DS', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=buttons&theme=${brand}`);
      // smart button bulb = brand color
      const bulbFill = await page.locator('.smart-btn svg path').first().evaluate((el) => getComputedStyle(el).fill);
      expect(bulbFill).toBe(c.brandColor);
      // chatbot bubble stripped (operator 2026-07-03): theme territory, not a DS component
      expect(await page.locator('.btn-floating').count()).toBe(0);
    });

    test('disabled buttons are surface-aware (readable on dark)', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=buttons&theme=${brand}`);
      const darkDisabled = page.locator('.section-dark .btn--primary[disabled]');
      const { text, bg, surface } = await darkDisabled.evaluate((el) => {
        const cs = getComputedStyle(el);
        return {
          text: cs.getPropertyValue('--disabled-text').trim(),
          bg: cs.getPropertyValue('--disabled-bg').trim(),
          surface: cs.getPropertyValue('--bg').trim(),
        };
      });
      expect(bg, 'disabled bg must differ from the dark surface').not.toBe(surface);
      expect(text, 'disabled text must be the INVERSE disabled color on dark').not.toBe('');
      // and the inverse-disabled text actually applies
      const inverseDisabled = brand === 'aiguild' ? 'rgb(140, 145, 136)' : 'rgb(88, 94, 102)';
      await expect(darkDisabled).toHaveCSS('color', inverseDisabled);
    });

    test('static <span> badge has NO hover effect and default cursor', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=badges&theme=${brand}`);
      const staticBadge = page.locator('.article-hero__badges span.badge--default');
      await expect(staticBadge).toHaveCSS('cursor', 'default');
      const bgBefore = await staticBadge.evaluate((el) => getComputedStyle(el).backgroundColor);
      await staticBadge.hover();
      // BOTH channels must stay inert: the element's own background AND ::after
      const { bgAfter, pseudoBg } = await staticBadge.evaluate((el) => ({
        bgAfter: getComputedStyle(el).backgroundColor,
        pseudoBg: getComputedStyle(el, '::after').backgroundColor,
      }));
      expect(bgAfter, 'element bg must not change on hover').toBe(bgBefore);
      expect(pseudoBg).toBe('rgba(0, 0, 0, 0)');
      await page.mouse.move(0, 0);
    });

    test('article headings step the FULL ramp (no orphan styles like 22px Lazzer)', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=prose&theme=${brand}`);
      const art = page.locator('.article-layout__content');
      // article h3 must be the heading-sm STYLE: 22px Inter (never Lazzer)
      const h3 = art.locator('h3');
      await expect(h3).toHaveCSS('font-size', '22px');
      const h3font = await h3.evaluate((el) => getComputedStyle(el).fontFamily);
      expect(h3font).toContain('Inter');
      expect(h3font).not.toContain('Lazzer');
      // article h2 = heading-md style (brand-divergent font, correct per brand)
      const h2font = await art.locator('h2').evaluate((el) => getComputedStyle(el).fontFamily);
      if (brand === 'aiguild') expect(h2font).toContain('Lazzer');
      else expect(h2font).toContain('Inter');
      await expect(art.locator('h2')).toHaveCSS('font-size', '28px');
    });

    test('NUMBERED HEADINGS: brand-tile number, auto-increment, content aligns to indent', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=prose&theme=${brand}`);
      const nh = page.locator('.numbered-headings');
      const headings = nh.locator('h3');
      // tile = --brand fill (yellow AIG / blue AIF), digit = --text-on-brand (dark)
      const tile = await headings.first().evaluate((el) => {
        const cs = getComputedStyle(el, '::before');
        return { bg: cs.backgroundColor, color: cs.color, content: cs.content, w: cs.width };
      });
      expect(tile.bg).toBe(c.brandColor);
      expect(tile.color).toBe(c.tileInk); // --text-on-brand: dark ink, readable on the brand tile
      expect(tile.w).toBe('44px');
      expect(tile.content).toContain('counter(numbered-heading)'); // number IS the counter
      // CSS counter auto-increments with zero markup change (Chromium reports the
      // unresolved counter() in ::before content, so assert the increment property)
      const inc = await headings.nth(1).evaluate((el) => getComputedStyle(el).counterIncrement);
      expect(inc).toContain('numbered-heading');
      // content indents to align under the heading text (tile 44 + gap 12 = 56)
      await expect(nh.locator('p').first()).toHaveCSS('margin-left', '56px');
    });

    test('INFO BOX: 5 colour variants (thick border + tint), 3 sizes, no icon/radius', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=info-box&theme=${brand}`);
      // accent per variant: info = --brand (per brand), status variants fixed hue,
      // neutral = --border-strong (greyscale, per brand)
      const accents = {
        info: c.brandColor,
        success: 'rgb(6, 118, 71)',   // --success #067647 (brand-identical)
        warning: 'rgb(161, 98, 7)',   // --warning #a16207
        error: 'rgb(180, 35, 24)',    // --error #b42318
        neutral: c.borderStrong,      // --border-strong (greyscale)
      };
      for (const [variant, accent] of Object.entries(accents)) {
        const box = page.locator(`.info-box--${variant}`).first();
        const m = await box.evaluate((el) => {
          const cs = getComputedStyle(el);
          return { bw: cs.borderLeftWidth, bc: cs.borderLeftColor, bg: cs.backgroundColor, radius: cs.borderTopLeftRadius, pl: cs.paddingLeft, pt: cs.paddingTop };
        });
        expect(m.bw).toBe('4px');           // thick accent border
        expect(m.bc).toBe(accent);          // border reads the variant accent
        expect(m.radius).toBe('0px');       // SQUARE corners (operator: no rounding)
        expect(m.pl).toBe('20px');          // border(4) + padding(20) = text lands at flow-indent 24, aligned with prose
        expect(m.pt).toBe('16px');          // vertical padding
        // background is a color-mix tint of the accent — Chromium resolves it to
        // an opaque rgb that is NEITHER the raw accent NOR fully transparent
        expect(m.bg).not.toBe(accent);
        expect(m.bg).not.toBe('rgba(0, 0, 0, 0)');
      }
      // no icon element exists (operator: simple box, no icon)
      expect(await page.locator('.info-box__icon').count()).toBe(0);
      // size axis: small 14 · default 16 · article 18
      const fs = (sel) => page.locator(sel).first().evaluate((el) => getComputedStyle(el).fontSize);
      expect(await fs('.info-box--small')).toBe('14px');
      expect(await fs('.info-box--article')).toBe('18px');
      // a default box (info, no size modifier) is 16px
      const def = await page.locator('.info-box--info:not(.info-box--small):not(.info-box--article)').first().evaluate((el) => getComputedStyle(el).fontSize);
      expect(def).toBe('16px');
      // ON DARK: status accents invert to the BRIGHT variants (border reads --status-*)
      const darkErr = await page.locator('.section-dark .info-box--error').first().evaluate((el) => getComputedStyle(el).borderLeftColor);
      expect(darkErr).toBe('rgb(253, 102, 77)'); // error-bright #fd664d
      const darkOk = await page.locator('.section-dark .info-box--success').first().evaluate((el) => getComputedStyle(el).borderLeftColor);
      expect(darkOk).toBe('rgb(62, 156, 106)'); // success-bright #3e9c6a
    });

    test('DATA TABLE: mono/uppercase header, 1px full grid incl header, size ladder, brand-tint signifiers', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=data-tables&theme=${brand}`);
      // header signature: monospace + UPPERCASE + 1px border ON the header cell (operator: border in header)
      const head = page.locator('.data-table thead th').first();
      const h = await head.evaluate((el) => {
        const cs = getComputedStyle(el);
        return { ff: cs.fontFamily, tt: cs.textTransform, fw: cs.fontWeight, bw: cs.borderBottomWidth, bg: cs.backgroundColor };
      });
      expect(h.ff).toContain('Spline Sans Mono'); // --font-mono
      expect(h.tt).toBe('uppercase');
      expect(h.fw).toBe('700');
      expect(h.bw).toBe('1px'); // 1px grid (operator ruling)
      expect(h.bg).toBe('rgba(0, 0, 0, 0)'); // NO fill — robust across surfaces
      // full 1px grid: body cells carry a 1px border on all sides
      await expect(page.locator('.data-table:not(.data-table--plain):not(.data-table--banded) tbody td').first()).toHaveCSS('border-bottom-width', '1px');
      // SIZE ladder (cell font): condensed 14 · standard 16 · large 18
      const cell = (sel) => page.locator(`${sel} tbody td`).first().evaluate((el) => getComputedStyle(el).fontSize);
      expect(await cell('.data-table--condensed')).toBe('14px');
      expect(await cell('.data-table--large')).toBe('18px');
      const std = await page.locator('.data-table:not(.data-table--condensed):not(.data-table--large):not(.data-table--plain):not(.data-table--banded) tbody td').first().evaluate((el) => getComputedStyle(el).fontSize);
      expect(std).toBe('16px');
      // header size scales: condensed 12 → large 14
      expect(await page.locator('.data-table--condensed thead th').first().evaluate((el) => getComputedStyle(el).fontSize)).toBe('12px');
      expect(await page.locator('.data-table--large thead th').first().evaluate((el) => getComputedStyle(el).fontSize)).toBe('14px');
      // status cell reuses --status-error (light surface → #b42318), text only
      const errColor = await page.locator('.data-table .cell--error').first().evaluate((el) => getComputedStyle(el).color);
      expect(errColor).toBe('rgb(180, 35, 24)');
      // column emphasis = translucent brand FILL (--brand-tint, 22% alpha), NOT a line transform
      const emph = await page.locator('.data-table th.is-emphasized').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        return { bl: cs.borderLeftWidth, bg: cs.backgroundColor };
      });
      expect(emph.bl).toBe('1px');                    // border unchanged from the grid — no line transform
      expect(emph.bg).not.toBe('rgba(0, 0, 0, 0)');   // a fill IS present
      expect(emph.bg).toContain('0.22');              // brand at 22% alpha (translucent, holds on any surface)
      // ON DARK: the status cell inverts to error-bright (the dark-scope override)
      const darkErr = await page.locator('.section-dark .data-table .cell--error').first().evaluate((el) => getComputedStyle(el).color);
      expect(darkErr).toBe('rgb(253, 102, 77)'); // error-bright #fd664d
      // WIDE TABLES (harvested .table-scroll): the wrapper scrolls horizontally,
      // the page body never overflows. Verified at a mobile viewport on the
      // guaranteed-wide demo (the 9-column min-width table).
      await expect(page.locator('.table-scroll').first()).toHaveCSS('overflow-x', 'auto');
      await page.setViewportSize({ width: 390, height: 1200 });
      await page.goto(`/?aifds_styleguide=1&item=data-tables&theme=${brand}`);
      const scroll = await page.locator('.table-scroll', { has: page.locator('table[style*="min-width"]') }).first().evaluate((el) => ({
        scrolls: el.scrollWidth > el.clientWidth,
        bodyOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
      }));
      expect(scroll.scrolls).toBe(true);        // the wide demo table scrolls inside the wrapper
      expect(scroll.bodyOverflow).toBe(false);  // the page itself does NOT scroll horizontally
    });

    test('RECORD LIST: abstract N-column cards — labels inside, consumer columns, status fields, action, dark invert', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=record-list&theme=${brand}`);
      // field labels live INSIDE the card (data-label ::before), mono + uppercase
      const lbl = await page.locator('.record__field[data-label]').first().evaluate((el) => {
        const cs = getComputedStyle(el, '::before');
        return { ff: cs.fontFamily, tt: cs.textTransform };
      });
      expect(lbl.ff).toContain('Spline Sans Mono');
      expect(lbl.tt).toBe('uppercase');
      // values at 14px
      expect(await page.locator('.record__field').first().evaluate((el) => getComputedStyle(el).fontSize)).toBe('14px');
      // ABSTRACTION: the field grids run DIFFERENT column templates (cohort 6 cols vs event 4) — proves N-column reuse
      const templates = await page.locator('.record__fields').evaluateAll((els) => els.map((el) => getComputedStyle(el).gridTemplateColumns));
      expect(new Set(templates).size).toBeGreaterThan(1);
      // the columns come from the consumer's --record-columns (set on .record-list)
      const varSet = await page.locator('.record-list').first().evaluate((el) => getComputedStyle(el).getPropertyValue('--record-columns').trim());
      expect(varSet.length).toBeGreaterThan(0);
      // status field reuses --status-* (warning on light = #a16207); no baked variant
      const warn = await page.locator('.record__field--warning').first().evaluate((el) => getComputedStyle(el).color);
      expect(warn).toBe('rgb(161, 98, 7)');
      // the action is just a field, pinned to the end of its column
      expect(await page.locator('.record__field--action').first().evaluate((el) => getComputedStyle(el).justifySelf)).toBe('end');
      // a title head + rich-text description render for the event consumer
      expect(await page.locator('.record__head .record__title').count()).toBeGreaterThan(0);
      expect(await page.locator('.record__head .record__description a').count()).toBeGreaterThan(0);
      // the description PARAGRAPH is 14px (same as the field values) — beats a host `main p` rule
      expect(await page.locator('.record__description p').first().evaluate((el) => getComputedStyle(el).fontSize)).toBe('14px');
      // TABLE BEHAVIOUR (subgrid): the status column lines up across records even
      // though one row has a much wider action button — a long value never shifts
      // the other rows' columns.
      const xs = await page.locator('.record-list').first().locator('.record__field--success, .record__field--warning, .record__field--error')
        .evaluateAll((els) => els.map((el) => Math.round(el.getBoundingClientRect().left)));
      expect(xs.length).toBeGreaterThan(1);
      expect(new Set(xs).size).toBe(1); // all identical → aligned
      // ON DARK: the status field inverts to the bright variant (warning-bright #e7a24a)
      const darkWarn = await page.locator('.section-dark .record__field--warning').first().evaluate((el) => getComputedStyle(el).color);
      expect(darkWarn).toBe('rgb(231, 162, 74)');
      // MOBILE (<768): the grid collapses to a stack — every field fills the row so
      // its value sits flush-right (one shared x), and the action is a full-width button.
      await page.setViewportSize({ width: 390, height: 1600 });
      await page.goto(`/?aifds_styleguide=1&item=record-list&theme=${brand}`);
      const rec = page.locator('.record-list .record').first();
      const valRights = await rec.locator('.record__field:not(.record__field--action)').evaluateAll((els) =>
        els.map((el) => { const r = document.createRange(); r.selectNodeContents(el); return Math.round(r.getBoundingClientRect().right); }));
      expect(valRights.length).toBeGreaterThan(1);
      expect(new Set(valRights).size).toBe(1); // all values flush-right on the same x
      // the action button's edges line up EXACTLY with a regular field's edges —
      // catches both the shrink/centre bug (align-self leak) and the content-box
      // overflow past the right padding (box-sizing bug).
      const mob = await rec.evaluate((r) => {
        const field = r.querySelector('.record__field:not(.record__field--action)').getBoundingClientRect();
        const btn = r.querySelector('.record__field--action .btn').getBoundingClientRect();
        return { fieldL: Math.round(field.left), fieldR: Math.round(field.right), btnL: Math.round(btn.left), btnR: Math.round(btn.right) };
      });
      expect(mob.btnL).toBe(mob.fieldL); // flush-left with the fields
      expect(mob.btnR).toBe(mob.fieldR); // flush-right — not overflowing the card padding
    });

    test('ICON STROKE: stepped law — <16 fine 1px · 16–32 default 1.5px · >32 heavy 3px (screen px via non-scaling-stroke)', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=icon-system&theme=${brand}`);
      // the specimen ramp renders arrow-right at 14/16/20/24/32/48
      const strokes = await page.locator('.sg-table .icon--stroked').evaluateAll((svgs) =>
        svgs.map((svg) => ({
          size: Math.round(svg.getBoundingClientRect().width),
          sw: getComputedStyle(svg.querySelector('path')).strokeWidth,
          ve: getComputedStyle(svg.querySelector('path')).vectorEffect,
        })));
      const bySize = Object.fromEntries(strokes.map((s) => [s.size, s.sw]));
      expect(bySize[14]).toBe('1px');   // fine
      expect(bySize[16]).toBe('1.5px'); // operator ruling: 16 itself is default — only BELOW 16 is fine
      expect(bySize[20]).toBe('1.5px'); // default
      expect(bySize[32]).toBe('1.5px'); // 32 itself stays default
      expect(bySize[48]).toBe('3px');   // heavy
      expect(strokes.every((s) => s.ve === 'non-scaling-stroke')).toBe(true); // screen px, no viewBox math
    });

    test('BREADCRUMB + PAGINATION + NAV TABS: harvested trio — voices, link idiom, touch chips, active chip constant', async ({ page }) => {
      // BREADCRUMB — caption-sized accent voice, text-colored idiom links
      await page.goto(`/?aifds_styleguide=1&item=breadcrumb&theme=${brand}`);
      const bc = await page.locator('.breadcrumbs').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        const link = el.querySelector('.breadcrumb__link');
        const lcs = getComputedStyle(link);
        return { ff: cs.fontFamily, size: cs.fontSize, weight: cs.fontWeight,
          linkColor: lcs.color, textColor: cs.color, deco: lcs.textDecorationLine, thick: lcs.textDecorationThickness, off: lcs.textUnderlineOffset };
      });
      expect(bc.ff).toContain('Space Grotesk'); // --font-accent (harvested font-family-secondary)
      expect(bc.size).toBe('14px');             // caption size
      expect(bc.weight).toBe('700');
      expect(bc.linkColor).toBe(bc.textColor);  // TEXT-colored — excluded from the global link chain
      expect(bc.deco).toBe('underline');
      expect(bc.thick).toBe('1px');             // link idiom resting
      expect(bc.off).toBe('4px');
      // PAGINATION — 44px calibrated chips, square, fiction-fixed weight, rail/current fills
      await page.goto(`/?aifds_styleguide=1&item=pagination&theme=${brand}`);
      const pg = await page.locator('.archive-pagination').first().evaluate((el) => {
        const num = el.querySelector('a.page-numbers');
        const cur = el.querySelector('.page-numbers.current');
        const rail = el.querySelector('.nav-links');
        const n = num.getBoundingClientRect();
        return { h: Math.round(n.height), radius: getComputedStyle(cur).borderRadius,
          weight: getComputedStyle(num).fontWeight, curBg: getComputedStyle(cur).backgroundColor,
          railBg: getComputedStyle(rail).backgroundColor, deco: getComputedStyle(num).textDecorationLine };
      });
      expect(pg.h).toBe(44);                    // calibrated touch target
      expect(pg.deco).toBe('none');             // chips are chips — excluded from the global link chain
      expect(pg.radius).toBe('0px');            // SQUARE — radius retirement (live 4px, GM exception)
      expect(pg.weight).toBe('400');            // declared medium/semibold was fiction — rendered reality
      expect(pg.curBg).toBe('rgb(255, 255, 255)'); // current = --bg on light
      expect(pg.railBg).not.toBe('rgba(0, 0, 0, 0)'); // the rail carries the --bg-alt fill
      // NAV TABS — lead-sized accent voice, scrollable row, constant white active chip on brand
      await page.goto(`/?aifds_styleguide=1&item=nav-tabs&theme=${brand}`);
      const tabs = await page.locator('.section-brand .nav-tabs').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        const a = el.querySelector('a.nav-tabs__tab');
        const act = el.querySelector('.nav-tabs__tab--active');
        return { overflow: cs.overflowX, ff: getComputedStyle(a).fontFamily, size: getComputedStyle(a).fontSize,
          actBg: getComputedStyle(act).backgroundColor, actDeco: getComputedStyle(act).textDecorationLine,
          aDeco: getComputedStyle(a).textDecorationLine };
      });
      expect(tabs.overflow).toBe('auto');       // scrollable row
      expect(tabs.ff).toContain('Space Grotesk');
      expect(tabs.size).toBe('24px');           // --lead-size
      expect(tabs.actBg).toBe('rgb(255, 255, 255)'); // CONSTANT white chip — even inside .section-brand
      expect(tabs.actDeco).not.toContain('underline');
      expect(tabs.aDeco).toContain('underline'); // inactive links keep the idiom underline
      // DOCKING (harvested contract, live-verified on production): the tab row
      // sits ON the hero's bottom edge — chip bottom == hero bottom == next-section top
      const dock = await page.locator('.section-brand').filter({ has: page.locator('.nav-tabs') }).first().evaluate((hero) => {
        const act = hero.querySelector('.nav-tabs__tab--active');
        const next = hero.nextElementSibling;
        return {
          heroB: Math.round(hero.getBoundingClientRect().bottom),
          chipB: Math.round(act.getBoundingClientRect().bottom),
          nextT: Math.round(next.getBoundingClientRect().top),
        };
      });
      expect(dock.chipB).toBe(dock.heroB); // the chip sits ON the edge
      expect(dock.nextT).toBe(dock.heroB); // …flush with the section below
      // mobile: the tab row scrolls inside itself; the page body never overflows
      await page.setViewportSize({ width: 390, height: 900 });
      await page.goto(`/?aifds_styleguide=1&item=nav-tabs&theme=${brand}`);
      const mob = await page.locator('.section-brand .nav-tabs').first().evaluate((el) => ({
        scrolls: el.scrollWidth > el.clientWidth,
        tabSize: getComputedStyle(el.querySelector('a.nav-tabs__tab')).fontSize,
        bodyOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
      }));
      expect(mob.scrolls).toBe(true);
      expect(mob.tabSize).toBe('16px');         // steps down to body-md
      expect(mob.bodyOverflow).toBe(false);
    });

    test('CARDS: reference + persona — one canonical, appearance from the background (surface-riding)', async ({ page }) => {
      // REFERENCE CARD — the dark testimonial is the SAME card + section-dark ON it
      await page.goto(`/?aifds_styleguide=1&item=reference-card&theme=${brand}`);
      const dark = await page.locator('.reference-card.section-dark').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        const name = getComputedStyle(el.querySelector('.reference-card__name'));
        const content = getComputedStyle(el.querySelector('.reference-card__content p'));
        const quote = getComputedStyle(el.querySelector('.reference-card__quote'));
        const quoteBefore = getComputedStyle(el.querySelector('.reference-card__quote'), '::before');
        return { bg: cs.backgroundColor, nameColor: name.color, nameSize: name.fontSize, nameWeight: name.fontWeight,
          contentFf: content.fontFamily, contentSize: content.fontSize, contentWeight: content.fontWeight,
          quoteFf: quote.fontFamily, quoteColor: quote.color, quoteGlyph: quoteBefore.content };
      });
      const light = await page.locator('.reference-card:not(.section-dark)').first().evaluate((el) => ({
        bg: getComputedStyle(el).backgroundColor,
        nameColor: getComputedStyle(el.querySelector('.reference-card__name')).color,
      }));
      expect(dark.bg).not.toBe(light.bg);              // SAME markup, different surface → different paint
      expect(dark.nameColor).not.toBe(light.nameColor); // roles re-resolve (no variant classes)
      expect(dark.nameSize).toBe('18px');               // heading-xs voice
      expect(dark.nameWeight).toBe('800');
      expect(dark.contentFf).toContain('Space Grotesk'); // the QUOTE voice (accent font)
      expect(dark.contentSize).toBe('18px');
      expect(dark.contentWeight).toBe('400');            // declared Light/300 was fiction — the Light law
      // THE QUOTE MARK: a Lazzer glyph in very muted brand (brand-tint alpha),
      // TOP-RIGHT — the avatar's mirror (same row, same 64px box, other side)
      expect(dark.quoteFf).toContain('Lazzer');
      expect(dark.quoteColor).toContain('0.22');          // --brand-tint (22% alpha)
      expect(dark.quoteGlyph).toContain('“');
      const mirror = await page.locator('.reference-card.section-dark').first().evaluate((card) => {
        const q = card.querySelector('.reference-card__quote').getBoundingClientRect();
        const av = card.querySelector('.reference-card__avatar').getBoundingClientRect();
        const c = card.getBoundingClientRect();
        return { w: Math.round(q.width), h: Math.round(q.height),
          rightGap: Math.round(c.right - q.right), leftGap: Math.round(av.left - c.left),
          sameRow: Math.abs(q.top - av.top) <= 1 };
      });
      expect(mirror.w).toBe(64);                    // same size as the picture
      expect(mirror.h).toBe(64);
      expect(mirror.rightGap).toBe(mirror.leftGap); // mirrored inset (the card padding)
      expect(mirror.sameRow).toBe(true);            // same vertical position as the picture
      // THE QUOTE VOICE (AIG-fixed grammar): p, li, strong, HEADLINES — ONE
      // font, ONE size; headlines = strong-as-a-block (same size, bold);
      // list markers inherit the text (counters never bold) in --text-disabled
      const voice = await page.locator('.reference-card__content').last().evaluate((el) => {
        const f = (sel) => { const n = el.querySelector(sel); const cs = getComputedStyle(n); return { ff: cs.fontFamily, size: cs.fontSize, weight: cs.fontWeight, color: cs.color }; };
        const ulMark = getComputedStyle(el.querySelector('ul li'), '::before');
        const olMark = getComputedStyle(el.querySelector('ol li'), '::before');
        return { p: f('p'), li: f('li'), strong: f('strong'), h3: f('h3'), h5: f('h5'),
          ulMarkColor: ulMark.backgroundColor, olMark: { color: olMark.color, weight: olMark.fontWeight, size: olMark.fontSize } };
      });
      for (const k of ['p', 'li', 'strong', 'h3', 'h5']) expect(voice[k].ff).toContain('Space Grotesk');
      expect(voice.li.size).toBe(voice.p.size);           // lists = the SAME size as text
      expect(voice.li.color).toBe(voice.p.color);         // li text = SAME color as p (the AIG fix)
      expect(voice.strong.size).toBe(voice.p.size);       // strong = same size, only bolder
      expect(voice.strong.weight).toBe('700');
      expect(voice.h3.size).toBe(voice.p.size);           // headlines = strong-as-a-block: SAME size
      expect(voice.h3.weight).toBe('700');
      expect(voice.h3.size).toBe(voice.h5.size);          // h3 == h5 — still one style
      expect(voice.h3.weight).toBe(voice.h5.weight);
      expect(voice.olMark.weight).toBe(voice.p.weight);   // counters inherit — never bold
      expect(voice.olMark.size).toBe(voice.p.size);
      expect(voice.olMark.color).toBe(voice.ulMarkColor); // ONE marker color (text-disabled) for ul + ol
      // RESPONSIVE (harvested ≤1023): the card stacks; the quote mark holds its
      // top-right mirror position
      await page.setViewportSize({ width: 900, height: 1200 });
      const stacked = await page.locator('.reference-card.section-dark').first().evaluate((card) => {
        const q = card.querySelector('.reference-card__quote').getBoundingClientRect();
        const c = card.getBoundingClientRect();
        return { dir: getComputedStyle(card.querySelector('.reference-card__body')).flexDirection,
          topRight: Math.round(c.right - q.right) === Math.round(q.top - c.top) };
      });
      expect(stacked.dir).toBe('column');
      expect(stacked.topRight).toBe(true);
      await page.setViewportSize({ width: 1280, height: 900 });
      // PERSONA CARD — removed from the dark-3 scope map; surface-riding via --raised
      await page.goto(`/?aifds_styleguide=1&item=persona-card&theme=${brand}`);
      const pDark = await page.locator('.section-dark .persona-card').first().evaluate((el) => ({
        bg: getComputedStyle(el).backgroundColor,
        name: getComputedStyle(el.querySelector('.persona-card__name')).fontSize,
        radius: getComputedStyle(el.querySelector('.persona-card__avatar')).borderRadius,
      }));
      const pLight = await page.locator('#persona-light-demo .persona-card').evaluate((el) => ({
        bg: getComputedStyle(el).backgroundColor,
      }));
      expect(pDark.bg).not.toBe(pLight.bg);   // one canonical card, two surfaces, two paints
      expect(pDark.radius).toBe('0px');       // avatar stays SQUARE (harvested spec)
      // CONTAINER-BASED ORIENTATION (ruled rebuild): at ONE viewport, the wide
      // slot renders horizontal (grid, 2 tracks) and the narrow slot vertical —
      // the container decides, never the viewport.
      const slots = await page.locator('.persona-card-slot').evaluateAll((els) =>
        els.map((slot) => {
          const card = slot.querySelector('.persona-card');
          const cs = getComputedStyle(card);
          const av = card.querySelector('.persona-card__avatar').getBoundingClientRect();
          const cd = card.getBoundingClientRect();
          const img = card.querySelector('.persona-card__avatar img');
          return { slotW: Math.round(slot.getBoundingClientRect().width),
            display: cs.display, cols: cs.gridTemplateColumns.split(' ').length,
            avW: Math.round(av.width), avH: Math.round(av.height),
            cardH: Math.round(cd.height),
            imgH: img ? Math.round(img.getBoundingClientRect().height) : null,
            shortDemo: slot.id === 'persona-short-demo' };
        }));
      const wide = slots.find((s) => s.slotW >= 560 && !s.shortDemo);
      const narrow = slots.find((s) => s.slotW < 560);
      expect(wide.display).toBe('grid');
      expect(wide.cols).toBe(2);              // photo track + content track
      // THE LAW (operator, final): the portrait is FULL HEIGHT — ALWAYS.
      // Photo cell AND the img itself equal the card height exactly, even
      // beside the deliberately long bio (the absolute-fill kills the
      // circular-sizing trap — the photo has zero say in the card's height).
      expect(Math.abs(wide.avH - wide.cardH)).toBeLessThanOrEqual(1);
      expect(Math.abs(wide.imgH - wide.cardH)).toBeLessThanOrEqual(1);
      expect(wide.avW).toBeLessThanOrEqual(320); // wider track: clamp cap
      expect(wide.avW).toBeGreaterThanOrEqual(200); // clamp floor
      expect(narrow.display).toBe('flex');    // same markup, narrow slot → vertical
      // SHORT BIO: the min-height guard — the card is never shorter than the
      // photo column is wide (no letterboxed sliver), and the portrait is
      // STILL full height.
      const short = slots.find((s) => s.shortDemo);
      expect(short.cardH).toBeGreaterThanOrEqual(short.avW - 1);
      expect(Math.abs(short.avH - short.cardH)).toBeLessThanOrEqual(1);
      expect(Math.abs(short.imgH - short.cardH)).toBeLessThanOrEqual(1);
      // BOTTOM BLOCK (operator contract): in the dark grid, the location meta
      // pins to the card bottom (aligned across different bio lengths) and the
      // socials sit BELOW the location — the lowest element.
      const grid = await page.locator('.section-dark').first().locator('.persona-card').evaluateAll((cards) =>
        cards.map((c) => {
          const meta = c.querySelector('.persona-card__meta');
          const soc = c.querySelector('.persona-card__socials');
          const cb = c.getBoundingClientRect().bottom;
          return {
            metaGap: meta ? Math.round(cb - meta.getBoundingClientRect().bottom) : null,
            socBelowMeta: meta && soc ? soc.getBoundingClientRect().top >= meta.getBoundingClientRect().bottom : null,
            socIsLowest: soc ? Math.round(cb - soc.getBoundingClientRect().bottom) : null,
          };
        }));
      const withSoc = grid.filter((g) => g.socBelowMeta !== null);
      expect(withSoc.length).toBeGreaterThan(1);
      expect(withSoc.every((g) => g.socBelowMeta)).toBe(true);          // socials under the location
      expect(withSoc.every((g) => g.socIsLowest <= 30)).toBe(true);     // socials are the lowest element
      const noSoc = grid.find((g) => g.socBelowMeta === null && g.metaGap !== null);
      expect(noSoc.metaGap).toBeLessThanOrEqual(30);                    // location alone still pins
      // WATERMARK meta (operator): the location line is very quiet — opacity .5
      const metaOpacity = await page.locator('.persona-card__meta').first().evaluate((el) => getComputedStyle(el).opacity);
      expect(metaOpacity).toBe('0.5');
      // LINKED persona: name link = card-title-link (inherit color, NO underline
      // at rest, no link-blue leak); photo wrapped in card-image-link
      const linked = await page.locator('.persona-card__name a.card-title-link').first().evaluate((a) => {
        const h = a.closest('.persona-card__name');
        return { deco: getComputedStyle(a).textDecorationLine, aColor: getComputedStyle(a).color, hColor: getComputedStyle(h).color };
      });
      expect(linked.deco).toBe('none');          // quiet at rest — hover earns the underline
      expect(linked.aColor).toBe(linked.hColor); // inherits the card voice, never link-blue
      expect(await page.locator('.persona-card__avatar a.card-image-link img').count()).toBeGreaterThan(0);
      // PHOTO HOVER GLOW (linked only): brand-tint radial corners — invisible at
      // rest, breathes in on hover, never blocks the click
      const avatarWithLink = page.locator('.persona-card__avatar').filter({ has: page.locator('a.card-image-link') }).first();
      const glowRest = await avatarWithLink.evaluate((el) => {
        const cs = getComputedStyle(el, '::after');
        return { opacity: cs.opacity, bg: cs.backgroundImage, pe: cs.pointerEvents };
      });
      expect(glowRest.opacity).toBe('0');                       // invisible at rest
      expect(glowRest.bg).toContain('radial-gradient');         // the corner glows
      expect(glowRest.pe).toBe('none');                         // photo stays clickable
      await avatarWithLink.locator('a.card-image-link').hover();
      await expect.poll(async () => avatarWithLink.evaluate((el) => getComputedStyle(el, '::after').opacity), { timeout: 2000 }).toBe('1');
      await page.mouse.move(0, 0); // release hover for any later assertions
      // AND: tokens.css no longer scopes .persona-card as dark-3 (the ruling)
      const scoped = await page.evaluate(async () => {
        const css = await (await fetch(document.querySelector('link[href*="tokens.css"]').href)).text();
        return css.includes('.persona-card');
      });
      expect(scoped).toBe(false);
    });

    test('PREVIEW CARD: one card, two axes — sizes, slots, no-photo hairline, hover zoom, actions grammar', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=preview-card&theme=${brand}`);
      // SIZE axis: default headline = card-heading (28, INTER ON BOTH BRANDS —
      // the operator ruling: Lazzer renders terribly at this size in cards),
      // condensed = heading-sm (22 / AIG 20)
      const normal = await page.locator('.preview-card:not(.preview-card--condensed) .preview-card__headline').first()
        .evaluate((el) => { const cs = getComputedStyle(el); return { size: cs.fontSize, ff: cs.fontFamily, weight: cs.fontWeight }; });
      const condensed = await page.locator('.preview-card--condensed .preview-card__headline').first()
        .evaluate((el) => getComputedStyle(el).fontSize);
      expect(normal.size).toBe('28px');
      expect(normal.ff).toContain('Inter');            // NEVER Lazzer in a card headline
      expect(normal.ff).not.toContain('Lazzer');
      expect(normal.weight).toBe('800');               // extrabold on both brands
      expect(parseFloat(condensed)).toBeLessThan(parseFloat(normal.size)); // condensed = smaller headline
      // NO-PHOTO anatomy: cards without a photo grow the top hairline automatically
      const noPhoto = await page.locator('.preview-card:not(:has(.preview-card__photo))').first().evaluate((el) => getComputedStyle(el).borderTopWidth);
      const withPhoto = await page.locator('.preview-card:has(.preview-card__photo)').first().evaluate((el) => getComputedStyle(el).borderTopWidth);
      expect(noPhoto).toBe('1px');
      expect(withPhoto).toBe('0px');
      // PHOTO hover zoom (linked photos, harvested 1.02)
      const photo = page.locator('a.preview-card__photo').first();
      await photo.hover();
      await expect.poll(async () => photo.locator('img').evaluate((el) => getComputedStyle(el).transform), { timeout: 2000 })
        .toContain('1.02');
      await page.mouse.move(0, 0);
      // META: caption voice between hairlines
      const meta = await page.locator('.preview-card__meta').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        return { ff: cs.fontFamily, size: cs.fontSize, bt: cs.borderTopWidth, bb: cs.borderBottomWidth };
      });
      expect(meta.ff).toContain('Space Grotesk'); // caption-font = accent
      expect(meta.size).toBe('14px');
      expect(meta.bt).toBe('1px');
      expect(meta.bb).toBe('1px');
      // SKILLS slot: the DS 'skills' icon at 16px, stroked per the icon law
      const skillsIcon = await page.locator('.preview-card__skills svg').first().evaluate((el) => ({
        w: Math.round(el.getBoundingClientRect().width),
        stroked: el.classList.contains('icon--stroked'),
      }));
      expect(skillsIcon.w).toBe(16);
      expect(skillsIcon.stroked).toBe(true);
      // ACTIONS: bottom-pinned row; the destructive member reads --status-error
      const destructive = await page.locator('.preview-card__actions .btn--destructive').first()
        .evaluate((el) => getComputedStyle(el).color);
      expect(destructive).toBe('rgb(180, 35, 24)'); // --status-error on light
      const actionsRow = await page.locator('.preview-card__actions').filter({ has: page.locator('.btn--destructive') })
        .first().evaluate((el) => el.querySelectorAll('.btn').length);
      expect(actionsRow).toBe(3); // Action 1 + Action 2 + Destructive
      // DESTRUCTIVE IS ALWAYS LAST — enforced by flex order: the specimen
      // authors it in the MIDDLE of the markup; it must still render last.
      const lastIsDestructive = await page.locator('.preview-card__actions').filter({ has: page.locator('.btn--destructive') })
        .first().evaluate((el) => {
          const btns = [...el.querySelectorAll('.btn')];
          const rightmost = btns.reduce((a, b) => a.getBoundingClientRect().right > b.getBoundingClientRect().right ? a : b);
          const authoredLast = btns[btns.length - 1]; // DOM order, not render order
          return { rightmostIsDestructive: rightmost.classList.contains('btn--destructive'),
            authoredLastIsDestructive: authoredLast.classList.contains('btn--destructive') };
        });
      expect(lastIsDestructive.rightmostIsDestructive).toBe(true);   // renders last…
      expect(lastIsDestructive.authoredLastIsDestructive).toBe(false); // …despite NOT being authored last
      // STACKING (the 3×3 simulation — where the friction lived): in every grid
      // row, all card bottoms equal AND all action rows share one top, despite
      // deliberately unequal content.
      const stack = await page.locator('#preview-stack-demo').evaluate((g) => {
        const cards = [...g.querySelectorAll('.preview-card')].map((c) => ({
          bottom: Math.round(c.getBoundingClientRect().bottom),
          actionsTop: Math.round(c.querySelector('.preview-card__actions').getBoundingClientRect().top),
          borderBottom: getComputedStyle(c).borderBottomStyle,
          borderTop: getComputedStyle(c).borderTopStyle,
        }));
        const rows = [cards.slice(0, 3), cards.slice(3, 6), cards.slice(6, 9)];
        return rows.map((r) => ({
          uniqueBottoms: new Set(r.map((c) => c.bottom)).size,
          uniqueActionTops: new Set(r.map((c) => c.actionsTop)).size,
          borderBottoms: r.map((c) => c.borderBottom),
          borderTops: r.map((c) => c.borderTop),
        }));
      });
      for (const row of stack) {
        expect(row.uniqueBottoms).toBe(1);    // bottom hairlines align across the row
        expect(row.uniqueActionTops).toBe(1); // action rows align across the row
      }
      // SINGLE SEPARATOR LAW (harvested: signal archive .articles-grid--signals):
      // the consumer grid strips border-bottom from every non-last-row card, so
      // a no-photo row's own top hairline is the ONLY line between rows.
      expect(stack[0].borderBottoms).toEqual(['none', 'none', 'none']);   // row 1 — stripped
      expect(stack[1].borderBottoms).toEqual(['none', 'none', 'none']);   // row 2 — stripped
      expect(stack[2].borderBottoms).toEqual(['solid', 'solid', 'solid']); // last row closes the stack
      expect(stack[1].borderTops).toEqual(['solid', 'solid', 'solid']);   // no-photo row keeps its top hairline (THE separator)
      expect(stack[0].borderTops).toEqual(['none', 'none', 'none']);      // photo row — no top line above photos
    });

    test('COURSE CARD: slot-driven orientation (720px contract), display voice, --bg-base fill, accents, inactive, grid alignment', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=course-card&theme=${brand}`);
      // ORIENTATION — the slot decides: same markup, 360px slot = column,
      // full-width slot = row with the fixed 420px image track
      const orient = await page.locator('#course-orientation-demo').evaluate((d) => {
        const cards = [...d.querySelectorAll('.course-info-card')];
        return cards.map((c) => ({
          dir: getComputedStyle(c).flexDirection,
          illW: Math.round(c.querySelector('.course-info-card__illustration-lg').getBoundingClientRect().width),
        }));
      });
      expect(orient[0].dir).toBe('column'); // narrow slot → vertical
      expect(orient[1].dir).toBe('row');    // wide slot → horizontal
      expect(orient[1].illW).toBe(420);     // fixed harvested track (operator-ruled)
      // TITLE — heading-lg + the display treatment (leading-snug 1.05, negative tracking)
      const title = await page.locator('#course-orientation-demo .course-info-card__title').nth(1)
        .evaluate((el) => {
          const cs = getComputedStyle(el);
          return { font: cs.fontFamily, weight: cs.fontWeight,
            ratio: parseFloat(cs.lineHeight) / parseFloat(cs.fontSize),
            tracking: parseFloat(cs.letterSpacing) };
        });
      expect(title.weight).toBe('900'); // heading-lg = weight-black
      if (brand === 'aiguild') expect(title.font).toContain('Lazzer');
      expect(title.ratio).toBeCloseTo(1.05, 2);   // --leading-snug
      expect(title.tracking).toBeLessThan(0);      // --tracking-display
      // EYEBROW — the mono-label recipe (snapped to --tracking-label)
      const eyebrow = await page.locator('#course-orientation-demo .course-info-card__eyebrow').first()
        .evaluate((el) => {
          const cs = getComputedStyle(el);
          return { font: cs.fontFamily, size: cs.fontSize, transform: cs.textTransform,
            tracking: parseFloat(cs.letterSpacing).toFixed(2) };
        });
      expect(eyebrow.font).toContain('Spline Sans Mono');
      expect(eyebrow.size).toBe('12px');
      expect(eyebrow.transform).toBe('uppercase');
      expect(eyebrow.tracking).toBe('0.96'); // 0.08em @ 12px
      // ACCENT AXIS — primary reads --brand-strong, quaternary reads --lime
      const accents = await page.locator('#course-accent-demo').evaluate((d) => {
        const get = (sel) => getComputedStyle(d.querySelector(sel)).color;
        const resolved = (name) => {
          const probe = document.createElement('span');
          probe.style.color = `var(${name})`;
          d.appendChild(probe);
          const c = getComputedStyle(probe).color;
          probe.remove();
          return c;
        };
        return {
          primary: get('.course-accent--primary'), brandStrong: resolved('--brand-strong'),
          quaternary: get('.course-accent--quaternary'), lime: resolved('--lime'),
        };
      });
      expect(accents.primary).toBe(accents.brandStrong);
      expect(accents.quaternary).toBe(accents.lime);
      // INACTIVE — grayscale image, disabled title, eyebrow beats its accent class
      const inactive = await page.locator('#course-state-demo .course-info-card--inactive').evaluate((c) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--text-disabled)';
        c.appendChild(probe);
        const disabled = getComputedStyle(probe).color;
        probe.remove();
        return {
          filter: getComputedStyle(c.querySelector('.course-info-card__illustration-lg img')).filter,
          title: getComputedStyle(c.querySelector('.course-info-card__title')).color,
          eyebrow: getComputedStyle(c.querySelector('.course-info-card__eyebrow')).color,
          disabled,
        };
      });
      expect(inactive.filter).toContain('grayscale');
      expect(inactive.title).toBe(inactive.disabled);
      expect(inactive.eyebrow).toBe(inactive.disabled); // beats course-accent--primary
      // FILL — --bg-base pops to the page surface: white on light, black on dark
      const fills = await page.evaluate(() => {
        const light = document.querySelector('#course-orientation-demo .course-info-card');
        const dark = document.querySelector('.section-dark .course-info-card');
        const resolved = (el, name) => {
          const probe = document.createElement('span');
          probe.style.color = `var(${name})`;
          el.appendChild(probe);
          const c = getComputedStyle(probe).color;
          probe.remove();
          return c;
        };
        return {
          light: getComputedStyle(light).backgroundColor, white: resolved(light, '--white'),
          dark: getComputedStyle(dark).backgroundColor, black: resolved(dark, '--black'),
        };
      });
      expect(fills.light).toBe(fills.white);
      expect(fills.dark).toBe(fills.black);
      // CTA — the .btn--md rung (52px, harvested "bigger CTA" = our existing ladder)
      const ctaH = await page.locator('#course-orientation-demo .course-info-card .btn').first()
        .evaluate((el) => getComputedStyle(el).height);
      expect(ctaH).toBe('52px'); // the .btn--md rung (content-box: rect adds the 2px borders)
      // GRID — three narrow slots: all vertical, bottoms + CTA rows align
      const grid = await page.locator('#course-grid-demo').evaluate((g) => {
        const cards = [...g.querySelectorAll('.course-info-card')];
        return {
          dirs: cards.map((c) => getComputedStyle(c).flexDirection),
          bottoms: new Set(cards.map((c) => Math.round(c.getBoundingClientRect().bottom))).size,
          ctaTops: new Set(cards.map((c) => Math.round(c.querySelector('.btn').getBoundingClientRect().top))).size,
        };
      });
      expect(grid.dirs).toEqual(['column', 'column', 'column']); // no count classes needed
      expect(grid.bottoms).toBe(1);
      expect(grid.ctaTops).toBe(1);
      // MOBILE 390 — vertical, no overflow, full-width CTA (touch affordance <=767)
      await page.setViewportSize({ width: 390, height: 900 });
      const mobile = await page.evaluate(() => {
        const card = document.querySelector('#course-orientation-demo .course-info-card');
        const btn = card.querySelector('.btn');
        const content = card.querySelector('.course-info-card__content');
        return {
          overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth,
          dir: getComputedStyle(card).flexDirection,
          btnFull: Math.abs(btn.getBoundingClientRect().width - (content.getBoundingClientRect().width - 48)) <= 1,
        };
      });
      expect(mobile.overflow).toBe(true);
      expect(mobile.dir).toBe('column');
      expect(mobile.btnFull).toBe(true);
    });

    test('MODAL: THE form modal — scrim, 560 box, heading-md title, DS form inside, LIVE aria engine, dvh sheet', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=modal&theme=${brand}`);
      // STATIC — scrim at the harvested 70% on --black; box = --bg, 560 canon
      const stat = await page.locator('#modal-static-default').evaluate((d) => {
        const overlay = d.querySelector('.modal__overlay');
        const box = d.querySelector('.modal__container');
        const probe = document.createElement('span');
        probe.style.color = 'var(--bg)';
        d.appendChild(probe);
        const bg = getComputedStyle(probe).color;
        probe.remove();
        return {
          scrim: getComputedStyle(overlay).backgroundColor,
          boxBg: getComputedStyle(box).backgroundColor,
          bg,
          boxW: getComputedStyle(box).maxWidth,
          pad: getComputedStyle(box).paddingTop,
          radius: getComputedStyle(box).borderRadius,
        };
      });
      expect(stat.scrim).toContain('0.7'); // color-mix 70% scrim alpha
      expect(stat.boxBg.replace(/rgba?/, '')).toContain(stat.bg.replace(/rgba?/, '').slice(0, 12)); // box = --bg
      // production renders 560 CONTENT + 48 padding = 656 total (live-measured)
      expect(stat.boxW).toBe('656px');
      expect(stat.pad).toBe('48px');
      expect(stat.radius).toBe('0px');
      // the production scroll engine: taller than its cap → the BOX scrolls
      // INSIDE itself (the static frame stands in for the 90vh viewport cap —
      // regression 2026-07-06: 90vh resolved taller than the embedded frame so
      // the box was clipped, never scrolling; the preview binds the cap to the
      // frame so the internal scroll is demonstrated, not faked).
      const scroll = await page.locator('#modal-static-default .modal__container').evaluate((el) => {
        const before = el.scrollTop;
        el.scrollTop = 200;
        const scrolled = el.scrollTop > before;
        el.scrollTop = before;
        return {
          overflowY: getComputedStyle(el).overflowY,
          overscroll: getComputedStyle(el).overscrollBehavior,
          overflows: el.scrollHeight > el.clientHeight + 50, // genuinely taller than the box
          scrolled,                                          // and actually scrollable
        };
      });
      expect(scroll.overflowY).toBe('auto');
      expect(scroll.overscroll).toBe('contain');
      expect(scroll.overflows).toBe(true);
      expect(scroll.scrolled).toBe(true);
      // COMPOSED CONTENT — intro text + the CONDENSED info box (operator:
      // --small fits the form density)
      const extras = await page.locator('#modal-static-default .modal__container').evaluate((box) => ({
        text: box.querySelectorAll('.modal__text').length,
        infoBox: box.querySelectorAll('.info-box.info-box--small').length,
      }));
      expect(extras.text).toBe(1);
      expect(extras.infoBox).toBe(1);
      // SCROLLABLE MODAL — the long form outgrows the viewport and scrolls
      // INSIDE the box (open it live, measure, scroll, close)
      await page.locator('[data-modal-open="sg-modal-long"]').click();
      const long = page.locator('#sg-modal-long');
      await expect(long).toHaveAttribute('aria-hidden', 'false');
      const scrolls = await long.locator('.modal__container').evaluate((el) => {
        const before = el.scrollTop;
        el.scrollTop = 200;
        return {
          overflows: el.scrollHeight > el.clientHeight + 50, // genuinely taller than the box
          scrolled: el.scrollTop > before,                    // and actually scrollable
        };
      });
      expect(scrolls.overflows).toBe(true);
      expect(scrolls.scrolled).toBe(true);
      await page.keyboard.press('Escape');
      await expect(long).toHaveAttribute('aria-hidden', 'true');
      // TITLE = heading-md bundle (brand-diverged font)
      const title = await page.locator('#modal-static-default .modal__title').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { font: cs.fontFamily, size: cs.fontSize };
      });
      expect(title.size).toBe('28px');
      if (brand === 'aiguild') expect(title.font).toContain('Lazzer');
      else expect(title.font).not.toContain('Lazzer');
      // THE FORM INSIDE — the modal body composes the DS form system:
      // .form-stack owns the rhythm, wrapped fields, consent row, a SIZED
      // submit (a ladder rung is REQUIRED — bare .btn has no dimensions)
      const form = await page.locator('#modal-static-default .modal__container').evaluate((box) => {
        const stack = box.querySelector('.form-stack');
        const submit = stack ? stack.querySelector('.btn--primary') : null;
        return {
          stackGap: stack ? getComputedStyle(stack).rowGap : null,
          fields: box.querySelectorAll('.form-group .form-control-wrapper .form-control').length,
          labelRows: box.querySelectorAll('.form-group .form-label-row .form-label').length,
          consent: box.querySelectorAll('.selection-item--consent').length,
          submitH: submit ? getComputedStyle(submit).height : null,
          submitSelf: submit ? getComputedStyle(submit).alignSelf : null,
          close: box.querySelectorAll('.modal__close').length, // ALWAYS present — no close-less variant exists
        };
      });
      expect(form.stackGap).toBe('16px');   // the named rhythm — no hand-rolled gaps
      expect(form.fields).toBeGreaterThanOrEqual(3);
      expect(form.labelRows).toBeGreaterThanOrEqual(3);
      // MANDATORY MARKER = brand deep, NOT red (harvested: both FF overrides
      // comment "brand deep instead of red"; --status-error is for errors)
      const mandatory = await page.locator('#modal-static-default .form-mandatory').first().evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--deep)';
        el.parentElement.appendChild(probe);
        const deep = getComputedStyle(probe).color;
        probe.style.color = 'var(--status-error)';
        const error = getComputedStyle(probe).color;
        probe.remove();
        return { color: getComputedStyle(el).color, deep, error };
      });
      expect(mandatory.color).toBe(mandatory.deep);
      expect(mandatory.color).not.toBe(mandatory.error);
      expect(form.consent).toBe(1);
      expect(form.submitH).toBe('52px');    // btn--md rung — never a bare .btn
      expect(form.submitSelf).toBe('flex-start'); // stack doesn't stretch buttons
      expect(form.close).toBe(1);
      // LIVE ENGINE — open: aria flips + body locks + focus lands in the form
      await page.locator('[data-modal-open="sg-modal-live"]').first().click();
      const live = page.locator('#sg-modal-live');
      await expect(live).toHaveAttribute('aria-hidden', 'false');
      const locked = await page.evaluate(() => document.body.classList.contains('modal-open'));
      expect(locked).toBe(true);
      // focus lands in the form (the engine's harvested 100ms delay — poll)
      await page.waitForFunction(() =>
        document.activeElement && document.activeElement.classList.contains('form-control'), null, { timeout: 2000 });
      // ESC closes + unlocks
      await page.keyboard.press('Escape');
      await expect(live).toHaveAttribute('aria-hidden', 'true');
      const unlocked = await page.evaluate(() => !document.body.classList.contains('modal-open'));
      expect(unlocked).toBe(true);
      // title override from the second opener (the registration modal's per-event title)
      await page.locator('[data-modal-title]').click();
      await expect(page.locator('#sg-modal-live .modal__title')).toHaveText(/overridden by this opener/);
      // overlay click closes
      await page.locator('#sg-modal-live .modal__overlay').click({ position: { x: 5, y: 5 } });
      await expect(live).toHaveAttribute('aria-hidden', 'true');
      // MOBILE 390 — full-screen dvh sheet, 64px top clearance, no overflow
      await page.setViewportSize({ width: 390, height: 700 });
      const sheet = await page.locator('#modal-static-default .modal__container').evaluate((el) => ({
        padTop: getComputedStyle(el).paddingTop,
        maxW: getComputedStyle(el).maxWidth,
        overscroll: getComputedStyle(el).overscrollBehavior,
      }));
      expect(sheet.padTop).toBe('64px');
      expect(sheet.maxW).toBe('100%');
      expect(sheet.overscroll).toBe('contain');
      const noOverflow = await page.evaluate(() =>
        document.documentElement.scrollWidth <= document.documentElement.clientWidth);
      expect(noOverflow).toBe(true);
    });

    test('ENGAGEMENT: ghost pills, clicked bulb state, toast grammar, LIVE optimistic engine (no-ajax graceful)', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=engagement&theme=${brand}`);
      // PILL VOICE — accent font, body size, bold, tertiary at rest, hairline frame
      const pill = await page.locator('.aif-engagement').first().evaluate((frame) => {
        const aha = frame.querySelector('.aif-aha');
        const cs = getComputedStyle(aha);
        const probe = document.createElement('span');
        probe.style.color = 'var(--text-tertiary)';
        frame.appendChild(probe);
        const tertiary = getComputedStyle(probe).color;
        probe.style.color = 'var(--border)';
        const border = getComputedStyle(probe).color;
        probe.remove();
        const fcs = getComputedStyle(frame);
        return { font: cs.fontFamily, size: cs.fontSize, weight: cs.fontWeight, color: cs.color, tertiary,
          borderTop: fcs.borderTopWidth + ' ' + fcs.borderTopStyle, borderColor: fcs.borderTopColor, border };
      });
      expect(pill.font).toContain('Space Grotesk');
      expect(pill.size).toBe('16px');
      expect(pill.weight).toBe('700');
      expect(pill.color).toBe(pill.tertiary);
      expect(pill.borderTop).toBe('1px solid');
      expect(pill.borderColor).toBe(pill.border);
      // CLICKED STATE (static demo) — filled bulb shows, outline hides, text = --text
      const clicked = await page.locator('#engagement-states .aif-aha--clicked').evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--text)';
        el.appendChild(probe);
        const text = getComputedStyle(probe).color;
        probe.remove();
        return {
          filled: getComputedStyle(el.querySelector('.icon-lightbulb-filled')).display,
          outline: getComputedStyle(el.querySelector('.icon-lightbulb')).display,
          color: getComputedStyle(el).color, text,
        };
      });
      expect(clicked.filled).toBe('block');
      expect(clicked.outline).toBe('none');
      expect(clicked.color).toBe(clicked.text);
      // share count hidden at zero
      await expect(page.locator('#engagement-states .aif-share__count[hidden]')).toBeHidden();
      // TOAST (static open) — raised fill, desaturated kit at rest
      const toast = await page.locator('#engagement-toast-demo .aif-engagement-toast').evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--raised)';
        el.appendChild(probe);
        const raised = getComputedStyle(probe).color;
        probe.remove();
        return { bg: getComputedStyle(el).backgroundColor, raised,
          kitFilter: getComputedStyle(el.querySelector('.a2a_kit a')).filter };
      });
      expect(toast.bg).toBe(toast.raised);
      expect(toast.kitFilter).toContain('grayscale');
      // LIVE ENGINE — click Aha!: optimistic +1, clicked class, aria, toast opens
      // with the CLONED kit and rewritten share URLs (no ajax-url = graceful)
      const liveAha = page.locator('.aif-engagement[data-post-id] .aif-aha');
      await liveAha.click();
      await expect(liveAha).toHaveClass(/aif-aha--clicked/);
      await expect(liveAha).toHaveAttribute('aria-pressed', 'true');
      const liveToast = page.locator('.aif-engagement[data-post-id] .aif-engagement-toast');
      await expect(liveToast).toHaveClass(/aif-engagement-toast--open/);
      const cloned = await liveToast.evaluate((t) => ({
        kits: t.querySelectorAll('.a2a_kit').length,
        linkedin: t.querySelector('.a2a_button_linkedin') ? t.querySelector('.a2a_button_linkedin').href : '',
      }));
      expect(cloned.kits).toBe(1); // kit cloned into the toast
      expect(cloned.linkedin).toContain('linkedin.com/sharing'); // placeholder href rewritten
      // count went optimistic 12 -> 13 (count element is display:none while the
      // thanks label shows — read textContent, not innerText)
      const count = await page.locator('.aif-engagement[data-post-id] .aif-aha__count').evaluate((el) => el.textContent);
      expect(count).toBe('13');
      // MOBILE 390 — no overflow, toast stacks
      await page.setViewportSize({ width: 390, height: 900 });
      const mob = await page.evaluate(() => ({
        overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth,
        toastDir: getComputedStyle(document.querySelector('#engagement-toast-demo .aif-engagement-toast')).flexDirection,
      }));
      expect(mob.overflow).toBe(true);
      expect(mob.toastDir).toBe('column');
    });

    test('COMMENTS: thread hairlines, L-connectors from avatar geometry, voices, link-idiom actions, tombstone', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=comments&theme=${brand}`);
      const demo = page.locator('#comments-anatomy-demo');
      // HEADING = heading-sm voice (Inter extrabold)
      const heading = await demo.locator('.article-comments__heading').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { font: cs.fontFamily, weight: cs.fontWeight };
      });
      expect(heading.font).toContain('Inter');
      expect(heading.weight).toBe('800');
      // LIST ARMOR — no markers, no counter padding on comment li
      const li = await demo.locator('.article-comments__list > .comment').first().evaluate((el) => ({
        listStyle: getComputedStyle(el).listStyleType,
        padLeft: getComputedStyle(el).paddingLeft,
        before: getComputedStyle(el, '::before').content,
      }));
      expect(li.listStyle).toBe('none');
      expect(li.padLeft).toBe('0px');
      expect(['none', 'normal']).toContain(li.before);
      // HAIRLINES — top of thread; below each top-level comment except the last
      const rails = await demo.evaluate((d) => {
        const list = d.querySelector('.article-comments__list');
        const tops = [...list.children].filter((el) => el.matches('.comment'));
        return {
          listTop: getComputedStyle(list).borderTopWidth,
          firstBottom: getComputedStyle(tops[0]).borderBottomWidth,
          lastBottom: getComputedStyle(tops[tops.length - 1]).borderBottomWidth,
        };
      });
      expect(rails.listTop).toBe('1px');
      expect(rails.firstBottom).toBe('1px');
      expect(rails.lastBottom).toBe('0px');
      // THREAD CONNECTORS — avatar-derived geometry: 72px indent, L 60x36 with
      // left+bottom borders, spine through non-last replies only
      const conn = await demo.evaluate((d) => {
        const replies = [...d.querySelectorAll('.children > .comment')];
        const cs0b = getComputedStyle(replies[0], '::before');
        const cs0a = getComputedStyle(replies[0], '::after');
        const cs1a = getComputedStyle(replies[1], '::after');
        return {
          indent: getComputedStyle(replies[0]).paddingLeft,
          lWidth: cs0b.width, lHeight: cs0b.height,
          lLeft: cs0b.borderLeftWidth, lBottom: cs0b.borderBottomWidth,
          spineFirst: cs0a.content !== 'none' && cs0a.borderLeftWidth === '1px',
          spineLast: cs1a.content,
        };
      });
      expect(conn.indent).toBe('72px'); // avatar 48 + gap 24
      expect(conn.lWidth).toBe('60px');
      expect(conn.lHeight).toBe('36px'); // corner lands at avatar center y=24
      expect(conn.lLeft).toBe('1px');
      expect(conn.lBottom).toBe('1px');
      expect(conn.spineFirst).toBe(true);   // non-last reply continues the spine
      expect(conn.spineLast).toBe('none');  // last reply ends the spine
      // AVATAR — DS avatar --xs (48, circle) + --initials (brand fill, on-brand ink)
      const avatar = await demo.locator('.avatar--xs.avatar--initials').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        const probe = document.createElement('span');
        probe.style.color = 'var(--brand)';
        el.parentElement.appendChild(probe);
        const brand = getComputedStyle(probe).color;
        probe.style.color = 'var(--text-on-brand)';
        const ink = getComputedStyle(probe).color;
        probe.remove();
        return { w: cs.width, radius: cs.borderRadius, bg: cs.backgroundColor, brand, color: cs.color, ink };
      });
      expect(avatar.w).toBe('48px');
      expect(avatar.bg).toBe(avatar.brand);
      expect(avatar.color).toBe(avatar.ink);
      // VOICES — fn = heading-xs (18/800); metadata = caption + text-secondary
      const voices = await demo.evaluate((d) => {
        const fn = getComputedStyle(d.querySelector('.fn'));
        const meta = getComputedStyle(d.querySelector('.comment-metadata'));
        const probe = document.createElement('span');
        probe.style.color = 'var(--text-secondary)';
        d.appendChild(probe);
        const secondary = getComputedStyle(probe).color;
        probe.remove();
        return { fnSize: fn.fontSize, fnWeight: fn.fontWeight, metaSize: meta.fontSize, metaColor: meta.color, secondary };
      });
      expect(voices.fnSize).toBe('18px');
      expect(voices.fnWeight).toBe('800');
      expect(voices.metaSize).toBe('14px');
      expect(voices.metaColor).toBe(voices.secondary);
      // ACTIONS — Reply <a> and Edit <button> BOTH read the link idiom
      const actions = await page.locator('#comments-states-demo').evaluate((d) => {
        const a = d.querySelector('.comment-actions a');
        const btn = d.querySelector('button.aif-comment-edit-link');
        const probe = document.createElement('span');
        probe.style.color = 'var(--link)';
        d.appendChild(probe);
        const link = getComputedStyle(probe).color;
        probe.remove();
        return {
          aColor: getComputedStyle(a).color, aDeco: getComputedStyle(a).textDecorationLine,
          bColor: getComputedStyle(btn).color, bDeco: getComputedStyle(btn).textDecorationLine,
          link,
        };
      });
      expect(actions.aColor).toBe(actions.link);
      expect(actions.aDeco).toBe('underline');
      expect(actions.bColor).toBe(actions.link);
      expect(actions.bDeco).toBe('underline');
      // TOMBSTONE — muted color only; avatar drops the brand fill for --raised
      const tomb = await page.locator('#comments-states-demo .aif-tombstone').evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--text-tertiary)';
        el.appendChild(probe);
        const tertiary = getComputedStyle(probe).color;
        probe.style.color = 'var(--raised)';
        const raised = getComputedStyle(probe).color;
        probe.remove();
        return {
          fn: getComputedStyle(el.querySelector('.fn')).color,
          body: getComputedStyle(el.querySelector('.comment-content p')).color,
          avatarBg: getComputedStyle(el.querySelector('.avatar--initials')).backgroundColor,
          tertiary, raised,
          actions: el.querySelectorAll('.comment-actions').length,
        };
      });
      expect(tomb.fn).toBe(tomb.tertiary);
      expect(tomb.body).toBe(tomb.tertiary);
      expect(tomb.avatarBg).toBe(tomb.raised);
      expect(tomb.actions).toBe(0); // tombstones carry no action row
      // MOBILE 390 — avatar 40, indent 56, no overflow
      await page.setViewportSize({ width: 390, height: 1200 });
      const mob = await page.evaluate(() => ({
        overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth,
        avatarW: getComputedStyle(document.querySelector('#comments-anatomy-demo .avatar--xs')).width,
        indent: getComputedStyle(document.querySelector('#comments-anatomy-demo .children > .comment')).paddingLeft,
      }));
      expect(mob.overflow).toBe(true);
      expect(mob.avatarW).toBe('40px');
      expect(mob.indent).toBe('56px');
    });

    test('STICKY BAR: slim skin, shadow-up when visible, chatbot axis, LIVE fraction show + SAMPLER flip, mobile transform', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=sticky-bar&theme=${brand}`);
      // STATIC email bar — the slim light canon: --bg fill, quiet 1px --border
      // edge, 40px controls, shadow-up ONLY while visible (it is), chatbot pad
      const skin = await page.locator('#sticky-static-email .sticky-bar').evaluate((bar) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--bg)';
        bar.appendChild(probe);
        const bg = getComputedStyle(probe).color;
        probe.style.color = 'var(--border)';
        const border = getComputedStyle(probe).color;
        probe.remove();
        const cs = getComputedStyle(bar);
        return {
          bg: cs.backgroundColor, bgRole: bg,
          borderTop: cs.borderTopWidth + ' ' + cs.borderTopStyle, borderColor: cs.borderTopColor, borderRole: border,
          shadow: cs.boxShadow,
          fieldH: Math.round(bar.querySelector('.sticky-bar__form .form-control-wrapper').getBoundingClientRect().height),
          btnH: Math.round(bar.querySelector('.sticky-bar__form .btn').getBoundingClientRect().height),
          padRight: getComputedStyle(bar.querySelector('.sticky-bar__inner')).paddingRight,
          padTop: getComputedStyle(bar.querySelector('.sticky-bar__inner')).paddingTop,
        };
      });
      expect(skin.bg).toBe(skin.bgRole);
      expect(skin.borderTop).toBe('1px solid'); // quiet edge, not a banner stripe
      expect(skin.borderColor).toBe(skin.borderRole);
      expect(skin.shadow).toContain('-10px'); // --shadow-up (upward)
      // THE BAR RUNG (operator-ruled): every control speaks ONE voice —
      // field floors at 40 on desktop pointers, the pair stretch matches
      // the submit, and ALL bar buttons read caption-size
      expect(skin.fieldH).toBe(40);
      expect(skin.btnH).toBe(skin.fieldH);
      const rung = await page.evaluate(() => {
        const read = (sel) => {
          const btn = document.querySelector(sel);
          return { font: getComputedStyle(btn).fontSize, h: Math.round(btn.getBoundingClientRect().height) };
        };
        return {
          submit: read('#sticky-static-email .sticky-bar__form .btn'),
          cta: read('#sticky-static-button .sticky-bar .btn'),
        };
      });
      expect(rung.submit.font).toBe('14px'); // one voice, no ladder rung in markup
      expect(rung.cta.font).toBe('14px');
      expect(rung.cta.h).toBe(40); // btn--sm's 38 stray died into the rung
      expect(skin.padRight).toBe('100px');    // --chatbot-clear
      expect(skin.padTop).toBe('6px');        // slim vertical rhythm
      // STATIC button bar on .section-dark — roles re-skin (dark fill, meta present)
      const dark = await page.locator('#sticky-static-button .sticky-bar').evaluate((bar) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--bg)';
        bar.appendChild(probe);
        const bg = getComputedStyle(probe).color;
        probe.remove();
        return { bg: getComputedStyle(bar).backgroundColor, bgRole: bg,
          meta: bar.querySelectorAll('.sticky-bar__meta').length };
      });
      expect(dark.bg).toBe(dark.bgRole); // the scope's --bg (dark), not a variant
      expect(dark.meta).toBe(1);
      // LIVE — hidden at top (fraction gate), slides in on scroll
      const live = page.locator('.sticky-bar[data-sticky-cta]');
      await expect(live).toHaveAttribute('aria-hidden', 'true');
      await page.locator('#sticky-live-light1').scrollIntoViewIfNeeded();
      await page.mouse.wheel(0, 200);
      await expect(live).toHaveClass(/sticky-bar--visible/);
      // THE SAMPLER — over the dark band the bar gains .section-dark…
      await page.evaluate(() => {
        const darkBand = document.querySelector('#sticky-live-dark');
        window.scrollTo(0, darkBand.offsetTop + darkBand.offsetHeight / 2 - window.innerHeight / 2);
      });
      await expect(live).toHaveClass(/section-dark/);
      // …and flips back over the light region below
      await page.evaluate(() => {
        const light = document.querySelector('#sticky-live-light2');
        window.scrollTo(0, light.offsetTop + light.offsetHeight / 2 - window.innerHeight / 2);
      });
      await expect(live).not.toHaveClass(/section-dark/);
      // MOBILE 390 — THE CHATBOT AXIS CHANGES THE SHAPE (harvested):
      // no chatbot = ONE full-width button; chatbot = clearance SURVIVES,
      // button natural width at the 14px submit size, bubble corner free
      await page.setViewportSize({ width: 390, height: 800 });
      const mob = await page.evaluate(() => {
        const read = (sel) => {
          const bar = document.querySelector(sel + ' .sticky-bar');
          const btn = bar.querySelector('.sticky-bar__btn-mobile');
          const inner = bar.querySelector('.sticky-bar__inner');
          return {
            pitch: getComputedStyle(bar.querySelector('.sticky-bar__pitch')).display,
            form: getComputedStyle(bar.querySelector('.sticky-bar__form')).display,
            btnDisplay: getComputedStyle(btn).display,
            btnW: Math.round(btn.getBoundingClientRect().width),
            btnFont: getComputedStyle(btn).fontSize,
            innerW: Math.round(inner.getBoundingClientRect().width),
            padRight: getComputedStyle(inner).paddingRight,
          };
        };
        return {
          plain: read('#sticky-axis-off'),
          chatbot: read('#sticky-axis-on'),
          overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth,
        };
      });
      // both transform: pitch + form die, the anchor button appears
      expect(mob.plain.pitch).toBe('none');
      expect(mob.plain.form).toBe('none');
      expect(mob.plain.btnDisplay).toBe('flex'); // inline-flex blockifies as a flex item
      expect(mob.chatbot.pitch).toBe('none');
      // ONE mobile button voice — 14px on BOTH states (the desktop-submit
      // size the bar collapses from; AIG's harvested reasoning unified)
      expect(mob.plain.btnFont).toBe('14px');
      expect(mob.chatbot.btnFont).toBe('14px');
      // NO CHATBOT — full width minus the 16px pads
      expect(mob.plain.padRight).toBe('16px');
      expect(Math.abs(mob.plain.btnW - (mob.plain.innerW - 32))).toBeLessThanOrEqual(1);
      // CHATBOT — 100px clearance survives; natural-width button
      expect(mob.chatbot.padRight).toBe('100px');
      expect(mob.chatbot.btnW).toBeLessThan(mob.chatbot.innerW - 120); // clearly not full width
      expect(mob.overflow).toBe(true);
    });

    test('BLURB + STACK GRID: closed rungs, numbered row, THE PIZZA PATTERN everywhere (interior-only separators), mobile', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=blurb&theme=${brand}`);
      // HEADLINE RUNGS — the closed set of three (18 / 22 / fluid display)
      const rungs = await page.locator('#blurb-rungs').evaluate((row) => {
        const hs = [...row.querySelectorAll('.blurb__headline')].map((h) => {
          const cs = getComputedStyle(h);
          return { size: parseFloat(cs.fontSize), font: cs.fontFamily, weight: cs.fontWeight };
        });
        return hs;
      });
      expect(Math.round(rungs[0].size)).toBe(18);       // sm = heading-xs
      expect(rungs[1].size).toBeGreaterThanOrEqual(20); // md = heading-sm (brand 20/22)
      expect(rungs[2].size).toBeGreaterThan(23.9);      // lg = fluid display clamp(24→32)
      expect(rungs[2].size).toBeLessThanOrEqual(32);
      expect(rungs[2].font).toContain('Inter');         // benefit-display = Inter BOTH brands
      expect(rungs[2].weight).toBe('800');
      // TEXT LADDER = the info-box sizes (18/16/14)
      const ladder = await page.locator('#blurb-ladder').evaluate((row) =>
        [...row.querySelectorAll('.blurb__text')].map((t) => getComputedStyle(t).fontSize));
      expect(ladder).toEqual(['18px', '16px', '14px']);
      // NUMBERED ROW — the counter is a grid duty; empty eyebrows auto-fill
      const numbered = await page.locator('#blurb-numbered').evaluate((row) => {
        const eyes = [...row.querySelectorAll('.blurb__eyebrow')];
        return {
          // Chromium reports counter() content unresolved — assert the
          // counter binding + that the pseudo actually renders (has width)
          counters: eyes.map((e) => getComputedStyle(e, '::before').content),
          reset: getComputedStyle(row).counterReset,
          increment: getComputedStyle(row.querySelector('.blurb')).counterIncrement,
          eyeWidths: eyes.map((e) => Math.round(e.getBoundingClientRect().width)),
          cellBorders: [...row.children].map((c) => getComputedStyle(c).borderTopWidth),
          gap: getComputedStyle(row).rowGap + ' ' + getComputedStyle(row).columnGap,
        };
      });
      for (const c of numbered.counters) expect(c).toContain('counter(blurb');
      expect(numbered.reset).toContain('blurb');
      expect(numbered.increment).toContain('blurb');
      for (const w of numbered.eyeWidths) expect(w).toBeGreaterThan(0); // the digits render
      // NO top-border grammar (operator veto): the harvested cert rule-tops DIE
      for (const b of numbered.cellBorders) expect(b).toBe('0px');
      expect(numbered.gap).toBe('1px 1px'); // the benefits row is a divided grid — interior cuts only
      // INTERIOR-ONLY on the light 3×2 (the newsletter landing) — the pizza
      // pattern everywhere: no line above row 1, no line on any outer edge
      const light = await page.locator('#blurb-rules-light').evaluate((grid) => {
        const cells = [...grid.children];
        const cs = (el) => getComputedStyle(el);
        const probe = document.createElement('span');
        probe.style.color = 'var(--border)';
        grid.parentElement.appendChild(probe);
        const border = getComputedStyle(probe).color;
        probe.remove();
        return {
          gap: cs(grid).rowGap + ' ' + cs(grid).columnGap,
          lineLayer: cs(grid).backgroundColor, border,
          borders: cells.map((c) => cs(c).borderTopWidth + cs(c).borderLeftWidth),
          rows: new Set(cells.map((c) => Math.round(c.getBoundingClientRect().top))).size,
        };
      });
      expect(light.gap).toBe('1px 1px');           // the gaps ARE the (interior) separators
      expect(light.lineLayer).toBe(light.border);  // painted in --border
      for (const b of light.borders) expect(b).toBe('0px0px'); // cells carry NO borders — no edge lines exist
      expect(light.rows).toBe(2);                  // the 3×2 multi-row case
      // THE PIZZA LAW — the divided grid paints the line through 1px gaps;
      // separators span the grid content, never the crust
      const pizza = await page.locator('#blurb-pizza-box').evaluate((box) => {
        const grid = box.querySelector('.stack-grid--divided');
        const cell = grid.children[0];
        const probe = document.createElement('span');
        probe.style.color = 'var(--border)';
        box.appendChild(probe);
        const border = getComputedStyle(probe).color;
        probe.style.color = 'var(--raised)';
        const raised = getComputedStyle(probe).color;
        probe.remove();
        const g = getComputedStyle(grid);
        return {
          gap: g.rowGap + ' ' + g.columnGap,
          lineLayer: g.backgroundColor, border,
          cellFill: getComputedStyle(cell).backgroundColor, raised,
          gridInsideBox: grid.getBoundingClientRect().left > box.getBoundingClientRect().left + 10, // the crust
          cellBorder: getComputedStyle(cell).borderTopWidth,
          // SINGLE-OWNERSHIP PADDING — cells pad interior sides only
          c0: (({ paddingTop: t, paddingRight: r, paddingBottom: b, paddingLeft: l }) =>
            [t, r, b, l])(getComputedStyle(grid.children[0])), // row 1 col 1: interior = right+bottom
          c4: (({ paddingTop: t, paddingRight: r, paddingBottom: b, paddingLeft: l }) =>
            [t, r, b, l])(getComputedStyle(grid.children[4])), // row 2 col 2: interior = top+left+right
        };
      });
      expect(pizza.gap).toBe('1px 1px');            // the gaps ARE the separators
      expect(pizza.lineLayer).toBe(pizza.border);   // painted in --border (normal, not strong)
      expect(pizza.cellFill).toBe(pizza.raised);    // cells repaint over the line layer
      expect(pizza.gridInsideBox).toBe(true);       // crust: separators never reach the box edge
      expect(pizza.c0).toEqual(['0px', '24px', '24px', '0px']); // crust owns the edges — no double padding
      expect(pizza.c4).toEqual(['24px', '24px', '0px', '24px']);
      // MOBILE 390 — single column; no overflow
      await page.setViewportSize({ width: 390, height: 900 });
      const mob = await page.evaluate(() => ({
        cols: getComputedStyle(document.querySelector('#blurb-pizza-box .stack-grid')).gridTemplateColumns.split(' ').length,
        overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth,
      }));
      expect(mob.cols).toBe(1);
      expect(mob.overflow).toBe(true);
    });

    test('HEADER: surface-riding chrome, the shrink, nav grammar, a11y dropdown, burger engine, progress, mobile', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=header&theme=${brand}`);
      // THE NEW ROLES resolve to the operator-ruled per-brand values
      const expected = brand === 'aifounders'
        ? { nav: 'rgb(152, 195, 217)', fill: 'rgb(160, 201, 47)' } // #98c3d9 / #a0c92f
        : { nav: 'rgb(99, 83, 27)', fill: 'rgb(215, 45, 164)' }; // #63531b / #d72da4
      const roles = await page.evaluate(() => {
        const probe = document.createElement('span');
        document.body.appendChild(probe);
        probe.style.color = 'var(--nav-active)';
        const nav = getComputedStyle(probe).color;
        probe.style.color = 'var(--progress-fill)';
        const fill = getComputedStyle(probe).color;
        probe.remove();
        return { nav, fill };
      });
      expect(roles.nav).toBe(expected.nav);
      expect(roles.fill).toBe(expected.fill);
      // GEOMETRY + SURFACE-RIDING: bg = the scope's --bg (variants are dead)
      const light = await page.locator('#header-light .main-header').evaluate((h) => {
        const probe = document.createElement('span');
        h.appendChild(probe);
        probe.style.color = 'var(--bg)';
        const bg = getComputedStyle(probe).color;
        probe.remove();
        const cs = getComputedStyle(h);
        return { height: h.getBoundingClientRect().height, z: cs.zIndex, bgc: cs.backgroundColor, bg };
      });
      expect(Math.round(light.height)).toBe(80);
      expect(light.z).toBe('100');
      expect(light.bgc).toBe(light.bg);
      const darkBg = await page.locator('#header-dark .main-header').evaluate((h) => {
        const probe = document.createElement('span');
        h.appendChild(probe);
        probe.style.color = 'var(--bg)';
        const bg = getComputedStyle(probe).color;
        probe.remove();
        return { bgc: getComputedStyle(h).backgroundColor, bg };
      });
      expect(darkBg.bgc).toBe(darkBg.bg); // same markup, dark scope
      // NAV GRAMMAR: SG 16/500 at the 36 rung; active underline = --nav-active
      const item = await page.locator('#header-light .nav-item--active').evaluate((el) => {
        const cs = getComputedStyle(el);
        const after = getComputedStyle(el, '::after');
        return { font: cs.fontFamily, size: cs.fontSize, h: el.getBoundingClientRect().height, underline: after.backgroundColor, uw: parseFloat(after.width) };
      });
      expect(item.font).toContain('Space Grotesk');
      expect(item.size).toBe('16px');
      expect(Math.round(item.h)).toBe(36);
      expect(item.underline).toBe(expected.nav); // the #98C3D9/#63531B hardcodes are dead
      expect(item.uw).toBeGreaterThan(0);
      // ACTION SLOT = a real DS button at the header rung
      const cta = await page.locator('#header-light .main-header .btn').evaluate((b) => ({
        minH: getComputedStyle(b).minHeight,
        fs: getComputedStyle(b).fontSize,
      }));
      expect(cta.minH).toBe('36px');
      expect(cta.fs).toBe('14px');
      // THE SHRINK (forced): 56, light → WHITE (--bg-base, operator) + shadow,
      // dark → raised + shadow, item rung 34
      const scrolled = await page.locator('#header-scrolled .main-header').evaluate((h) => {
        const probe = document.createElement('span');
        h.appendChild(probe);
        probe.style.color = 'var(--bg-base)';
        const base = getComputedStyle(probe).color;
        probe.remove();
        const cs = getComputedStyle(h);
        return {
          height: h.getBoundingClientRect().height,
          bg: cs.backgroundColor, base,
          shadow: cs.boxShadow,
          navH: h.querySelector('.nav-item').getBoundingClientRect().height,
        };
      });
      expect(Math.round(scrolled.height)).toBe(56);
      expect(scrolled.bg).toBe(scrolled.base); // white on the light band
      expect(scrolled.bg).toBe('rgb(255, 255, 255)');
      expect(scrolled.shadow).not.toBe('none');
      expect(Math.round(scrolled.navH)).toBe(34);
      const scrolledDark = await page.locator('#header-scrolled-dark .main-header').evaluate((h) => {
        const probe = document.createElement('span');
        h.appendChild(probe);
        probe.style.color = 'var(--raised)';
        const raised = getComputedStyle(probe).color;
        probe.remove();
        return {
          bg: getComputedStyle(h).backgroundColor, raised,
          shadow: getComputedStyle(h).boxShadow,
          bars: h.querySelectorAll('.reading-progress').length,
        };
      });
      expect(scrolledDark.bg).toBe(scrolledDark.raised); // the dark lift stays
      expect(scrolledDark.shadow).not.toBe('none');
      expect(scrolledDark.bars).toBe(0); // the progress bar is an AXIS: articles only
      // PROGRESS: scrolled-only, 2px, fill = --progress-fill, engine attached
      const prog = await page.locator('#header-scrolled .reading-progress').evaluate((bar) => {
        const after = getComputedStyle(bar, '::after');
        return { display: getComputedStyle(bar).display, h: getComputedStyle(bar).height, fill: after.backgroundColor, transform: after.transform, anim: after.animationName };
      });
      expect(prog.display).toBe('block');
      expect(prog.h).toBe('2px');
      expect(prog.fill).toBe(expected.fill);
      // modern path: the scroll-timeline animation owns the fill; fallback: the forced var
      expect(prog.anim.includes('progress') || prog.transform.startsWith('matrix(0.6')).toBe(true);
      // A11Y — :focus-within opens the dropdown (the mapped keyboard gap, fixed)
      await page.locator('#header-light .nav-item__trigger').focus();
      const dd = await page.locator('#header-light .nav-dropdown').evaluate((d) => getComputedStyle(d).display);
      expect(dd).toBe('flex');
      // production's inert --active dropdown class got its rule
      const ddActive = await page.locator('#header-light .nav-dropdown-item--active').evaluate((a) => getComputedStyle(a).textDecorationColor);
      expect(ddActive).toBe(expected.nav);
      // BURGER ENGINE (demo frame): cross + aria + overlay, NO page lock
      await page.locator('#header-mobile .burger-toggle').click();
      // the cross transition is 0.3s — poll the settled state
      await page.waitForFunction(() => {
        const mid = document.querySelector('#header-mobile .line-mid');
        return mid && getComputedStyle(mid).opacity === '0';
      });
      const burger = await page.locator('#header-mobile').evaluate((demo) => {
        const b = demo.querySelector('.burger-toggle');
        return {
          aria: b.getAttribute('aria-expanded'),
          top: getComputedStyle(b.querySelector('.line-top')).transform,
          overlay: getComputedStyle(demo.querySelector('.mobile-menu-overlay')).display,
          bodyLocked: document.body.classList.contains('menu-open'),
        };
      });
      expect(burger.aria).toBe('true');
      expect(burger.top).not.toBe('none'); // the cross rotation applied
      expect(burger.overlay).toBe('flex');
      expect(burger.bodyLocked).toBe(false); // demo mode never locks the page
      // mobile items: desktop weight, PRIMARY color (operator polish)
      const mobItem = await page.locator('#header-mobile .mobile-nav-item--sub').evaluate((el) => {
        const probe = document.createElement('span');
        el.parentElement.appendChild(probe);
        probe.style.color = 'var(--text)';
        const text = getComputedStyle(probe).color;
        probe.remove();
        return { weight: getComputedStyle(el).fontWeight, color: getComputedStyle(el).color, text };
      });
      expect(mobItem.weight).toBe('500');
      expect(mobItem.color).toBe(mobItem.text);
      // the parent chevron died — no .mobile-nav-arrow may exist
      expect(await page.locator('#header-mobile .mobile-nav-arrow').count()).toBe(0);
      await page.keyboard.press('Escape');
      const closedOverlay = await page.locator('#header-mobile .mobile-menu-overlay').evaluate((o) => getComputedStyle(o).display);
      expect(closedOverlay).toBe('none');
      // MOBILE 390 — the knobs: 64 header, desktop nav → burger
      await page.setViewportSize({ width: 390, height: 900 });
      // the height rides the 0.25s shrink transition — poll the settled value
      await page.waitForFunction(() => {
        const h = document.querySelector('#header-light .main-header');
        return h && Math.round(h.getBoundingClientRect().height) === 64;
      });
      const mob = await page.locator('#header-light .main-header').evaluate((h) => ({
        desktop: getComputedStyle(h.querySelector('.site-nav--desktop')).display,
        mobile: getComputedStyle(h.querySelector('.site-nav--mobile')).display,
      }));
      expect(mob.desktop).toBe('none');
      expect(mob.mobile).toBe('flex');
    });

    test('FOOTER: band stack, dark-2 stripe + knob-synced pull, canon columns, idioms, scope-alias drop, chatbot axis, mobile', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=footer&theme=${brand}`);
      // SHELL: paints nothing itself — the markup scope (.section-dark) does;
      // the dark-1 `.footer` ALIAS IS DEAD (a bare .footer stays light)
      const scopes = await page.evaluate(() => {
        const full = document.querySelector('#footer-full');
        const probe = document.createElement('div');
        probe.className = 'footer';
        document.querySelector('.sg-main').appendChild(probe);
        const bare = getComputedStyle(probe).getPropertyValue('--bg').trim();
        const light = getComputedStyle(document.querySelector('.sg-main')).getPropertyValue('--bg').trim();
        probe.remove();
        const band = full.querySelector('.footer__newsletter-section');
        return {
          shellBg: getComputedStyle(full).backgroundColor,
          scopeBg: getComputedStyle(full).getPropertyValue('--bg').trim(),
          bandBg: getComputedStyle(band).backgroundColor,
          bandScopeBg: getComputedStyle(band).getPropertyValue('--bg').trim(),
          bareIsLight: bare === light,
          bandPull: getComputedStyle(band).marginTop,
          shellPadTop: getComputedStyle(full).paddingTop,
          shellPadBottom: getComputedStyle(full).paddingBottom,
        };
      });
      expect(scopes.bareIsLight).toBe(true); // the scope-map alias is gone
      expect(scopes.bandScopeBg).not.toBe(scopes.scopeBg); // dark-2 ≠ dark-1 (the band LIVES)
      expect(scopes.bandBg).toContain('rgb'); // band paints its scope bg
      expect(scopes.shellPadTop).toBe('80px');
      expect(scopes.bandPull).toBe('-80px'); // knob-synced negative pull
      expect(scopes.shellPadBottom).toBe('56px'); // --chatbot-clear on the full demo
      const plainPad = await page.locator('#footer-minimal').evaluate((f) => getComputedStyle(f).paddingBottom);
      expect(plainPad).toBe('24px'); // plain shell without the axis
      // CANON COLUMNS: stack-grid 3-up, QUATERNARY eyebrow (palette-direct dark-400)
      const cols = await page.locator('#footer-full .stack-grid').evaluate((g) => {
        const eye = g.querySelector('.blurb__eyebrow');
        const probe = document.createElement('span');
        g.appendChild(probe);
        probe.style.color = 'var(--dark-400)';
        const q = getComputedStyle(probe).color;
        probe.remove();
        return {
          cols: getComputedStyle(g).gridTemplateColumns.split(' ').length,
          eyeColor: getComputedStyle(eye).color, q,
          gap: getComputedStyle(g).rowGap,
        };
      });
      expect(cols.cols).toBe(3);
      expect(cols.eyeColor).toBe(cols.q); // operator: footer eyebrows = quaternary
      expect(cols.gap).toBe('80px');
      // THE ARROW-LINK IDIOM: prefix un-underlined, label underlined, dark link roles
      const subtle = await page.locator('#footer-full .footer__subtle-link').first().evaluate((a) => {
        const probe = document.createElement('span');
        a.parentElement.appendChild(probe);
        probe.style.color = 'var(--link)';
        const link = getComputedStyle(probe).color;
        probe.remove();
        return {
          before: getComputedStyle(a, '::before').content,
          color: getComputedStyle(a).color, link,
          rootDeco: getComputedStyle(a).textDecorationLine,
          spanDeco: getComputedStyle(a.querySelector('span')).textDecorationLine,
        };
      });
      expect(subtle.before).toContain('→'); // the arrow prefix
      expect(subtle.color).toBe(subtle.link);
      expect(subtle.rootDeco).toBe('none');
      expect(subtle.spanDeco).toBe('underline');
      // LEGAL: divider = --raised (2px), links KEEP the underline on hover (link law)
      const legal = await page.locator('#footer-full .footer__legal-section').evaluate((s) => {
        const d = s.querySelector('.footer__divider');
        const probe = document.createElement('span');
        s.appendChild(probe);
        probe.style.color = 'var(--raised)';
        const raised = getComputedStyle(probe).color;
        probe.style.color = 'var(--text-tertiary)';
        const tertiary = getComputedStyle(probe).color;
        probe.remove();
        return {
          dividerBg: getComputedStyle(d).backgroundColor, raised,
          dividerH: getComputedStyle(d).height,
          legalColor: getComputedStyle(s.querySelector('.footer__legal')).color, tertiary,
        };
      });
      expect(legal.dividerBg).toBe(legal.raised);
      expect(legal.dividerH).toBe('2px');
      expect(legal.legalColor).toBe(legal.tertiary);
      await page.locator('#footer-full .footer__legal-link').first().hover();
      const hovered = await page.locator('#footer-full .footer__legal-link').first().evaluate((a) => ({
        deco: getComputedStyle(a).textDecorationLine,
      }));
      expect(hovered.deco).toBe('underline'); // the LAW: hover never removes it
      // CONSENT NOTE — standard capture anatomy (operator 2026-07-07):
      // the note voice = TERTIARY text (grayish, never white, never brand —
      // the harvested tint-light cream was vetoed twice: first as a
      // palette-direct read, then as a color), link rides --link
      const note = await page.locator('#footer-full .footer__newsletter-section .mc4wp-consent-note').evaluate((n) => {
        const probe = document.createElement('span');
        n.parentElement.appendChild(probe);
        probe.style.color = 'var(--text-tertiary)';
        const noteRole = getComputedStyle(probe).color;
        probe.style.color = 'var(--link)';
        const link = getComputedStyle(probe).color;
        probe.remove();
        return {
          size: getComputedStyle(n).fontSize,
          color: getComputedStyle(n).color, noteRole,
          linkColor: getComputedStyle(n.querySelector('a')).color, link,
          linkDeco: getComputedStyle(n.querySelector('a')).textDecorationLine,
        };
      });
      expect(note.size).toBe('16px');
      expect(note.color).toBe(note.noteRole); // the voice is a ROLE, not a palette read
      // …and on the dark band the role resolves to the quiet gray (dark-300)
      expect(note.color).toBe(brand === 'aiguild' ? 'rgb(183, 187, 180)' : 'rgb(139, 145, 158)');
      expect(note.linkColor).toBe(note.link);
      expect(note.linkDeco).toBe('underline');
      // BOTTOM BAR: the 16px chrome pair; icons QUATERNARY at rest
      // (operator; browser-verified production ~rgb(106,106,106))
      const bar = await page.locator('#footer-full .footer__bottom-bar').evaluate((b) => {
        const probe = document.createElement('span');
        b.appendChild(probe);
        probe.style.color = 'var(--dark-400)';
        const muted = getComputedStyle(probe).color;
        probe.remove();
        return {
          gap: getComputedStyle(b.querySelector('.footer__social-icons')).gap,
          iconH: b.querySelector('.footer__social-link svg').getBoundingClientRect().height,
          iconColor: getComputedStyle(b.querySelector('.footer__social-link')).color,
          muted,
          rendered: b.querySelectorAll('.footer__social-link svg').length,
        };
      });
      expect(bar.gap).toBe('16px'); // GM from the raw 20
      expect(Math.round(bar.iconH)).toBe(16);
      expect(bar.iconColor).toBe(bar.muted);
      expect(bar.rendered).toBe(3); // every catalog icon renders (facebook joined 2026-07-06)
      // MOBILE 390: single column, stacked bottom bar, chatbot 80 / plain 16
      await page.setViewportSize({ width: 390, height: 900 });
      const mob = await page.evaluate(() => ({
        cols: getComputedStyle(document.querySelector('#footer-full .stack-grid')).gridTemplateColumns.split(' ').length,
        barDir: getComputedStyle(document.querySelector('#footer-full .footer__bottom-bar')).flexDirection,
        fullPadBottom: getComputedStyle(document.querySelector('#footer-full')).paddingBottom,
        plainPadBottom: getComputedStyle(document.querySelector('#footer-minimal')).paddingBottom,
        bandPull: getComputedStyle(document.querySelector('#footer-full .footer__newsletter-section')).marginTop,
        // THE STACKED CAPTURE keeps its control heights (production 52/52;
        // the flex-basis-0-overrides-height trap collapsed both, 2026-07-07)
        captureDir: getComputedStyle(document.querySelector('#footer-full .footer__newsletter-section .mc4wp-form-fields')).flexDirection,
        fieldH: Math.round(document.querySelector('#footer-full .footer__newsletter-section .form-control-wrapper').getBoundingClientRect().height),
        submitH: Math.round(document.querySelector('#footer-full .footer__newsletter-section .btn').getBoundingClientRect().height),
        // THE CONJOIN LAW: the join border-collapse is ROW-ONLY — the
        // stacked field keeps all four borders + the 8px breath
        fieldBorderRight: getComputedStyle(document.querySelector('#footer-full .footer__newsletter-section .form-control-wrapper')).borderRightWidth,
        captureGap: getComputedStyle(document.querySelector('#footer-full .footer__newsletter-section .mc4wp-form-fields')).gap,
      }));
      expect(mob.cols).toBe(1);
      expect(mob.barDir).toBe('column');
      expect(mob.fullPadBottom).toBe('80px'); // harvested mobile bubble clearance
      expect(mob.plainPadBottom).toBe('16px');
      expect(mob.bandPull).toBe('-40px'); // the pull follows the knob at every breakpoint
      expect(mob.captureDir).toBe('column'); // the stack (CONJOIN LAW, <600)
      expect(mob.fieldH).toBe(52); // production-verified stacked field
      expect(mob.submitH).toBeGreaterThanOrEqual(52); // production-verified stacked submit
      expect(mob.fieldBorderRight).toBe('2px'); // the join collapse never leaks into the stack
      expect(mob.captureGap).toBe('8px'); // the stacked breath
    });

    test('INFO BAR: V2 skin — raised band, 4px brand rule at the perex indent, bold voice, 1..n, surfaces', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=info-bar&theme=${brand}`);
      // THE V2 SKIN on the dark host
      const dark = await page.locator('#infobar-dark .info-bar').evaluate((bar) => {
        const item = bar.querySelector('.info-bar__item');
        const p = item.querySelector('p');
        const probe = document.createElement('span');
        probe.style.color = 'var(--raised)';
        bar.appendChild(probe);
        const raised = getComputedStyle(probe).color;
        probe.style.color = 'var(--brand)';
        const brandC = getComputedStyle(probe).color;
        probe.remove();
        const ics = getComputedStyle(item);
        const pcs = getComputedStyle(p);
        return {
          fill: getComputedStyle(bar).backgroundColor, raised,
          rule: ics.borderLeftWidth + ' ' + ics.borderLeftStyle, ruleColor: ics.borderLeftColor, brandC,
          pad: ics.paddingLeft,
          font: pcs.fontFamily, size: pcs.fontSize, weight: pcs.fontWeight,
        };
      });
      expect(dark.fill).toBe(dark.raised);        // surface-riding band
      expect(dark.rule).toBe('4px solid');        // V2: 4px (was 2)
      expect(dark.ruleColor).toBe(dark.brandC);   // brand accent
      expect(dark.pad).toBe('20px');              // THE PEREX INDENT: flow-indent(24) − stroke-4
      expect(dark.font).toContain('Space Grotesk');
      expect(dark.size).toBe('16px');
      expect(dark.weight).toBe('700');            // V2: bold (was regular)
      // 1..n — four statements share one row on a real full-width page
      // (the styleguide sidebar narrows the wrapper below 4× the wrap
      // floor at 1280 — wrapping there is the floor working as designed)
      await page.setViewportSize({ width: 1720, height: 900 });
      const four = await page.locator('#infobar-four .info-bar__wrapper').evaluate((w) => {
        const items = [...w.querySelectorAll('.info-bar__item')];
        const tops = new Set(items.map((i) => Math.round(i.getBoundingClientRect().top)));
        const widths = items.map((i) => Math.round(i.getBoundingClientRect().width));
        return { count: items.length, rows: tops.size, spread: Math.max(...widths) - Math.min(...widths) };
      });
      expect(four.count).toBe(4);
      expect(four.rows).toBe(1);                  // one row at production width
      expect(four.spread).toBeLessThanOrEqual(2); // equal columns
      await page.setViewportSize({ width: 1280, height: 900 });
      // SURFACE — the same bar on light resolves a different raised value
      const light = await page.locator('#infobar-light .info-bar').evaluate((bar) => getComputedStyle(bar).backgroundColor);
      expect(light).not.toBe(dark.fill);
      // MOBILE 390 — statements stack, no overflow
      await page.setViewportSize({ width: 390, height: 900 });
      const mob = await page.evaluate(() => {
        const items = [...document.querySelectorAll('#infobar-dark .info-bar__item')];
        const tops = new Set(items.map((i) => Math.round(i.getBoundingClientRect().top)));
        return { rows: tops.size, overflow: document.documentElement.scrollWidth <= document.documentElement.clientWidth };
      });
      expect(mob.rows).toBe(3); // stacked
      expect(mob.overflow).toBe(true);
    });

    test('SELECTION HOVER = the input border transform (one idiom) + group mandatory marker', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=checkbox&theme=${brand}`);
      const chip = page.locator('.selection-group .selection-item--checkbox').nth(1); // unchecked
      // REST — same border as the input wrapper (--field-border)
      const rest = await chip.evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--field-border)';
        el.appendChild(probe);
        const fieldBorder = getComputedStyle(probe).color;
        probe.style.color = 'var(--field-border-strong)';
        const strong = getComputedStyle(probe).color;
        probe.style.color = 'var(--link)';
        const link = getComputedStyle(probe).color;
        probe.remove();
        return { border: getComputedStyle(el.querySelector('.selection-control')).borderTopColor, fieldBorder, strong, link };
      });
      expect(rest.border).toBe(rest.fieldBorder);
      // HOVER — the input transform (--field-border-strong), NEVER the --link jump
      await chip.hover();
      await expect.poll(async () =>
        chip.locator('.selection-control').evaluate((el) => getComputedStyle(el).borderTopColor)
      ).toBe(rest.strong);
      const hovered = await chip.locator('.selection-control').evaluate((el) => getComputedStyle(el).borderTopColor);
      expect(hovered).not.toBe(rest.link);
      // CHECKED — hover must NOT touch it: the accent border wins, no --field-border-strong
      // leak (regression 2026-07-06: the :not(disabled) raised hover specificity above
      // the checked rule; :not(checked) restores checked-wins).
      const checkedChip = page.locator('.selection-group .selection-item--checkbox').nth(0); // checked
      const accent = await checkedChip.evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--control-accent)';
        el.appendChild(probe);
        const c = getComputedStyle(probe).color;
        probe.remove();
        return c;
      });
      const checkedRest = await checkedChip.locator('.selection-control').evaluate((el) => getComputedStyle(el).borderTopColor);
      expect(checkedRest).toBe(accent);
      await checkedChip.hover();
      const checkedHovered = await checkedChip.locator('.selection-control').evaluate((el) => getComputedStyle(el).borderTopColor);
      expect(checkedHovered, 'checked chip border must stay --control-accent on hover').toBe(accent);
      expect(checkedHovered).not.toBe(rest.strong);
      await page.mouse.move(0, 0); // release hover
      // DISABLED — no hover reaction
      const disabledChip = page.locator('.selection-item--checkbox:has(.selection-input:disabled)').first();
      await disabledChip.hover();
      const disabledBorder = await disabledChip.locator('.selection-control').evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--disabled-border)';
        el.parentElement.appendChild(probe);
        const disabled = getComputedStyle(probe).color;
        probe.remove();
        return { border: getComputedStyle(el).borderTopColor, disabled };
      });
      expect(disabledBorder.border).toBe(disabledBorder.disabled);
      // GROUP MANDATORY — the checkbox group's label row carries the marker
      const groupMark = await page.locator('[role="group"] .form-label-row .form-mandatory').count();
      expect(groupMark).toBe(1);
      // radio group too
      await page.goto(`/?aifds_styleguide=1&item=radio&theme=${brand}`);
      const radioMark = await page.locator('[role="radiogroup"] .form-label-row .form-mandatory').count();
      expect(radioMark).toBe(1);
    });

    test('ACCORDION: harvested card (heading-xs title, body-lg answer, --deep arrow), height engine, exclusive mode', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=accordion&theme=${brand}`);
      const first = page.locator('.accordion').first();
      // card grammar: 1px border, --bg fill (AIG border unified onto both brands)
      await expect(first).toHaveCSS('border-top-width', '1px');
      // title = the heading-xs bundle verbatim (harvested 18/Inter/extrabold/1.35)
      const title = await first.locator('.accordion__title').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { size: cs.fontSize, weight: cs.fontWeight, lh: (parseFloat(cs.lineHeight) / parseFloat(cs.fontSize)).toFixed(2) };
      });
      expect(title.size).toBe('18px');
      expect(title.weight).toBe('800');
      expect(title.lh).toBe('1.35');
      // icon = --deep per brand (the --color-primary-deep twin), 24px box
      const icon = await first.locator('.accordion__icon').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { color: cs.color, w: cs.width };
      });
      expect(icon.color).toBe(brand === 'aifounders' ? 'rgb(0, 103, 177)' : 'rgb(138, 106, 0)');
      expect(icon.w).toBe('24px');
      // ICON LAWS: currentColor (stroke = the wrapper's --deep) + constant 1.5px
      // visual stroke via the token + non-scaling-stroke (a 20-viewBox at 24px
      // would render 1.8px without it)
      const iconPath = await first.locator('.accordion__icon svg path').first().evaluate((el) => {
        const cs = getComputedStyle(el);
        return { sw: cs.strokeWidth, ve: cs.vectorEffect, stroke: cs.stroke };
      });
      expect(iconPath.sw).toBe('1.5px');
      expect(iconPath.ve).toBe('non-scaling-stroke');
      expect(iconPath.stroke).toBe(icon.color); // currentColor chain intact
      // closed: content collapsed, aria-expanded=false
      await expect(first.locator('.accordion__header')).toHaveAttribute('aria-expanded', 'false');
      expect(await first.locator('.accordion__content').evaluate((el) => el.clientHeight)).toBe(0);
      // OPEN: click → class + aria + measured-height engine settles to auto; icon points up (-90°)
      await first.locator('.accordion__header').click();
      await expect(first).toHaveClass(/accordion--open/);
      await expect(first.locator('.accordion__header')).toHaveAttribute('aria-expanded', 'true');
      await expect.poll(async () => first.locator('.accordion__content').evaluate((el) => el.style.height), { timeout: 3000 }).toBe('auto');
      const rot = await first.locator('.accordion__icon').evaluate((el) => getComputedStyle(el).transform);
      expect(rot).toBe('matrix(0, -1, 1, 0, 0, 0)'); // rotate(-90deg)
      // answer voice = body-lg (18px regular)
      const body = await first.locator('.accordion__inner').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { size: cs.fontSize, weight: cs.fontWeight };
      });
      expect(body.size).toBe('18px');
      expect(body.weight).toBe('400');
      // EXCLUSIVE mode (the AIF newsletter behavior): opening the second closes the first
      const group = page.locator('[data-accordion="exclusive"]');
      const exFirst = group.locator('.accordion').nth(0);
      const exSecond = group.locator('.accordion').nth(1);
      await exFirst.locator('.accordion__header').click();
      await expect(exFirst).toHaveClass(/accordion--open/);
      await exSecond.locator('.accordion__header').click();
      await expect(exSecond).toHaveClass(/accordion--open/);
      await expect(exFirst).not.toHaveClass(/accordion--open/);
      await expect(exFirst.locator('.accordion__header')).toHaveAttribute('aria-expanded', 'false');
    });

    test('TYPOGRAPHY: bundles render, FLOW LAW rhythm, fiction fixes, mobile remap', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=typography&theme=${brand}`);
      // first specimen = hero -> Lazzer (operator ruling)
      const font = await page.locator('.sg-type-sample').first().evaluate((el) => getComputedStyle(el).fontFamily);
      expect(font).toContain('Lazzer');
      // MECHANISM LAW (Carbon): behavior by CLASS — display fluid, content ramp
      // one step, reading/UI constant; mechanisms never diverge per brand
      const heroRow = await page.locator('tr').filter({ hasText: /--hero-\*/ }).first().textContent();
      expect(heroRow).toContain('fluid');
      const lgRow = await page.locator('tr').filter({ hasText: /--heading-lg-\*/ }).first().textContent();
      expect(lgRow).toContain('clamp(32px, 3.2vw, 44px)'); // AIG values, real
      expect(lgRow).toContain('clamp(28px, 4.7vw, 36px)'); // AIF now fluid too — same mechanism
      expect(lgRow).toContain('fluid');
      const mdRow = await page.locator('tr').filter({ hasText: /--heading-md-\*/ }).first().textContent();
      expect(mdRow).toContain('step');
      const bodyRow = await page.locator('tr').filter({ hasText: /--body-md-\*/ }).first().textContent();
      expect(bodyRow).toContain('constant');
      // CASE primitive lives ABOVE the styles (operator): eyebrow renders CAPS
      const eyebrowRow = page.locator('tr').filter({ hasText: /--eyebrow-\*/ }).first();
      expect(await eyebrowRow.textContent()).toContain('case-upper');
      const eyebrowTransform = await eyebrowRow.locator('.sg-type-sample').evaluate((el) => getComputedStyle(el).textTransform);
      expect(eyebrowTransform).toBe('uppercase');
      // FLOW LAW on the REAL article shape: h1 -> perex -> body -> full ramp
      const spec = page.locator('[data-test="rhythm-specimen"]');
      // h1 = heading-xl: fluid size, leading 1.1 (the 'wrong line spacing' pin)
      const h1m = await spec.locator('h1').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { fs: parseFloat(cs.fontSize), lh: parseFloat(cs.lineHeight), mt: cs.marginTop };
      });
      expect(h1m.mt).toBe('0px');
      expect(Math.abs(h1m.lh / h1m.fs - 1.1)).toBeLessThan(0.01);
      // hierarchy: h1 must dwarf the article h2 (heading-md 28) — never 'same size'
      const h2fs = await spec.locator('h2').first().evaluate((el) => parseFloat(getComputedStyle(el).fontSize));
      expect(h2fs).toBe(28); // article context: ramp steps down
      expect(h1m.fs).toBeGreaterThan(h2fs * 1.5);
      // before ~2x after (48/24, 40/24, 32/16)
      await expect(spec.locator('h2').first()).toHaveCSS('margin-top', '48px');
      await expect(spec.locator('h2').first()).toHaveCSS('margin-bottom', '24px');
      await expect(spec.locator('h3')).toHaveCSS('margin-top', '40px');
      await expect(spec.locator('h4')).toHaveCSS('margin-top', '32px');
      await expect(spec.locator('h4')).toHaveCSS('margin-bottom', '16px');
      await expect(spec.locator('p:not(.text--perex)').first()).toHaveCSS('margin-bottom', '24px');
      // HARVEST TRUTH (2026-07-03 live, 7 pages): ONE 24px voice (lead = bold);
      // subheadline + lead-quote bundles DELETED (the 24px-medium never existed live)
      const t = await page.evaluate(() => {
        const cs = getComputedStyle(document.documentElement);
        return {
          lead: cs.getPropertyValue('--lead-weight').trim(),
          deadSub: cs.getPropertyValue('--subheadline-weight').trim(),
          deadLq: cs.getPropertyValue('--lead-quote-weight').trim(),
          hero: cs.getPropertyValue('--hero-size').trim(),
          heroLh: cs.getPropertyValue('--hero-leading').trim(),
          h2: cs.getPropertyValue('--heading-lg-size').trim(),
        };
      });
      expect(t.lead).toBe('700');
      expect(t.deadSub).toBe('');
      expect(t.deadLq).toBe('');
      // the REAL homepage headline: clamp(48,7vw,96) at 0.95 (was 80px fiction)
      expect(t.hero).toContain('96px');
      expect(t.heroLh).toBe('0.95');
      // heading-lg size DIVERGES per brand (harvested): AIG fluid 32->44, AIF 36
      // MECHANISM LAW: heading-lg is FLUID on both brands (values differ, mechanism never does)
      if (brand === 'aiguild') expect(t.h2).toContain('44px');
      else expect(t.h2).toContain('36px');
      // MOBILE (layer 4): AIF re-declares its own h2 size; AIG's clamp floors itself
      await page.setViewportSize({ width: 375, height: 800 });
      const mob = await page.evaluate(() => {
        const cs = getComputedStyle(document.documentElement);
        const px = (name) => {
          const d = document.createElement('div');
          d.style.fontSize = cs.getPropertyValue(name);
          document.body.appendChild(d);
          const r = parseFloat(getComputedStyle(d).fontSize);
          d.remove();
          return r;
        };
        return { lg: px('--heading-lg-size'), md: px('--heading-md-size'), sm: px('--heading-sm-size'), xs: px('--heading-xs-size') };
      });
      if (brand === 'aifounders') expect(mob.lg).toBe(28);
      else expect(mob.lg).toBe(32);
      // MOBILE RAMP LAW (operator: 'lg and md almost same on mobile' — measured
      // md=sm=22 collision): every step strictly decreasing
      expect(mob.md).toBe(22); // operator dial 2026-07-03 (was 24)
      expect(mob.sm).toBe(20);
      expect(mob.lg).toBeGreaterThan(mob.md);
      expect(mob.md).toBeGreaterThan(mob.sm);
      expect(mob.sm).toBeGreaterThan(mob.xs);
    });

    test('BREAKPOINTS: closed set of 4 cuts, generated tab, the boundary BITES at 767/768', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=breakpoints&theme=${brand}`);
      // the four canonical cuts, generated from tokens/breakpoints.json
      const table = page.locator('[data-test="bp-table"]');
      for (const [name, value, form] of [
        ['bp-sm', '600px', '(max-width: 599px)'],
        ['bp-md', '768px', '(max-width: 767px)'],
        ['bp-lg', '1024px', '(max-width: 1023px)'],
        ['bp-xl', '1440px', '(min-width: 1440px)'],
      ]) {
        const row = await table.locator('tr').filter({ hasText: name }).first().textContent();
        expect(row).toContain(value);
        expect(row, `${name}: BOUNDARY LAW query forms`).toContain(form);
      }
      expect(await table.locator('tr').count()).toBe(5); // header + exactly 4 — closed set
      // five buckets on the bar, base bucket included
      const bar = page.locator('[data-test="bp-bar"]');
      for (const bucket of ['phone', 'tablet', 'desktop', 'wide']) {
        await expect(bar.locator('span', { hasText: bucket }).first()).toBeVisible();
      }
      // live indicator tracks the viewport into the right bucket
      await page.setViewportSize({ width: 800, height: 700 });
      await expect(page.locator('[data-test="bp-live"]')).toContainText('tablet');
      await expect(page.locator('[data-test="bp-live"]')).toContainText('800');
      // THE CUT BITES: content ramp steps exactly at the bp-md boundary —
      // heading-md is 28 at 768 (desktop) and 22 at 767 (mobile step)
      const mdAt = async () => page.evaluate(() => {
        const d = document.createElement('div');
        d.style.fontSize = getComputedStyle(document.documentElement).getPropertyValue('--heading-md-size');
        document.body.appendChild(d);
        const r = parseFloat(getComputedStyle(d).fontSize);
        d.remove();
        return r;
      });
      await page.setViewportSize({ width: 768, height: 700 });
      expect(await mdAt(), 'AT the cut (768) = desktop value').toBe(28);
      await page.setViewportSize({ width: 767, height: 700 });
      expect(await mdAt(), 'one px below (767) = the mobile step').toBe(22);
    });

    test('TEXT INVERSION LAW: prose text readable on every surface (the wildcard is dead)', async ({ page }) => {
      const expectedText = {
        'light-1': c.textPrimary, 'light-2': c.textPrimary, 'light-3': c.textPrimary,
        'dark-1': c.inverseText, 'dark-2': c.inverseText, 'dark-3': c.inverseText,
        'brand': c.textPrimary, 'support': c.textPrimary,
      };
      for (const [surface, color] of Object.entries(expectedText)) {
        await expect(
          cell(page, surface).locator('.text--perex'),
          `perex text on ${surface}`
        ).toHaveCSS('color', color);
      }
    });

    test('perex border adapts per surface (harvested map + proposed brand/support)', async ({ page }) => {
      const expected = {
        'light-1': c.support,        // harvested: support accent on white
        'light-2': c.inverseLink,    // harvested: gray band → inverse-link
        'light-3': c.brandColor,     // harvested: neutral band → full brand
        'dark-1': c.deepMuted,       // harvested AIF dark homepage (deep-muted)
        'brand': c.support,          // operator 2026-07-03: support tint on brand
        'support': c.supportStrong,  // PROPOSED: the band again
      };
      for (const [surface, color] of Object.entries(expected)) {
        await expect(
          cell(page, surface).locator('.text--perex'),
          `perex border on ${surface}`
        ).toHaveCSS('border-left-color', color);
      }
    });

    test('FORMS: the model — element x scale x surface, states on top (no FF in the DS)', async ({ page }) => {
      const bgSecondary = brand === 'aiguild' ? 'rgb(242, 242, 242)' : 'rgb(247, 248, 251)';
      const inverseSecondary = brand === 'aiguild' ? 'rgb(22, 22, 22)' : 'rgb(18, 23, 28)';
      const tertiary = brand === 'aiguild' ? 'rgb(228, 228, 228)' : 'rgb(238, 242, 246)';

      // ── Form composition tab: scale × surface, checked states, consent ──
      await page.goto(`/?aifds_styleguide=1&item=form-composition&theme=${brand}`);
      // light field reads field tokens
      await expect(page.locator('.form-group .form-control-wrapper').first()).toHaveCSS('background-color', bgSecondary);
      // STATES: checked draws with brand
      await expect(page.locator('.selection-item--checkbox .selection-input:checked + .selection-control').first()).toHaveCSS('background-color', c.brandColor);
      await expect(page.locator('.selection-item--radio .selection-input:checked + .selection-control').first()).toHaveCSS('border-color', c.brandColor);
      // SCALE axis: LARGE = token root, SMALL = scope remap — same markup, sizes only
      await expect(page.locator('[data-test="scale-large"] .form-control').first()).toHaveCSS('font-size', '16px');
      await expect(page.locator('[data-test="scale-large"] .selection-control').first()).toHaveCSS('width', '24px');
      await expect(page.locator('[data-test="scale-small"] .form-control').first()).toHaveCSS('font-size', '14px');
      await expect(page.locator('[data-test="scale-small"] .selection-control').first()).toHaveCSS('width', '20px');
      // chip↔label gap tightens with the scale too (--selection-gap: 16 -> 8)
      await expect(page.locator('[data-test="scale-large"] .selection-item').first()).toHaveCSS('column-gap', '16px');
      await expect(page.locator('[data-test="scale-small"] .selection-item').first()).toHaveCSS('column-gap', '8px');
      // scale never swaps the voice: label families EQUAL across scales
      const largeFont = await page.locator('[data-test="scale-large"] .selection-label').first().evaluate((el) => getComputedStyle(el).fontFamily);
      const smallFont = await page.locator('[data-test="scale-small"] .selection-label').first().evaluate((el) => getComputedStyle(el).fontFamily);
      expect(smallFont).toBe(largeFont);
      // checkmark glyph scales with the control (fixed 12x6 broke on small chips)
      const checkW = await page.locator('[data-test="scale-small"] .selection-input:checked + .selection-control').first()
        .evaluate((el) => getComputedStyle(el, '::after').width);
      expect(checkW).toBe('10px');
      // CONSENT = the selection voice, one size quieter, NOTHING else
      const consentLabel = page.locator('[data-test="scale-large"] .selection-item--consent .selection-label');
      const plainLabel = page.locator('[data-test="scale-large"] .selection-item--checkbox:not(.selection-item--consent) .selection-label');
      await expect(consentLabel).toHaveCSS('font-size', '14px');
      const [consentVoice, plainVoice] = await Promise.all([
        consentLabel.evaluate((el) => { const cs = getComputedStyle(el); return `${cs.fontFamily}|${cs.fontWeight}|${cs.color}`; }),
        plainLabel.first().evaluate((el) => { const cs = getComputedStyle(el); return `${cs.fontFamily}|${cs.fontWeight}|${cs.color}`; }),
      ]);
      expect(consentVoice).toBe(plainVoice);
      // consent links follow the standard LINK IDIOM — no special color
      const { linkColor, linkToken } = await page.locator('[data-test="scale-large"] .selection-item--consent a').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { linkColor: cs.color, linkToken: cs.getPropertyValue('--link').trim() };
      });
      expect(linkToken.length > 0).toBe(true);
      const tokenAsRgb = await page.evaluate((t) => { const el = document.createElement('div'); el.style.color = t; document.body.appendChild(el); const c = getComputedStyle(el).color; el.remove(); return c; }, linkToken);
      expect(linkColor).toBe(tokenAsRgb);
      // SURFACE axis: field roles adapt on dark; composes with scale
      await expect(page.locator('[data-test="dark-field"] .form-control-wrapper')).toHaveCSS('background-color', inverseSecondary);
      await expect(page.locator('[data-test="dark-field"] .form-control')).toHaveCSS('color', c.inverseText);
      // rest border = --field-border (the INPUT pair — operator 2026-07-05:
      // one hover idiom across all input components; hover steps to strong)
      const darkFieldBorder = await page.locator('[data-test="dark-selection"]').evaluate((el) => {
        const probe = document.createElement('span');
        probe.style.color = 'var(--field-border)';
        el.appendChild(probe);
        const v = getComputedStyle(probe).color;
        probe.remove();
        return v;
      });
      await expect(page.locator('[data-test="dark-selection"] .selection-item--radio .selection-control')).toHaveCSS('border-color', darkFieldBorder);
      await expect(page.locator('[data-test="dark-field-small"] .form-control-wrapper')).toHaveCSS('background-color', inverseSecondary);
      await expect(page.locator('[data-test="dark-field-small"] .form-control')).toHaveCSS('font-size', '14px');
      // BRAND surface (job-board filter): the checked accent transforms to --control-accent = black,
      // never brand-on-brand. Same markup, color transform only.
      await expect(page.locator('[data-test="brand-selection"] .selection-item--checkbox .selection-input:checked + .selection-control').first()).toHaveCSS('background-color', c.darkBg);
      await expect(page.locator('[data-test="brand-selection"] .selection-item--radio .selection-input:checked + .selection-control').first()).toHaveCSS('border-color', c.darkBg);
      await expect(page.locator('[data-test="brand-selection"] .segmented-option--active')).toHaveCSS('background-color', c.darkBg);

      // ── Input tab: disabled LOOKS disabled (surface-disabled bg); small scale remaps input + textarea ──
      await page.goto(`/?aifds_styleguide=1&item=input&theme=${brand}`);
      await expect(page.locator('.form-control-wrapper:has(> .form-control:disabled)').first()).toHaveCSS('background-color', tertiary);
      await expect(page.locator('[data-test="input-large"] input.form-control').first()).toHaveCSS('font-size', '16px');
      await expect(page.locator('[data-test="input-small"] input.form-control').first()).toHaveCSS('font-size', '14px');
      await expect(page.locator('[data-test="input-small"] textarea.form-control')).toHaveCSS('font-size', '14px');

      // ── Select tab: menu items follow the field scale ──
      await page.goto(`/?aifds_styleguide=1&item=select&theme=${brand}`);
      await expect(page.locator('[data-test="select-small"] .form-select-item').first()).toHaveCSS('font-size', '14px');

      // ── Datepicker tab: collapsed trigger follows the scale; calendar states ──
      await page.goto(`/?aifds_styleguide=1&item=datepicker&theme=${brand}`);
      await expect(page.locator('[data-test="datepicker-small"] .form-control')).toHaveCSS('font-size', '14px');
      const today = page.locator('.calendar-day--today');
      await expect(today).not.toHaveCSS('color', c.brandColor);
      expect(await today.evaluate((el) => getComputedStyle(el).boxShadow)).toContain('inset');
      await expect(page.locator('.calendar-day--selected')).toHaveCSS('background-color', c.brandColor);

      // ── Checkbox tab: a disabled control dims its LABEL to --disabled-text (matches a disabled input) ──
      await page.goto(`/?aifds_styleguide=1&item=checkbox&theme=${brand}`);
      const disLabelColor = await page.locator('.selection-item--checkbox:has(.selection-input:disabled) .selection-label').first().evaluate((el) => getComputedStyle(el).color);
      const disabledTextRgb = await page.evaluate(() => { const d = document.createElement('div'); d.style.color = 'var(--disabled-text)'; document.body.appendChild(d); const c = getComputedStyle(d).color; d.remove(); return c; });
      expect(disLabelColor).toBe(disabledTextRgb);
    });

    test('COLORS tab: three layers on one page, generated, both brands, no legacy names', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=colors&theme=${brand}`);
      // LAYER 1 palette: the eight groups
      for (const g of ['Primary', 'Secondary', 'Tertiary', 'Quaternary', 'Light theme', 'Dark theme', 'Status', 'Special']) {
        await expect(page.locator('h3', { hasText: g }).first()).toBeVisible();
      }
      // palette row: name + both brand values + intent, one row
      const brandRow = await page.locator('tr', { hasText: '--brand ' }).or(page.locator('tr').filter({ hasText: /--brand/ })).first().textContent();
      expect(brandRow).toContain('#f5c400');
      expect(brandRow).toContain('#00b5ff');
      expect(brandRow).toContain('Brand elements');
      // cold/warm near-white pair holds
      const paperRow = await page.locator('tr', { hasText: '#fffdf6' }).first().textContent();
      expect(paperRow).toContain('#f6fdff');
      // LAYER 2: single-hop ref + resolved values + intent + WHERE it transforms
      const btnRow = await page.locator('tr').filter({ hasText: /--button-bg/ }).first().textContent();
      expect(btnRow).toContain('brand');
      expect(btnRow).toContain('#f5c400');
      expect(btnRow).toContain('Buttons');
      // 'transforms on' column: field-bg declares its dark/brand story right in the row
      // (there are NO -dark token names by design — same name, re-declared per background)
      const fieldRow = await page.locator('tr').filter({ hasText: /--field-bg\s/ }).first().textContent();
      expect(fieldRow).toContain('Dark');
      expect(fieldRow).toContain('Brand');
      const selRow = await page.locator('tr').filter({ hasText: /--selection-bg/ }).first().textContent();
      expect(selRow).toContain('constant');
      // LAYER 3: ONE matrix — rows = changing tokens, columns = backgrounds,
      // current theme only, dot = inherits (operator design)
      const matrix = page.locator('[data-test="transforms-matrix"]');
      for (const col of ['Default', 'Soft band', 'Neutral band', 'Dark', 'Brand', 'Support', 'Intent']) {
        await expect(matrix.locator('th', { hasText: col }).first()).toBeVisible();
      }
      const textRow = await matrix.locator('tr').filter({ hasText: /--text\s/ }).first().textContent();
      expect(textRow).toContain('paper');  // dark transform visible in the row
      expect(textRow).toContain('·');      // and it inherits somewhere too
      const btnMatrixRow = await matrix.locator('tr').filter({ hasText: /--button-bg\s/ }).first().textContent();
      expect(btnMatrixRow).toContain('black'); // the newsletter law, readable in one row
      // NO legacy vocabularies anywhere on the page
      const pageText = await page.locator('main').textContent();
      expect(pageText).not.toContain('--color-');
      expect(pageText).not.toContain('--surface-');
      expect(pageText).not.toContain('MISSING');
      // no empty value cells, ever
      const emptyCells = await page.locator('.sg-table td').evaluateAll(
        (tds) => tds.filter((td) => td.textContent.trim() === '').length
      );
      expect(emptyCells).toBe(0);
    });

    test('FORMS: input pair joins on desktop, stacks + restores border on mobile', async ({ page }) => {
      await page.goto(`/?aifds_styleguide=1&item=form-composition&theme=${brand}`);
      const pairField = page.locator('[data-test="pair-light"] .form-control-wrapper');
      await expect(pairField).toHaveCSS('border-right-width', '0px'); // joined
      await page.setViewportSize({ width: 375, height: 800 });
      await expect(pairField).toHaveCSS('border-right-width', '2px'); // restored
      const dir = await page.locator('[data-test="pair-light"]').evaluate((el) => getComputedStyle(el).flexDirection);
      expect(dir).toBe('column'); // stacked
    });

    test('status-error brightens on dark surfaces (destructive/error text stays readable)', async ({ page }) => {
      // audit 2026-07-03: #b42318 on black failed contrast; dark scopes remap
      // status-error → error-bright (the palette had the bright twin unused)
      const err = (s) => cell(page, s).evaluate((el) => getComputedStyle(el).getPropertyValue('--status-error').trim());
      expect(await err('light-1')).toBe('#b42318');
      for (const s of ['dark-1', 'dark-2', 'dark-3']) {
        expect(await err(s), `status-error on ${s}`).toBe('#fd664d');
      }
    });

    test('tertiary hover never equals its surface (raised wraps to primary on *-3)', async ({ page }) => {
      for (const s of ['light-1', 'light-2', 'light-3', 'dark-1', 'dark-2', 'dark-3']) {
        const btn = cell(page, s).locator('.btn--tertiary');
        const { hoverBg, surfaceBg } = await btn.evaluate((el) => {
          const cs = getComputedStyle(el);
          return {
            hoverBg: cs.getPropertyValue('--button-tertiary-bg-hover').trim(),
            surfaceBg: cs.getPropertyValue('--bg').trim(),
          };
        });
        expect(hoverBg, `${s}: tertiary hover must differ from surface`).not.toBe(surfaceBg);
      }
    });
  });
}
