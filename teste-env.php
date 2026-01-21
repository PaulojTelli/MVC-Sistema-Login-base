<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->load();

echo '<pre>';
echo 'APP_ENV: ' . ($_ENV['APP_ENV'] ?? 'não definido') . PHP_EOL;
echo 'DB_DSN: ' . ($_ENV['DB_DSN'] ?? 'não definido') . PHP_EOL;
echo 'asd: ' . ($_ENV['ASD'] ?? 'não definido') . PHP_EOL;
echo 'DEBUG: ';
var_dump(env('APP_DEBUG'));
echo '</pre>';
