<?php
session_start();


use core\Router;

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// carrega o .env da raiz
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// debug por ambiente
if ($_ENV['APP_DEBUG'] ?? false) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/config/helpers.php';

$router = new Router;


