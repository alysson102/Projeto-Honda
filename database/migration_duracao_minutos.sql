-- =====================================================
-- Migração: duracao_horas → duracao_minutos
-- Motivo:
--   - Revisões rápidas (1.000 / 6.000 km) duram ~20 min;
--     bloquear 1 hora inteira era excessivo.
--   - DATE_ADD em coluna TIME retorna NULL no MySQL 8+;
--     a nova query usa ADDTIME evitando o bug de conflito.
--
-- Execute no phpMyAdmin ou via CLI:
--   mysql -u root -p projeto_honda < database/migration_duracao_minutos.sql
-- =====================================================

USE `projeto_honda`;

SET @has_duracao_horas := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'agendamentos'
      AND COLUMN_NAME = 'duracao_horas'
);

SET @has_duracao_minutos := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'agendamentos'
      AND COLUMN_NAME = 'duracao_minutos'
);

SET @sql_alter := IF(
    @has_duracao_horas = 1,
    'ALTER TABLE `agendamentos` CHANGE COLUMN `duracao_horas` `duracao_minutos` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT ''Duração do serviço em minutos (30 = revisão rápida, 120 = revisão completa)''',
    IF(
        @has_duracao_minutos = 0,
        'ALTER TABLE `agendamentos` ADD COLUMN `duracao_minutos` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT ''Duração do serviço em minutos (30 = revisão rápida, 120 = revisão completa)'' AFTER `horario_inicio`',
        'SELECT 1'
    )
);

PREPARE stmt_alter FROM @sql_alter;
EXECUTE stmt_alter;
DEALLOCATE PREPARE stmt_alter;

UPDATE `agendamentos`
    SET `duracao_minutos` = `duracao_minutos` * 60
    WHERE `duracao_minutos` IN (1, 2);  

-- 2) Recriar índice de conflito com a coluna nova
SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'agendamentos'
      AND INDEX_NAME = 'idx_conflito_check'
);

SET @sql_drop_idx := IF(
    @idx_exists > 0,
    'DROP INDEX `idx_conflito_check` ON `agendamentos`',
    'SELECT 1'
);

PREPARE stmt_drop_idx FROM @sql_drop_idx;
EXECUTE stmt_drop_idx;
DEALLOCATE PREPARE stmt_drop_idx;

SET @idx_exists_after_drop := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'agendamentos'
      AND INDEX_NAME = 'idx_conflito_check'
);

SET @sql_create_idx := IF(
    @idx_exists_after_drop = 0,
    'CREATE INDEX `idx_conflito_check` ON `agendamentos` (`data_agendamento`, `horario_inicio`, `duracao_minutos`, `status`)',
    'SELECT 1'
);

PREPARE stmt_create_idx FROM @sql_create_idx;
EXECUTE stmt_create_idx;
DEALLOCATE PREPARE stmt_create_idx;

-- =====================================================
-- Concluído.  Valores esperados após migração:
--   Revisão 1.000 / 6.000 km  →  30 minutos
--   Revisão 12.000+ km         → 120 minutos
-- =====================================================
