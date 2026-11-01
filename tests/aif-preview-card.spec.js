const { test, expect } = require('@playwright/test');

/**
 * Preview card — prose-leak guard.
 *
 * Guards the 2026-07-16 root fix: theme.css's .content-section h2/h3 prose
 * rules rode a LAYOUT class at (0,1,1) and out-gunned every component
 * headline at single-class (0,1,0) — the DS card headline's 8px bottom
 * margin rendered as the prose 32px on /clanky/ and the homepage grids.
 * The rules are now guarded with :not([class]) so they only match unclassed
 * WYSIWYG headings. If a prose-context rule ever leaks into a component
 * headline again, these fail.
 */

const CARD_HEADLINE_GAP = '8px'; // --spacing-8 per the DS .preview-card__headline spec

for (const path of ['/clanky/', '/']) {
  test(`preview-card headline keeps the DS 8px gap on ${path}`, async ({ page }) => {
    await page.goto(path);
    const headline = page.locator('.preview-card .preview-card__headline').first();
    await headline.waitFor();
    await expect(headline).toHaveCSS('margin-bottom', CARD_HEADLINE_GAP);
    await expect(headline).toHaveCSS('margin-top', '0px');
  });
}

test('prose rhythm in content sections is untouched by the guard', async ({ page }) => {
  await page.goto('/');
  // Unclassed WYSIWYG headings must still get the theme's prose margins.
  const prose = page.locator('.content-section h2:not([class])').first();
  await prose.waitFor();
  await expect(prose).toHaveCSS('margin-bottom', '32px'); // --spacing-32
});
