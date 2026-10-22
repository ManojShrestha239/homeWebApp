# Official PHP 8.3 FPM image on Alpine Linux (small and fast)
FROM php:8.3-fpm-alpine

# Install system libraries needed by the PHP extensions (runtime versions)
RUN apk add --no-cache \
    libpq \
    icu-libs \
    libzip \
    oniguruma \
    unzip \
    bash

# Install build tools temporarily, compile extensions, then remove the tools
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    # Core extensions Laravel needs for PostgreSQL, math, i18n, zip, and speed
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath intl zip opcache \
    # Redis extension (phpredis is fast; Laravel uses it when REDIS_CLIENT=phpredis)
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Remove build tools so the final image stays small
    && apk del .build-deps

# Copy the Composer binary from the official Composer image (no manual install)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Load our custom PHP settings
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# All commands run from the Laravel folder
WORKDIR /var/www/backend

# php-fpm listens on port 9000 by default (Nginx talks to it there)
EXPOSE 9000