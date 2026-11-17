<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OtpCode extends Model
{
    protected $table = 'otp_codes';

    public $timestamps = false; // only has created_at

    protected $fillable = [
        'phone',
        'code',
        'is_used',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used'    => 'boolean',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** True if this OTP is still valid (not used, not expired) */
    public function isValid(): bool
    {
        return ! $this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Keyed hash of an OTP. Codes are never stored in plain text, so a
     * database dump or a read-only SQL leak doesn't hand out live login
     * codes. HMAC with APP_KEY (and the phone) so the 10^6 possible codes
     * can't be precomputed without the key.
     */
    public static function hashCode(string $phone, string $code): string
    {
        return hash_hmac('sha256', $phone . '|' . $code, (string) config('app.key'));
    }

    /**
     * Store a new OTP for a phone number.
     * Invalidates any previous unused OTPs for the same number.
     * Runs in a transaction so parallel send-otp calls can't leave two live
     * codes for one number.
     */
    public static function issue(string $phone, string $code): static
    {
        return DB::transaction(function () use ($phone, $code) {
            static::where('phone', $phone)
                  ->where('is_used', false)
                  ->update(['is_used' => true]);

            return static::create([
                'phone'      => $phone,
                'code'       => static::hashCode($phone, $code),
                'is_used'    => false,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Find a valid (unused + unexpired) OTP for a phone number.
     */
    public static function findValid(string $phone, string $code): ?static
    {
        return static::where('phone', $phone)
                     ->where('code', static::hashCode($phone, $code))
                     ->where('is_used', false)
                     ->where('expires_at', '>', now())
                     ->latest('created_at')
                     ->first();
    }
}
