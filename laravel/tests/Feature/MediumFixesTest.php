<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\CitizenReport;
use App\Models\GovernmentOffice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for the Medium audit fixes (docs/changes 078).
 *
 * Note: on the SQLite test database the citizen_reports.status column only
 * allows the original five statuses (the MySQL-only migrations that add
 * 'Approved'/'Rejected' are skipped), so these tests stay inside
 * Submitted / Pending Validation / Assigned to Office / In Progress / Resolved.
 *
 *   php artisan test --filter=MediumFixesTest
 */
class MediumFixesTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role, array $extra = []): User
    {
        return User::create($extra + [
            'name'          => 'Test ' . $role,
            'email'         => $role . '@example.test',
            'password_hash' => Hash::make('correct-horse'),
            'role'          => $role,
            'is_active'     => true,
        ]);
    }

    private function office(string $abbr): GovernmentOffice
    {
        return GovernmentOffice::create(['name' => $abbr . ' Office', 'abbreviation' => $abbr, 'handles' => 'Both']);
    }

    private function report(?GovernmentOffice $office, string $status): CitizenReport
    {
        $citizen = Citizen::firstOrCreate(['phone' => '639170000001'], [
            'full_name' => 'Juan Dela Cruz',
            'barangay'  => 'Aplaya',
            'pin_hash'  => Hash::make('123456'),
        ]);

        return CitizenReport::createWithReferenceNumber([
            'citizen_id'         => $citizen->id,
            'category'           => 'Infrastructure',
            'concern'            => 'Pothole',
            'barangay'           => 'Aplaya',
            'status'             => $status,
            'assigned_office_id' => $office?->id,
            'is_public'          => true,
        ]);
    }

    private function bearer(User|Citizen $who): array
    {
        return [
            'Authorization' => 'Bearer ' . $who->createToken('t')->plainTextToken,
            'Accept'        => 'application/json',
        ];
    }

    // ── M1: office staff only see their own office's reports ────────────

    public function test_office_staff_cannot_open_another_offices_report(): void
    {
        $cenroReport = $this->report($this->office('CENRO'), CitizenReport::STATUS_ASSIGNED);
        $ceoReport   = $this->report($this->office('CEO'), CitizenReport::STATUS_ASSIGNED);
        $headers     = $this->bearer($this->staff('ceo'));

        $this->getJson("/api/admin/citizen-reports/{$cenroReport->id}", $headers)->assertNotFound();
        $this->getJson("/api/admin/citizen-reports/{$cenroReport->reference_number}", $headers)->assertNotFound();
        $this->getJson("/api/admin/citizen-reports/{$ceoReport->id}", $headers)->assertOk();
    }

    public function test_office_summary_and_analytics_are_scoped(): void
    {
        $this->report($this->office('CENRO'), CitizenReport::STATUS_ASSIGNED);
        $this->report($this->office('CEO'), CitizenReport::STATUS_IN_PROGRESS);
        $this->report(null, CitizenReport::STATUS_PENDING_VALIDATION);

        $ceo = $this->bearer($this->staff('ceo'));
        $this->getJson('/api/admin/citizen-reports/summary', $ceo)
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.in_progress', 1);

        $admin = $this->bearer($this->staff('super_admin'));
        $this->getJson('/api/analytics', $admin)
            ->assertOk()
            ->assertJsonPath('data.summary.total_reports', 3)
            ->assertJsonPath('data.status_distribution.labels', CitizenReport::STATUS_ORDER);
    }

    // ── M2: deactivated accounts lose access immediately ───────────────

    public function test_existing_token_stops_working_after_deactivation(): void
    {
        $ceo     = $this->staff('ceo');
        $headers = $this->bearer($ceo);
        $this->getJson('/api/user', $headers)->assertOk();

        $ceo->update(['is_active' => false]);

        $this->getJson('/api/user', $headers)->assertForbidden();
        $this->assertSame(0, $ceo->tokens()->count(), 'the token should be revoked');
    }

    public function test_deactivated_citizen_token_is_refused(): void
    {
        $citizen = Citizen::create([
            'full_name' => 'Maria Santos', 'phone' => '639170000009',
            'barangay' => 'Aplaya', 'pin_hash' => Hash::make('123456'),
        ]);
        $headers = $this->bearer($citizen);
        $citizen->update(['is_active' => false]);

        $this->getJson('/api/mobile/reports', $headers)->assertForbidden();
    }

    public function test_tokens_expire(): void
    {
        $this->assertNotNull(config('sanctum.expiration'), 'Sanctum tokens must expire');
    }

    // ── M3: workflow transitions are enforced ───────────────────────────

    public function test_resolved_report_cannot_be_moved_back(): void
    {
        $office = $this->office('CEO');
        $report = $this->report($office, CitizenReport::STATUS_RESOLVED);
        $admin  = $this->bearer($this->staff('super_admin'));

        $this->postJson("/api/admin/citizen-reports/{$report->id}/status", ['status' => 'Submitted'], $admin)
            ->assertStatus(422);
        $this->assertSame(CitizenReport::STATUS_RESOLVED, $report->fresh()->status);
    }

    public function test_office_can_progress_its_own_report(): void
    {
        $office = $this->office('CEO');
        $report = $this->report($office, CitizenReport::STATUS_ASSIGNED);
        $ceo    = $this->bearer($this->staff('ceo'));

        $this->postJson("/api/admin/citizen-reports/{$report->id}/status", ['status' => 'In Progress'], $ceo)->assertOk();
        $this->postJson("/api/admin/citizen-reports/{$report->id}/status", ['status' => 'Resolved'], $ceo)->assertOk();

        $fresh = $report->fresh();
        $this->assertSame(CitizenReport::STATUS_RESOLVED, $fresh->status);
        $this->assertNotNull($fresh->resolved_at);
        // One activity per real transition (no duplicates).
        $this->assertSame(2, $fresh->activities()->count());
    }

    public function test_cannot_mark_assigned_without_an_office(): void
    {
        $report = $this->report(null, CitizenReport::STATUS_PENDING_VALIDATION);
        $admin  = $this->bearer($this->staff('super_admin'));

        $this->postJson("/api/admin/citizen-reports/{$report->id}/status", ['status' => 'Assigned to Office'], $admin)
            ->assertStatus(422);
    }

    // ── M19: reports need an official barangay ──────────────────────────

    public function test_report_with_unknown_barangay_is_rejected(): void
    {
        $citizen = Citizen::create([
            'full_name' => 'Pedro Penduko', 'phone' => '639170000002',
            'barangay' => 'Aplaya', 'pin_hash' => Hash::make('123456'),
        ]);

        $this->postJson('/api/mobile/reports', [
            'category' => 'Infrastructure', 'concern' => 'Pothole', 'barangay' => 'Unknown Barangay',
        ], $this->bearer($citizen))->assertStatus(422);
    }
}
