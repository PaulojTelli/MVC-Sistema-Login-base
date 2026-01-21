<?php

$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$domain = preg_replace('/:\d+$/', '', $domain);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $domain,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// carrega o .env da raiz
Dotenv::createImmutable(__DIR__)->load();

// helper env()
require_once __DIR__ . '/config/helpers.php';

// debug por ambiente (usa env() pra converter "true"/"false")
if (env('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

use core\Router;

$router = new Router;
