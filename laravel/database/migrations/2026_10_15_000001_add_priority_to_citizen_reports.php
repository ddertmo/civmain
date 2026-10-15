<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('citizen_reports', 'priority')) {
            Schema::table('citizen_reports', function (Blueprint $table) {
                $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium')->after('severity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('citizen_reports', 'priority')) {
            Schema::table('citizen_reports', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};