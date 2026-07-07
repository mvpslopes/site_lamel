<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

auth_check();

$user = auth_user();
$pageTitle = 'Meu perfil';
$activeMenu = 'profile';
$data = [
    'full_name' => $user['full_name'],
    'username' => $user['username'],
    'email' => $user['email'] ?? '',
    'profile_image' => $user['profile_image'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $errors = [];
    $successMessages = [];

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
    $changingPassword = $currentPassword !== '' || $newPassword !== '' || $newPasswordConfirm !== '';
    $uploadingPhoto = !empty($_FILES['profile_image']['name']);

    if (!$changingPassword && !$uploadingPhoto) {
        flash('error', 'Selecione uma foto ou preencha os campos de senha para salvar.');
        redirect(admin_url('profile.php'));
    }

    if ($uploadingPhoto) {
        try {
            $uploaded = handle_profile_image_upload($_FILES['profile_image'], $user['username']);
            if ($uploaded) {
                if (!empty($user['profile_image'])) {
                    delete_public_file($user['profile_image']);
                }

                $stmt = db()->prepare('UPDATE users SET profile_image = ? WHERE id = ?');
                $stmt->execute([$uploaded, $user['id']]);
                auth_user(true);
                $successMessages[] = 'Foto de perfil atualizada.';
            }
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }

    if ($changingPassword && !$errors) {
        if ($currentPassword === '') {
            $errors[] = 'Informe a senha atual.';
        }
        if ($newPassword === '') {
            $errors[] = 'Informe a nova senha.';
        }
        if ($newPassword !== '' && strlen($newPassword) < 8) {
            $errors[] = 'A nova senha deve ter no mínimo 8 caracteres.';
        }
        if ($newPassword !== $newPasswordConfirm) {
            $errors[] = 'A confirmação da nova senha não confere.';
        }

        if (!$errors) {
            $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                $errors[] = 'Senha atual incorreta.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
                $update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $update->execute([$hash, $user['id']]);
                $successMessages[] = 'Senha alterada com sucesso.';
            }
        }
    }

    if ($errors) {
        flash('error', implode(' ', $errors));
    } elseif ($successMessages) {
        flash('success', implode(' ', $successMessages));
    }

    redirect(admin_url('profile.php'));
}

$user = auth_user();
$data['profile_image'] = $user['profile_image'] ?? '';

require __DIR__ . '/includes/layout-header.php';
?>

<div class="panel profile-panel">
    <div class="profile-header">
        <?php if (!empty($data['profile_image'])): ?>
            <img src="<?= e(site_url($data['profile_image'])) ?>" alt="<?= e($user['full_name']) ?>" class="profile-header-avatar">
        <?php else: ?>
            <div class="profile-header-avatar profile-header-avatar-fallback"><?= e(user_avatar_initial($user)) ?></div>
        <?php endif; ?>
        <div>
            <h2 class="profile-header-name"><?= e($user['full_name']) ?></h2>
            <p class="profile-header-meta">@<?= e($user['username']) ?> · <?= e(role_label($user['role'])) ?></p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('profile.php')) ?>" class="profile-form">
        <?= csrf_field() ?>

        <section class="profile-section">
            <h3 class="profile-section-title">Foto de perfil</h3>
            <div class="form-group full">
                <label for="profile_image">Alterar foto</label>
                <div class="image-upload-field profile-upload-field">
                    <div class="image-preview-box profile-preview-box<?= !empty($data['profile_image']) ? ' has-image' : '' ?>" id="profile-image-preview-box">
                        <?php if (!empty($data['profile_image'])): ?>
                            <img src="<?= e(site_url($data['profile_image'])) ?>" alt="Foto de perfil" class="image-preview-main">
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
        </section>

        <section class="profile-section">
            <h3 class="profile-section-title">Alterar senha</h3>
            <div class="form-grid profile-password-grid">
                <div class="form-group full">
                    <label for="current_password">Senha atual</label>
                    <input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="new_password">Nova senha</label>
                    <input class="form-control" type="password" id="new_password" name="new_password" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="new_password_confirm">Confirmar nova senha</label>
                    <input class="form-control" type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
                </div>
            </div>
            <p class="form-help">Preencha os três campos apenas se quiser trocar a senha. Mínimo de 8 caracteres.</p>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
