<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\CitizenNotification;
use App\Models\CitizenReport;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the security audit fixes (docs/changes 076, 077).
 *
 * Runs on the SQLite in-memory database from phpunit.xml (APP_ENV=testing,
 * so the local-debug shortcuts — OTP echo, ticketless registration — are OFF,
 * exactly as in production).
 *
 *   php artisan test --filter=SecurityRegressionTest
 */
class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '639171112222';

    // ── helpers ─────────────────────────────────────────────────────────

    private function staff(string $role = 'super_admin'): User
    {
        return User::create([
            'name'          => 'Test ' . $role,
            'email'         => $role . '@example.test',
            'password_hash' => Hash::make('correct-horse'),
            'role'          => $role,
            'is_active'     => true,
        ]);
    }

    private function citizen(string $phone = self::PHONE): Citizen
    {
        return Citizen::create([
            'full_name' => 'Juan Dela Cruz',
            'phone'     => $phone,
            'barangay'  => 'Aplaya',
            'pin_hash'  => Hash::make('123456'),
            'is_active' => true,
        ]);
    }

    private function bearer(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'];
    }

    private function registerPayload(array $extra = []): array
    {
        return $extra + [
            'fullName' => 'Maria Santos',
            'barangay' => 'Aplaya',
            'pin'      => '654321',
            'phone'    => '0' . substr(self::PHONE, 2),
        ];
    }

    // ── C1: citizen tokens must not work on staff routes ─────────────────

    public function test_citizen_token_is_rejected_on_staff_routes(): void
    {
        $token = $this->citizen()->createToken('mobile')->plainTextToken;

        foreach (['/api/admin/citizen-reports', '/api/users', '/api/analytics/summary', '/api/notifications'] as $url) {
            $status = $this->getJson($url, $this->bearer($token))->status();
            $this->assertContains($status, [401, 403], "{$url} accepted a citizen token (HTTP {$status})");
        }
    }

    public function test_staff_token_still_works_on_staff_routes(): void
    {
        $token = $this->staff()->createToken('auth_token')->plainTextToken;

        $this->getJson('/api/admin/citizen-reports', $this->bearer($token))->assertOk();
    }

    // ── H1: registration needs a verified-phone ticket ───────────────────

    public function test_register_without_ticket_is_refused(): void
    {
        $this->postJson('/api/mobile/auth/register', $this->registerPayload())
            ->assertStatus(422)
            ->assertJsonPath('message', 'Please verify your mobile number first.');

        $this->assertDatabaseMissing('citizens', ['phone' => self::PHONE]);
    }

    public function test_verified_otp_ticket_allows_registration_once_and_only_for_that_phone(): void
    {
        OtpCode::issue(self::PHONE, '246810');

        $ticket = $this->postJson('/api/mobile/auth/verify-otp', ['phone' => self::PHONE, 'otp' => '246810'])
            ->assertOk()
            ->assertJsonPath('data.isNewUser', true)
            ->json('data.registrationTicket');
        $this->assertIsString($ticket);

        // The ticket is bound to the verified number.
        $this->postJson('/api/mobile/auth/register', $this->registerPayload([
            'phone' => '09179998888', 'registrationTicket' => $ticket,
        ]))->assertStatus(422);

        $this->postJson('/api/mobile/auth/register', $this->registerPayload(['registrationTicket' => $ticket]))
            ->assertCreated();

        $this->assertDatabaseHas('citizens', ['phone' => self::PHONE]);
    }

    public function test_otp_can_only_be_redeemed_once(): void
    {
        $this->citizen();
        OtpCode::issue(self::PHONE, '135790');

        $this->postJson('/api/mobile/auth/verify-otp', ['phone' => self::PHONE, 'otp' => '135790'])->assertOk();
        $this->postJson('/api/mobile/auth/verify-otp', ['phone' => self::PHONE, 'otp' => '135790'])->assertStatus(422);
    }

    // ── H2: brute-force limits ───────────────────────────────────────────

    public function test_wrong_otps_lock_out_and_burn_the_code(): void
    {
        $this->citizen();
        OtpCode::issue(self::PHONE, '111111');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/mobile/auth/verify-otp', ['phone' => self::PHONE, 'otp' => '999999'])
                ->assertStatus(422);
        }

        // Locked out — even the correct code is refused now.
        $this->postJson('/api/mobile/auth/verify-otp', ['phone' => self::PHONE, 'otp' => '111111'])
            ->assertStatus(429);

        $this->assertSame(0, OtpCode::where('phone', self::PHONE)->where('is_used', false)->count());
    }

    public function test_wrong_pins_lock_out_and_errors_do_not_reveal_registration(): void
    {
        $this->citizen();

        // Same message for an unknown number and a wrong PIN.
        $unknown = $this->postJson('/api/mobile/auth/login', ['phone' => '09170000000', 'pin' => '000000'])
            ->assertStatus(401)->json('message');
        $wrong = $this->postJson('/api/mobile/auth/login', ['phone' => self::PHONE, 'pin' => '000000'])
            ->assertStatus(401)->json('message');
        $this->assertSame($unknown, $wrong);

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/mobile/auth/login', ['phone' => self::PHONE, 'pin' => '000000'])->assertStatus(401);
        }

        $this->postJson('/api/mobile/auth/login', ['phone' => self::PHONE, 'pin' => '123456'])
            ->assertStatus(429);
    }

    public function test_staff_login_locks_out_after_repeated_failures(): void
    {
        $this->staff();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'super_admin@example.test', 'password' => 'nope'])
                ->assertStatus(401);
        }

        $this->postJson('/api/login', ['email' => 'super_admin@example.test', 'password' => 'correct-horse'])
            ->assertStatus(429);
    }

    // ── H3: the admin bell must not touch citizens' inboxes ──────────────

    public function test_staff_mark_all_read_does_not_change_citizen_notifications(): void
    {
        $citizen = $this->citizen();
        $note = CitizenNotification::send($citizen->id, 'Title', 'Message');
        $token = $this->staff('ceo')->createToken('auth_token')->plainTextToken;

        $this->getJson('/api/notifications/unread-count', $this->bearer($token))
            ->assertOk()->assertJsonPath('data.unread_count', 1);

        $this->postJson('/api/notifications/read-all', [], $this->bearer($token))->assertOk();

        $this->getJson('/api/notifications/unread-count', $this->bearer($token))
            ->assertJsonPath('data.unread_count', 0);

        // The citizen still sees it as unread.
        $this->assertFalse($note->fresh()->is_read);
    }

    public function test_staff_cannot_delete_citizen_notifications(): void
    {
        $citizen = $this->citizen();
        $note = CitizenNotification::send($citizen->id, 'Title', 'Message');
        $token = $this->staff()->createToken('auth_token')->plainTextToken;

        $this->deleteJson('/api/notifications', [], $this->bearer($token));
        $this->deleteJson('/api/notifications/' . $note->id, [], $this->bearer($token));

        $this->assertNotNull($note->fresh());
    }

    // ── H5: reference numbers never collide ──────────────────────────────

    public function test_reference_numbers_are_unique_even_after_a_delete(): void
    {
        $token = $this->citizen()->createToken('mobile')->plainTextToken;
        $payload = ['category' => 'Infrastructure', 'concern' => 'Pothole', 'barangay' => 'Aplaya'];

        $first  = $this->postJson('/api/mobile/reports', $payload, $this->bearer($token))->assertCreated();
        $second = $this->postJson('/api/mobile/reports', $payload, $this->bearer($token))->assertCreated();

        // Old scheme (count + 1) reissued the newest number after a delete.
        CitizenReport::whereKey($first->json('data.id'))->delete();
        $third = $this->postJson('/api/mobile/reports', $payload, $this->bearer($token))->assertCreated();

        $refs = CitizenReport::pluck('reference_number')->all();
        $this->assertCount(2, $refs);
        $this->assertCount(2, array_unique($refs));
        foreach ($refs as $ref) {
            $this->assertMatchesRegularExpression('/^CW-\d{4}-\d{5,}$/', $ref);
        }
        $this->assertNotSame($second->json('data.referenceNumber'), $third->json('data.referenceNumber'));
    }
}
