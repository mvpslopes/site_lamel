<?php

declare(strict_types=1);

session_start();

$configPath = __DIR__ . '/../config/config.local.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/config.example.php';
}

$config = require $configPath;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$pdo = db_connect($config['db']);

function app_config(string $key = null, mixed $default = null): mixed
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path ? $root . '/' . ltrim($path, '/') : $root;
}

function public_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);
    return $path ? $root . '/' . ltrim($path, '/') : $root;
}

function admin_url(string $path = ''): string
{
    $base = rtrim(app_config('site.url', ''), '/') . '/admin';
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function site_url(string $path = ''): string
{
    $base = rtrim(app_config('site.url', ''), '/');
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    $value = $_SESSION['_old'][$key] ?? $default;
    return $value;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}
