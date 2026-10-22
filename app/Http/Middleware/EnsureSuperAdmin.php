<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasRole('Super Administrator') || $user->can('superadmin.view')),
            403,
            'Super Administrator platform privileges required.'
        );

        return $next($request);
    }
}

