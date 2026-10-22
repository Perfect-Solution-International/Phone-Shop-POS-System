<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(
            Str::lower((string) $request->input('email')).'|'.$request->ip()
        ));

        $this->routes(function (): void {
            Route::middleware('api')->prefix('api')->name('api.')->group(base_path('routes/api.php'));
            foreach (['super-admin', 'shop', 'customer-portal'] as $area) {
                Route::middleware('web')->prefix($area)->name($area.'.')->group(base_path('routes/'.$area.'.php'));
            }
            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }
}
