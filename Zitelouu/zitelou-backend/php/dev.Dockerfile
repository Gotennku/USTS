FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    acl git zip unzip libpq-dev libonig-dev libxml2-dev libzip-dev curl \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir des arguments pour l'UID et le GID
ARG USER_ID=1000
ARG GROUP_ID=1000

# Créer un groupe et un utilisateur avec les mêmes ID que l'utilisateur hôte
RUN groupadd -g ${GROUP_ID} bernard \
    && useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash bernard \
    && mkdir -p /var/www/api \
    && setfacl -R -m u:www-data:rwX -m u:bernard:rwX /var/www/api \
    && setfacl -dR -m u:www-data:rwX -m u:bernard:rwX /var/www/api

WORKDIR /var/www/api

USER bernard

CMD ["php-fpm"]