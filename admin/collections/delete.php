<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('collections/index.php'));
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$collection = $id ? get_collection($id) : null;

if (!$collection) {
    flash('error', 'Coleção não encontrada.');
    redirect(admin_url('collections/index.php'));
}

delete_public_file($collection['cover_image']);

$stmt = db()->prepare('DELETE FROM collections WHERE id = ?');
$stmt->execute([$id]);

flash('success', 'Coleção excluída com sucesso.');
redirect(admin_url('collections/index.php'));
