<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client For Authentication Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Client For
    | Authentication Provider package.
    |
    */

    'default_role_id' => env('CLIENT_AUTH_DEFAULT_ROLE_ID', 2),
    
    'default_country_code' => env('CLIENT_AUTH_DEFAULT_COUNTRY_CODE', '+92'),
    
    'default_country_iso_code' => env('CLIENT_AUTH_DEFAULT_COUNTRY_ISO_CODE', 'PK'),
    
    'provider_name' => env('CLIENT_AUTH_PROVIDER_NAME', 'Bullseye'),
    
    'route_prefix' => env('CLIENT_AUTH_ROUTE_PREFIX', 'api'),
    
    'route_middleware' => env('CLIENT_AUTH_ROUTE_MIDDLEWARE', 'api'),
];
