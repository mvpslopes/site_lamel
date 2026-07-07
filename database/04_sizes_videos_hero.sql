-- ============================================================
-- LaMel - Tamanhos, vídeos de produto e slides do hero
-- Execute após 03_user_profile_image.sql
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `products`
    ADD COLUMN `size_type` ENUM('none', 'clothing', 'footwear') NOT NULL DEFAULT 'none' AFTER `size_info`,
    ADD COLUMN `available_sizes` JSON DEFAULT NULL AFTER `size_type`;

CREATE TABLE IF NOT EXISTS `product_videos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `video_path` VARCHAR(500) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_videos_product` (`product_id`),
    CONSTRAINT `fk_product_videos_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `image_path` VARCHAR(500) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_hero_slides_active` (`is_active`),
    KEY `idx_hero_slides_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
