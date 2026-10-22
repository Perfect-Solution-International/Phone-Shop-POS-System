<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \Laravel\Sanctum\Sanctum::ignoreMigrations();
    }

    public function boot(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
