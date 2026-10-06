<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Super administrator gate.
 *
 * CEO and CENRO staff are allowed to *read* every panel route, so safe
 * methods (GET/HEAD/OPTIONS) pass straight through. Every write —
 * POST/PUT/PATCH/DELETE — must come from a `super_admin`, otherwise the
 * request is refused with 403 before the controller runs.
 *
 * Registered as the `superadmin` alias in bootstrap/app.php and attached to
 * the /api/admin/* and /admin/* groups.
 */
class EnsureSuperAdmin
{
    /** HTTP verbs that are allowed for every authenticated role. */
    private const READ_VERBS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::READ_VERBS, true)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || $user->role !== 'super_admin') {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Super administrator access is required.',
                ], 403);
            }

            return redirect()
                ->back()
                ->with('error', 'Super administrator access is required.');
        }

        return $next($request);
    }
}
