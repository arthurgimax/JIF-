-- =====================================================================
-- JIF+ — Plataforma de Gestão e Inteligência Esportiva
-- Esquema Oficial do Banco de Dados Relacional (MySQL / MariaDB)
-- Especificação Técnica: spec.md (Seção 6) | Mecanismo: InnoDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `jif_plus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jif_plus`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `estatisticas_equipes`;
DROP TABLE IF EXISTS `estatisticas_atletas`;
DROP TABLE IF EXISTS `partidas`;
DROP TABLE IF EXISTS `equipe_atletas`;
DROP TABLE IF EXISTS `atletas`;
DROP TABLE IF EXISTS `equipes`;
DROP TABLE IF EXISTS `modalidades`;
DROP TABLE IF EXISTS `competicoes`;
DROP TABLE IF EXISTS `instituicoes`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. TABELA DE USUÁRIOS E PERFIS DE ACESSO (RBAC)
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha_hash` VARCHAR(255) NOT NULL,
    `perfil` ENUM('admin', 'organizador', 'treinador', 'atleta') NOT NULL DEFAULT 'atleta',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_usuarios_email` (`email`),
    INDEX `idx_usuarios_perfil` (`perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABELA DE INSTITUIÇÕES E CAMPI
CREATE TABLE `instituicoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `sigla` VARCHAR(30) NOT NULL,
    `campus` VARCHAR(100) NOT NULL,
    `estado` CHAR(2) NOT NULL,
    INDEX `idx_instituicoes_sigla` (`sigla`),
    INDEX `idx_instituicoes_campus` (`campus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABELA DE COMPETIÇÕES
CREATE TABLE `competicoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `instituicao_organizadora_id` INT NOT NULL,
    `nome` VARCHAR(150) NOT NULL,
    `edicao` VARCHAR(50) NOT NULL,
    `ano` INT NOT NULL,
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE NOT NULL,
    `local_sede` VARCHAR(150) NOT NULL,
    `status` ENUM('planejada', 'em_andamento', 'encerrada') NOT NULL DEFAULT 'planejada',
    CONSTRAINT `fk_competicoes_instituicao`
        FOREIGN KEY (`instituicao_organizadora_id`) REFERENCES `instituicoes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_competicoes_status` (`status`),
    INDEX `idx_competicoes_ano` (`ano`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABELA DE MODALIDADES ESPORTIVAS
CREATE TABLE `modalidades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `competicao_id` INT NOT NULL,
    `nome` VARCHAR(100) NOT NULL,
    `categoria` ENUM('masculino', 'feminino', 'misto') NOT NULL,
    `tipo` ENUM('coletiva', 'individual') NOT NULL,
    `status` ENUM('ativa', 'inativa') NOT NULL DEFAULT 'ativa',
    CONSTRAINT `fk_modalidades_competicao`
        FOREIGN KEY (`competicao_id`) REFERENCES `competicoes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_modalidades_nome` (`nome`),
    INDEX `idx_modalidades_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABELA DE EQUIPES
CREATE TABLE `equipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `instituicao_id` INT NOT NULL,
    `competicao_id` INT NOT NULL,
    `modalidade_id` INT NOT NULL,
    `responsavel_id` INT NOT NULL,
    `nome` VARCHAR(150) NOT NULL,
    CONSTRAINT `fk_equipes_instituicao`
        FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_equipes_competicao`
        FOREIGN KEY (`competicao_id`) REFERENCES `competicoes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_equipes_modalidade`
        FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_equipes_responsavel`
        FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_equipes_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABELA DE ATLETAS
CREATE TABLE `atletas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `instituicao_id` INT NOT NULL,
    `nome` VARCHAR(150) NOT NULL,
    `matricula_estudantil` VARCHAR(50) NOT NULL,
    `data_nascimento` DATE NULL,
    `foto_url` VARCHAR(255) NULL,
    `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_atletas_usuario`
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_atletas_instituicao`
        FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_atletas_matricula` (`matricula_estudantil`),
    INDEX `idx_atletas_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABELA DE VÍNCULO EQUIPE-ATLETA (INSCRIÇÃO)
CREATE TABLE `equipe_atletas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `equipe_id` INT NOT NULL,
    `atleta_id` INT NOT NULL,
    `numero_camisa` INT NULL,
    `posicao` VARCHAR(50) NULL,
    `status_inscricao` ENUM('regular', 'suspenso', 'inativo') NOT NULL DEFAULT 'regular',
    CONSTRAINT `fk_equipe_atletas_equipe`
        FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_equipe_atletas_atleta`
        FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_equipe_atleta` (`equipe_id`, `atleta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABELA DE PARTIDAS
CREATE TABLE `partidas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `competicao_id` INT NOT NULL,
    `modalidade_id` INT NOT NULL,
    `equipe_a_id` INT NOT NULL,
    `equipe_b_id` INT NOT NULL,
    `fase_rodada` VARCHAR(50) NOT NULL,
    `data_hora` DATETIME NOT NULL,
    `local_quadra` VARCHAR(150) NOT NULL,
    `placar_a` INT NULL DEFAULT NULL,
    `placar_b` INT NULL DEFAULT NULL,
    `situacao` ENUM('agendada', 'em_andamento', 'encerrada', 'cancelada') NOT NULL DEFAULT 'agendada',
    CONSTRAINT `fk_partidas_competicao`
        FOREIGN KEY (`competicao_id`) REFERENCES `competicoes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_partidas_modalidade`
        FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_partidas_equipe_a`
        FOREIGN KEY (`equipe_a_id`) REFERENCES `equipes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_partidas_equipe_b`
        FOREIGN KEY (`equipe_b_id`) REFERENCES `equipes` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_partidas_data_hora` (`data_hora`),
    INDEX `idx_partidas_situacao` (`situacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABELA DE ESTATÍSTICAS INDIVIDUAIS DE ATLETAS
CREATE TABLE `estatisticas_atletas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `partida_id` INT NOT NULL,
    `atleta_id` INT NOT NULL,
    `equipe_id` INT NOT NULL,
    `tipo_estatistica` VARCHAR(50) NOT NULL, -- 'gol', 'ponto', 'falta', 'cartao_amarelo', 'cartao_vermelho'
    `valor` INT NOT NULL DEFAULT 1,
    CONSTRAINT `fk_estatisticas_partida`
        FOREIGN KEY (`partida_id`) REFERENCES `partidas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_estatisticas_atleta`
        FOREIGN KEY (`atleta_id`) REFERENCES `atletas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_estatisticas_equipe`
        FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_estatisticas_tipo` (`tipo_estatistica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABELA DE ESTATÍSTICAS COLETIVAS DE EQUIPES
CREATE TABLE `estatisticas_equipes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `partida_id` INT NOT NULL,
    `equipe_id` INT NOT NULL,
    `tipo_estatistica` VARCHAR(50) NOT NULL, -- 'pontos_total', 'faltas_coletivas', 'sets_vencidos'
    `valor` INT NOT NULL,
    CONSTRAINT `fk_estatisticas_equipes_partida`
        FOREIGN KEY (`partida_id`) REFERENCES `partidas` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_estatisticas_equipes_equipe`
        FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_estatisticas_equipes_tipo` (`tipo_estatistica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
