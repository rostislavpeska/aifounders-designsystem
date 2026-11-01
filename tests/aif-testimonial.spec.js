const { test, expect } = require('@playwright/test');

/**
 * Testimonial ([testimonial] shortcode → DS reference-card).
 *
 * Guards the 2026-07-16 root fix: component shortcodes format their copy with
 * aif_component_content() (core wpautop/wptexturize only) instead of
 * apply_filters('the_content') — the full post pipeline let AddToAny inject
 * its share row INSIDE the card. If share icons ever reappear inside a
 * testimonial, this fails.
 */

test.describe('Testimonial card', () => {
  test('renders as a reference-card with NO content bolt-ons inside', async ({ page }) => {
    await page.goto('/newsletter/'); // CZ newsletter LP carries a live testimonial locally
    const card = page.locator('.shortcode-testimonial');
    await expect(card).toHaveCount(1);
    await expect(card.locator('.reference-card__name')).toBeVisible();

    // The bug: AddToAny (or any the_content bolt-on) injected inside the card.
    await expect(card.locator('.a2a_kit, .addtoany_share_save_container, [class*="addtoany"]')).toHaveCount(0);
    // And the raw shortcode must never leak as text.
    await expect(page.locator('main')).not.toContainText('[testimonial');
  });
});
