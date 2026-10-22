<?php

namespace App\Models\Scopes;

use App\Services\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BelongsToTenantScope implements Scope
{
    /**
     * Apply the tenant isolation scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // If query explicitly removed any tenant or shop scope, bypass
        if (in_array('shop', $builder->removedScopes(), true)
            || in_array('tenant', $builder->removedScopes(), true)
            || in_array(static::class, $builder->removedScopes(), true)) {
            return;
        }

        $tenantKey = method_exists($model, 'getTenantKeyName')
            ? $model->getTenantKeyName()
            : 'shop_id';

        $qualifiedColumn = $model->qualifyColumn($tenantKey);

        // Prevent duplicate condition if applied through multiple aliases
        foreach ($builder->getQuery()->wheres as $where) {
            if (($where['column'] ?? null) === $qualifiedColumn || ($where['sql'] ?? null) === '1 = 0') {
                return;
            }
        }

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
            $builder->where($qualifiedColumn, $resolvedTenantId);
        } else {
            // Fail-closed: outside an active tenant context, return zero records
            $builder->whereRaw('1 = 0');
        }
    }
}

