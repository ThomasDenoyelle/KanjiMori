FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    libzip-dev \
    bash \
    nodejs \
    npm \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
