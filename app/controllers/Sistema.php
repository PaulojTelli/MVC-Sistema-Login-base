<?php

namespace app\controllers;

use app\models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Sistema
{
    // Começo de login 
    public function login()
    {
        $error = null;

        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost:3000'; // Fallback para evitar undefined index

        // Se a sessão ainda NÃO foi iniciada, aí sim define os params e inicia
        if (session_status() === PHP_SESSION_NONE) {

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
        }

        // Se já estava ativa, não tenta mudar cookie params aqui.


        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Validação do token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Solicitação inválida.";
            } else {
                // Obter e sanitizar entradas
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');

                // Validação básica dos campos
                if (empty($username) || empty($password)) {
                    $error = "Por favor, preencha todos os campos.";
                } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                    $error = "Nome de usuário inválido.";
                } else {
                    // Implementação de limitação de tentativas de login
                    if ($this->isRateLimited($username)) {
                        $error = "Muitas tentativas de login. Por favor, tente novamente mais tarde.";
                    } else {
                        $userModel = new User();
                        $user = $userModel->findUserByUsername($username);

                        if ($user && password_verify($password, $user['password'])) {
                            // Autenticação bem-sucedida

                            // Regenerar ID da sessão para prevenir fixação de sessão
                            session_regenerate_id(true);

                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                            $_SESSION['role'] = htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8');
                            $_SESSION['token'] = $user['token'];

                            // Redirecionar para o dashboard
                            header("Location: ?router=Inicio/index");
                            exit;
                        } else {
                            // Autenticação falhou
                            $this->incrementLoginAttempts($username);
                            $error = 'Usuário ou senha incorretos.  <p class="esqueci_senha"><a href="?router=Sistema/gerarRecuperacao">Esqueci minha senha</a></p>';
                        }
                    }
                }
            }
        }

        // Gerar token CSRF para o formulário
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        require_once __DIR__ . '/../views/login.php';
    }

    /**
     * Verifica se o usuário atingiu o limite de tentativas de login.
     */
    private function isRateLimited($username)
    {
        // Implementação simples usando sessão
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        $attempts = $_SESSION['login_attempts'][$username]['count'] ?? 0;
        $lastAttempt = $_SESSION['login_attempts'][$username]['last'] ?? 0;
        $maxAttempts = 5;
        $lockoutTime = 15 * 60; // 15 minutos

        if ($attempts >= $maxAttempts) {
            if ((time() - $lastAttempt) < $lockoutTime) {
                return true;
            } else {
                // Resetar contagem após o período de bloqueio
                $_SESSION['login_attempts'][$username]['count'] = 0;
                $_SESSION['login_attempts'][$username]['last'] = 0;
                return false;
            }
        }

        return false;
    }

    /**
     * Incrementa o contador de tentativas de login.
     */
    private function incrementLoginAttempts($username)
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        if (!isset($_SESSION['login_attempts'][$username])) {
            $_SESSION['login_attempts'][$username] = ['count' => 0, 'last' => 0];
        }

        $_SESSION['login_attempts'][$username]['count'] += 1;
        $_SESSION['login_attempts'][$username]['last'] = time();
    }
    // Fim de login 

    // comeco de cadastro 
    public function cadastro()
    {
        $error = null;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $passwordconf = $_POST['passwordconf'] ?? null;
            $email = $_POST['email'] ?? null;
            // $isAdmin = isset($_POST['is_admin']) ? true : false;

            if (!$username || !$password || !$email) {
                $error = "Por favor, preencha todos os campos.";
            } elseif ($password !== $passwordconf) {
                $error = "Senhas não conferem.";
            } else {
                try {
                    // Gera um token único para o usuário
                    $token = bin2hex(random_bytes(16));

                    // Cria o usuário no banco de dados com o token
                    $userModel = new User();
                    $userModel->createUser($username, $email, $password, $token);

                    $this->enviarEmailCadastro($username, $email, $password);

                    // Redireciona para a página de login após o registro bem-sucedido
                    header("Location: ?router=Sistema/login");
                    exit;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        require_once __DIR__ . '/../views/cadastro.php';
    }

    private function enviarEmailCadastro($username, $email, $password)
    {
        // Carrega o autoloader do Composer
        require 'vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mailer@scgeomatica.com.br';
            $mail->Password   = '@Scgeomatica2024@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // ou PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = 465;

            // Remetente e destinatário
            $mail->setFrom('mailer@scgeomatica.com.br', 'SC Geomática');
            $mail->addAddress($email, $username);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Bem-vindo ao Suporte SC Geomática - Instruções de Acesso';



            $mail->Body    = '
                     <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Email Formulário Recebido</title>
                    </head>
                    <body style="font-family: Arial, sans-serif;">
                        <div style="background-color: #c7c4c4; padding: 20px; ">
                        <div style="background: #007bff; padding: 10px; color: white; text-align: center; border-radius: 10px;">
                            <br><br>
                            <h1>Bem vindo(a) ao Suporte SC Geomática!</h1>
                            <p>Confira abaixo as suas instruções de acesso</p>
                            <br><br>
                        </div>
                        <div style="background-color: white; padding: 20px; margin-top: 10px; border-radius: 10px;">
                        <p>Acesse o suporte através do link: <a href="#"> link de acesso</a></p>
                        <br>
                        <p>E entre com as suas credenciais</p>
                            <strong>Usuário:</strong> ' . $username . '<br>
                            <strong>Senha:</strong>' . $password . '<br>
                        
                            <br>
                            <p>Quaisquer dúvidas entre em contato conosco através do:</p>
                            <p> <a href="https://scgeomatica.com.br/fale-conosco/">Fale conosco:</a></p>
                            
                        </div>
                        <div style="margin-top: 20px; text-align: center; font-size: 0.8em; color: #777;">
                            <p><b>AVISO:</b><br> <br>
                            Esta campanha está sendo gerenciada por Studio Silver - Marketing Digital - Consulte nossos serviços em <a href="https://www.studiosilver.com.br">www.studiosilver.com.br</a></p>
                            <img style="margin-left: 3px; transform: translateY(-2.5px);"
                            src="https://studiosilver.com.br/lib/logo-studiosilver.png" alt="Logo Studio Silver">
                        </div>
                        </div>
                    </body>
                    </html>
                ';

            $mail->AltBody = "
                                Bem vindo ao suporte SC Geomática \n
                                Siga abaixo as intruçoes de acesso \n
                                Acesse o suporte através do link: link de acesso \n
                                E entre com as suas credenciais \n
                                Usuário: $username \n
                                Senha: $password \n
                                Quaisquer dúvidas entre em contato conosco através do: \n
                                https://scgeomatica.com.br/fale-conosco/ \n
                ";

            $mail->send();
        } catch (Exception $e) {
            // Tratar erros de envio
            error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
            // Opcional: Definir uma mensagem de erro para o usuário
        }
    }

    // fim de cadastro 

    public function recuperarSenha()
    {
        // // Instanciar o model de usuário
        $userModel = new User();

        // Obter parâmetros GET
        $user = isset($_GET['user']) ? $_GET['user'] : null;
        $token = isset($_GET['token']) ? $_GET['token'] : null;

        // Verificar se usuário e token estão presentes
        if (!$user || !$token) {
            header("Location: ?router=Sistema/logout");
            exit;
        }

        // Verificar a validade do usuário e do token
        $userAut = $userModel->checkUserTokenRecuperacao($user, $token);

        if (!$userAut) {
            // Se inválido, redireciona para logout
            header("Location: ?router=Sistema/logout");
            exit;
        } else {
            // Inicializar variáveis para a view
            $erro = '';
            $sucesso = '';

            // Verifica se o formulário foi submetido
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Captura a nova senha do formulário
                $novaSenha = isset($_POST['nova_senha']) ? trim($_POST['nova_senha']) : '';
                $confirmarSenha = isset($_POST['confirmar_senha']) ? trim($_POST['confirmar_senha']) : '';

                // Validações básicas
                if (empty($novaSenha) || empty($confirmarSenha)) {
                    $erro = "Ambos os campos de senha são obrigatórios.";
                } elseif ($novaSenha !== $confirmarSenha) {
                    $erro = "As senhas não coincidem.";
                } elseif (strlen($novaSenha) < 6) { // Exemplo de validação de complexidade
                    $erro = "A senha deve ter pelo menos 6 caracteres.";
                } else {
                    // Opcional: Hash da senha antes de salvar
                    $hashedSenha = password_hash($novaSenha, PASSWORD_BCRYPT);

                    // Atualiza a senha no banco de dados
                    $updateSuccess = $userModel->updatePassword($user, $hashedSenha);

                    if ($updateSuccess) {
                        // Senha atualizada com sucesso, redireciona ou exibe mensagem
                        $sucesso = "Senha atualizada com sucesso. <a href='?router=Sistema/login'>Clique aqui para fazer login.</a>";
                        // Opcional: Invalida o token após a atualização
                        $userModel->invalidateTokenRecuperacao($user, $token);
                    } else {
                        // Falha ao atualizar a senha, exibe erro
                        $erro = "Erro ao atualizar a senha. Por favor, tente novamente.";
                    }
                }
            }

            // Carrega a view e passa as variáveis de erro/sucesso
            require_once __DIR__ . '/../views/recuperar-senha.php';
        }
    }



    public function gerarRecuperacao()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $user = trim($_POST['username'] ?? '');

            // Verifica se o campo de usuário não está vazio
            if (empty($user)) {
                // Redireciona ou exibe uma mensagem de erro
                echo "O campo de usuário é obrigatório.";
                exit;
            }

            // Instanciar o model de usuário
            $userModel = new User();
            $email = $userModel->getUserEmailByUsername($user);

            // Verifica se o email foi encontrado
            if (!$email) {
                // Evite revelar se o usuário existe ou não por razões de segurança

                exit;
            }

            // Gerar token de recuperação de forma segura
            try {
                $tokenRecuperacao = bin2hex(random_bytes(32));
                echo "Token gerado <br><br> ";
            } catch (Exception $e) {
                // Em caso de erro na geração do token
                echo "Erro ao gerar token de recuperação. Tente novamente.";
                exit;
            }

            $dataValidade = date('Y-m-d H:i:s', strtotime('+1 day'));
            $userModel->updateTokenRecuperacao($user, $tokenRecuperacao, $dataValidade);
            echo "Token atualizado <br><br> ";

            // Defina a URL base do seu site
            $baseUrl = "localhost:3000"; // Substitua pela URL real do seu site
            $linkRecuperacao = $baseUrl . "/?router=Sistema/recuperarSenha&user=" . urlencode($user) . "&token=" . urlencode($tokenRecuperacao);
            echo "link gerado: " . $linkRecuperacao . " <br><br> ";

            // Configurar o PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mailer@scgeomatica.com.br';
                $mail->Password   = '@Scgeomatica2024@';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // TLS ou SSL
                $mail->Port       = 465;

                // Remetente e destinatário
                $mail->setFrom('mailer@scgeomatica.com.br', 'SC Geomática');
                $mail->addAddress($email, $user);

                // Conteúdo do e-mail
                $mail->isHTML(true);
                $mail->Subject = 'Recuperação de Senha - Suporte SC Geomática';
                $mail->Body    = "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Recuperação de Senha</title>
                </head>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='background-color: #c7c4c4; padding: 20px;'>
                        <div style='background: #007bff; padding: 10px; color: white; text-align: center; border-radius: 10px;'>
                            <h1>Recuperar Senha - SC Geomática</h1>
                            <p>Siga as instruções abaixo para recuperar sua senha.</p>
                        </div>
                        <div style='background-color: white; padding: 20px; margin-top: 10px; border-radius: 10px;'>
                            <p>Olá, <strong>{$user}</strong>!</p>
                            <p>Você solicitou a recuperação da sua senha. Clique no link abaixo para redefini-la:</p>
                            <p><a href='{$linkRecuperacao}'>Redefinir Senha</a></p>
                            <p>Se você não solicitou essa recuperação, por favor, ignore este e-mail.</p>
                            <hr>
                            <p>Quaisquer dúvidas, entre em contato conosco através do <a href='https://scgeomatica.com.br/fale-conosco/'>Fale Conosco</a>.</p>
                        </div>
                        <div style='margin-top: 20px; text-align: center; font-size: 0.8em; color: #777;'>
                            <p><strong>AVISO:</strong><br>
                            Esta campanha está sendo gerenciada por Studio Silver - Marketing Digital. Consulte nossos serviços em <a href='https://www.studiosilver.com.br'>www.studiosilver.com.br</a>.</p>
                            <img style='margin-left: 3px; transform: translateY(-2.5px);' src='https://studiosilver.com.br/lib/logo-studiosilver.png' alt='Logo Studio Silver'>
                        </div>
                    </div>
                </body>
                </html>
            ";
                $mail->AltBody = "Olá, {$user}. Você solicitou a recuperação da sua senha. Use o link para redefinir: {$linkRecuperacao}";

                // Enviar o e-mail
                $mail->send();
                echo 'E-mail de recuperação de senha enviado com sucesso!';
            } catch (Exception $e) {
                // Em produção, evite exibir erros detalhados para o usuário
                // Utilize logs para registrar o erro
                error_log("Erro ao enviar e-mail de recuperação para {$email}: {$mail->ErrorInfo}");
                echo "Erro ao enviar e-mail de recuperação de senha. Tente novamente mais tarde.";
            }
        }

        // Carregar a view da página de esqueci a senha
        require_once __DIR__ . '/../views/esqueci-senha.php';
    }


    public function logout()
    {
        // Garante que a sessão exista
        if (session_status() === PHP_SESSION_ACTIVE) {

            // Limpa todas as variáveis da sessão
            $_SESSION = [];

            // Apaga o cookie de sessão no browser
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            // Destrói a sessão no servidor
            session_destroy();
        }

        header("Location: /");
        exit;
    }
}
