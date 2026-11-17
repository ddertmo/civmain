<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secondary indexes for the queries every page runs.
 *
 * - citizen_reports: status filters/grouping (lists, summaries, analytics),
 *   the public community feed (is_public + status), date ranges and sorting
 *   (created_at), per-category / per-barangay analytics.
 * - citizen_notifications: the citizen inbox (citizen_id + is_read) and the
 *   admin bell, which counts by created_at every 30 s.
 * - otp_codes: findValid() looks up phone + code.
 *
 * Each index is skipped if it already exists, because installs that
 * imported civilwatch_FINAL.sql run migrate on top of an existing schema.
 */
return new class extends Migration
{
    private const INDEXES = [
        'citizen_reports' => [
            'cr_status_idx'          => ['status'],
            'cr_public_status_idx'   => ['is_public', 'status'],
            'cr_created_at_idx'      => ['created_at'],
            'cr_category_idx'        => ['category'],
            'cr_barangay_idx'        => ['barangay'],
        ],
        'citizen_notifications' => [
            'cn_citizen_read_idx'    => ['citizen_id', 'is_read'],
            'cn_created_at_idx'      => ['created_at'],
        ],
        'otp_codes' => [
            'otp_phone_code_idx'     => ['phone', 'code'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    continue;
                }
                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
