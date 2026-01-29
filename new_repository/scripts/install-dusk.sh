#!/bin/bash
#
# Laravel Dusk Installation Script
#

cd "$(dirname "$0")/.."

echo "Installing Laravel Dusk..."
composer require --dev laravel/dusk

echo "Installing Dusk..."
php artisan dusk:install

echo "ChromeDriver installation..."
php artisan dusk:chrome-driver --detect

echo "Dusk setup completed!"
