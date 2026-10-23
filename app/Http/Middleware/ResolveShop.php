<?php

namespace App\Http\Middleware;

/**
 * Backward-compatible alias for ResolveTenantContext.
 */
class ResolveShop extends ResolveTenantContext
{
    // Inherits secure server-side tenant resolution and global bindings
}
