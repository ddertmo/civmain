<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the Low audit fixes (docs/changes 079).
 *
 *   php artisan test --filter=LowFixesTest
 */
class LowFixesTest extends TestCase
{
    use RefreshDatabase;

    private function staffHeaders(string $role): array
    {
        $user = User::create([
            'name'          => 'Test ' . $role,
            'email'         => $role . '@example.test',
            'password_hash' => Hash::make('correct-horse'),
            'role'          => $role,
            'is_active'     => true,
        ]);

        return [
            'Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken,
            'Accept'        => 'application/json',
        ];
    }

    // L2 — only the super admin can list staff accounts
    public function test_office_staff_cannot_list_staff_accounts(): void
    {
        $ceo = $this->staffHeaders('ceo');

        $this->getJson('/api/users', $ceo)->assertForbidden();
        $this->getJson('/api/admin/users', $ceo)->assertForbidden();
        $this->getJson('/api/users', $this->staffHeaders('super_admin'))->assertOk();
    }

    // L3 — the catch-all HTML route can't leave public/
    public function test_catch_all_route_rejects_path_traversal(): void
    {
        $this->get('/../resources/views/welcome.blade.html')->assertNotFound();
        $this->get('/%2e%2e/%2e%2e/storage/x.html')->assertNotFound();
        $this->get('/index.html')->assertOk();
    }

    // L6 — OTP codes are stored as keyed hashes, still verifiable
    public function test_otp_codes_are_not_stored_in_plain_text(): void
    {
        $otp = OtpCode::issue('639171234000', '482916');

        $this->assertNotSame('482916', $otp->fresh()->code);
        $this->assertSame(64, strlen($otp->fresh()->code));
        $this->assertNotNull(OtpCode::findValid('639171234000', '482916'));
        $this->assertNull(OtpCode::findValid('639171234000', '000000'));
    }

    // L7 — HTML responses carry the security headers
    public function test_html_pages_send_security_headers(): void
    {
        $response = $this->get('/index.html')->assertOk();

        $this->assertStringContainsString("frame-ancestors 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    // L11 — a non-numeric id is a 404, not a 500 TypeError
    public function test_non_numeric_ids_return_404(): void
    {
        $admin = $this->staffHeaders('super_admin');

        $this->postJson('/api/admin/citizen-reports/abc/validate', [], $admin)->assertNotFound();
        $this->putJson('/api/users/abc', [], $admin)->assertNotFound();
    }
}
