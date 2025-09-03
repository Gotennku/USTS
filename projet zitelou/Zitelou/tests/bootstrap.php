<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Forcer une base SQLite fichier pour garder le schéma pendant tout le processus
$_ENV['DATABASE_URL'] = 'sqlite:///' . dirname(__DIR__) . '/var/test.db';
$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'];

if (!empty($_SERVER['APP_DEBUG'])) {
    umask(0o000);
}
