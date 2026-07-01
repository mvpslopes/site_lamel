<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

$pageTitle = 'Produtos';
$activeMenu = 'products';
$products = get_products();

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Catálogo de produtos</h2>
        <button type="button" class="btn btn-primary" data-admin-modal-open data-modal-url="<?= e(admin_url('products/form.php?partial=1')) ?>" data-modal-title="Novo produto">
            Novo produto
        </button>
    </div>

    <?php if (!$products): ?>
        <div class="empty-state">Nenhum produto cadastrado.</div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Produto</th>
                    <th>Coleção</th>
                    <th>Preço</th>
                    <th>Destaque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><img src="<?= e(site_url($product['main_image'])) ?>" alt="" class="table-thumb"></td>
                        <td>
                            <strong><?= e($product['name']) ?></strong><br>
                            <small><?= e($product['badge'] ?: 'Sem badge') ?></small>
                        </td>
                        <td><?= e($product['collection_name'] ?? '-') ?></td>
                        <td><?= e(format_money((float) $product['price'])) ?></td>
                        <td><?= $product['is_featured'] ? 'Sim' : 'Não' ?></td>
                        <td>
                            <span class="badge <?= $product['is_active'] ? 'badge-success' : 'badge-muted' ?>">
                                <?= $product['is_active'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="btn btn-outline btn-sm" data-admin-modal-open data-modal-url="<?= e(admin_url('products/form.php?id=' . $product['id'] . '&partial=1')) ?>" data-modal-title="Editar produto">
                                    Editar
                                </button>
                                <form method="post" action="<?= e(admin_url('products/delete.php')) ?>" data-confirm-delete="Excluir este produto?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$modalFormBase = admin_url('products/form.php');
$modalTitleNew = 'Novo produto';
$modalTitleEdit = 'Editar produto';
require __DIR__ . '/../includes/modal-shell.php';
require __DIR__ . '/../includes/layout-footer.php';
