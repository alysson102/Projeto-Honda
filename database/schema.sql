-- -----------------------------------------------
-- Schema do banco de dados - Projeto Honda
-- Execute este script no phpMyAdmin ou via MySQL CLI:
--   mysql -u root -p < database/schema.sql
-- -----------------------------------------------

CREATE DATABASE IF NOT EXISTS `projeto_honda`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `projeto_honda`;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120)  NOT NULL,
    `email`      VARCHAR(180)  NOT NULL,
    `telefone`   VARCHAR(255)   NOT NULL,
  `profile_photo` VARCHAR(255) NULL,
    `password`   VARCHAR(255)  NOT NULL,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Tabela de agendamentos
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
    `duracao_minutos`   SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Duração em minutos (30 = rápida, 120 = completa)',
    `observacoes`       TEXT,
    `status`            ENUM('pendente', 'confirmado', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    `user_id`           INT UNSIGNED,
    `created_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_data_horario` (`data_agendamento`, `horario_inicio`),
    KEY `idx_email` (`email`),
    KEY `idx_placa` (`placa`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Índice para verificar conflitos de agendamento
CREATE INDEX idx_conflito_check ON `agendamentos` (`data_agendamento`, `horario_inicio`, `duracao_minutos`, `status`);
