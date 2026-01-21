<?php include 'app/layout/header.php'; ?>

<style>
  body { background-color: #cacaca; }

  .auth-card{
    width: 100%;
    max-width: 420px;
    background: #fff;
    padding: 42px 38px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,.1);
  }

  .auth-card label,
  .auth-card input { color:#000; }

  .auth-btn{
    background-color:#000 !important;
    border-color:#000 !important;
    color:#fff !important;
  }
  .auth-btn:hover,
  .auth-btn:active,
  .auth-btn:focus{
    background-color:#fff !important;
    color:#000 !important;
    border-color:#000 !important;
  }

  .auth-logo{
    width:150px;
    display:block;
    margin:0 auto 18px auto;
  }

  .alert{
    border-radius: 10px;
  }
</style>

<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; padding: 20px;">
  <div class="auth-card">

    <img class="auth-logo" src="https://studiosilver.com.br/wp-content/uploads/2023/05/logo-studio-silver-new.png" alt="Logo Studio Silver">

    <h5 class="text-center mb-3">Recuperar acesso</h5>
    <p class="text-center text-muted mb-4" style="font-size: 0.95rem;">
      Informe seu usuário para receber o link de redefinição por e-mail.
    </p>

    <?php if (!empty($erro)): ?>
      <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <div style="font-size: 18px; line-height: 1;">⚠️</div>
        <div><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
      <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
        <div style="font-size: 18px; line-height: 1;">✅</div>
        <div><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8'); ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="?router=Sistema/gerarRecuperacao" novalidate>
      <?php if (!empty($_SESSION['csrf_token'])): ?>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label for="username" class="form-label">Usuário</label>
        <input
          type="text"
          class="form-control"
          id="username"
          name="username"
          required
          autocomplete="username"
        >
      </div>

      <button type="submit" class="btn auth-btn w-100 mb-2">Enviar recuperação</button>

      <div class="text-center mt-3">
        <a href="?router=Sistema/login" style="text-decoration:none;">Voltar para login</a>
      </div>
    </form>

  </div>
</div>

<?php include 'app/layout/footer.php'; ?>
