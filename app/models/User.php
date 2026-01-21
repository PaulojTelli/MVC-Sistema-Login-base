<?php 

namespace app\models;

use PDO;

class User extends Connection
{
    public function findUserByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $email, $password, $token)
    {
        // Verifica se o username ou email já existe
        if ($this->findUserByUsername($username) || $this->findUserByEmail($email)) {
            throw new \Exception("Usuário ou email já estão em uso.");
        }

        // Criptografa a senha
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insere o novo usuário no banco de dados
        $sql = "INSERT INTO users (username, email, password, token, created_at) VALUES (:username, :email, :password, :token, NOW())";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':token', $token);

        if (!$stmt->execute()) {
            throw new \Exception("Erro ao registrar o usuário. Por favor, tente novamente.");
        }

        return true;
    }

    public function findUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserEmailByUsername($user){
        $sql = "SELECT email FROM users WHERE username = :username";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':username', $user);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkUserToken($user, $token)
    {
        try {
            $sql = "SELECT 1 FROM users WHERE username = :username AND token = :token LIMIT 1";
            $stmt = $this->connect()->prepare($sql);
            $stmt->bindParam(':username', $user, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn() !== false;
        }
        catch (\PDOException $e) {
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erro ao verificar token do usuário '$user': " . $e->getMessage() . PHP_EOL;  
            $logFile = __DIR__ . 'app/logs/user.log'; 
            error_log($errorMessage, 3, $logFile);
            
            return false;
        }
    }
    public function checkUserTokenRecuperacao($user, $token)
    {
        try {
            // Seleciona o token e sua data de expiração
            $sql = "SELECT token_expiracao FROM users WHERE username = :username AND token_recuperacao = :token LIMIT 1";
            $stmt = $this->connect()->prepare($sql);
            $stmt->bindParam(':username', $user, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            // Busca a data de expiração do token
            $tokenExpiracao = $stmt->fetchColumn();

            // Verifica se o token foi encontrado e se a data de expiração é válida
            if ($tokenExpiracao !== false) {
                // Compara a data atual com a data de expiração
                if (new \DateTime() <= new \DateTime($tokenExpiracao)) {
                    return true; // O token é válido e ainda não expirou
                }
            }

            return false; // Token não encontrado ou expirado
        }
        catch (\PDOException $e) {
            // Log de erro
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erro ao verificar token do usuário '$user': " . $e->getMessage() . PHP_EOL;  
            $logFile = __DIR__ . '/app/logs/user.log'; 
            error_log($errorMessage, 3, $logFile);

            return false;
        }
    }


public function updateTokenRecuperacao($user, $tokenRecuperacao, $dataValidade){
    try {
        $sql = "UPDATE users SET token_recuperacao = :token_recuperacao, token_expiracao = :data_validade WHERE username = :username";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':token_recuperacao', $tokenRecuperacao, PDO::PARAM_STR);
        $stmt->bindParam(':data_validade', $dataValidade, PDO::PARAM_STR);
        $stmt->bindParam(':username', $user, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
    catch (\PDOException $e) {
        $errorMessage = "[". date('Y-m-d H:i:s'). "] Erro ao atualizar token de recuperação do usuário '$user': ". $e->getMessage(). PHP_EOL;  
        $logFile = __DIR__ . 'app/logs/user.log'; 
        error_log($errorMessage, 3, $logFile);
        
        return false;
    }
}

public function updatePassword($user, $hashedSenha){
    try {
        $sql = "UPDATE users SET password = :password WHERE username = :username";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':password', $hashedSenha, PDO::PARAM_STR);
        $stmt->bindParam(':username', $user, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
    catch (\PDOException $e) {
        $errorMessage = "[". date('Y-m-d H:i:s'). "] Erro ao atualizar senha do usuário '$user': ". $e->getMessage(). PHP_EOL;  
        $logFile = __DIR__. 'app/logs/user.log'; 
        error_log($errorMessage, 3, $logFile);
        
        return false;
    }
}

public function invalidateTokenRecuperacao($user, $token){
    try {
        $sql = "UPDATE users SET token_recuperacao = NULL WHERE username = :username AND token_recuperacao = :token";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':username', $user, PDO::PARAM_STR);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
    catch (\PDOException $e) {
        $errorMessage = "[". date('Y-m-d H:i:s'). "] Erro ao invalidar token do usuário '$user': ". $e->getMessage(). PHP_EOL;  
        $logFile = __DIR__. 'app/logs/user.log'; 
        error_log($errorMessage, 3, $logFile);
        
        return false;
    }
}

public function generateTokenRecuperacao($user){
    try {
        $token = bin2hex(random_bytes(16));
        $sql = "UPDATE users SET token_recuperacao = :token WHERE username = :username";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':username', $user, PDO::PARAM_STR);
        $stmt->execute();

        return $token;
    }
    catch (\PDOException $e) {
        $errorMessage = "[". date('Y-m-d H:i:s'). "] Erro ao gerar token de recuperação do usuário '$user': ". $e->getMessage(). PHP_EOL;  
        $logFile = __DIR__. 'app/logs/user.log'; 
        error_log($errorMessage, 3, $logFile);
        
        return false;
    }
}




}
