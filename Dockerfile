# PHP 8.3 + Alpine
FROM php:8.3-fpm-alpine

# system deps
RUN apk add --no-cache \
    git zip unzip \
    icu-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql intl mbstring gd zip

# ✅ Node.js + npm NA ALPINE
RUN apk add --no-cache nodejs npm

# Composer (z oficjalnego obrazu)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# (opcjonalnie) cache dla composera
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www

# jeżeli chcesz, żeby npm pisał jako www-data (czasem lepiej root; patrz uwagi poniżej)
USER www-data

CMD ["php-fpm"]
