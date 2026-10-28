<?php

namespace App\Providers;

use App\Models\Platform\Shop;
use App\Services\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \Laravel\Sanctum\Sanctum::ignoreMigrations();

        // Register TenantContext as a singleton
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });

        $this->app->alias(TenantContext::class, 'tenant.context');

        // Payment Gateway Manager & Interface Binding
        $this->app->singleton(\App\Services\Payment\PaymentGatewayManager::class, function () {
            return new \App\Services\Payment\PaymentGatewayManager();
        });

        $this->app->bind(\App\Services\Payment\Contracts\PaymentGatewayInterface::class, function ($app) {
            return $app->make(\App\Services\Payment\PaymentGatewayManager::class)->driver();
        });
    }

    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

        Event::listen(
            Registered::class,
            SendEmailVerificationNotification::class,
        );

        // Super Administrator possesses universal access across all tenant shop modules
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Administrator') ? true : null;
        });
    }
}
