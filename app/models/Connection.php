<?php

namespace app\models;

use PDO;
use PDOException;

abstract class Connection
{
    protected function connect(): PDO
    {
        // Lê do .env
        $dsn  = env('DB_DSN');
        $user = env('DB_USER');
        $pass = env('DB_PASS');

        if (!$dsn || !$user) {
            throw new \RuntimeException('Configuração de banco ausente no .env');
        }

        try {
            $conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $conn->exec("SET NAMES utf8");

            return $conn;
        } catch (PDOException $error) {
            if (env('APP_DEBUG', false)) {
                throw $error;
            }

            throw new \RuntimeException('Erro ao conectar ao banco de dados.');
        }
    }
}
