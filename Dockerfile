# --- Etapa 1: Builder de PHP (Composer) ---
FROM composer:2 as composer_builder
WORKDIR /app
COPY . .
# Instalamos dependencias optimizadas
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts --ignore-platform-reqs

# --- Etapa 2: Builder de Node (Vite) ---
FROM node:20-alpine as node_builder
WORKDIR /app
COPY . .
# Copiamos los vendors para que Vite pueda encontrar archivos si es necesario
COPY --from=composer_builder /app/vendor /app/vendor
RUN npm ci
RUN npm run build

# --- Etapa 3: Imagen Final ---
FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema y extensiones PHP
# pcntl es obligatorio para Reverb y Queues
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev \
    postgresql-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip bcmath gd pcntl

# Limpiar caché de apk
RUN rm -rf /var/cache/apk/*

# Configurar Nginx y Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor.d/supervisord.ini

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar código fuente y dependencias
COPY --from=composer_builder /app .
COPY --from=node_builder /app/public/build ./public/build

# Configurar Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permisos finales
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto 80 (Render inyectará la variable PORT, pero internamente escuchamos 80)
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor.d/supervisord.ini"]