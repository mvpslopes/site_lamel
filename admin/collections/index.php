<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_check();

$pageTitle = 'Coleções';
$activeMenu = 'collections';
$collections = get_collections();

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Coleções</h2>
        <button type="button" class="btn btn-primary" data-admin-modal-open data-modal-url="<?= e(admin_url('collections/form.php?partial=1')) ?>" data-modal-title="Nova coleção">
            Nova coleção
        </button>
    </div>

    <?php if (!$collections): ?>
        <div class="empty-state">Nenhuma coleção cadastrada.</div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Destaque</th>
                    <th>Status</th>
                    <th>Ordem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($collections as $collection): ?>
                    <tr>
                        <td><strong><?= e($collection['name']) ?></strong></td>
                        <td><?= e($collection['slug']) ?></td>
                        <td><?= $collection['is_featured'] ? 'Sim' : 'Não' ?></td>
                        <td>
                            <span class="badge <?= $collection['is_active'] ? 'badge-success' : 'badge-muted' ?>">
                                <?= $collection['is_active'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </td>
                        <td><?= (int) $collection['sort_order'] ?></td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="btn btn-outline btn-sm" data-admin-modal-open data-modal-url="<?= e(admin_url('collections/form.php?id=' . $collection['id'] . '&partial=1')) ?>" data-modal-title="Editar coleção">
                                    Editar
                                </button>
                                <form method="post" action="<?= e(admin_url('collections/delete.php')) ?>" data-confirm-delete="Excluir esta coleção?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $collection['id'] ?>">
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
$modalFormBase = admin_url('collections/form.php');
$modalTitleNew = 'Nova coleção';
$modalTitleEdit = 'Editar coleção';
require __DIR__ . '/../includes/modal-shell.php';
require __DIR__ . '/../includes/layout-footer.php';
