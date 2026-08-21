-- ═══════════════════════════════════════════════════════════════
--  OYAMA HUB — Schema v3.0 (Consolidated)
-- ═══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS oyama_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
  
USE oyama_hub;

-- ───────────────────────────────────────────────────────────────
-- 1. FAIXAS (Independent)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS faixas (
    id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(70)      NOT NULL,
    ordem      TINYINT UNSIGNED NOT NULL UNIQUE,
    cor        VARCHAR(20)      NOT NULL DEFAULT '#d4af37',
    requisitos TEXT             DEFAULT NULL
);

-- ───────────────────────────────────────────────────────────────
-- 2. CATEGORIAS DE KIHON (Independent)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS kihon_categorias (
    id     TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug   VARCHAR(20)      NOT NULL UNIQUE,
    nome   VARCHAR(60)      NOT NULL,
    kanji  VARCHAR(30)      NOT NULL,
    cor    VARCHAR(7)       NOT NULL,
    numero TINYINT UNSIGNED NOT NULL UNIQUE
);

-- ───────────────────────────────────────────────────────────────
-- 3. KATAS (Independent)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS katas (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100) NOT NULL,
    descricao  TEXT         NOT NULL,
    video_url  VARCHAR(255) DEFAULT NULL,
    imagem_url VARCHAR(255) DEFAULT NULL,
    categoria  VARCHAR(50)  NOT NULL DEFAULT 'Norte (Shotokan)',
    nivel      ENUM('iniciante','intermediario','avancado') NOT NULL DEFAULT 'iniciante',
    ordem      TINYINT UNSIGNED NOT NULL DEFAULT 0
);

-- ───────────────────────────────────────────────────────────────
-- 4. EXERCÍCIOS KYOKUSHIN (Independent)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exercicios_kyokushin (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100) NOT NULL,
    categoria  VARCHAR(50)  NOT NULL,
    tipo       ENUM('Força','Resistência','Técnica','Mobilidade','Soco','Chute','Defesa','Cotovelada','Joelhada') DEFAULT 'Técnica',
    descricao  TEXT         DEFAULT NULL,
    quantidade VARCHAR(50)  DEFAULT NULL,
    video_url  VARCHAR(255) DEFAULT NULL
);

-- ───────────────────────────────────────────────────────────────
-- 5. USUÁRIOS (Depends on faixas)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    senha_hash  VARCHAR(255)    NOT NULL,        
    nascimento  DATE            NOT NULL,           
    tipo        ENUM('aluno','professor','admin') NOT NULL DEFAULT 'aluno',
    faixa_id    TINYINT UNSIGNED DEFAULT NULL,
    ativo       BOOLEAN         NOT NULL DEFAULT TRUE,
    foto_perfil VARCHAR(255)    DEFAULT 'default_avatar.png',
    criado_em   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (faixa_id) REFERENCES faixas(id)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_tipo    (tipo),
    INDEX idx_faixa   (faixa_id)
);

-- ───────────────────────────────────────────────────────────────
-- 6. KIHONS (Depends on kihon_categorias)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS kihons (
    id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id TINYINT UNSIGNED  NOT NULL,
    nome         VARCHAR(100)      NOT NULL,
    romaji       VARCHAR(100)      NOT NULL,
    kana         VARCHAR(30)       NOT NULL,
    descricao    TEXT              NOT NULL,
    video_url    VARCHAR(255)      DEFAULT NULL,
    nivel        ENUM('iniciante','intermediario','avancado') NOT NULL DEFAULT 'iniciante',
    ordem        TINYINT UNSIGNED  NOT NULL DEFAULT 0,

    FOREIGN KEY (categoria_id) REFERENCES kihon_categorias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_categoria (categoria_id),
    INDEX idx_nivel     (nivel)
);

-- ───────────────────────────────────────────────────────────────
-- 7. TREINOS REGISTRADOS (Depends on usuarios)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS treinos (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT UNSIGNED  NOT NULL,
    nome         VARCHAR(100)  NOT NULL DEFAULT 'Treino Kyokushin',
    descricao    TEXT          DEFAULT NULL,
    nivel        ENUM('iniciante','intermediario','avancado') DEFAULT 'iniciante',
    duracao_min  SMALLINT UNSIGNED NOT NULL,
    observacoes  TEXT          DEFAULT NULL,
    data_treino  DATE          NOT NULL,
    criado_em    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_usuario_data (usuario_id, data_treino)
);

-- ───────────────────────────────────────────────────────────────
-- 8. TREINO EXERCICIOS (Depends on treinos e exercicios_kyokushin)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS treino_exercicios (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    treino_id    INT UNSIGNED NOT NULL,
    exercicio_id INT DEFAULT NULL,
    descricao    VARCHAR(255) NOT NULL,
    series       TINYINT UNSIGNED DEFAULT 3,
    repeticoes   TINYINT UNSIGNED DEFAULT 15,

    FOREIGN KEY (treino_id) REFERENCES treinos(id)
        ON DELETE CASCADE,
        
    FOREIGN KEY (exercicio_id) REFERENCES exercicios_kyokushin(id)
        ON DELETE SET NULL
);

