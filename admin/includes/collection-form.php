<?php

declare(strict_types=1);

/** @var array $data */
/** @var array|null $collection */
/** @var string $formAction */
/** @var bool $isModal */

$isModal = $isModal ?? false;
$formAction = $formAction ?? admin_url('collections/form.php' . (!empty($collection['id']) ? '?id=' . (int) $collection['id'] : ''));
?>

<form method="post" enctype="multipart/form-data" action="<?= e($formAction) ?>" class="collection-form">
    <?= csrf_field() ?>

    <div class="form-grid">
        <div class="form-group">
            <label for="name">Nome</label>
            <input class="form-control" type="text" id="name" name="name" data-slug-source value="<?= e($data['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input class="form-control" type="text" id="slug" name="slug" data-slug-target value="<?= e($data['slug']) ?>">
        </div>
        <div class="form-group full">
            <label for="description">Descrição</label>
            <textarea class="form-textarea" id="description" name="description"><?= e($data['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="sort_order">Ordem</label>
            <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= (int) $data['sort_order'] ?>">
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_active" <?= !empty($data['is_active']) ? 'checked' : '' ?>> Coleção ativa</label>
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_featured" <?= !empty($data['is_featured']) ? 'checked' : '' ?>> Exibir em destaque</label>
        </div>
        <div class="form-group full">
            <label for="cover_image">Imagem de capa</label>
            <input class="form-control" type="file" id="cover_image" name="cover_image" accept="image/*">
            <?php if (!empty($data['cover_image'])): ?>
                <img src="<?= e(site_url($data['cover_image'])) ?>" alt="" class="image-preview">
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar coleção</button>
        <?php if ($isModal): ?>
            <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
        <?php else: ?>
            <a href="<?= e(admin_url('collections/index.php')) ?>" class="btn btn-outline">Cancelar</a>
        <?php endif; ?>
    </div>
</form>
