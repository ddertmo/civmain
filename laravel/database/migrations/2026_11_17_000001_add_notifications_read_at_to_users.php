<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-staff read marker for the admin notification bell.
 *
 * The bell shows the shared citizen_notifications feed. Its read state used to
 * be the citizens' own `is_read` flag, so "mark all read" in the admin panel
 * marked every citizen's inbox read (and "clear" deleted it). Each staff user
 * now keeps their own "read up to" timestamp instead.
 *
 * hasColumn guard: installs that imported civilwatch_FINAL.sql run migrate on
 * top of an existing schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'notifications_read_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('notifications_read_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'notifications_read_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notifications_read_at');
            });
        }
    }
};
