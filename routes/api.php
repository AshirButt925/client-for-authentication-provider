<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('client-for-authentication-provider.route_prefix', 'api'),
    'middleware' => config('client-for-authentication-provider.route_middleware', 'api'),
], function () {
    Route::post('/create-or-login-user', 'ClientForAuthenticationProvider\Http\Controllers\ClientAuthController@miniAppCreateOrLoginUser');
});