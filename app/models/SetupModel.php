<?php

namespace app\models;

use PDO;

class SetupModel extends Connection
{
    public function testConnection(): bool
    {
        $pdo = $this->connect();
        // ping simples
        $pdo->query("SELECT 1");
        return true;
    }

    public function tableExists(string $table): bool
    {
        $pdo = $this->connect();

        // MySQL/MariaDB
        $sql = "SELECT 1
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = :t
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':t', $table, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function ensureDatabaseReady(): void
    {
        $this->testConnection();

        if (!$this->tableExists('users')) {
            $this->createUsersTable();
        } else {
            // se já existe, você pode evoluir aqui para checar colunas (migrations simples)
            // por enquanto, só garante que existe.
        }
    }

    private function createUsersTable(): void
    {
        $pdo = $this->connect();

        $sql = "
        CREATE TABLE users (
          id INT UNSIGNED NOT NULL AUTO_INCREMENT,
          username VARCHAR(50) NOT NULL,
          email VARCHAR(255) NOT NULL,
          password VARCHAR(255) NOT NULL,

          role VARCHAR(50) NOT NULL DEFAULT 'user',

          token CHAR(32) NULL,
          token_recuperacao CHAR(64) NULL,
          token_expiracao DATETIME NULL,

          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

          PRIMARY KEY (id),
          UNIQUE KEY uq_users_username (username),
          UNIQUE KEY uq_users_email (email),
          KEY idx_users_token (token),
          KEY idx_users_token_recuperacao (token_recuperacao),
          KEY idx_users_username_tokenrec (username, token_recuperacao)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $pdo->exec($sql);
    }

    public function createAdminIfNotExists(string $username, string $email, string $password, string $role = 'admin'): bool
    {
        $pdo = $this->connect();

        // verifica se já existe
        $check = $pdo->prepare("SELECT 1 FROM users WHERE username = :u OR email = :e LIMIT 1");
        $check->bindValue(':u', $username, PDO::PARAM_STR);
        $check->bindValue(':e', $email, PDO::PARAM_STR);
        $check->execute();

        if ($check->fetchColumn() !== false) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16)); // 32 chars

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, token, created_at)
            VALUES (:u, :e, :p, :r, :t, NOW())
        ");
        $stmt->bindValue(':u', $username, PDO::PARAM_STR);
        $stmt->bindValue(':e', $email, PDO::PARAM_STR);
        $stmt->bindValue(':p', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':r', $role, PDO::PARAM_STR);
        $stmt->bindValue(':t', $token, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }
}