-- ───────────────────────────────────────────────────────────────
-- 9. PROGRESSO (Depends on usuarios)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS progresso (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT UNSIGNED     NOT NULL,
    tipo          ENUM('kata','kihon') NOT NULL,
    referencia_id SMALLINT UNSIGNED NOT NULL,
    concluido     BOOLEAN          NOT NULL DEFAULT FALSE,
    atualizado    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_progresso (usuario_id, tipo, referencia_id),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ───────────────────────────────────────────────────────────────
-- 10. LOGS DE ATIVIDADE DO SISTEMA (Depends on usuarios)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS atividades (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED DEFAULT NULL,
    acao       VARCHAR(255) NOT NULL,
    detalhes   TEXT         DEFAULT NULL,
    ip         VARCHAR(45)  DEFAULT NULL,
    criado_em  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
);

-- ═══════════════════════════════════════════════════════════════
--  SEED DATA
-- ═══════════════════════════════════════════════════════════════

-- Faixas
INSERT IGNORE INTO faixas (nome, ordem) VALUES
('Branca (Iniciante)',              1),
('Laranja (10º Kyu)',               2),
('Azul (8º Kyu)',                   3),
('Amarela (6º Kyu)',                4),
('Verde (4º Kyu)',                  5),
('Marrom (2º Kyu)',                 6),
('Marrom com Ponta Preta (1º Kyu)', 7),
('Preta (1º Dan)',                  8);

-- Categorias de Kihon
INSERT IGNORE INTO kihon_categorias (slug, nome, kanji, cor, numero) VALUES
('tsuki', 'Socos',           '突き · Tsuki', '#c0392b', 1),
('geri',  'Chutes',          '蹴り · Geri',  '#d4af37', 2),
('uke',   'Bloqueios',       '受け · Uke',   '#2980b9', 3),
('dachi', 'Posições',        '立ち · Dachi', '#27ae60', 4),
('uchi',  'Golpes Especiais','打ち · Uchi',  '#8e44ad', 5);

-- Kihons
INSERT IGNORE INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(1, 'Soco Direto',        'Seiken Tsuki', '正拳',   'Soco básico com os dois primeiros nós dos dedos.', 'https://www.youtube.com/watch?v=C88wANMHb0Q&pp=ygUVc2Vpa2VuIHRzdWtpIHR1dG9yaWFs',  'iniciante',     1),
(1, 'Soco Reverso',       'Gyaku Tsuki',  '逆突き',  'Soco com a mão oposta à perna da frente.', 'https://www.youtube.com/watch?v=DBzOc2_ETEA&pp=ygULZ3lha3UgdHN1a2nSBwkJ2goBhyohjO8%3D',   'iniciante',     2),
(2, 'Chute Frontal',      'Mae Geri',     '前蹴り',  'Chute em linha reta para frente.', 'https://www.youtube.com/watch?v=yPROqoPx3z8&pp=ygUIbWFlIGdlcmk%3D',       'iniciante',     1),
(2, 'Chute Circular',     'Mawashi Geri', '回し蹴り', 'Chute em arco horizontal com o peito do pé.', 'http://youtube.com/watch?v=hYChPGOfzHU',   'iniciante',     2),
(3, 'Bloqueio Alto',      'Jodan Uke',    '上段受け', 'Bloqueio ascendente do antebraço.', 'https://www.youtube.com/watch?v=WS7ys0uxyMU&pp=0gcJCdoKAYcqIYzv', 'iniciante', 1),
(4, 'Posição Frontal',    'Zenkutsu Dachi','前屈立ち', 'Perna da frente dobrada a 90°.', 'https://www.youtube.com/watch?v=b93Pmv1b44U&pp=ygUOemVua3V0c3UgZGFjaGk%3D',    'iniciante', 2);

-- Katas
INSERT IGNORE INTO katas (nome, descricao, video_url, nivel, ordem) VALUES
('Taikyoku Sono Ichi', 'Primeiro kata do Kyokushin.', 'https://www.youtube.com/watch?v=NCS6QB3ODnM', 'iniciante', 1),
('Taikyoku Sono Ni', 'Idêntico ao Sono Ichi, porém no nível Jodan.', 'https://www.youtube.com/watch?v=W-y0Myy8i9Q', 'iniciante', 2),
('Pinan Sono Ichi', 'Primeiro kata da série Pinan.', 'https://www.youtube.com/watch?v=WejnMH3Q21w', 'iniciante', 4),
('Sanchin no Kata', 'Kata isométrico com respiração Ibuki.', 'https://www.youtube.com/watch?v=pYDEjLVmAmI', 'intermediario', 13);

-- Exercícios Kyokushin
INSERT IGNORE INTO exercicios_kyokushin (nome, categoria, tipo) VALUES
('Seiken Choku Tsuki','Soco', 'Soco'),
('Mae Geri','Chute', 'Chute'),
('Jodan Uke','Defesa', 'Defesa'),
('Empi Uchi Jodan','Cotovelada', 'Cotovelada'),
('Hiza Geri Jodan','Joelhada', 'Joelhada');

-- ═══════════════════════════════════════════════════════════════
--  ADMIN USER SETUP
-- ═══════════════════════════════════════════════════════════════
-- Creates the admin user if not exists
INSERT IGNORE INTO usuarios (nome, email, senha_hash, nascimento, tipo, faixa_id, ativo, foto_perfil) 
VALUES (
    'Administrador Sistema', 
    'admin@admin.com', 
    '$2y$10$r/TVXDwz0muD99PadzTJ2ubc2KTYMfzH0q4kArsAwongs0fLhYRIO', 
    '1990-01-01', 
    'admin', 
    8, 
    1, 
    'default_avatar.png'
);
