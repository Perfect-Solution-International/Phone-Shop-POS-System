<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Shop;
use Closure;
use InvalidArgumentException;

class TenantContext
{
    /**
     * Get the currently active tenant Shop instance.
     */
    public function get(): ?Shop
    {
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
            if ($tenant instanceof Shop) {
                return $tenant;
            }
        }

        $id = $this->id();
        if ($id) {
            $tenant = Shop::find($id);
            if ($tenant) {
                app()->instance('tenant', $tenant);
                app()->instance(Shop::class, $tenant);
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Get the active tenant identifier.
     */
    public function id(): ?int
    {
        if (app()->bound('tenant.id')) {
            return (int) app('tenant.id');
        }

        if (app()->bound('shop.id')) {
            return (int) app('shop.id');
        }

        if (app()->bound('tenant')) {
            $tenant = app('tenant');
            if ($tenant instanceof Shop) {
                return (int) $tenant->id;
            }
        }

        return null;
    }

    /**
     * Determine if an active tenant context is resolved.
     */
    public function check(): bool
    {
        return $this->id() !== null;
    }

    /**
     * Bind a tenant instance and its identifier globally into the container.
     */
    public function set(Shop $tenant): void
    {
        $tenantId = (int) $tenant->id;

        app()->instance('tenant', $tenant);
        app()->instance('tenant.id', $tenantId);
        app()->instance('shop.id', $tenantId);
        app()->instance(Shop::class, $tenant);
    }

    /**
     * Set the active tenant context using a tenant ID.
     */
    public function setById(int $tenantId): void
    {
        $tenant = Shop::findOrFail($tenantId);
        $this->set($tenant);
    }

    /**
     * Clear all tenant context bindings from the application container.
     */
    public function clear(): void
    {
        app()->forgetInstance('tenant');
        app()->forgetInstance('tenant.id');
        app()->forgetInstance('shop.id');
        app()->forgetInstance(Shop::class);
    }

    /**
     * Execute a callback in the context of a given tenant, restoring the previous context afterwards.
     */
    public function runForTenant(Shop|int $tenant, Closure $callback): mixed
    {
        $previousTenant = $this->get();
        $previousTenantId = $this->id();

        $target = $tenant instanceof Shop ? $tenant : Shop::find($tenant);

        if (! $target) {
            throw new InvalidArgumentException("Tenant [{$tenant}] could not be resolved.");
        }

        $this->set($target);

        try {
            return $callback($target);
        } finally {
            if ($previousTenant instanceof Shop) {
                $this->set($previousTenant);
            } elseif ($previousTenantId !== null) {
                app()->instance('tenant.id', $previousTenantId);
                app()->instance('shop.id', $previousTenantId);
            } else {
                $this->clear();
            }
        }
    }
}

