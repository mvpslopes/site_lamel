<?php

declare(strict_types=1);

/** @var array $data */
/** @var array|null $userRow */
/** @var string $formAction */
/** @var bool $isModal */

$isModal = $isModal ?? false;
$formAction = $formAction ?? admin_url('users/form.php' . (!empty($userRow['id']) ? '?id=' . (int) $userRow['id'] : ''));
$profileImageUrl = !empty($data['profile_image']) ? site_url($data['profile_image']) : null;
?>

<form method="post" enctype="multipart/form-data" action="<?= e($formAction) ?>" class="user-form">
    <?= csrf_field() ?>

    <div class="form-grid">
        <div class="form-group full">
            <label for="profile_image">Foto de perfil</label>
            <div class="image-upload-field profile-upload-field">
                <div class="image-preview-box profile-preview-box<?= $profileImageUrl ? ' has-image' : '' ?>" id="profile-image-preview-box">
                    <?php if ($profileImageUrl): ?>
                        <img src="<?= e($profileImageUrl) ?>" alt="Foto de perfil" class="image-preview-main">
                    <?php else: ?>
                        <div class="image-preview-placeholder">
                            <span>Sem foto</span>
                        </div>
                    <?php endif; ?>
                </div>
                <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/*" data-preview-target="profile-image-preview-box">
                <p class="form-help">Opcional. JPG, PNG ou WebP até 5 MB.</p>
            </div>
        </div>
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
        <?php if (!$userRow): ?>
        <div class="form-group">
            <label for="password">Senha</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar senha</label>
            <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <?php else: ?>
        <div class="form-group">
            <label for="password">Nova senha <span class="form-help-inline">(opcional)</span></label>
            <input class="form-control" type="password" id="password" name="password" autocomplete="new-password">
            <p class="form-help">Deixe em branco para manter a senha atual.</p>
        </div>
        <?php endif; ?>
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
