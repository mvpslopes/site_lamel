<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_url('products/index.php'));
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$product = $id ? get_product($id) : null;

if (!$product) {
    flash('error', 'Produto não encontrado.');
    redirect(admin_url('products/index.php'));
}

$images = get_product_images($id);
foreach ($images as $image) {
    delete_public_file($image['image_path']);
}

$videos = get_product_videos($id);
foreach ($videos as $video) {
    delete_public_file($video['video_path']);
}

delete_public_file($product['main_image']);

$stmt = db()->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

flash('success', 'Produto excluído com sucesso.');
redirect(admin_url('products/index.php'));
