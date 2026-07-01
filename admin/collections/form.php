<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isPartial = isset($_GET['partial']) && $_GET['partial'] === '1';
$collection = $id ? get_collection($id) : null;
$pageTitle = $collection ? 'Editar coleção' : 'Nova coleção';
$activeMenu = 'collections';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isPartial) {
    $openParam = $id ? 'edit&id=' . $id : 'new';
    redirect(admin_url('collections/index.php?open=' . $openParam));
}

$data = $collection ?: [
    'name' => '',
    'slug' => '',
    'description' => '',
    'is_active' => 1,
    'is_featured' => 0,
    'sort_order' => 0,
    'cover_image' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'cover_image' => $collection['cover_image'] ?? '',
    ];

    $errors = [];
    if ($data['name'] === '') $errors[] = 'Informe o nome da coleção.';
    if ($data['slug'] === '') $data['slug'] = slugify($data['name']);
    $data['slug'] = unique_slug('collections', slugify($data['slug']), $id ?: null);

    try {
        if (!empty($_FILES['cover_image']['name'])) {
            $uploaded = handle_image_upload($_FILES['cover_image'], 'colecoes/' . $data['name']);
            if ($uploaded) {
                if ($collection && $collection['cover_image']) {
                    delete_public_file($collection['cover_image']);
                }
                $data['cover_image'] = $uploaded;
            }
        }

        if (!$errors) {
            if ($collection) {
                $stmt = db()->prepare('UPDATE collections SET name = ?, slug = ?, description = ?, cover_image = ?, is_active = ?, is_featured = ?, sort_order = ? WHERE id = ?');
                $stmt->execute([
                    $data['name'], $data['slug'], $data['description'], $data['cover_image'] ?: null,
                    $data['is_active'], $data['is_featured'], $data['sort_order'], $id
                ]);
                flash('success', 'Coleção atualizada com sucesso.');
            } else {
                $stmt = db()->prepare('INSERT INTO collections (name, slug, description, cover_image, is_active, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $data['name'], $data['slug'], $data['description'], $data['cover_image'] ?: null,
                    $data['is_active'], $data['is_featured'], $data['sort_order']
                ]);
                flash('success', 'Coleção criada com sucesso.');
            }

            redirect(admin_url('collections/index.php'));
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }

    if (!empty($errors)) {
        flash('error', implode(' ', $errors));
        set_old($data);
        $openParam = $id ? 'edit&id=' . $id : 'new';
        redirect(admin_url('collections/index.php?open=' . $openParam));
    }
}

if (!empty($_SESSION['_old'])) {
    $data = array_merge($data, $_SESSION['_old']);
    clear_old();
}

$formAction = admin_url('collections/form.php' . ($id ? '?id=' . $id : ''));
$isModal = $isPartial;

if ($isPartial) {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/../includes/collection-form.php';
    exit;
}

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <?php require __DIR__ . '/../includes/collection-form.php'; ?>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
