<?php

declare(strict_types=1);

/** @var array $data */
/** @var array|null $product */
/** @var array $images */
/** @var array $videos */
/** @var array $collections */
/** @var string $formAction */
/** @var bool $isModal */

$isModal = $isModal ?? false;
$videos = $videos ?? [];
$formAction = $formAction ?? admin_url('products/form.php' . (!empty($product['id']) ? '?id=' . (int) $product['id'] : ''));
$selectedSizes = decode_product_sizes($data['available_sizes'] ?? null);
if (!$selectedSizes && !empty($data['available_sizes']) && is_array($data['available_sizes'])) {
    $selectedSizes = $data['available_sizes'];
}
$sizeType = $data['size_type'] ?? 'none';
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
        <div class="form-group full size-section">
            <div class="size-section-header">
                <label for="size_type">Tamanhos do produto</label>
                <p class="form-hint">Escolha a categoria e marque quais tamanhos este produto possui em estoque.</p>
            </div>

            <div class="form-group">
                <label for="size_type">Categoria de tamanho</label>
                <select class="form-select" id="size_type" name="size_type" data-size-type-select>
                    <?php foreach (size_type_options() as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $sizeType === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group size-options-panel<?= $sizeType === 'clothing' ? '' : ' hidden' ?>" data-size-panel="clothing">
                <label>Tamanhos de roupa disponíveis</label>
                <div class="size-chip-grid" data-size-field>
                    <?php foreach (clothing_size_options() as $option): ?>
                        <label class="size-chip">
                            <input type="checkbox" name="sizes[]" value="<?= e($option) ?>" <?= in_array($option, $selectedSizes, true) ? 'checked' : '' ?>>
                            <span><?= e($option) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="size-field-actions">
                    <button type="button" class="btn btn-outline btn-sm" data-size-select-all>Selecionar todos</button>
                    <button type="button" class="btn btn-outline btn-sm" data-size-clear>Limpar seleção</button>
                    <span class="size-selected-count" data-size-count></span>
                </div>
                <p class="form-hint">Clique nos tamanhos para marcar ou desmarcar.</p>
            </div>

            <div class="form-group size-options-panel<?= $sizeType === 'footwear' ? '' : ' hidden' ?>" data-size-panel="footwear">
                <label>Tamanhos de calçado disponíveis (34 ao 56)</label>
                <div class="size-chip-grid size-chip-grid-compact" data-size-field>
                    <?php foreach (footwear_size_options() as $option): ?>
                        <label class="size-chip">
                            <input type="checkbox" name="sizes[]" value="<?= e($option) ?>" <?= in_array($option, $selectedSizes, true) ? 'checked' : '' ?>>
                            <span><?= e($option) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="size-field-actions">
                    <button type="button" class="btn btn-outline btn-sm" data-size-select-all>Selecionar todos</button>
                    <button type="button" class="btn btn-outline btn-sm" data-size-clear>Limpar seleção</button>
                    <span class="size-selected-count" data-size-count></span>
                </div>
                <p class="form-hint">Clique nas numerações para marcar ou desmarcar.</p>
            </div>
        </div>
        <div class="form-group">
            <label for="size_info">Observação de tamanho (opcional)</label>
            <input class="form-control" type="text" id="size_info" name="size_info" value="<?= e($data['size_info']) ?>" placeholder="Ex.: Modelagem justa">
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
        <div class="form-group full">
            <label for="product_videos">Vídeos curtos / Reels (MP4, WebM ou MOV — até 50 MB)</label>
            <?php if ($videos): ?>
                <div class="video-preview-list">
                    <?php foreach ($videos as $video): ?>
                        <div class="video-preview-item">
                            <video src="<?= e(site_url($video['video_path'])) ?>" muted playsinline preload="metadata"></video>
                            <span class="video-preview-name"><?= e(basename($video['video_path'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <input class="form-control" type="file" id="product_videos" name="product_videos[]" accept="video/mp4,video/webm,video/quicktime" multiple>
            <p class="form-hint">Formato vertical recomendado (9:16), como reels do Instagram.</p>
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
