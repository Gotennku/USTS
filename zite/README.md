# Stack Symfony + Nginx + MySQL via Docker Compose

services:
  php: PHP-FPM + Composer
  nginx: Reverse proxy / static assets
  db: MySQL 8.0

Ports:
  App: http://localhost:8080
  MySQL: 3306

Étapes si le projet Symfony n'existe pas encore:

1. Démarrer les conteneurs (construction):
   docker-compose up --build -d

2. Créer un nouveau projet Symfony dans le conteneur:
   docker-compose exec php bash -c "composer create-project symfony/skeleton temp && mv temp/* temp/.* . 2>/dev/null || true"
   OU version web complète:
   docker-compose exec php composer create-project symfony/website-skeleton .

3. Donner les droits (si nécessaire):
   docker-compose exec php chown -R www-data:www-data var

4. Mettre à jour la variable DATABASE_URL dans .env.local:
   DATABASE_URL="mysql://symfony:symfony@db:3306/symfony?charset=utf8mb4"

5. Créer la base et lancer les migrations:
   docker-compose exec php bin/console doctrine:database:create
   docker-compose exec php bin/console doctrine:migrations:migrate -n

Redémarrage rapide:
  docker-compose up -d

Logs:
  docker-compose logs -f php
  docker-compose logs -f nginx
  docker-compose logs -f db

Reconstruction après changement Dockerfile:
  docker-compose build --no-cache php

Nettoyage:
  docker-compose down -v

