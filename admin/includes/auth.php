<?php

declare(strict_types=1);

function auth_user(bool $refresh = false): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($refresh) {
        $user = null;
    }

    if ($user === null) {
        $stmt = db()->prepare('SELECT id, username, full_name, email, profile_image, role, is_active FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;

        if (!$user || !(int) $user['is_active']) {
            auth_logout();
            return null;
        }
    }

    return $user;
}

function auth_check(): void
{
    if (!auth_user()) {
        redirect(admin_url('index.php'));
    }
}

function auth_guest(): void
{
    if (auth_user()) {
        redirect(admin_url('dashboard.php'));
    }
}

function auth_is_root(): bool
{
    $user = auth_user();
    return $user && $user['role'] === 'root';
}

function auth_require_root(): void
{
    auth_check();

    if (!auth_is_root()) {
        flash('error', 'Apenas usuários Root podem acessar esta área.');
        redirect(admin_url('dashboard.php'));
    }
}

function auth_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([trim($username)]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'];

    $update = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $update->execute([$user['id']]);

    $_SESSION['show_admin_splash'] = true;

    return true;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Token de segurança inválido.');
    }
}
