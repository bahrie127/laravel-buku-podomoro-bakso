#!/bin/bash

# Script untuk deployment yang bersih
# Jalankan script ini setelah upload file ke server production

echo "Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Clearing Filament cached components..."
php artisan filament:clear-cached-components

echo "Optimizing application..."
php artisan optimize

echo "Running migrations (if needed)..."
# php artisan migrate --force

echo "Deployment completed!"
