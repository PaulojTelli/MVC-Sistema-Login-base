<?php include 'app/layout/header.php'; ?>

<main class="container py-5">
  <div class="row align-items-center g-4">
    <div class="col-12 col-lg-7">
      <span class="badge bg-primary-subtle text-primary mb-3">Sistema de Acesso</span>
      <h1 class="display-5 fw-bold mb-3">Bem-vindo ao painel inicial</h1>
      <p class="lead text-muted mb-4">Gerencie contas, permissões e fluxo de acesso com segurança e praticidade.</p>

      <?php if (!empty($_SESSION['user_name'])): ?>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary" href="<?= env('APP_URL') ?>/Sistema/logout">
            <i class="bi bi-box-arrow-right me-2"></i>Sair
          </a>
          <?php if ($_SESSION['role'] == "admin"): ?>
            <a class="btn btn-outline-primary" href="<?= env('APP_URL') ?>/Dashboard/index">
              <i class="bi bi-speedometer2 me-2"></i>Ir para o Dashboard
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary" href="<?= env('APP_URL') ?>/Sistema/login">
            <i class="bi bi-person-check me-2"></i>Entrar
          </a>
          <a class="btn btn-outline-primary" href="<?= env('APP_URL') ?>/Sistema/cadastro">
            <i class="bi bi-person-plus me-2"></i>Criar conta
          </a>
          <a class="btn btn-link text-decoration-none" href="<?= env('APP_URL') ?>/setup">
            <i class="bi bi-gear me-1"></i>Primeiro setup
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <?php if (!empty($_SESSION['user_name'])): ?>
            <h5 class="card-title mb-3">Sessão ativa</h5>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                   style="width: 48px; height: 48px;">
                <i class="bi bi-person"></i>
              </div>
              <div>
                <div class="fw-semibold"><?= $_SESSION['user_name'] ?></div>
                <small class="text-muted">Perfil: <?= $_SESSION['role'] ?></small>
              </div>
            </div>
            <p class="text-muted mb-0">Use o menu acima para navegar pelos recursos do sistema.</p>
          <?php else: ?>
            <h5 class="card-title mb-3">Comece agora</h5>
            <p class="text-muted">Faça login para acessar o painel e gerenciar os recursos disponíveis.</p>
            <div class="d-grid gap-2">
              <a class="btn btn-primary" href="/Sistema/login">Acessar minha conta</a>
              <a class="btn btn-outline-secondary" href="/Sistema/cadastro">Não tenho cadastro</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</main>

<?php include 'app/layout/footer.php'; ?>
