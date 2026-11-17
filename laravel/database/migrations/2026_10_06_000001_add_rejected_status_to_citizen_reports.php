<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * citizen_reports.status is a MySQL ENUM. Adding STATUS_REJECTED in PHP
     * alone is not enough — MySQL refuses values outside the column's list,
     * so every reject would fail. This widens the column to accept 'Rejected'.
     */
    public function up(): void
    {
        // MODIFY … ENUM is MySQL/MariaDB-only. Skip on other drivers (e.g.
        // the SQLite in-memory test database) so `php artisan test` can migrate.
        if (! Schema::hasTable('citizen_reports')
            || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
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

    public function down(): void
    {
        // MODIFY … ENUM is MySQL/MariaDB-only. Skip on other drivers (e.g.
        // the SQLite in-memory test database) so `php artisan test` can migrate.
        if (! Schema::hasTable('citizen_reports')
            || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE citizen_reports
            MODIFY status ENUM(
                'Submitted',
                'Pending Validation',
                'Assigned to Office',
                'In Progress',
                'Resolved'
            ) NOT NULL DEFAULT 'Submitted'
        SQL);
    }
};
