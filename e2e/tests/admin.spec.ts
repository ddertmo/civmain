import { test, expect } from '@playwright/test';
import { ADMIN_EMAIL, ADMIN_PASSWORD, loginWebAdmin } from './helpers';

test.describe('HTML/JS web admin (:8000)', () => {
  test('super admin can sign in and land on the dashboard', async ({ page }) => {
    await loginWebAdmin(page);
    await expect(page.locator('.page-title')).toContainText('Welcome back');
    await expect(page.locator('#statCards')).toBeVisible();
  });

  test('dashboard renders the recent-reports table and map', async ({ page }) => {
    await loginWebAdmin(page);
    await expect(page.locator('#recentReportsBody')).toBeVisible();
    await expect(page.locator('#dashMap')).toBeVisible();
  });

  test('pending reports page renders', async ({ page }) => {
    await loginWebAdmin(page);
    await page.goto('/pending-reports.html');
    await expect(page.locator('body')).toContainText(/pending/i);
  });
});

test.describe('Blade admin panel (/admin/login)', () => {
  test('admin can sign in with a server-side session', async ({ page }) => {
    await page.goto('/admin/login');
    await page.locator('input[name="email"]').fill(ADMIN_EMAIL);
    await page.locator('input[name="password"]').fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: /sign in/i }).click();
    await expect(page).toHaveURL(/\/admin\/(dashboard|citizen-reports)/);
  });
});
