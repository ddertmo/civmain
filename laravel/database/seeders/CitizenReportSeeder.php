<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CitizenReportSeeder extends Seeder
{
    /**
     * Intentionally empty.
     *
     * Dummy citizen reports have been removed so the admin panel only shows
     * real submissions from the Flutter app.
     *
     * The old `reports:clear-dummy` command was removed: it treated
     * CW-2026-00101…00110 as dummy rows, but those are now numbers that real
     * reports receive, so running it deleted real reports.
     */
    public function run(): void
    {
        $this->command->info('CitizenReportSeeder — skipped (no dummy data).');
    }
}
