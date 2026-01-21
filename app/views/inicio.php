<?php include  'app/layout/header.php'; ?>
<h1>Bem-vindo à Página Inicial</h1>
<p>Esta é a página inicial do sistema.</p>
<?php if($_SESSION['user_name']): ?>
<p>Olá, <?= $_SESSION['user_name'] ?>. Você está autenticado.</p>
<?php if($userAut): ?>
    <p>Autenticado por Token</p>
    <?php endif;?>
<p>Você é um  <?= $_SESSION['role'] ?></p>


<?php if($_SESSION['role'] == "admin"): ?>
    <p> <a href="?router=Dashboard/index"> Ir para Dashboard</a></p>
    <?php endif;?>

<a href="?router=Sistema/logout">Logout</a>

<?php else: ?>
    <p><a href="?router=Sistema/login">Clique aqui para fazer login</a></p>
    <p><a href="?router=Sistema/cadastro">Clique aqui para fazer cadastro</a></p>

   


<?php endif;?>


<?php include 'app/layout/footer.php'; ?>