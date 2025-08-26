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


### Manual service provider registration (Laravel 5.3/5.4)

Add the service provider to `config/app.php`:

```php
ClientForAuthenticationProvider\ClientForAuthenticationProviderServiceProvider::class,
```

Then publish assets (specify the provider explicitly):

```bash
php artisan vendor:publish --provider="ClientForAuthenticationProvider\ClientForAuthenticationProviderServiceProvider" --tag=client-for-authentication-provider-config
php artisan vendor:publish --provider="ClientForAuthenticationProvider\ClientForAuthenticationProviderServiceProvider" --tag=client-for-authentication-provider-routes
php artisan vendor:publish --provider="ClientForAuthenticationProvider\ClientForAuthenticationProviderServiceProvider" --tag=client-for-authentication-provider-controllers
```
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
