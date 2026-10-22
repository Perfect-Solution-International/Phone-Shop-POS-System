<?php

namespace App\Support\Concerns;

use App\Models\Scopes\BelongsToTenantScope;
use App\Services\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait for a model.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', new BelongsToTenantScope());

        static::creating(function (Model $model): void {
            $tenantKey = method_exists($model, 'getTenantKeyName')
                ? $model->getTenantKeyName()
                : 'shop_id';

            $resolvedTenantId = null;

            if (app()->bound(TenantContext::class)) {
                $resolvedTenantId = app(TenantContext::class)->id();
            } elseif (app()->bound('tenant.id')) {
                $resolvedTenantId = app('tenant.id');
            } elseif (app()->bound('shop.id')) {
                $resolvedTenantId = app('shop.id');
            } elseif (app()->bound('tenant')) {
                $resolvedTenantId = app('tenant')?->id;
            }

            if ($resolvedTenantId !== null) {
                $model->{$tenantKey} = $resolvedTenantId;
            }

            if (! $model->{$tenantKey}) {
                throw new LogicException('Tenant-owned records require an explicit tenant context.');
            }
        });
    }

    /**
     * Get the tenant foreign key column name for this model.
     */
    public function getTenantKeyName(): string
    {
        return property_exists($this, 'tenantKeyName') ? $this->tenantKeyName : 'shop_id';
    }
}

