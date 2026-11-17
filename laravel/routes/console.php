<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Tokens now expire (config/sanctum.php); remove expired rows daily.
// Needs the Laravel scheduler running (`php artisan schedule:work`, or a
// Windows Task Scheduler entry for `php artisan schedule:run` every minute).
Schedule::command('sanctum:prune-expired --hours=24')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
