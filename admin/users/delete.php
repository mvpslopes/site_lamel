<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_require_root();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('users/index.php'));
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$currentUser = auth_user();

if ($id === (int) $currentUser['id']) {
    flash('error', 'Você não pode desativar o próprio usuário.');
    redirect(admin_url('users/index.php'));
}

$userRow = get_user($id);
if (!$userRow) {
    flash('error', 'Usuário não encontrado.');
    redirect(admin_url('users/index.php'));
}

$stmt = db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
$stmt->execute([$id]);

flash('success', 'Usuário desativado com sucesso.');
redirect(admin_url('users/index.php'));
