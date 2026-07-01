-- ============================================================
-- LaMel - Dados iniciais
-- Execute APÓS o 01_schema.sql
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Usuário Root padrão
-- Usuário: marcus.lopes
-- Senha: *.Admin14!
-- ------------------------------------------------------------
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `email`, `role`, `is_active`)
VALUES (
    'marcus.lopes',
    '$2a$12$lgNteckC8LHl4nUS517KRuqxTimFldrW.808vmYr8lfoFHCoYcDzq',
    'Marcus Lopes',
    NULL,
    'root',
    1
)
ON DUPLICATE KEY UPDATE
    `password_hash` = VALUES(`password_hash`),
    `role` = 'root',
    `is_active` = 1;

-- ------------------------------------------------------------
-- Coleções iniciais
-- ------------------------------------------------------------
INSERT INTO `collections` (`name`, `slug`, `description`, `is_active`, `is_featured`, `sort_order`) VALUES
('Destaques da Semana', 'destaques-da-semana', 'Peças em destaque na vitrine principal', 1, 1, 1),
('Vestidos', 'vestidos', 'Vestidos elegantes e sofisticados', 1, 0, 2),
('Conjuntos', 'conjuntos', 'Conjuntos completos para ocasiões especiais', 1, 0, 3),
('Básicos Premium', 'basicos-premium', 'Peças essenciais com acabamento premium', 1, 0, 4);

-- ------------------------------------------------------------
-- Produtos iniciais (catálogo atual do site)
-- ------------------------------------------------------------
INSERT INTO `products` (`collection_id`, `name`, `slug`, `description`, `price`, `size_info`, `badge`, `main_image`, `is_active`, `is_featured`, `sort_order`) VALUES
(4, 'Body Tule', 'body-tule', 'Body em tule com design xadrez elegante e sofisticado', 69.99, 'Tamanho único', 'Novo', 'produtos/Body Tule/body_tule (1).jpeg', 1, 1, 1),
(3, 'Conjunto Bella', 'conjunto-bella', 'Blusa social + calça pantalona. Tecido: Viscolinho. Veste até o tamanho 44', 199.90, 'Tamanho único (até 44)', 'Mais Vendido', 'produtos/Conjunto Bella/cojunto_bella (1).jpeg', 1, 1, 2),
(4, 'T-shirt Premium', 't-shirt-premium', 'Camiseta premium com acabamento de alta qualidade', 79.99, 'Tamanho único', NULL, 'produtos/T-shirt Premium/t-shirt_premium (1).jpeg', 1, 1, 3),
(2, 'Vestido Ayla', 'vestido-ayla', 'Vestido com bojo, elástico e regulagem. Tecido: Viscolinho. Veste até o tamanho 42/44', 179.99, 'Tamanho único (até 42/44)', NULL, 'produtos/Vestido Ayla/vestido_ayla (1).jpeg', 1, 1, 4),
(2, 'Vestido Fluyte', 'vestido-fluyte', 'Linha premium. Vestido em seda com estampa diferenciada. Tamanho único veste do P ao G1', 269.90, 'Tamanho único (P ao G1)', 'Exclusivo', 'produtos/Vestido Fluyte/vestido_fluyte.jpeg', 1, 1, 5);

-- ------------------------------------------------------------
-- Imagens dos produtos
-- ------------------------------------------------------------
INSERT INTO `product_images` (`product_id`, `image_path`, `sort_order`) VALUES
(1, 'produtos/Body Tule/body_tule (1).jpeg', 1),
(1, 'produtos/Body Tule/body_tule (2).jpeg', 2),
(1, 'produtos/Body Tule/body_tule (3).jpeg', 3),
(2, 'produtos/Conjunto Bella/cojunto_bella (1).jpeg', 1),
(2, 'produtos/Conjunto Bella/cojunto_bella (2).jpeg', 2),
(2, 'produtos/Conjunto Bella/cojunto_bella (3).jpeg', 3),
(2, 'produtos/Conjunto Bella/cojunto_bella (4).jpeg', 4),
(2, 'produtos/Conjunto Bella/cojunto_bella (5).jpeg', 5),
(3, 'produtos/T-shirt Premium/t-shirt_premium (1).jpeg', 1),
(3, 'produtos/T-shirt Premium/t-shirt_premium (2).jpeg', 2),
(4, 'produtos/Vestido Ayla/vestido_ayla (1).jpeg', 1),
(4, 'produtos/Vestido Ayla/vestido_ayla (2).jpeg', 2),
(4, 'produtos/Vestido Ayla/vestido_ayla (3).jpeg', 3),
(4, 'produtos/Vestido Ayla/vestido_ayla (4).jpeg', 4),
(5, 'produtos/Vestido Fluyte/vestido_fluyte.jpeg', 1);
