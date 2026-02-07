<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Main API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Auth Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Auth/Routes/api.php'));

            // User Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/User/Routes/api.php'));

            // Organization Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Organization/Routes/api.php'));

            // Role Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Role/Routes/api.php'));

            // Quality Standard Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Standard/Routes/api.php'));

            // Quality Assessment Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Assessment/Routes/api.php'));

            // Attachment Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Attachment/Routes/api.php'));

            // Dashboard Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Dashboard/Routes/api.php'));

            // Report Domain
            Route::middleware('api')
                ->prefix('api')
                ->group(app_path('Domain/Report/Routes/api.php'));
        });
    }
}
