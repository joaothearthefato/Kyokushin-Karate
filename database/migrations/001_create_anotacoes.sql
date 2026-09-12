-- Oyama Hub migration 001
-- Safe to run on existing databases. The canonical definition lives in database/schema.sql.
CREATE TABLE IF NOT EXISTS anotacoes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    titulo     VARCHAR(255) NOT NULL,
    conteudo   TEXT NOT NULL,
    categoria  VARCHAR(50) NOT NULL DEFAULT 'Geral',
    cor        VARCHAR(20) NOT NULL DEFAULT 'purple',
    criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_anotacoes_usuario (usuario_id),
    INDEX idx_anotacoes_categoria (usuario_id, categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    usado_em   DATETIME DEFAULT NULL,
    criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_password_resets_expiry (expires_at),
    INDEX idx_password_resets_user (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
