import { APIRequestContext, Page, expect } from '@playwright/test';

export const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL || 'admin@civilwatch.ph';
export const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || 'Admin@2026!';

// Sign in through the HTML/JS admin at "/" (index.html). It stores the Sanctum
// token in localStorage and redirects super_admin to dashboard.html.
export async function loginWebAdmin(page: Page): Promise<void> {
  await page.goto('/');
  await page.locator('#email').fill(ADMIN_EMAIL);
  await page.locator('#password').fill(ADMIN_PASSWORD);
  await page.locator('#loginBtn').click();
  await page.waitForURL('**/dashboard.html', { timeout: 20_000 });
}

// Staff login through the API — returns the raw login payload (token + user).
export async function apiLogin(request: APIRequestContext): Promise<{
  token: string;
  user: { role: string; name: string; email: string };
}> {
  const res = await request.post('/api/login', {
    data: { email: ADMIN_EMAIL, password: ADMIN_PASSWORD },
  });
  expect(res.ok(), `admin API login failed: HTTP ${res.status()}`).toBeTruthy();
  const json = await res.json();
  expect(json?.data?.token, 'login response had no data.token').toBeTruthy();
  return json.data;
}

// A phone number that is very unlikely to collide with a previous run.
export function uniquePhone(): string {
  return '+639' + String(Date.now()).slice(-9);
}
