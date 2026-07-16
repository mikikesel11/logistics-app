import { defineConfig, devices } from '@playwright/test';

/**
 * E2E config. Assumes the Laravel API is already running at VITE_API_PROXY
 * (default http://localhost:8000) with a seeded admin user. Playwright starts
 * the Vite dev server itself and drives the SPA at http://localhost:5173.
 *
 *   cd api && php artisan migrate:fresh --seed && php artisan serve   # terminal 1
 *   cd web && npm run test:e2e                                        # terminal 2
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:5173',
    trace: 'on-first-retry',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:5173',
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
  },
});
