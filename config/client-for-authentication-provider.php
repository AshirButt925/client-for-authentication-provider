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
```

```php:packages/ClientForAuthenticationProvider/routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use ClientForAuthenticationProvider\Http\Controllers\ClientAuthController;

Route::group([
    'prefix' => config('client-for-authentication-provider.route_prefix', 'api'),
    'middleware' => config('client-for-authentication-provider.route_middleware', 'api'),
], function () {
    Route::post('/create-or-login-user', [ClientAuthController::class, 'miniAppCreateOrLoginUser']);
});
```

```php:packages/ClientForAuthenticationProvider/src/Http/Controllers/ClientAuthController.php
<?php

namespace ClientForAuthenticationProvider\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use UnexpectedValueException;

class ClientAuthController extends Controller
{
    /**
     * Handle mini app login with token verification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miniAppCreateOrLoginUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_data' => 'required',
                'user_data.email' => 'required|email',
                'user_data.mobile' => 'required|string',
                'user_data.first_name' => 'required|string',
                'user_data.last_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed', 
                    'errors' => $validator->errors()
                ]);
            }

            $userEmail = $request->user_data['email'];
            $userModel = config('auth.providers.users.model', 'App\Models\User');
            $existingUser = $userModel::where('email', $userEmail)->first();
            
            if ($existingUser) {
                $updateExistingUser = [
                    'status' => 'approved',
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ];

                if (empty($existingUser->phone)) {
                    $updateExistingUser['phone'] = $request->user_data['mobile'];
                }

                if (empty($existingUser->email)) {
                    $updateExistingUser['email'] = $request->user_data['email'];
                }

                $existingUser->update($updateExistingUser);
                $existingUser->refresh();
                $jwtToken = $existingUser->createToken('token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'token' => $jwtToken,
                    'data' => $existingUser
                ]);
            }

            return $this->createNewUserFromVerificationData($request->user_data);
        } catch (UnexpectedValueException $e) {
            return response()->json(['success' => false, 'message' => 'Invalid token']);
        } catch (Exception $e) {
            Log::error('Mini App Login Error: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create new user from verification data
     *
     * @param array $userData
     * @return \Illuminate\Http\JsonResponse
     */
    private function createNewUserFromVerificationData(array $userData): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $config = config('client-for-authentication-provider');
            $countryCode = $userData['country_code'] ?? $config['default_country_code'];
            $userData['mobile'] = str_replace($countryCode, '', $userData['mobile']);
            
            $userModel = config('auth.providers.users.model', 'App\Models\User');
            
            $newUser = [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'role_id' => $config['default_role_id'],
                'email' => $userData['email'],
                'phone' => $userData['mobile'],
                'country_code' => $countryCode,
                'country_iso_code' => $userData['country_iso_code'] ?? $config['default_country_iso_code'],
                'provider' => $config['provider_name'],
                'status' => 'approved',
                'provider_user_id' => $userData['id'] ?? $userData['email'],
                'password' => $userData['password'] ?? bcrypt(str_random(16)),
                'is_mealsmash_pending' => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ];

            $createdUser = $userModel::create($newUser);
            $createdUser->refresh();

            $jwtToken = $createdUser->createToken('token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'token' => $jwtToken,
                'data' => $createdUser
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Mini App Login Error: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
```

```php:packages/ClientForAuthenticationProvider/src/Http/Controllers/Controller.php
<?php

namespace ClientForAuthenticationProvider\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
```

```php:packages/ClientForAuthenticationProvider/README.md
# Client For Authentication Provider

A Laravel package for handling client authentication with mini app login functionality.

## Installation

1. Add the package to your Laravel project:

```bash
composer require client-for-authentication-provider/client-for-authentication-provider
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag=client-for-authentication-provider-config
```

3. Publish the routes (optional):

```bash
php artisan vendor:publish --tag=client-for-authentication-provider-routes
```

4. Publish the controllers (optional):

```bash
php artisan vendor:publish --tag=client-for-authentication-provider-controllers
```

## Configuration

The package configuration file is located at `config/client-for-authentication-provider.php`. You can customize the following options:

- `default_role_id`: Default role ID for new users (default: 2)
- `default_country_code`: Default country code (default: '+92')
- `default_country_iso_code`: Default country ISO code (default: 'PK')
- `provider_name`: Provider name (default: 'Bullseye')
- `route_prefix`: Route prefix (default: 'api')
- `route_middleware`: Route middleware (default: 'api')

## Usage

### API Endpoint

The package provides the following API endpoint:

**POST** `/api/create-or-login-user`

#### Request Body

```json
{
    "user_data": {
        "email": "user@example.com",
        "mobile": "1234567890",
        "first_name": "John",
        "last_name": "Doe",
        "country_code": "+92",
        "country_iso_code": "PK",
        "password": "password123"
    }
}
```

#### Response

```json
{
    "success": true,
    "token": "1|abc123...",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "1234567890",
        // ... other user fields
    }
}
```

## Customization

### Publishing Controllers

If you want to customize the controllers, publish them to your application:

```bash
php artisan vendor:publish --tag=client-for-authentication-provider-controllers
```

The controllers will be published to `app/Http/Controllers/ClientForAuthenticationProvider/`.

### Publishing Routes

If you want to customize the routes, publish them to your application:

```bash
php artisan vendor:publish --tag=client-for-authentication-provider-routes
```

The routes will be published to `routes/client/authentication-provider.php`.

## Requirements

- PHP >= 8.2
- Laravel >= 10.0
- Laravel Sanctum

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
```

Now let me create the necessary directories and files for the package structure:

```bash:packages/ClientForAuthenticationProvider/.gitignore
/vendor/
composer.lock
.phpunit.result.cache
```

```php:packages/ClientForAuthenticationProvider/tests/TestCase.php
<?php

namespace ClientForAuthenticationProvider\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
```

```php:packages/ClientForAuthenticationProvider/tests/CreatesApplication.php
<?php

namespace ClientForAuthenticationProvider\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
```

```php:packages/ClientForAuthenticationProvider/tests/ClientAuthControllerTest.php
<?php

namespace ClientForAuthenticationProvider\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mini_app_create_or_login_user_with_valid_data()
    {
        $userData = [
            'user_data' => [
                'email' => 'test@example.com',
                'mobile' => '1234567890',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'country_code' => '+92',
                'country_iso_code' => 'PK',
                'password' => 'password123'
            ]
        ];

        $response = $this->postJson('/api/create-or-login-user', $userData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'token',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone'
                    ]
                ]);
    }

    public function test_mini_app_create_or_login_user_with_invalid_data()
    {
        $userData = [
            'user_data' => [
                'email' => 'invalid-email',
                'mobile' => '',
                'first_name' => '',
                'last_name' => ''
            ]
        ];

        $response = $this->postJson('/api/create-or-login-user', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }
}
```

Now let me update the main composer.json to include the package:

```json:composer.json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": [
        "laravel",
        "framework"
    ],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.10.1"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "pestphp/pest": "^3.8",
        "pestphp/pest-plugin-laravel": "^3.2"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\"
