<?php

declare(strict_types=1);

/** @var array $data */
/** @var array|null $userRow */
/** @var string $formAction */
/** @var bool $isModal */

$isModal = $isModal ?? false;
$formAction = $formAction ?? admin_url('users/form.php' . (!empty($userRow['id']) ? '?id=' . (int) $userRow['id'] : ''));
?>

<form method="post" action="<?= e($formAction) ?>" class="user-form">
    <?= csrf_field() ?>

    <div class="form-grid">
        <div class="form-group">
            <label for="full_name">Nome completo</label>
            <input class="form-control" type="text" id="full_name" name="full_name" value="<?= e($data['full_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Usuário</label>
            <input class="form-control" type="text" id="username" name="username" value="<?= e($data['username']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($data['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="role">Perfil</label>
            <select class="form-select" id="role" name="role">
                <option value="admin" <?= ($data['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="root" <?= ($data['role'] ?? '') === 'root' ? 'selected' : '' ?>>Root</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Senha <?= $userRow ? '(deixe em branco para manter)' : '' ?></label>
            <input class="form-control" type="password" id="password" name="password" <?= $userRow ? '' : 'required' ?>>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar senha</label>
            <input class="form-control" type="password" id="password_confirm" name="password_confirm" <?= $userRow ? '' : 'required' ?>>
        </div>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="is_active" <?= !empty($data['is_active']) ? 'checked' : '' ?>> Usuário ativo</label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar usuário</button>
        <?php if ($isModal): ?>
            <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
        <?php else: ?>
            <a href="<?= e(admin_url('users/index.php')) ?>" class="btn btn-outline">Cancelar</a>
        <?php endif; ?>
    </div>
</form>
