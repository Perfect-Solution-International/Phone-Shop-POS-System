<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveShop
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->shop_id, 403, 'A shop assignment is required.');
        app()->instance('shop.id', (int) $request->user()->shop_id);

        try {
            return $next($request);
        } finally {
            app()->forgetInstance('shop.id');
        }
    }
}
