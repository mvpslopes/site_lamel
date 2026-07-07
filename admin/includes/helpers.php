<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower((string) $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-') ?: 'item';
}

function format_money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d/m/Y H:i', strtotime($date));
}

function role_label(string $role): string
{
    return $role === 'root' ? 'Root' : 'Admin';
}

function badge_options(): array
{
    return ['', 'Novo', 'Mais Vendido', 'Exclusivo', 'Promoção'];
}

function clothing_size_options(): array
{
    return ['P', 'M', 'G', 'GG', 'G1', 'G2', 'G3', 'G4', 'G5', 'G6'];
}

function footwear_size_options(): array
{
    return array_map('strval', range(34, 56));
}

function size_type_options(): array
{
    return [
        'none' => 'Sem tamanho',
        'clothing' => 'Roupa (P ao G6)',
        'footwear' => 'Calçado (34 ao 56)',
    ];
}

function normalize_product_sizes(string $sizeType, array $selected): array
{
    $allowed = match ($sizeType) {
        'clothing' => clothing_size_options(),
        'footwear' => footwear_size_options(),
        default => [],
    };

    $normalized = [];
    foreach ($selected as $size) {
        $size = trim((string) $size);
        if (in_array($size, $allowed, true)) {
            $normalized[] = $size;
        }
    }

    return array_values(array_unique($normalized));
}

function decode_product_sizes(?string $json): array
{
    if (!$json) {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? array_values($decoded) : [];
}

function get_product_videos(int $productId): array
{
    $stmt = db()->prepare('SELECT * FROM product_videos WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function get_hero_slides(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM hero_slides';
    if ($onlyActive) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    return db()->query($sql)->fetchAll();
}

function get_hero_slide(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM hero_slides WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_all_catalog_image_paths(): array
{
    $paths = [];

    $products = db()->query('SELECT main_image FROM products WHERE main_image IS NOT NULL AND main_image != ""')->fetchAll();
    foreach ($products as $product) {
        $paths[] = $product['main_image'];
    }

    $images = db()->query('SELECT image_path FROM product_images ORDER BY sort_order ASC, id ASC')->fetchAll();
    foreach ($images as $image) {
        $paths[] = $image['image_path'];
    }

    $slides = db()->query('SELECT image_path FROM hero_slides')->fetchAll();
    foreach ($slides as $slide) {
        $paths[] = $slide['image_path'];
    }

    return array_values(array_unique(array_filter($paths)));
}

function get_collections(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM collections';
    if ($onlyActive) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, name ASC';

    return db()->query($sql)->fetchAll();
}

function get_collection(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM collections WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_products(): array
{
    $sql = 'SELECT p.*, c.name AS collection_name
            FROM products p
            LEFT JOIN collections c ON c.id = p.collection_id
            ORDER BY p.sort_order ASC, p.name ASC';
    return db()->query($sql)->fetchAll();
}

function get_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_product_images(int $productId): array
{
    $stmt = db()->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function get_users(): array
{
    return db()->query('SELECT id, username, full_name, email, profile_image, role, is_active, last_login_at, created_at FROM users ORDER BY role DESC, full_name ASC')->fetchAll();
}

function get_user(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, username, full_name, email, profile_image, role, is_active, last_login_at, created_at FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function dashboard_stats(): array
{
    $products = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $activeProducts = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
    $collections = (int) db()->query('SELECT COUNT(*) FROM collections')->fetchColumn();
    $users = (int) db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();

    return compact('products', 'activeProducts', 'collections', 'users');
}

function handle_image_upload(array $file, string $subdir, string $baseFolder = 'produtos'): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro ao enviar a imagem.');
    }

    if (($file['size'] ?? 0) > app_config('upload.max_size', 5242880)) {
        throw new RuntimeException('A imagem excede o tamanho máximo de 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = app_config('upload.allowed_mimes', []);

    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Formato de imagem não permitido.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };

    $folderName = trim(str_replace(['/', '\\'], '-', $subdir), '-');
    $uploadDir = public_path($baseFolder . '/' . $folderName);

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Não foi possível criar a pasta de upload.');
    }

    $filename = slugify(pathinfo($file['name'], PATHINFO_FILENAME)) . '_' . time() . '.' . $ext;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Falha ao salvar a imagem.');
    }

    return $baseFolder . '/' . $folderName . '/' . $filename;
}

function handle_video_upload(array $file, string $subdir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro ao enviar o vídeo.');
    }

    if (($file['size'] ?? 0) > app_config('upload.video_max_size', 52428800)) {
        throw new RuntimeException('O vídeo excede o tamanho máximo de 50 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = app_config('upload.video_allowed_mimes', []);

    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Formato de vídeo não permitido. Use MP4, WebM ou MOV.');
    }

    $ext = match ($mime) {
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        default => 'mp4',
    };

    $folderName = trim(str_replace(['/', '\\'], '-', $subdir), '-');
    $uploadDir = public_path('produtos/' . $folderName . '/videos');

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Não foi possível criar a pasta de vídeos.');
    }

    $filename = 'reel_' . slugify(pathinfo($file['name'], PATHINFO_FILENAME)) . '_' . time() . '.' . $ext;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Falha ao salvar o vídeo.');
    }

    return 'produtos/' . $folderName . '/videos/' . $filename;
}

function handle_profile_image_upload(array $file, string $identifier): ?string
{
    return handle_image_upload($file, $identifier, 'perfis');
}

function user_profile_image_url(?array $user): ?string
{
    if (!$user || empty($user['profile_image'])) {
        return null;
    }

    return site_url($user['profile_image']);
}

function user_avatar_initial(?array $user): string
{
    $name = trim((string) ($user['full_name'] ?? $user['username'] ?? 'U'));
    return strtoupper(substr($name, 0, 1));
}

function delete_public_file(?string $path): void
{
    if (!$path) {
        return;
    }

    $fullPath = public_path($path);
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function unique_slug(string $table, string $slug, ?int $ignoreId = null): string
{
    $base = $slug;
    $counter = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];

        if ($ignoreId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreId;
        }

        $stmt = db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $base . '-' . $counter;
        $counter++;
    }
}
