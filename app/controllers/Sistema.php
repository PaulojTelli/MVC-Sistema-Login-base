<?php

namespace app\controllers;

use app\models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Sistema
{

    public function index()
    {
        header("Location: " . env('APP_URL') . "/Inicio/index");
        exit;
    }

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
                            header("Location: " . env('APP_URL') . "/Inicio/index");
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
                    header("Location: " . env('APP_URL') . "/Sistema/login");
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
            $mail->Host       = env('SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('SMTP_USER');
            $mail->Password   = env('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = env('SMTP_PORT');

            // Remetente e destinatário
            $mail->setFrom(env('SMTP_USER'), env('APP_NAME'));
            $mail->addAddress($email, $username);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Bem-vindo ao Suporte '.env('APP_NAME').' - Instruções de Acesso';



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
            header("Location: " . env('APP_URL') . "/Sistema/logout");
            exit;
        }

        // Verificar a validade do usuário e do token
        $userAut = $userModel->checkUserTokenRecuperacao($user, $token);

        if (!$userAut) {
            // Se inválido, redireciona para logout
            header("Location: " . env('APP_URL') . "/Sistema/logout");
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
                        $sucesso = "Senha atualizada com sucesso. <a href=' <?= env('APP_URL') ?> Sistema/login'>Clique aqui para fazer login.</a>";
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
        $erro = '';
        $sucesso = '';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $user = trim($_POST['username'] ?? '');

            if (empty($user)) {
                $erro = "Informe seu usuário para receber o link de recuperação.";
            } else {

                $userModel = new User();
                $email = $userModel->getUserEmailByUsername($user);

                // Se seu model retornar array por algum motivo:
                if (is_array($email)) {
                    $email = $email['email'] ?? null;
                }

                // Mensagem genérica SEM entregar se existe ou não (boa prática)
                $msgGenerica = "Se o usuário existir, você receberá um e-mail com instruções para redefinir a senha.";

                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $sucesso = $msgGenerica;
                } else {

                    try {
                        $tokenRecuperacao = bin2hex(random_bytes(32));
                        $dataValidade = date('Y-m-d H:i:s', strtotime('+1 day'));
                        $userModel->updateTokenRecuperacao($user, $tokenRecuperacao, $dataValidade);

                        $linkRecuperacao = env('APP_URL')
                            . "" . env('APP_URL') . "/Sistema/recuperarSenha"
                            . "&user=" . urlencode($user)
                            . "&token=" . urlencode($tokenRecuperacao);

                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                        // SMTP
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.hostinger.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'mailer@scgeomatica.com.br';
                        $mail->Password   = '@Scgeomatica2024@';
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;

                        $mail->setFrom('mailer@scgeomatica.com.br', 'SC Geomática');
                        $mail->addAddress($email, $user);

                        $mail->isHTML(true);
                        $mail->Subject = 'Recuperação de Senha - Suporte SC Geomática';

                        $mail->Body = "
                        <div style='font-family: Arial, sans-serif;'>
                            <h2>Recuperação de senha</h2>
                            <p>Olá, <strong>{$user}</strong>.</p>
                            <p>Clique no botão abaixo para redefinir sua senha:</p>
                            <p>
                              <a href='{$linkRecuperacao}'
                                 style='display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:12px 16px;border-radius:10px;'>
                                 Redefinir senha
                              </a>
                            </p>
                            <p style='color:#666;font-size:12px;'>Se você não solicitou, ignore este e-mail.</p>
                        </div>
                    ";

                        $mail->AltBody = "Olá, {$user}. Redefina sua senha: {$linkRecuperacao}";

                        $mail->send();

                        // feedback bonito e “limpo”
                        $sucesso = $msgGenerica;
                    } catch (\Throwable $e) {
                        error_log("Erro recuperação senha: " . $e->getMessage());
                        $erro = "Não foi possível enviar o e-mail agora. Tente novamente em alguns minutos.";
                    }
                }
            }
        }

        // A view vai usar $erro e $sucesso
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
