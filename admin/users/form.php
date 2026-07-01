<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

auth_require_root();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isPartial = isset($_GET['partial']) && $_GET['partial'] === '1';
$userRow = $id ? get_user($id) : null;
$pageTitle = $userRow ? 'Editar usuário' : 'Novo usuário';
$activeMenu = 'users';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isPartial) {
    $openParam = $id ? 'edit&id=' . $id : 'new';
    redirect(admin_url('users/index.php?open=' . $openParam));
}

$data = $userRow ?: [
    'username' => '',
    'full_name' => '',
    'email' => '',
    'role' => 'admin',
    'is_active' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => $_POST['role'] === 'root' ? 'root' : 'admin',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    $errors = [];
    if ($data['username'] === '') $errors[] = 'Informe o nome de usuário.';
    if ($data['full_name'] === '') $errors[] = 'Informe o nome completo.';
    if (!$userRow && $password === '') $errors[] = 'Informe a senha do novo usuário.';
    if ($password !== '' && strlen($password) < 8) $errors[] = 'A senha deve ter no mínimo 8 caracteres.';
    if ($password !== $passwordConfirm) $errors[] = 'As senhas não conferem.';

    if (!$errors) {
        $usernameCheck = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
        $usernameCheck->execute([$data['username'], $id]);
        if ($usernameCheck->fetch()) {
            $errors[] = 'Este nome de usuário já está em uso.';
        }
    }

    if (!$errors) {
        if ($userRow) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = db()->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([
                    $data['username'], $data['full_name'], $data['email'] ?: null,
                    $data['role'], $data['is_active'], $hash, $id
                ]);
            } else {
                $stmt = db()->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?');
                $stmt->execute([
                    $data['username'], $data['full_name'], $data['email'] ?: null,
                    $data['role'], $data['is_active'], $id
                ]);
            }
            flash('success', 'Usuário atualizado com sucesso.');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare('INSERT INTO users (username, password_hash, full_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $data['username'], $hash, $data['full_name'], $data['email'] ?: null,
                $data['role'], $data['is_active']
            ]);
            flash('success', 'Usuário criado com sucesso.');
        }

        redirect(admin_url('users/index.php'));
    }

    flash('error', implode(' ', $errors));
    set_old($data);
    $openParam = $id ? 'edit&id=' . $id : 'new';
    redirect(admin_url('users/index.php?open=' . $openParam));
}

if (!empty($_SESSION['_old'])) {
    $data = array_merge($data, $_SESSION['_old']);
    clear_old();
}

$formAction = admin_url('users/form.php' . ($id ? '?id=' . $id : ''));
$isModal = $isPartial;

if ($isPartial) {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/../includes/user-form.php';
    exit;
}

require __DIR__ . '/../includes/layout-header.php';
?>

<div class="panel">
    <?php require __DIR__ . '/../includes/user-form.php'; ?>
</div>

<?php require __DIR__ . '/../includes/layout-footer.php'; ?>
