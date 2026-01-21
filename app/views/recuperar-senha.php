<?php include  'app/layout/header.php'; ?>
   <style>
        /* Estilos básicos para o formulário */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 21dvh;
        }
        .erro {
            color: red;
            margin-bottom: 15px;
        }
        .sucesso {
            color: green;
            margin-bottom: 15px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        .navbar{
            display: none;
        }
    </style>

<div class="container">
    <h2>Recuperar Senha</h2>

    <?php if (!empty($erro)) : ?>
        <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <?php if (!empty($sucesso)) : ?>
        <div class="sucesso"><?php echo $sucesso; ?></div>
    <?php else : ?>
        <?php
            // Obter os parâmetros GET atuais para incluí-los na action do formulário
            $currentUser = isset($_GET['user']) ? urlencode($_GET['user']) : '';
            $currentToken = isset($_GET['token']) ? urlencode($_GET['token']) : '';
            $actionUrl = "?router=Usuario/recuperarSenha&user={$currentUser}&token={$currentToken}";
        ?>
        <form method="POST" action="">
            <label for="nova_senha">Nova Senha:</label><br>
            <input type="password" id="nova_senha" name="nova_senha" required><br>

            <label for="confirmar_senha">Confirmar Nova Senha:</label><br>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required><br>

            <button type="submit">Atualizar Senha</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'app/layout/footer.php'; ?>