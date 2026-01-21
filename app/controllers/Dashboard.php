<?php

namespace app\controllers;

use app\models\User;
use app\models\Video;

class Dashboard
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?router=Sistema/login");
            exit;
        }

        // Teste autenticação com token
        $userModel = new User();
        $userAut = $userModel->checkUserToken($_SESSION['user_name'], $_SESSION['token']);
        if (!$userAut) {
            header("Location: ?router=Sistema/logout");
            exit;
        }

        require_once __DIR__ . '/../views/dashboard.php';
    }

    

   
}
