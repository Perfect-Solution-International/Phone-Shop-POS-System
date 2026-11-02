<?php

namespace App\Support\Concerns;

use App\Models\Scopes\BelongsToTenantScope;

trait BelongsToShop
{
    use BelongsToTenant;

    /**
     * Boot the BelongsToShop trait alias for backwards-compatibility.
     */
    public static function bootBelongsToShop(): void
    {
        // Register 'shop' named scope alias so withoutGlobalScope('shop') works identically
        static::addGlobalScope('shop', new BelongsToTenantScope());
    }
}
