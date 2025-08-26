<?php

use Illuminate\Support\Facades\Route;
use ClientForAuthenticationProvider\Http\Controllers\ClientAuthController;

Route::group([
    'prefix' => config('client-for-authentication-provider.route_prefix', 'api'),
    'middleware' => config('client-for-authentication-provider.route_middleware', 'api'),
], function () {
    Route::post('/create-or-login-user', [ClientAuthController::class, 'miniAppCreateOrLoginUser']);
});
