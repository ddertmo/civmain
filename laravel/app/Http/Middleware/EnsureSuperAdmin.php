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

    /**
     * `superadmin`     → writes need super_admin, reads open to every staff role.
     * `superadmin:all` → reads need super_admin too (e.g. the staff account
     *                    list: names, emails and roles of every account).
     */
    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        if ($scope !== 'all' && in_array($request->method(), self::READ_VERBS, true)) {
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
