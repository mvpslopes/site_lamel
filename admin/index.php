<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

auth_guest();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Informe usuário e senha.';
    } elseif (!auth_login($username, $password)) {
        $error = 'Credenciais inválidas ou usuário inativo.';
    } else {
        redirect(admin_url('dashboard.php'));
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | LaMel Admin</title>
    <link rel="icon" type="image/png" href="<?= e(site_url('logo/logo_lamel_-3.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=5">
</head>
<body class="login-body">
    <div class="login-wrapper visible" id="login-content">
    <div class="login-card">
        <img src="<?= e(site_url('logo/logo_lamel_-2.png')) ?>" alt="LaMel" class="login-logo">
        <h1 class="login-title">Área Interna</h1>
        <p class="login-subtitle">Acesso restrito para gestão de produtos e coleções</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="" id="login-form">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="username">Usuário</label>
                <input class="form-control" type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label for="password">Senha</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Entrar</button>
        </form>

        <a href="<?= e(site_url()) ?>" class="btn-back-site login-back-btn">
            <span class="btn-back-icon">←</span> Voltar para o site
        </a>
    </div>
    </div>

    <script src="<?= e(admin_url('assets/js/admin.js')) ?>?v=5"></script>
</body>
</html>
