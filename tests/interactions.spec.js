/**
 * L4 — BEHAVIOR CONTRACTS (the "right-looking wrong thing" detector).
 * PLAYWRIGHT-AGENTIC §5 L4, pilot built 2026-07-06 (audit Phase D).
 *
 * NOT a sweep — deliberate per-component contracts. §10 law 3: these
 * assertions and the component doc's behavioral prose are THE SAME
 * SENTENCES; if they diverge, one of them is lying.
 *
 * Pilot: MODAL + ACCORDION (both engines run live on their specimens).
 * The header's burger/dropdown and the sticky bar's rung already carry
 * behavior assertions in tokens.spec.js — L4 grows one component per
 * distill from here.
 */

const { test, expect } = require('@playwright/test');

const BRANDS = ['aifounders', 'aiguild'];

for (const brand of BRANDS) {
  // ── MODAL — contract from docs/components/modal.md ──────────────────────
  // "data-modal-open opens by id · the page locks behind it · ESC closes ·
  //  focus moves into the modal and RESTORES to the opener on close ·
  //  the close button always exists · taller-than-viewport forms scroll
  //  INSIDE the container"
  test(`L4 modal contract — ${brand}`, async ({ page }) => {
    await page.goto(`/?aifds_styleguide=1&item=modal&theme=${brand}`);
    const opener = page.locator('[data-modal-open="sg-modal-live"]').first();

    // opens by id + aria flips + scroll lock
    await opener.click();
    const modal = page.locator('#sg-modal-live');
    await expect(modal).toHaveAttribute('aria-hidden', 'false');
    await expect(page.locator('body')).toHaveClass(/modal-open/);

    // the close button ALWAYS exists (the operator's no-closeless-modal law)
    await expect(modal.locator('.modal__close')).toBeVisible();

    // focus moves INTO the modal (engine focuses the first field, delayed)
    await page.waitForFunction(() => {
      const m = document.querySelector('#sg-modal-live');
      return m && m.contains(document.activeElement);
    });

    // ESC closes + focus RESTORES to the opener + lock releases
    await page.keyboard.press('Escape');
    await expect(modal).toHaveAttribute('aria-hidden', 'true');
    await expect(page.locator('body')).not.toHaveClass(/modal-open/);
    await page.waitForFunction(() => {
      const el = document.activeElement;
      return el && el.matches('[data-modal-open="sg-modal-live"]');
    });

    // the long form scrolls INSIDE the container (page stays locked)
    await page.locator('[data-modal-open="sg-modal-long"]').click();
    const long = page.locator('#sg-modal-long .modal__container');
    const scrolls = await long.evaluate((el) => el.scrollHeight > el.clientHeight);
    expect(scrolls, 'taller-than-viewport modal must scroll internally').toBe(true);
    await page.keyboard.press('Escape');
  });

  // ── ACCORDION — contract from docs/components/accordion.md ──────────────
  // "click toggles; default mode = INDEPENDENT (open items stay open);
  //  [data-accordion=exclusive] = opening one CLOSES its open sibling;
  //  aria-expanded mirrors state; height animates to real content height
  //  and clears to auto"
  test(`L4 accordion contract — ${brand}`, async ({ page }) => {
    await page.goto(`/?aifds_styleguide=1&item=accordion&theme=${brand}`);

    // INDEPENDENT (default): two opened items BOTH stay open
    const independent = page.locator('.sg-content > .accordion, .sg-content div:not([data-accordion]) > .accordion');
    const first = page.locator('.accordion').first();
    const second = page.locator('.accordion').nth(1);
    await first.locator('.accordion__header').click();
    await expect(first).toHaveClass(/accordion--open/);
    await expect(first.locator('.accordion__header')).toHaveAttribute('aria-expanded', 'true');
    await second.locator('.accordion__header').click();
    await expect(second).toHaveClass(/accordion--open/);
    await expect(first, 'independent mode: the first stays open').toHaveClass(/accordion--open/);

    // toggle closes + aria mirrors
    await first.locator('.accordion__header').click();
    await expect(first).not.toHaveClass(/accordion--open/);
    await expect(first.locator('.accordion__header')).toHaveAttribute('aria-expanded', 'false');

    // EXCLUSIVE: opening one closes the open sibling
    const group = page.locator('[data-accordion="exclusive"]');
    const exA = group.locator('.accordion').first();
    const exB = group.locator('.accordion').nth(1);
    await exA.locator('.accordion__header').click();
    await expect(exA).toHaveClass(/accordion--open/);
    await exB.locator('.accordion__header').click();
    await expect(exB).toHaveClass(/accordion--open/);
    await expect(exA, 'exclusive mode: the sibling closed').not.toHaveClass(/accordion--open/);

    // height clears to auto after the transition (content can reflow)
    await page.waitForFunction(() => {
      const open = document.querySelector('[data-accordion="exclusive"] .accordion--open .accordion__content');
      return open && open.style.height === 'auto';
    });
  });
}
