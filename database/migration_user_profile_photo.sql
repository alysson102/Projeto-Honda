-- =====================================================
-- Migração: adicionar foto de perfil em users
-- Execute no phpMyAdmin ou MySQL CLI:
--   mysql -u root -p projeto_honda < database/migration_user_profile_photo.sql
-- =====================================================

USE `projeto_honda`;

SET @column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'profile_photo'
);

SET @sql_add_column := IF(
    @column_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `telefone`',
    'SELECT 1'
);

PREPARE stmt_add_column FROM @sql_add_column;
EXECUTE stmt_add_column;
DEALLOCATE PREPARE stmt_add_column;

-- =====================================================
-- Concluído.
-- =====================================================
