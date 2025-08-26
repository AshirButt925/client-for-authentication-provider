<?php

namespace ClientForAuthenticationProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ClientForAuthenticationProviderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/client-for-authentication-provider.php', 'client-for-authentication-provider'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $appRoutes = base_path('routes/client/authentication-provider.php');

        if (file_exists($appRoutes)) {
            require $appRoutes;
        } else {
            // Load routes with version-specific syntax
            $this->loadVersionCompatibleRoutes();
        }

        $this->publishes([
            __DIR__.'/../config/client-for-authentication-provider.php' => config_path('client-for-authentication-provider.php'),
        ], 'client-for-authentication-provider-config');

        $this->publishes([
            __DIR__.'/../routes/api.php' => base_path('routes/client/authentication-provider.php'),
        ], 'client-for-authentication-provider-routes');

        $this->publishes([
            __DIR__.'/Http/Controllers/' => base_path('app/Http/Controllers/ClientForAuthenticationProvider/'),
        ], 'client-for-authentication-provider-controllers');
    }

    private function loadVersionCompatibleRoutes()
    {
        Route::group([
            'prefix' => config('client-for-authentication-provider.route_prefix', 'api'),
            'middleware' => config('client-for-authentication-provider.route_middleware', 'api'),
        ], function () {
            // Laravel 5.3-8.x syntax
            if (version_compare(app()->version(), '8.0', '<')) {
                Route::post('/create-or-login-user', 'ClientForAuthenticationProvider\Http\Controllers\ClientAuthController@miniAppCreateOrLoginUser');
            } else {
                // Laravel 8.x+ syntax
                Route::post('/create-or-login-user', [\ClientForAuthenticationProvider\Http\Controllers\ClientAuthController::class, 'miniAppCreateOrLoginUser']);
            }
        });
    }
}