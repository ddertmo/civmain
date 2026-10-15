<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            if (! Schema::hasColumn('citizens', 'street')) {
                $table->string('street', 150)->nullable()->after('email');
            }
            if (! Schema::hasColumn('citizens', 'purok')) {
                $table->string('purok', 100)->nullable()->after('street');
            }
            if (! Schema::hasColumn('citizens', 'province')) {
                $table->string('province', 100)->default('Davao del Sur')->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            foreach (['street', 'purok', 'province'] as $column) {
                if (Schema::hasColumn('citizens', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
