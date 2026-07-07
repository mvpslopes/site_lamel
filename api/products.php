<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/bootstrap.php';

try {
    $productsStmt = api_db()->query(
        'SELECT p.id, p.collection_id, p.name, p.slug, p.description, p.price, p.size_info, p.size_type, p.available_sizes,
                p.badge, p.main_image, p.is_featured, p.sort_order, c.name AS collection_name, c.slug AS collection_slug
         FROM products p
         LEFT JOIN collections c ON c.id = p.collection_id
         WHERE p.is_active = 1
         ORDER BY p.sort_order ASC, p.name ASC'
    );
    $products = $productsStmt->fetchAll();

    $imagesStmt = api_db()->query(
        'SELECT pi.product_id, pi.image_path, pi.sort_order
         FROM product_images pi
         INNER JOIN products p ON p.id = pi.product_id
         WHERE p.is_active = 1
         ORDER BY pi.sort_order ASC, pi.id ASC'
    );

    $imagesByProduct = [];
    foreach ($imagesStmt->fetchAll() as $image) {
        $imagesByProduct[(int) $image['product_id']][] = $image['image_path'];
    }

    $videosStmt = api_db()->query(
        'SELECT pv.product_id, pv.video_path, pv.sort_order
         FROM product_videos pv
         INNER JOIN products p ON p.id = pv.product_id
         WHERE p.is_active = 1
         ORDER BY pv.sort_order ASC, pv.id ASC'
    );

    $videosByProduct = [];
    foreach ($videosStmt->fetchAll() as $video) {
        $videosByProduct[(int) $video['product_id']][] = $video['video_path'];
    }

    $responseProducts = [];
    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $gallery = $imagesByProduct[$productId] ?? [];
        if (!$gallery) {
            $gallery = [$product['main_image']];
        }

        $sizes = [];
        if (!empty($product['available_sizes'])) {
            $decoded = json_decode((string) $product['available_sizes'], true);
            if (is_array($decoded)) {
                $sizes = array_values($decoded);
            }
        }

        $responseProducts[] = [
            'id' => $productId,
            'collection_id' => $product['collection_id'] ? (int) $product['collection_id'] : null,
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => (float) $product['price'],
            'size' => $product['size_info'],
            'size_type' => $product['size_type'] ?? 'none',
            'sizes' => $sizes,
            'badge' => $product['badge'],
            'image' => $product['main_image'],
            'images' => $gallery,
            'videos' => $videosByProduct[$productId] ?? [],
            'collection' => $product['collection_name'],
            'collection_slug' => $product['collection_slug'],
            'is_featured' => (bool) $product['is_featured'],
        ];
    }

    $collectionsStmt = api_db()->query(
        'SELECT id, name, slug, description, cover_image, is_featured, sort_order, created_at
         FROM collections
         WHERE is_active = 1
         ORDER BY created_at ASC, id ASC'
    );

    $heroImages = [];
    try {
        $heroStmt = api_db()->query(
            'SELECT image_path FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        );
        $heroImages = array_column($heroStmt->fetchAll(), 'image_path');
    } catch (Throwable $e) {
        $heroImages = [];
    }

    if (!$heroImages) {
        $heroImages = array_values(array_map(
            fn ($product) => $product['image'],
            array_filter($responseProducts, fn ($product) => $product['is_featured'])
        ));
    }

    if (!$heroImages) {
        $heroImages = array_column($responseProducts, 'image');
    }

    echo json_encode([
        'success' => true,
        'products' => $responseProducts,
        'collections' => $collectionsStmt->fetchAll(),
        'hero_images' => $heroImages,
        'generated_at' => date('c'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível carregar os produtos.',
    ], JSON_UNESCAPED_UNICODE);
}
