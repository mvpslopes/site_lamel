<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeMenu */

$user = auth_user();
$success = flash('success');
$error = flash('error');
$showSplash = !empty($_SESSION['show_admin_splash']);
if ($showSplash) {
    unset($_SESSION['show_admin_splash']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | LaMel Admin</title>
    <link rel="icon" type="image/png" href="<?= e(site_url('logo/logo_lamel_-3.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=9">
</head>
<body class="admin-body">
    <?php if ($showSplash): ?>
    <div id="admin-splash" class="admin-splash">
        <div class="admin-splash-content">
            <img src="<?= e(site_url('logo/logo_lamel_-2.png')) ?>" alt="LaMel" class="admin-splash-logo">
            <p class="admin-splash-percent" id="admin-splash-percent">0%</p>
            <div class="admin-splash-bar">
                <div class="admin-splash-fill" id="admin-splash-fill"></div>
            </div>
            <p class="admin-splash-label">Carregando painel interno...</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-layout<?= $showSplash ? ' panel-loading' : ' visible' ?>" id="admin-panel-content">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-inner">
                    <img src="<?= e(site_url('logo/logo_lamel_-2.png')) ?>" alt="LaMel" class="sidebar-logo">
                    <span class="sidebar-badge">Painel Interno</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= e(admin_url('dashboard.php')) ?>" class="nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon">◆</span> Dashboard
                </a>
                <a href="<?= e(admin_url('products/index.php')) ?>" class="nav-item <?= ($activeMenu ?? '') === 'products' ? 'active' : '' ?>">
                    <span class="nav-icon">◇</span> Produtos
                </a>
                <a href="<?= e(admin_url('collections/index.php')) ?>" class="nav-item <?= ($activeMenu ?? '') === 'collections' ? 'active' : '' ?>">
                    <span class="nav-icon">◈</span> Coleções
                </a>
                <?php if (auth_is_root()): ?>
                <a href="<?= e(admin_url('users/index.php')) ?>" class="nav-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                    <span class="nav-icon">◎</span> Usuários
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
                    <div>
                        <strong><?= e($user['full_name']) ?></strong>
                        <span class="user-role"><?= e(role_label($user['role'])) ?></span>
                    </div>
                </div>
                <a href="<?= e(admin_url('logout.php')) ?>" class="sidebar-link logout">Sair</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="topbar-kicker">LaMel</p>
                    <h1 class="topbar-title"><?= e($pageTitle) ?></h1>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>
