-- LaMel - Foto de perfil dos usuários do painel
-- Execute no phpMyAdmin se o banco já existir

ALTER TABLE `users`
    ADD COLUMN `profile_image` VARCHAR(500) DEFAULT NULL AFTER `email`;
