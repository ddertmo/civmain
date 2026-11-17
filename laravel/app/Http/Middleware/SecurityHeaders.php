<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers for HTML responses (Blade admin, and the static
 * panel pages when served through Laravel, e.g. `php artisan serve`).
 * Apache sends the same policy for static files via public/.htaccess —
 * keep the two in sync.
 *
 * script-src still needs 'unsafe-inline' (the panel pages use inline
 * scripts and onclick handlers), so XSS defence remains output escaping
 * (Utils.esc). The policy still blocks: scripts from any other host,
 * plugins (object-src), <base> hijacking, framing (clickjacking) and form
 * posts to other sites.
 */
class SecurityHeaders
{
    public const CSP = "default-src 'self'; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; "
        . "font-src 'self' data: https://fonts.gstatic.com; "
        . "img-src * data: blob:; "
        . "connect-src 'self'; "
        . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $type = (string) $response->headers->get('Content-Type', '');
        if ($type === '' || str_contains($type, 'text/html')) {
            $response->headers->set('Content-Security-Policy', self::CSP);
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
