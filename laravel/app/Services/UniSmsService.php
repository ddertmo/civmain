<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * UniSMS - primary SMS provider (docs/TASKS.md task 1).
 *
 * API docs: https://unismsapi.com/docs/sms
 * Plain REST + Basic auth: the secret key is the username, password empty.
 * Laravel's Http facade is enough - no Composer dependency. Everything
 * provider-specific sits behind config('services.unisms.*') so another
 * provider could be swapped in later (keep it swappable per the task).
 */
class UniSmsService
{
    /** True once a secret key is present (UNISMS_SECRET_KEY in .env). */
    public function configured(): bool
    {
        return (bool) config('services.unisms.key');
    }

    /**
     * POST /sms - queue one message.
     *
     * Returns a stable shape regardless of outcome:
     * ['ok' => bool, 'status' => int, 'reference_id' => ?string, 'error' => ?string]
     */
    public function send(string $recipient, string $content, array $metadata = []): array
    {
        if (! $this->configured()) {
            return $this->fail(0, 'UniSMS not configured (UNISMS_SECRET_KEY missing).');
        }

        if (! config('services.unisms.sender_id')) {
            return $this->fail(0, 'UNISMS_SENDER_ID missing - copy your test Sender ID from the UniSMS dashboard.');
        }

        $payload = [
            'recipient' => $this->formatRecipient($recipient),
            'content'   => $content,
            'sender_id' => config('services.unisms.sender_id'),
        ];

        if ($metadata !== []) {
            $payload['metadata'] = $metadata;
        }

        $response = $this->client()->post('/sms', $payload);

        if ($response->successful()) {
            return [
                'ok'           => true,
                'status'       => $response->status(),
                'reference_id' => $response->json('message.reference_id'),
                'error'        => null,
            ];
        }

        return $this->fail($response->status(), $this->describe($response));
    }

    /**
     * GET /sms/:reference_id - message status
     * ('pending' | 'retrying' | 'sent' | 'failed'), or null when unavailable.
     */
    public function status(string $referenceId): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $response = $this->client()->get('/sms/'.rawurlencode($referenceId));

        return $response->successful() ? $response->json('message') : null;
    }

    /** GET /account - current SMS credit balance, or null when unavailable. */
    public function credits(): ?int
    {
        if (! $this->configured()) {
            return null;
        }

        $response = $this->client()->get('/account');

        return $response->successful() ? $response->json('sms_credits') : null;
    }

    protected function client(): PendingRequest
    {
        return Http::withBasicAuth(config('services.unisms.key'), '')
            ->timeout(15)
            ->baseUrl(rtrim(config('services.unisms.base_url', 'https://unismsapi.com/api'), '/'));
    }

    /**
     * UniSMS expects +639123456789 or 09123456789. Internally we store numbers
     * as 639XXXXXXXXX (see MobileAuthController::normalizePhone), so make sure
     * the canonical +63 form is used whatever we were handed.
     */
    protected function formatRecipient(string $recipient): string
    {
        $recipient = preg_replace('/[^\d+]/', '', $recipient);

        if (str_starts_with($recipient, '+')) {
            return $recipient;
        }
        if (str_starts_with($recipient, '63')) {
            return '+'.$recipient;
        }
        if (str_starts_with($recipient, '0')) {
            return '+63'.substr($recipient, 1);
        }
        if (str_starts_with($recipient, '9')) {
            return '+63'.$recipient;
        }

        return '+'.$recipient;
    }

    /** Turn a non-2xx response into an honest, human-readable reason (never the key). */
    protected function describe(Response $response): string
    {
        return match ($response->status()) {
            401     => 'UniSMS 401 Unauthorized - check UNISMS_SECRET_KEY.',
            422     => 'UniSMS 422 Unprocessable Entity: '.mb_substr($response->body(), 0, 300),
            429     => 'UniSMS 429 Too Many Requests - retry shortly.',
            default => 'UniSMS HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300),
        };
    }

    protected function fail(int $status, string $error): array
    {
        return ['ok' => false, 'status' => $status, 'reference_id' => null, 'error' => $error];
    }
}
