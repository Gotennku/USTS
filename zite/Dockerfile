FROM php:8.2-fpm

# Arguments permettant d'ajuster l'UID/GID si besoin (optionnel)
ARG PUID=1000
ARG PGID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
        APP_ENV=dev

# Packages système nécessaires à Symfony + extensions
RUN apt-get update && apt-get install -y \
        git curl unzip zip \
        libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev libjpeg-dev libfreetype6-dev \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip opcache \
        && rm -rf /var/lib/apt/lists/*

# Configuration PHP (opcache basique)
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.jit_buffer_size=64M'; \
    echo 'opcache.jit=tracing'; \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=25M'; \
    echo 'post_max_size=25M'; \
} > /usr/local/etc/php/conf.d/symfony.ini

# Installer Composer (image officielle comme source)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le code (si vide au début ce n'est pas grave)
COPY . /var/www/html

# Installer les dépendances seulement si composer.json existe (évite l'échec lors d'un projet vierge)
RUN if [ -f composer.json ]; then \
            composer install --no-interaction --prefer-dist --optimize-autoloader; \
        else \
            echo "composer.json absent - vous créerez le projet Symfony après le démarrage"; \
        fi

CMD ["php-fpm"]
