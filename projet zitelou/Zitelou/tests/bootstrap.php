<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Forcer environnement test avant chargement du Kernel
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Forcer une base SQLite fichier pour garder le schéma pendant tout le processus
$_ENV['DATABASE_URL'] = 'sqlite:///' . dirname(__DIR__) . '/var/test.db';
$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'];

if (!empty($_SERVER['APP_DEBUG'])) {
    umask(0o000);
}

// Génération de clés RSA tests si les chemins /tmp/private.test.pem n'existent pas ou illisibles
$testPriv = '/tmp/private.test.pem';
$testPub  = '/tmp/public.test.pem';
if (!is_readable($testPriv) || !is_readable($testPub)) {
    $res = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    if ($res) {
        openssl_pkey_export($res, $privKey);
        $details = openssl_pkey_get_details($res);
        $pubKey = $details['key'] ?? null;
        if ($privKey && $pubKey) {
            @file_put_contents($testPriv, $privKey);
            @file_put_contents($testPub, $pubKey);
        }
    }
}
// Injecter variables si pas déjà définies
$_ENV['JWT_SECRET_KEY'] = $_ENV['JWT_SECRET_KEY'] ?? $testPriv;
$_ENV['JWT_PUBLIC_KEY'] = $_ENV['JWT_PUBLIC_KEY'] ?? $testPub;
$_SERVER['JWT_SECRET_KEY'] = $_ENV['JWT_SECRET_KEY'];
$_SERVER['JWT_PUBLIC_KEY'] = $_ENV['JWT_PUBLIC_KEY'];
