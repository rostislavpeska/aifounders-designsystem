const { test, expect } = require('@playwright/test');

/**
 * Blue Protocol card ([protocol] shortcode) on /en/about/.
 *
 * Guards the 2026-07-16 fix: the card is a DS `.section-deep` surface, so every
 * colour resolves from surface roles (--bg/--text/--link/--link-hover/--bullet)
 * — no per-element colour, no !important, no cascade hacks in the theme. This
 * spec asserts the computed result so it can't silently regress.
 */

const PAPER = 'rgb(246, 253, 255)';       // --paper (#f6fdff) → primary-inverse "white" on the AIF surface
const DEEP = 'rgb(0, 103, 177)';          // --deep   → #0067b1 card fill
const SUPPORT_STRONG = 'rgb(100, 213, 255)'; // --support-strong → link

const css = (loc, props, pseudo = null) =>
  loc.evaluate((el, { props, pseudo }) => {
    const s = getComputedStyle(el, pseudo);
    return Object.fromEntries(props.map((p) => [p, s[p]]));
  }, { props, pseudo });

test.describe('Blue Protocol card', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/en/about/');
    await expect(page.locator('.protocol-card')).toBeVisible();
  });

  test('fill is the deep-blue surface (--deep)', async ({ page }) => {
    const { backgroundColor } = await css(page.locator('.protocol-card'), ['backgroundColor']);
    expect(backgroundColor).toBe(DEEP);
  });

  test('name = Lazzer / 36px / 900, white', async ({ page }) => {
    const s = await css(page.locator('.protocol-card__name'), ['fontFamily', 'fontSize', 'fontWeight', 'color']);
    expect(s.fontFamily).toContain('Lazzer');
    expect(s.fontSize).toBe('36px');
    expect(s.fontWeight).toBe('900');
    expect(s.color).toBe(PAPER);
  });

  test('content <p> and <li> render the SAME voice — Space Grotesk / 500 / white', async ({ page }) => {
    // Content-dependent: prod's card copy may carry only a <ul> (DB content, not code).
    test.skip(await page.locator('.protocol-card__content p').count() === 0,
      'no <p> in this environment\'s card copy');
    const props = ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color'];
    const p = await css(page.locator('.protocol-card__content p').first(), props);
    const li = await css(page.locator('.protocol-card__content li').first(), props);
    expect(p).toEqual(li);                    // the p!=li bug the operator flagged
    expect(li.fontFamily).toContain('Space Grotesk');
    expect(li.fontSize).toBe('18px');
    expect(li.fontWeight).toBe('500');
    expect(li.color).toBe(PAPER);             // all ink is primary-inverse (white)
  });

  test('link is support-blue and transforms to white on hover', async ({ page }) => {
    // Content-dependent: prod's card copy may carry no link (DB content, not code).
    test.skip(await page.locator('.protocol-card__content a').count() === 0,
      'no <a> in this environment\'s card copy');
    const a = page.locator('.protocol-card__content a').first();
    expect((await css(a, ['color'])).color).toBe(SUPPORT_STRONG);
    await a.hover();
    await expect.poll(() => a.evaluate((el) => getComputedStyle(el).color)).toBe(PAPER); // --link-hover
  });

  test('bullet is a 14px white (--bullet) arrow', async ({ page }) => {
    const b = await css(page.locator('.protocol-card__content li').first(), ['backgroundColor', 'width', 'height'], '::before');
    expect(b.backgroundColor).toBe(PAPER);
    expect(b.width).toBe('14px');
    expect(b.height).toBe('14px');
  });

  test('markup has no logo and no icon', async ({ page }) => {
    await expect(page.locator('.protocol-card__logo')).toHaveCount(0);
    await expect(page.locator('.protocol-card__icon')).toHaveCount(0);
  });
});
