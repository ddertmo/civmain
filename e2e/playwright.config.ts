import { defineConfig, devices } from '@playwright/test';

// Where the Laravel app is served. START_ALL.bat / RUN_APP.bat already put it
// on 127.0.0.1:8000; override with E2E_BASE_URL if you serve it elsewhere.
const BASE_URL = process.env.E2E_BASE_URL || 'http://127.0.0.1:8000';

// PHP binary. On the PC, XAMPP ships PHP but does not always put it on PATH,
// so RUN_TESTS.bat sets PHP_BIN=C:\xampp\php\php.exe when it finds it.
const PHP_BIN = process.env.PHP_BIN || 'php';

export default defineConfig({
  testDir: './tests',
  timeout: 45_000,
  expect: { timeout: 7_000 },
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,

  reporter: [
    ['list'],
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
  ],

  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10_000,
    navigationTimeout: 20_000,
  },

  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],

  // Auto-start the backend if it is not already running. When you launched the
  // app through START_ALL.bat / RUN_APP.bat, reuseExistingServer skips this.
  webServer: {
    command: `"${PHP_BIN}" artisan serve --host=127.0.0.1 --port=8000`,
    cwd: '../laravel',
    url: `${BASE_URL}/api/ping`,
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
    stdout: 'pipe',
    stderr: 'pipe',
  },
});
