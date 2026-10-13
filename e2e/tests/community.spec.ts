import { test, expect } from '@playwright/test';

test.describe('public surface (no login)', () => {
  test('API ping reports the service is alive', async ({ request }) => {
    const res = await request.get('/api/ping');
    expect(res.ok(), `ping failed: HTTP ${res.status()}`).toBeTruthy();
    const json = await res.json();
    expect(json.message).toContain('CivilWatch API');
  });

  test('public community feed responds with JSON', async ({ request }) => {
    const res = await request.get('/api/mobile/reports/community');
    expect(res.ok(), `community feed failed: HTTP ${res.status()}`).toBeTruthy();
    const json = await res.json();
    expect(json.success).toBe(true);
    expect(Array.isArray(json.data)).toBe(true);
  });

  test('a guest is redirected away from the admin dashboard', async ({ page }) => {
    await page.goto('/dashboard.html');
    await page.waitForURL('**/index.html');
    await expect(page.locator('#loginForm')).toBeVisible();
  });
});
