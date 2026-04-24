-- ═══════════════════════════════════════════════════════════════
--  OYAMA HUB — Migrations v2.1
--  Execute este arquivo APÓS o sql.sql principal
-- ═══════════════════════════════════════════════════════════════

USE oyama_hub;

-- ── Tabela de Anotações ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS anotacoes (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED    NOT NULL,
    titulo      VARCHAR(150)    NOT NULL,
    conteudo    TEXT            NOT NULL,
    cor         ENUM('red','gold','green','blue','purple') NOT NULL DEFAULT 'red',
    criado_em   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,

    INDEX idx_anotacoes_usuario (usuario_id),
    FULLTEXT idx_anotacoes_busca (titulo, conteudo)
);

-- ── Coluna favorito em progresso ──────────────────────────────
ALTER TABLE progresso
    ADD COLUMN IF NOT EXISTS favorito BOOLEAN NOT NULL DEFAULT FALSE;

-- ── Coluna foto_perfil em usuarios ────────────────────────────
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(255) DEFAULT NULL;

-- ── Coluna ativo em katas ──────────────────────────────────────
ALTER TABLE katas
    ADD COLUMN IF NOT EXISTS ativo BOOLEAN NOT NULL DEFAULT TRUE;

-- ── Coluna ativo em kihons ─────────────────────────────────────
ALTER TABLE kihons
    ADD COLUMN IF NOT EXISTS ativo BOOLEAN NOT NULL DEFAULT TRUE;

-- ── Tabela para recuperação de senha ─────────────────────────
CREATE TABLE IF NOT EXISTS reset_senha (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED    NOT NULL,
    token       VARCHAR(64)     NOT NULL UNIQUE,
    expira_em   DATETIME        NOT NULL,
    usado       BOOLEAN         NOT NULL DEFAULT FALSE,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,

    INDEX idx_reset_token (token)
);
