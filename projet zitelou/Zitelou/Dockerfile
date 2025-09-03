FROM php:8.3-cli

ARG INSTALL_XDEBUG=0
ENV APP_ENV=dev \
    XDEBUG_MODE=off
# Activer la couverture: docker compose run --rm -e XDEBUG_MODE=coverage php composer coverage

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip opcache \
    && if [ "$INSTALL_XDEBUG" = "1" ]; then pecl install xdebug && docker-php-ext-enable xdebug ; fi

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching deps layer
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction || true

# Then copy rest of source
COPY . .

RUN composer dump-autoload --optimize

# Démarre le serveur web PHP intégré pour Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
