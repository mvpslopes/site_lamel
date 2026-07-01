<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_require_root();

$pageTitle = 'Usuários';
$activeMenu = 'users';
$users = get_users();

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Usuários do sistema</h2>
        <button type="button" class="btn btn-primary" data-admin-modal-open data-modal-url="<?= e(admin_url('users/form.php?partial=1')) ?>" data-modal-title="Novo usuário">
            Novo usuário
        </button>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Usuário</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Status</th>
                <th>Último acesso</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $userRow): ?>
                <tr>
                    <td><strong><?= e($userRow['full_name']) ?></strong></td>
                    <td><?= e($userRow['username']) ?></td>
                    <td><?= e($userRow['email'] ?? '-') ?></td>
                    <td><span class="badge <?= $userRow['role'] === 'root' ? 'badge-root' : 'badge-gold' ?>"><?= e(role_label($userRow['role'])) ?></span></td>
                    <td>
                        <span class="badge <?= $userRow['is_active'] ? 'badge-success' : 'badge-muted' ?>">
                            <?= $userRow['is_active'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td><?= e(format_date($userRow['last_login_at'])) ?></td>
                    <td>
                        <div class="table-actions">
                            <button type="button" class="btn btn-outline btn-sm" data-admin-modal-open data-modal-url="<?= e(admin_url('users/form.php?id=' . $userRow['id'] . '&partial=1')) ?>" data-modal-title="Editar usuário">
                                Editar
                            </button>
                            <?php if ((int) $userRow['id'] !== (int) auth_user()['id']): ?>
                                <form method="post" action="<?= e(admin_url('users/delete.php')) ?>" data-confirm-delete="Desativar este usuário?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $userRow['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Desativar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$modalFormBase = admin_url('users/form.php');
$modalTitleNew = 'Novo usuário';
$modalTitleEdit = 'Editar usuário';
require __DIR__ . '/../includes/modal-shell.php';
require __DIR__ . '/../includes/layout-footer.php';
