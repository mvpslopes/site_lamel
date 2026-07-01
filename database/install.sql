-- ============================================================
-- LaMel - Instalação completa do banco
-- Execute este arquivo no phpMyAdmin (aba SQL)
-- ============================================================

SOURCE 01_schema.sql;
SOURCE 02_seed.sql;

-- Se o phpMyAdmin não suportar SOURCE, execute manualmente:
-- 1) database/01_schema.sql
-- 2) database/02_seed.sql
