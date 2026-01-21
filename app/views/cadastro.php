<?php include  'app/layout/header.php'; ?>




<style>
    body {
        background-color: #cacaca;
        font-family: Arial, sans-serif;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #fff;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 500px;
        margin: auto;
    }

    label {
        color: #000;
        margin-bottom: 5px;
        width: 100%;
    }

    input {
        color: #000;
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    button {
        background-color: #000;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 70%;
    }

    button:hover {
        background-color: #fff;
        color: #000;
        border: 1px solid #000;
    }

    .error {
        color: red;
        font-size: 14px;
        margin-bottom: 20px;
    }
</style>

    <div class="d-flex justify-content-center align-items-center" style="height: 100dvh;">
        <form method="POST" action="?router=Sistema/cadastro">
            <h2>Registrar cliente</h2>
            <div class="mt-2">
       
                <label for="username" class="form-label">Empresa</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="">
                <label for="passwordconf" class="form-label">Confrimar Senha</label>
                <input type="password" class="form-control" id="passwordconf" name="passwordconf" required>
            </div>
            <div class="">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    </div>

    <?php include 'app/layout/footer.php'; ?>