<?php

declare(strict_types=1);

/** @var array $data */
/** @var array|null $product */
/** @var array $images */
/** @var array $collections */
/** @var string $formAction */
/** @var bool $isModal */

$isModal = $isModal ?? false;
$formAction = $formAction ?? admin_url('products/form.php' . (!empty($product['id']) ? '?id=' . (int) $product['id'] : ''));
?>

<form method="post" enctype="multipart/form-data" action="<?= e($formAction) ?>" class="product-form" data-product-form>
    <?= csrf_field() ?>
    <?php if ($isModal): ?>
        <input type="hidden" name="from_modal" value="1">
    <?php endif; ?>

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
            <label for="price">Preço</label>
            <input class="form-control" type="text" id="price" name="price" value="<?= e((string) $data['price']) ?>" required>
        </div>
        <div class="form-group">
            <label for="size_info">Tamanho</label>
            <input class="form-control" type="text" id="size_info" name="size_info" value="<?= e($data['size_info']) ?>">
        </div>
        <div class="form-group">
            <label for="collection_id">Coleção</label>
            <select class="form-select" id="collection_id" name="collection_id">
                <option value="">Sem coleção</option>
                <?php foreach ($collections as $collection): ?>
                    <option value="<?= (int) $collection['id'] ?>" <?= (string) $data['collection_id'] === (string) $collection['id'] ? 'selected' : '' ?>>
                        <?= e($collection['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="badge">Badge</label>
            <select class="form-select" id="badge" name="badge">
                <?php foreach (badge_options() as $option): ?>
                    <option value="<?= e($option) ?>" <?= ($data['badge'] ?? '') === $option ? 'selected' : '' ?>><?= e($option ?: 'Nenhum') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sort_order">Ordem</label>
            <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= (int) $data['sort_order'] ?>">
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_active" <?= !empty($data['is_active']) ? 'checked' : '' ?>> Produto ativo</label>
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_featured" <?= !empty($data['is_featured']) ? 'checked' : '' ?>> Exibir em destaque</label>
        </div>
        <div class="form-group full">
            <label for="main_image">Imagem principal</label>
            <div class="image-upload-field">
                <div class="image-preview-box<?= !empty($data['main_image']) ? ' has-image' : '' ?>" id="main-image-preview-box">
                    <?php if (!empty($data['main_image'])): ?>
                        <img src="<?= e(site_url($data['main_image'])) ?>" alt="Preview do produto" class="image-preview-main">
                    <?php else: ?>
                        <div class="image-preview-placeholder">
                            <span>Nenhuma imagem selecionada</span>
                        </div>
                    <?php endif; ?>
                </div>
                <input class="form-control" type="file" id="main_image" name="main_image" accept="image/*" data-preview-target="main-image-preview-box" <?= $product ? '' : 'required' ?>>
            </div>
        </div>
        <div class="form-group full">
            <label for="gallery_images">Imagens adicionais</label>
            <div class="image-preview-list" id="gallery-preview-list">
                <?php if ($images): ?>
                    <?php foreach ($images as $image): ?>
                        <img src="<?= e(site_url($image['image_path'])) ?>" alt="Imagem do produto" class="image-preview-existing">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <input class="form-control" type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple data-preview-list-target="gallery-preview-list">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar produto</button>
        <?php if ($isModal): ?>
            <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
        <?php else: ?>
            <a href="<?= e(admin_url('products/index.php')) ?>" class="btn btn-outline">Cancelar</a>
        <?php endif; ?>
    </div>
</form>
