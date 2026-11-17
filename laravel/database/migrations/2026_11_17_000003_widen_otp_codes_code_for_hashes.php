<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * otp_codes.code now holds an HMAC-SHA256 hex digest (64 chars) instead of
 * the plain 6-digit code (OtpCode::hashCode). Plain codes still in the table
 * can never match a hash, so they are expired here (they live 5 minutes
 * anyway).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('otp_codes')) {
            return;
        }

        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('code', 64)->change();
        });

        DB::table('otp_codes')->where('is_used', false)->update(['is_used' => true]);
    }

    public function down(): void
    {
        // Hashes don't fit in 10 chars; clear them before shrinking back.
        if (! Schema::hasTable('otp_codes')) {
            return;
        }

        DB::table('otp_codes')->delete();

        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('code', 10)->change();
        });
    }
};
