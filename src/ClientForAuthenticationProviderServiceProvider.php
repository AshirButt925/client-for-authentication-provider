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
            require $appRoutes; // 5.3-safe; prefer app's published routes
        } else {
            require __DIR__.'/../routes/api.php'; // fallback to package routes
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
}