<?php

/**
 * Installation script for ClientForAuthenticationProvider package
 */

echo "Installing ClientForAuthenticationProvider package...\n";

// Check if Laravel is installed
if (!file_exists(base_path('artisan'))) {
    echo "Error: Laravel not found. Please run this script from a Laravel project root.\n";
    exit(1);
}

// Publish configuration
echo "Publishing configuration...\n";
exec('php artisan vendor:publish --tag=client-for-authentication-provider-config --force');

// Publish routes
echo "Publishing routes...\n";
exec('php artisan vendor:publish --tag=client-for-authentication-provider-routes --force');

// Publish controllers
echo "Publishing controllers...\n";
exec('php artisan vendor:publish --tag=client-for-authentication-provider-controllers --force');

echo "Installation completed successfully!\n";
echo "Routes are available at: routes/client/authentication-provider.php\n";
echo "Controllers are available at: app/Http/Controllers/ClientForAuthenticationProvider/\n";
echo "Configuration is available at: config/client-for-authentication-provider.php\n";
