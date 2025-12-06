<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Bu iÅŸlem iÃ§in yetkiniz bulunmuyor.');
        }

        return $next($request);
    }
}
