<?php

namespace App\Http\Middleware;

use App\Models\Platform\Shop;
use App\Services\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Resolve and bind tenant context safely from the authenticated user's session/token.
     * User parameters from query/body/headers are strictly ignored to prevent context manipulation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super Administrators possess universal access to all shop portals and tenant workspaces
        if ($user && ($user->hasRole('Super Administrator') || $user->can('superadmin.view'))) {
            $activeShopId = session('superadmin_active_shop_id') ?: $user->shop_id;
            $tenant = $activeShopId ? Shop::find($activeShopId) : null;

            if (! $tenant) {
                $tenant = Shop::first();
            }

            if (! $tenant) {
                $tenant = Shop::create([
                    'name' => 'Demo Phone Shop',
                    'address' => '100 Galle Road, Colombo',
                    'country' => 'Sri Lanka',
                ]);
            }

            $user->shop_id = $tenant->id;
            $tenantId = (int) $tenant->id;
        } else {
            // 1. Must be authenticated and assigned to a tenant
            abort_unless($user && $user->shop_id, 403, 'A shop assignment is required.');

            // 2. Resolve active tenant_id strictly from the authenticated user record
            $tenantId = (int) $user->shop_id;

            // 3. Ensure the tenant workspace exists in the database
            $tenant = Shop::find($tenantId);
            abort_unless($tenant, 403, 'Assigned tenant workspace does not exist or is inaccessible.');
        }

        // 4. Bind tenant instance globally to container and request context
        $this->tenantContext->set($tenant);
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenantId);

        try {
            return $next($request);
        } finally {
            // Clean up to prevent tenant context leakage across requests
            $this->tenantContext->clear();
        }
    }
}

