#!/bin/sh

# Salir si ocurre un error
set -e

# 1. Crear directorios de caché de Laravel
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs

# 2. Permisos
chown -R www-data:www-data storage bootstrap/cache

# 3. Optimización de Laravel para Producción
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Ejecutar el comando pasado al contenedor (Supervisord)
exec "$@"