-- =====================================================
-- Migração: Criar tabela de agendamentos
-- =====================================================
-- Execute este script no phpMyAdmin ou MySQL:
--   mysql -u root -p projeto_honda < database/migration_agendamentos.sql

USE `projeto_honda`;

-- Criar tabela de agendamentos (se não existir)
CREATE TABLE IF NOT EXISTS `agendamentos` (
    `id`                INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `nome`              VARCHAR(120)      NOT NULL,
    `email`             VARCHAR(180)      NOT NULL,
    `telefone`          VARCHAR(15)       NOT NULL,
    `marca_modelo`      VARCHAR(100)      NOT NULL,
    `ano_moto`          INT UNSIGNED      NOT NULL,
    `chassi`            VARCHAR(30)       NOT NULL,
    `placa`             VARCHAR(10)       NOT NULL,
    `quilometragem`     INT UNSIGNED      NOT NULL,
    `tipo_revisao`      INT UNSIGNED      NOT NULL COMMENT '1000, 6000, 12000, etc',
    `data_agendamento`  DATE              NOT NULL,
    `horario_inicio`    TIME              NOT NULL,
    `duracao_horas`     INT UNSIGNED      NOT NULL DEFAULT 1,
    `observacoes`       TEXT,
    `status`            ENUM('pendente', 'confirmado', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    `user_id`           INT UNSIGNED,
    `created_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_data_horario` (`data_agendamento`, `horario_inicio`),
    KEY `idx_email` (`email`),
    KEY `idx_placa` (`placa`),
    KEY `idx_status` (`status`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Criar índice para verificar conflitos de agendamento
CREATE INDEX idx_conflito_check ON `agendamentos` 
(`data_agendamento`, `horario_inicio`, `duracao_horas`) 
WHERE `status` IN ('pendente', 'confirmado');

-- =====================================================
-- Confirmação
-- =====================================================
-- Tabela 'agendamentos' criada com sucesso!
-- Agora execute: php bootstrap/app.php (ou seu comando de inicialização)
