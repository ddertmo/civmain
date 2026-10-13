import { test, expect } from '@playwright/test';
import { uniquePhone } from './helpers';

// The citizen side of CivilWatch is the Flutter app; in the web repo the
// citizen-facing surface is the /api/mobile/* endpoints it calls. These tests
// exercise that contract end to end.
test.describe('citizen-facing mobile API (/api/mobile)', () => {
  test('a citizen can register, log in and submit a report', async ({ request }) => {
    const phone = uniquePhone();
    const pin = '123456';

    const register = await request.post('/api/mobile/auth/register', {
      data: { fullName: 'E2E Playwright', barangay: 'San Jose', pin, phone },
    });
    expect(register.ok(), `register failed: HTTP ${register.status()}`).toBeTruthy();

    const login = await request.post('/api/mobile/auth/login', {
      data: { phone, pin },
    });
    expect(login.ok(), `login failed: HTTP ${login.status()}`).toBeTruthy();
    const loginJson = await login.json();
    const token = loginJson?.data?.token;
    expect(token, 'login response had no data.token').toBeTruthy();

    const report = await request.post('/api/mobile/reports', {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      data: {
        category: 'Infrastructure',
        concern: 'E2E Playwright report',
        description: 'Created by the Playwright E2E suite.',
        barangay: 'San Jose',
        severity: 'Minor',
      },
    });
    expect(report.ok(), `report submit failed: HTTP ${report.status()}`).toBeTruthy();
  });

  test('admin API rejects a write for a non-super-admin', async ({ request }) => {
    const login = await request.post('/api/login', {
      data: {
        email: process.env.E2E_CEO_EMAIL || 'ceo@civilwatch.ph',
        password: process.env.E2E_CEO_PASSWORD || 'Ceo@2026!',
      },
    });
    // If the CEO account is absent in this database, skip rather than fail.
    test.skip(!login.ok(), 'CEO seed account not present');

    const json = await login.json();
    const token = json?.data?.token;
    const write = await request.post('/api/admin/offices', {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      data: { name: 'E2E forbidden office' },
    });
    expect(write.status(), 'CEO write should be forbidden').toBe(403);
  });
});
