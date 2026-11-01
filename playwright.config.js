/**
 * Automated design-system checks against the LIVE local WordPress stack
 * (AIF stack on :8090 — WP_DEBUG opens the styleguide). Docker must be up.
 * Run: npm run test:tokens
 *
 * (v1 config targeted http-server + components.html — retired with the v1
 * HTML styleguide. Multi-browser/mobile VRT returns at the adoption stage
 * per the rollout plan; token assertions need one engine.)
 */
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: true,
  reporter: [['list']],
  use: {
    // Override for Docker/CI: BASE_URL=http://host.docker.internal:8090 (or the
    // wp service name on the compose network). Default = the local published port.
    baseURL: process.env.BASE_URL || 'http://localhost:8090',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
