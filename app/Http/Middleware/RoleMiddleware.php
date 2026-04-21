<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple role-based access control (RBAC).
 *
 * Examples:
 * - middleware('role:super_admin')
 * - middleware('role:super_admin,admin_satker')
 */
class RoleMiddleware
{
    /**
     * @param  string  ...$roles Allowed roles (e.g. "super_admin")
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Normally this middleware is used together with 'auth'.
        if (! $user) {
            abort(401);
        }

        // Block inactive users — log them out cleanly
        if (isset($user->status) && $user->status === 'inactive') {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.']);
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Basic sanity check for admin satker accounts.
        // Admin Satker MUST be attached to a satker.
        if ($user->role === User::ROLE_ADMIN_SATKER && $user->satker_id === null) {
            abort(403, 'Akun admin satker belum terhubung dengan satker.');
        }

        return $next($request);
    }
}

