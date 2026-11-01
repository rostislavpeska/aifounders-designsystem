const { test, expect } = require('@playwright/test');

/**
 * TEMPLATES group — the zero-hallucination gate (operator law 2026-07-16:
 * "basically all pages are 100% hallucinated" — never again).
 *
 * Each Templates item must embed the REAL page of this install in a
 * same-origin iframe. These specs assert, INSIDE the embedded document, the
 * signature markup only the genuine template produces — a hand-composed
 * replica cannot pass, because the signatures are theme-owned composition
 * (hero-aif, aif-dark-section, article-hero, archive-header, aif-landing,
 * aif-login) that the styleguide shell does not even load CSS for.
 *
 * Plus the brand toggle: aiguild must re-stamp data-theme INSIDE the embed
 * (real composition, AIG tokens — the Stage-3 preview).
 */

const ITEMS = {
  'template-homepage': ['section.hero-aif.section-brand', '.aif-dark-section', '.info-bar', '.articles-grid', '.newsletter-cta'],
  'template-page':     ['main.content', '.content-section.section-light'],
  'template-article':  ['body.single-post', '.article-hero', '.article-layout'],
  'template-archive':  ['.archive-header', '.articles-grid--archive', '.archive-pagination'],
  'template-landing':  ['body.aif-landing', 'main.aif-landing__main'],
  'template-auth':     ['.nav-tabs', '.aif-login'],
};

for (const [slug, signatures] of Object.entries(ITEMS)) {
  test(`${slug} embeds the REAL page (signature markup present)`, async ({ page }) => {
    await page.goto(`/?aifds_styleguide=1&item=${slug}&theme=aifounders`);
    const iframe = page.locator('iframe.sgt-frame');
    await expect(iframe, 'showcase must be an embed, not composed markup').toHaveCount(1);
    const frame = page.frameLocator('iframe.sgt-frame');
    for (const sig of signatures) {
      await expect(frame.locator(sig).first(), `real-template signature ${sig}`).toBeAttached({ timeout: 15000 });
    }
    // The strip must disclose the source URL (operator can open it 1:1).
    await expect(page.locator('.sgt-strip a').first()).toHaveAttribute('href', /https?:\/\//);
  });
}

test('brand toggle re-stamps data-theme inside the embedded real page', async ({ page }) => {
  await page.goto('/?aifds_styleguide=1&item=template-homepage&theme=aiguild');
  const frame = page.frameLocator('iframe.sgt-frame');
  await expect(frame.locator('section.hero-aif').first()).toBeAttached({ timeout: 15000 });
  await expect
    .poll(async () => page.locator('iframe.sgt-frame').evaluate((f) => f.contentDocument.documentElement.getAttribute('data-theme')), { timeout: 20000 })
    .toBe('aiguild');
  // And the tokens actually flipped: the brand role resolves to AIG yellow.
  await expect
    .poll(async () =>
      page.locator('iframe.sgt-frame').evaluate((f) =>
        getComputedStyle(f.contentDocument.querySelector('section.hero-aif')).backgroundColor
      ), { timeout: 20000 }
    )
    .toBe('rgb(245, 196, 0)'); // --brand aiguild #f5c400
});
