<?php include  'app/layout/header.php'; 

include  'app/layout/sidebar.php';

?>
<style>
    .section{
        margin-left:300px;
        margin-top: 30px;
    }
</style>
<div class="section">
<h1>Bem-vindo ao Dashboard</h1>
<p>Olá, <?= $_SESSION['user_name'] ?>. Você está autenticado.</p>
<?php if($userAut): ?>
    <p>Autenticado por Token</p>
    <?php endif;?>
<a href="?router=Sistema/logout">Logout</a>

</div>

<?php include 'app/layout/footer.php'; ?>