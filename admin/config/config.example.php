<?php

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'u179630068_lamel_bd',
        'user' => 'u179630068_lamel_user',
        'pass' => 'SUA_SENHA_AQUI',
        'charset' => 'utf8mb4',
    ],
    'site' => [
        'url' => 'https://lamelmodas.com.br',
        'name' => 'LaMel',
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'webp'],
        'video_max_size' => 52428800,
        'video_allowed_mimes' => ['video/mp4', 'video/webm', 'video/quicktime'],
    ],
];
