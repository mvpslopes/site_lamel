<?php

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/admin/config/config.local.php';
if (!file_exists($configPath)) {
    $configPath = dirname(__DIR__) . '/admin/config/config.example.php';
}

$config = require $configPath;
require_once dirname(__DIR__) . '/admin/includes/db.php';

$pdo = db_connect($config['db']);

function api_db(): PDO
{
    global $pdo;
    return $pdo;
}
