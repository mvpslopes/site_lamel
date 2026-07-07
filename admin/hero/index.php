<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

$pageTitle = 'Hero da página inicial';
$activeMenu = 'hero';
$slides = get_hero_slides();
$catalogImages = get_all_catalog_image_paths();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $imagePath = trim($_POST['image_path'] ?? '');
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($_FILES['hero_image']['name'])) {
                $uploaded = handle_image_upload($_FILES['hero_image'], 'hero');
                if ($uploaded) {
                    $imagePath = $uploaded;
                }
            }

            if ($imagePath === '') {
                throw new RuntimeException('Selecione ou envie uma imagem para o hero.');
            }

            $stmt = db()->prepare('INSERT INTO hero_slides (image_path, sort_order, is_active) VALUES (?, ?, ?)');
            $stmt->execute([$imagePath, $sortOrder, $isActive]);
            flash('success', 'Slide adicionado ao hero.');
        } elseif ($action === 'update') {
            $slideId = (int) ($_POST['id'] ?? 0);
            $slide = $slideId ? get_hero_slide($slideId) : null;
            if (!$slide) {
                throw new RuntimeException('Slide não encontrado.');
            }

            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $stmt = db()->prepare('UPDATE hero_slides SET sort_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$sortOrder, $isActive, $slideId]);
            flash('success', 'Slide atualizado.');
        } elseif ($action === 'delete') {
            $slideId = (int) ($_POST['id'] ?? 0);
            $slide = $slideId ? get_hero_slide($slideId) : null;
            if (!$slide) {
                throw new RuntimeException('Slide não encontrado.');
            }

            if (str_contains($slide['image_path'], 'produtos/hero/')) {
                delete_public_file($slide['image_path']);
            }
            $stmt = db()->prepare('DELETE FROM hero_slides WHERE id = ?');
            $stmt->execute([$slideId]);
            flash('success', 'Slide removido do hero.');
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }

    redirect(admin_url('hero/index.php'));
}

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Imagens do hero</h2>
    </div>

    <p class="panel-description">Escolha as imagens exibidas no banner principal do site. A ordem define a sequência do slideshow.</p>

    <form method="post" enctype="multipart/form-data" class="hero-add-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <div class="form-grid">
            <div class="form-group">
                <label for="hero_image">Enviar nova imagem</label>
                <input class="form-control" type="file" id="hero_image" name="hero_image" accept="image/*">
            </div>
            <div class="form-group">
                <label for="image_path">Ou escolher imagem existente</label>
                <select class="form-select" id="image_path" name="image_path">
                    <option value="">Selecione uma imagem do catálogo</option>
                    <?php foreach ($catalogImages as $imagePath): ?>
                        <option value="<?= e($imagePath) ?>"><?= e($imagePath) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Ordem</label>
                <input class="form-control" type="number" id="sort_order" name="sort_order" value="0">
            </div>
            <div class="form-group">
                <label class="form-check"><input type="checkbox" name="is_active" checked> Slide ativo</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Adicionar ao hero</button>
        </div>
    </form>
</div>

<div class="panel">
  <?php if (!$slides): ?>
      <div class="empty-state">Nenhuma imagem configurada. O site usará os produtos em destaque como fallback.</div>
  <?php else: ?>
      <div class="hero-slides-grid">
          <?php foreach ($slides as $slide): ?>
              <div class="hero-slide-card">
                  <img src="<?= e(site_url($slide['image_path'])) ?>" alt="Slide do hero" class="hero-slide-preview">
                  <form method="post" class="hero-slide-form">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
                      <div class="form-grid">
                          <div class="form-group">
                              <label>Ordem</label>
                              <input class="form-control" type="number" name="sort_order" value="<?= (int) $slide['sort_order'] ?>">
                          </div>
                          <div class="form-group">
                              <label class="form-check">
                                  <input type="checkbox" name="is_active" <?= $slide['is_active'] ? 'checked' : '' ?>> Ativo
                              </label>
                          </div>
                      </div>
                      <div class="form-actions">
                          <button type="submit" class="btn btn-outline btn-sm">Salvar</button>
                      </div>
                  </form>
                  <form method="post" data-confirm-delete="Remover esta imagem do hero?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                  </form>
              </div>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
