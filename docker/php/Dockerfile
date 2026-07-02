FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    libzip-dev \
    bash \
    nodejs \
    npm

RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
