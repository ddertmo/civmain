<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects requests from a deactivated account (staff User or Citizen).
 *
 * Login already refuses inactive accounts, but a token or session issued
 * before deactivation kept working. Here the current API token is revoked
 * (or the web session ended) and the request refused. Registered as the
 * `active` alias; placed after the auth middleware on every protected group.
 */
class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if ($user && isset($user->is_active) && ! $user->is_active) {
            $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.',
                ], 403);
            }

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
