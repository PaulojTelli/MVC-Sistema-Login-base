<?php

namespace app\controllers;

use app\models\SetupModel;

class Setup
{
    public function index()
    {
        $setup = new SetupModel();

        $status = [
            'db_ok'        => false,
            'users_exists' => false,
            'errors'       => [],
        ];

        try {
            $status['db_ok'] = $setup->testConnection();
            if ($status['db_ok']) {
                $status['users_exists'] = $setup->tableExists('users');
            }
        } catch (\Throwable $e) {
            $status['errors'][] = $e->getMessage();
        }

        // View simples (pode trocar por view MVC depois)
        header('Content-Type: text/html; charset=utf-8');

        echo "<h2>Setup inicial</h2>";

        echo "<p>DB: " . ($status['db_ok'] ? "OK" : "FALHOU") . "</p>";
        echo "<p>Tabela users: " . ($status['users_exists'] ? "EXISTE" : "NÃO EXISTE") . "</p>";

        if (!empty($status['errors'])) {
            echo "<pre style='color:#b00'>" . htmlspecialchars(implode("\n", $status['errors'])) . "</pre>";
        }

        echo "<hr>";
        echo "<p><a href='?router=Setup/run'>Rodar setup (criar estrutura)</a></p>";
        echo "<p><a href='?router=Setup/createAdmin'>Criar admin inicial</a></p>";

        echo "<hr>";
        echo "<small>Depois de configurar, remova/restrinja este controller em produção.</small>";
    }

    public function run()
    {
        $setup = new SetupModel();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $setup->ensureDatabaseReady();

            echo json_encode([
                'ok' => true,
                'message' => 'Setup executado. Estrutura verificada/criada com sucesso.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    /**
     * Cria um admin inicial se não existir.
     * Ajuste os dados abaixo e rode 1x.
     */
    public function createAdmin()
    {
        $setup = new SetupModel();

        // >>>> ALTERE AQUI <<<<
        $username = 'Paulo';
        $email    = 'paulo@studiosilver.com.br';
        $password = '@Sucesso2023@';
        $role     = 'admin';
        // >>>> ALTERE AQUI <<<<

        header('Content-Type: application/json; charset=utf-8');

        try {
            $setup->ensureDatabaseReady();
            $created = $setup->createAdminIfNotExists($username, $email, $password, $role);

            echo json_encode([
                'ok' => true,
                'created' => $created,
                'message' => $created
                    ? 'Admin criado com sucesso.'
                    : 'Admin já existia, nada a fazer.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        exit;
    }
}
