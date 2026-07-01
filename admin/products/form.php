<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isPartial = isset($_GET['partial']) && $_GET['partial'] === '1';
$product = $id ? get_product($id) : null;
$images = $product ? get_product_images($id) : [];
$collections = get_collections();
$pageTitle = $product ? 'Editar produto' : 'Novo produto';
$activeMenu = 'products';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isPartial) {
    $openParam = $id ? 'edit&id=' . $id : 'new';
    redirect(admin_url('products/index.php?open=' . $openParam));
}

$data = $product ?: [
    'name' => '',
    'slug' => '',
    'description' => '',
    'price' => '',
    'size_info' => '',
    'badge' => '',
    'collection_id' => '',
    'is_active' => 1,
    'is_featured' => 0,
    'sort_order' => 0,
    'main_image' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => str_replace(',', '.', trim($_POST['price'] ?? '0')),
        'size_info' => trim($_POST['size_info'] ?? ''),
        'badge' => trim($_POST['badge'] ?? ''),
        'collection_id' => $_POST['collection_id'] !== '' ? (int) $_POST['collection_id'] : null,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'main_image' => $product['main_image'] ?? '',
    ];

    $errors = [];
    if ($data['name'] === '') $errors[] = 'Informe o nome do produto.';
    if ($data['price'] === '' || !is_numeric($data['price'])) $errors[] = 'Informe um preço válido.';
    if ($data['slug'] === '') $data['slug'] = slugify($data['name']);
    $data['slug'] = unique_slug('products', slugify($data['slug']), $id ?: null);

    try {
        if (!$product && empty($_FILES['main_image']['name'])) {
            $errors[] = 'Envie a imagem principal do produto.';
        }

        if (!$errors) {
            if (!empty($_FILES['main_image']['name'])) {
                $uploaded = handle_image_upload($_FILES['main_image'], $data['name']);
                if ($uploaded) {
                    if ($product && $product['main_image']) {
                        delete_public_file($product['main_image']);
                    }
                    $data['main_image'] = $uploaded;
                }
            }

            if ($product) {
                $stmt = db()->prepare('UPDATE products SET collection_id = ?, name = ?, slug = ?, description = ?, price = ?, size_info = ?, badge = ?, main_image = ?, is_active = ?, is_featured = ?, sort_order = ? WHERE id = ?');
                $stmt->execute([
                    $data['collection_id'], $data['name'], $data['slug'], $data['description'], $data['price'],
                    $data['size_info'], $data['badge'] ?: null, $data['main_image'], $data['is_active'],
                    $data['is_featured'], $data['sort_order'], $id
                ]);
                $productId = $id;
                flash('success', 'Produto atualizado com sucesso.');
            } else {
                $stmt = db()->prepare('INSERT INTO products (collection_id, name, slug, description, price, size_info, badge, main_image, is_active, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $data['collection_id'], $data['name'], $data['slug'], $data['description'], $data['price'],
                    $data['size_info'], $data['badge'] ?: null, $data['main_image'], $data['is_active'],
                    $data['is_featured'], $data['sort_order']
                ]);
                $productId = (int) db()->lastInsertId();
                flash('success', 'Produto criado com sucesso.');
            }

            if (!empty($_FILES['gallery_images']['name'][0])) {
                $galleryCount = count($_FILES['gallery_images']['name']);
                $sort = count(get_product_images($productId));

                for ($i = 0; $i < $galleryCount; $i++) {
                    $file = [
                        'name' => $_FILES['gallery_images']['name'][$i],
                        'type' => $_FILES['gallery_images']['type'][$i],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                        'error' => $_FILES['gallery_images']['error'][$i],
                        'size' => $_FILES['gallery_images']['size'][$i],
                    ];

                    if ($file['error'] === UPLOAD_ERR_NO_FILE) continue;

                    $path = handle_image_upload($file, $data['name']);
                    if ($path) {
                        $sort++;
                        $imgStmt = db()->prepare('INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)');
                        $imgStmt->execute([$productId, $path, $sort]);
                    }
                }
            }

            redirect(admin_url('products/index.php'));
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        set_old($data);
        $openParam = $id ? 'edit&id=' . $id : 'new';
        redirect(admin_url('products/index.php?open=' . $openParam));
    }
}

if (!empty($_SESSION['_old'])) {
    $data = array_merge($data, $_SESSION['_old']);
    clear_old();
}

$formAction = admin_url('products/form.php' . ($id ? '?id=' . $id : ''));
$isModal = $isPartial;

if ($isPartial) {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/../includes/product-form.php';
    exit;
}

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <?php require __DIR__ . '/../includes/product-form.php'; ?>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
