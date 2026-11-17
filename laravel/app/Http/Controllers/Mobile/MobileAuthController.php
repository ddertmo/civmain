<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\OtpCode;
use App\Services\UniSmsService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    // ── Brute-force limits (per phone number, on top of the per-IP route
    // throttles in routes/api.php). A 6-digit PIN or OTP has only 10^6
    // values, so failures must be capped per target, not just per client.
    private const PIN_MAX_FAILURES   = 5;    // wrong PINs …
    private const PIN_DECAY_SECONDS  = 900;  // … per 15 minutes
    private const OTP_MAX_FAILURES   = 5;    // wrong OTPs, then all codes are burned
    private const OTP_DECAY_SECONDS  = 600;
    private const OTP_SEND_MAX       = 3;    // SMS sends …
    private const OTP_SEND_DECAY     = 600;  // … per 10 minutes (SMS credits cost money)
    private const REG_TICKET_MINUTES = 15;   // verified-phone ticket lifetime

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/login
    // Body: { phone, pin }
    // Direct phone + PIN login — no OTP required for returning users.
    // ─────────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'pin'   => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $normalized = $this->normalizePhone($request->phone);

        $limitKey = 'pin-login:' . $normalized;
        if (RateLimiter::tooManyAttempts($limitKey, self::PIN_MAX_FAILURES)) {
            return $this->tooManyAttempts($limitKey, 'Too many incorrect PIN attempts.');
        }

        // Find citizen by phone. One generic message for "no account" and
        // "wrong PIN", so this endpoint can't be used to test which numbers
        // are registered.
        $citizen = Citizen::where('phone', $normalized)->first();

        if (! $citizen || ! $citizen->pin_hash || ! Hash::check($request->pin, $citizen->pin_hash)) {
            RateLimiter::hit($limitKey, self::PIN_DECAY_SECONDS);

            return response()->json([
                'success' => false,
                'message' => 'Incorrect mobile number or PIN.',
            ], 401);
        }

        RateLimiter::clear($limitKey);

        if (! $citizen->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Revoke old tokens and issue a fresh one
        $citizen->tokens()->delete();
        $token = $citizen->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'token'   => $token,
                'citizen' => $this->citizenResponse($citizen),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/send-otp
    // Body: { phone: "09XXXXXXXXX" }
    // Generates a 6-digit OTP and stores it, then sends it by real SMS via
    // UniSMS (docs/TASKS.md task 1) when UNISMS_SECRET_KEY is configured.
    // With no key the old dev behaviour stays: the code is echoed in the
    // response while APP_DEBUG=true (never in production).
    // ─────────────────────────────────────────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(0|\+63|63)?9\d{9}$/'],
        ]);

        $normalized = $this->normalizePhone($request->phone);

        // Cap SMS sends per number: stops SMS flooding a victim's phone and
        // draining UniSMS credits.
        $sendKey = 'otp-send:' . $normalized;
        if (RateLimiter::tooManyAttempts($sendKey, self::OTP_SEND_MAX)) {
            return $this->tooManyAttempts($sendKey, 'Too many verification codes requested.');
        }
        RateLimiter::hit($sendKey, self::OTP_SEND_DECAY);

        // A fresh code gets a fresh set of verification attempts.
        RateLimiter::clear('otp-verify:' . $normalized);

        // Generate a random 6-digit OTP
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP (invalidates any previous unused ones for this number)
        OtpCode::issue($normalized, $code);

        // UniSMS (docs/TASKS.md task 1): real SMS when configured, honest
        // failure signals, and the debug echo gated behind APP_DEBUG.
        $provider = app(UniSmsService::class);
        $sms      = null;

        if ($provider->configured()) {
            $sms = $provider->send(
                $normalized,
                "CivilWatch: your verification code is {$code}. It expires in 5 minutes.",
                ['purpose' => 'otp', 'app' => 'civilwatch'],
            );

            if (! $sms['ok']) {
                Log::warning('UniSMS OTP send failed', [
                    // Masked: logs are kept and shared more widely than the DB.
                    'phone'  => substr($normalized, 0, 4) . str_repeat('*', max(0, strlen($normalized) - 7)) . substr($normalized, -3),
                    'status' => $sms['status'],
                    'reason' => $sms['error'],
                ]);
            }
        }

        $data = [
            'phone'    => $normalized,
            'sms_sent' => (bool) ($sms['ok'] ?? false),
        ];

        // Provider error details (which can include up to 300 chars of the
        // provider's response body) are for developers only; clients get the
        // generic messages below. The full reason is in the log.
        if ($this->isLocalDebug()) {
            if (! $provider->configured()) {
                $data['sms_error'] = 'UniSMS not configured (UNISMS_SECRET_KEY missing).';
            } elseif (! $sms['ok']) {
                $data['sms_error'] = $sms['error'];
            }
        }

        // Dev autofill (the Flutter OTP screen uses it only when present).
        // Echoing the code hands account takeover to whoever calls send-otp,
        // so it requires BOTH a local environment AND debug — APP_DEBUG alone
        // is not enough, because a server deployed from .env.example has it on.
        $echoOtp = $this->isLocalDebug();

        if ($echoOtp) {
            $data['otp'] = $code;
        }

        // No key + no echo = the user could never receive the code: fail honestly.
        if (! $provider->configured() && ! $echoOtp) {
            return response()->json([
                'success' => false,
                'message' => 'SMS service is not configured (UNISMS_SECRET_KEY missing).',
            ], 503);
        }

        // Sending was attempted and failed while the echo is off: don't fake success.
        if ($sms && ! $sms['ok'] && ! $echoOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send the verification SMS. Please try again.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => ($sms && $sms['ok']) ? 'OTP sent via SMS.' : 'OTP generated successfully.',
            'data'    => $data,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/verify-otp
    // Body: { phone, otp }
    // Returns: { token, isNewUser }
    // ─────────────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $normalized = $this->normalizePhone($request->phone);

        if ($locked = $this->otpLockout($normalized)) {
            return $locked;
        }

        $otpRecord = OtpCode::findValid($normalized, $request->otp);

        // Consume atomically: only one concurrent request can flip is_used,
        // so the same code can never be redeemed twice.
        if (! $otpRecord || ! $this->consumeOtp($otpRecord)) {
            $this->recordOtpFailure($normalized);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        RateLimiter::clear('otp-verify:' . $normalized);

        // Check if citizen already exists
        $citizen = Citizen::where('phone', $normalized)->first();

        if ($citizen) {
            if (! $citizen->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.',
                ], 403);
            }

            // Returning user — issue token immediately
            $token = $citizen->createToken('mobile')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'OTP verified.',
                'data'    => [
                    'token'     => $token,
                    'isNewUser' => false,
                    'citizen'   => $this->citizenResponse($citizen),
                ],
            ]);
        }

        // New user — issue a short-lived ticket proving this phone was just
        // verified. register() requires it, so nobody can register (and get
        // a token for) a phone number they don't control. Only a hash of the
        // ticket is stored.
        $ticket = Str::random(64);
        Cache::put(
            $this->ticketCacheKey($ticket),
            $normalized,
            now()->addMinutes(self::REG_TICKET_MINUTES)
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Please complete registration.',
            'data'    => [
                'token'              => null,
                'isNewUser'          => true,
                'phone'              => $normalized,
                'registrationTicket' => $ticket,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/verify-reset-otp
    // Body: { phone, otp }
    // Checks the OTP for a password reset WITHOUT consuming it, so the user
    // gets immediate feedback before choosing a new PIN. resetPin() then
    // consumes the same code atomically.
    // ─────────────────────────────────────────────────────────────────────
    public function verifyResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(0|\+63|63)?9\d{9}$/'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $normalized = $this->normalizePhone($request->phone);

        // This check does not consume the code, so without a cap it would be
        // an unlimited OTP-guessing oracle.
        if ($locked = $this->otpLockout($normalized)) {
            return $locked;
        }

        $citizen = Citizen::where('phone', $normalized)->first();

        if (! $citizen) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with that mobile number.',
            ], 404);
        }

        if (! $citizen->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        if (! OtpCode::findValid($normalized, $request->otp)) {
            $this->recordOtpFailure($normalized);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified.',
            'data'    => ['phone' => $normalized],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/reset-pin
    // Body: { phone, otp, pin }
    // If the OTP is valid, replaces the citizen's PIN and revokes every
    // existing token so any stolen session is signed out.
    // ─────────────────────────────────────────────────────────────────────
    public function resetPin(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(0|\+63|63)?9\d{9}$/'],
            'otp'   => ['required', 'string', 'size:6'],
            'pin'   => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $normalized = $this->normalizePhone($request->phone);

        if ($locked = $this->otpLockout($normalized)) {
            return $locked;
        }

        $citizen = Citizen::where('phone', $normalized)->first();

        if (! $citizen) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with that mobile number.',
            ], 404);
        }

        if (! $citizen->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        $otpRecord = OtpCode::findValid($normalized, $request->otp);

        $reset = $otpRecord && DB::transaction(function () use ($citizen, $otpRecord, $request) {
            // Atomic consume inside the transaction: a concurrent request
            // that lost the race changes nothing.
            if (! $this->consumeOtp($otpRecord)) {
                return false;
            }
            $citizen->update(['pin_hash' => Hash::make($request->pin)]);
            $citizen->tokens()->delete();

            return true;
        });

        if (! $reset) {
            $this->recordOtpFailure($normalized);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        RateLimiter::clear('otp-verify:' . $normalized);
        RateLimiter::clear('pin-login:' . $normalized);

        return response()->json([
            'success' => true,
            'message' => 'PIN reset successfully. Please log in with your new PIN.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/register
    // Body: { fullName, email (optional), barangay, pin, phone }
    // ─────────────────────────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $normalized = $this->normalizePhone((string) ($request->phone ?? ''));

        // ── Proof of phone ownership ──────────────────────────────────────
        // The ticket comes from verify-otp (new users only) and is bound to
        // the verified number. Without it anyone could register any number.
        // Local debug only: a missing ticket is allowed so the Flutter debug
        // "Mock" registration (change 072, skips SMS) keeps working.
        $ticketKey = $request->filled('registrationTicket')
            ? $this->ticketCacheKey((string) $request->registrationTicket)
            : null;
        $ticketPhone = $ticketKey ? Cache::get($ticketKey) : null;

        if ($ticketPhone !== $normalized && ! ($ticketKey === null && $this->isLocalDebug())) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your mobile number first.',
            ], 422);
        }

        // ── Validation ────────────────────────────────────────────────────
        try {
            $request->validate([
                'fullName' => ['required', 'string', 'max:150', function ($attr, $val, $fail) {
                    if (count(array_filter(explode(' ', trim($val)))) < 2) {
                        $fail('Please enter both your first and last name.');
                    }
                }],
                'email'    => 'nullable|email|max:191|unique:citizens,email',
                'street'   => 'nullable|string|max:150',
                'purok'    => 'nullable|string|max:100',
                'barangay' => ['required', 'string', 'in:' . implode(',', self::BARANGAYS)],
                'city'     => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'pin'      => ['required', 'string', 'regex:/^\d{6}$/'],
                'phone'    => [
                    'required', 'string',
                    function ($attr, $val, $fail) use ($normalized) {
                        if (Citizen::where('phone', $normalized)->exists()) {
                            $fail('This mobile number is already registered. Please sign in instead.');
                        }
                    },
                ],
            ]);
        } catch (ValidationException $e) {
            // Return first validation error as a clean, user-friendly message
            $first = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $first,
                'errors'  => $e->errors(),
            ], 422);
        }

        // ── Create citizen + token ────────────────────────────────────────
        try {
            $citizen = Citizen::create([
                'full_name' => $request->fullName,
                'email'     => $request->email,
                'phone'     => $normalized,
                'street'    => $request->street ?: null,
                'purok'     => $request->purok ?: null,
                'barangay'  => $request->barangay,
                'city'      => $request->city ?: 'Digos City',
                'province'  => $request->province ?: 'Davao del Sur',
                'pin_hash'  => Hash::make($request->pin),
            ]);
        } catch (QueryException $e) {
            // Safety net for any race-condition duplicate key violation
            if ($e->errorInfo[1] === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'This mobile number is already registered. Please sign in instead.',
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }

        // One ticket, one account.
        if ($ticketKey) {
            Cache::forget($ticketKey);
        }

        $token = $citizen->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data'    => [
                'token'     => $token,
                'isNewUser' => false,
                'citizen'   => $this->citizenResponse($citizen),
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/auth/logout
    // ─────────────────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user('citizen')->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/mobile/auth/me
    // ─────────────────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->citizenResponse($request->user('citizen')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    /** Local development with debug on — the only place dev shortcuts apply. */
    private function isLocalDebug(): bool
    {
        return app()->environment('local') && (bool) config('app.debug');
    }

    private function ticketCacheKey(string $ticket): string
    {
        return 'reg-ticket:' . hash('sha256', $ticket);
    }

    /** Flip is_used false→true; false when another request already used it. */
    private function consumeOtp(OtpCode $otp): bool
    {
        return OtpCode::whereKey($otp->getKey())
            ->where('is_used', false)
            ->update(['is_used' => true]) === 1;
    }

    /** 429 once a phone has used up its OTP guesses. */
    private function otpLockout(string $phone): ?JsonResponse
    {
        $key = 'otp-verify:' . $phone;

        return RateLimiter::tooManyAttempts($key, self::OTP_MAX_FAILURES)
            ? $this->tooManyAttempts($key, 'Too many incorrect codes.')
            : null;
    }

    /**
     * Count a wrong OTP. At the limit every outstanding code for the phone
     * is burned, so guessing has to restart with a new (rate-limited) SMS.
     */
    private function recordOtpFailure(string $phone): void
    {
        $key = 'otp-verify:' . $phone;
        RateLimiter::hit($key, self::OTP_DECAY_SECONDS);

        if (RateLimiter::attempts($key) >= self::OTP_MAX_FAILURES) {
            OtpCode::where('phone', $phone)
                ->where('is_used', false)
                ->update(['is_used' => true]);
        }
    }

    private function tooManyAttempts(string $key, string $reason): JsonResponse
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = max(1, (int) ceil($seconds / 60));

        return response()->json([
            'success'     => false,
            'message'     => "{$reason} Please try again in {$minutes} minute(s).",
            'retry_after' => $seconds,
        ], 429)->header('Retry-After', (string) $seconds);
    }

    private function citizenResponse(Citizen $citizen): array
    {
        return [
            'id'              => (string) $citizen->id,
            'fullName'        => $citizen->full_name,
            'phoneNumber'     => $citizen->phone,
            'street'          => $citizen->street,
            'purok'           => $citizen->purok,
            'barangay'        => $citizen->barangay,
            'city'            => $citizen->city,
            'province'        => $citizen->province,
            'joinedDate'      => $citizen->created_at?->toIso8601String(),
            'totalReports'    => $citizen->total_reports,
            'resolvedReports' => $citizen->resolved_reports,
            'avatarUrl'       => null,
        ];
    }

    /**
     * Normalise any Philippine phone number to 639XXXXXXXXX format.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (str_starts_with($phone, '+63')) return substr($phone, 1);
        if (str_starts_with($phone, '0'))   return '63' . substr($phone, 1);
        if (str_starts_with($phone, '9'))   return '63' . $phone;

        return $phone;
    }

    // Official 26 barangays — single list on the model, shared with report
    // validation. Keep in sync with AppStrings.barangays in the Flutter app.
    private const BARANGAYS = \App\Models\CitizenReport::BARANGAYS;
}
