<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * citizen_reports.status is a MySQL ENUM. Adding STATUS_APPROVED in PHP
     * alone is not enough — MySQL refuses values outside the column's list,
     * so every approve would fail. This widens the column to accept
     * 'Approved' between 'Pending Validation' and 'Assigned to Office'.
     */
    public function up(): void
    {
        if (! Schema::hasTable('citizen_reports')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE citizen_reports
            MODIFY status ENUM(
                'Submitted',
                'Pending Validation',
                'Approved',
                'Assigned to Office',
                'In Progress',
                'Resolved',
                'Rejected'
            ) NOT NULL DEFAULT 'Submitted'
        SQL);
    }

    public function down(): void
    {
        if (! Schema::hasTable('citizen_reports')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE citizen_reports
            MODIFY status ENUM(
                'Submitted',
                'Pending Validation',
                'Assigned to Office',
                'In Progress',
                'Resolved',
                'Rejected'
            ) NOT NULL DEFAULT 'Submitted'
        SQL);
    }
};