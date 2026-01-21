<?php include  'app/layout/header.php'; ?>

<style>
    body {
        background-color: #cacaca;
    }

    form {
        flex-direction: column;
        display: flex;
        align-items: center;
        background-color: #fff;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    label {
        color: #000;
    }

    input {
        color: #000;
    }
    button {
        background-color: #000;
        color: #fff;
    }
    button:hover {
        background-color: #fff;
        color: #000;
    }
    button:active {
        background-color: #fff;
        color: #000;
    }
    button:focus {
        background-color: #fff;
        color: #000;
    }
    p.esqueci_senha {
        text-align: center;
        margin-bottom: -30px;
    }
</style>
<body>
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh;">
        <form method="POST" action="?router=Sistema/gerarRecuperacao">
            <img width="150" class="text-center align-items-center mb-3" src="https://studiosilver.com.br/wp-content/uploads/2023/05/logo-studio-silver-new.png" alt="Logo Studio Silver">  
            <div class="mb-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" class="form-control" id="username" name="username">
            </div>
           
            <button type="submit" class="btn btn-primary mb-3">Enviar recuperação</button>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
           
        </form>
    </div>
    <?php include 'app/layout/footer.php'; ?>