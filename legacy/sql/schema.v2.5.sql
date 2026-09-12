-- LEGACY: a fonte operacional do schema é database/schema.sql.
-- Este arquivo é mantido apenas como referência histórica da versão 2.5.
-- Novas instalações e migrações devem usar database/schema.sql e database/migrations/.
CREATE DATABASE IF NOT EXISTS oyama_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
  
USE oyama_hub;

-- ───────────────────────────────────────────────────────────────
-- 1. FAIXAS
-- ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS faixas (
    id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(70)      NOT NULL,
    ordem      TINYINT UNSIGNED NOT NULL UNIQUE,
    cor        VARCHAR(20)      NOT NULL DEFAULT '#d4af37',
    requisitos TEXT             DEFAULT NULL
);

-- ───────────────────────────────────────────────────────────────
-- 2. USUÁRIOS
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
-- 3. CATEGORIAS DE KIHON
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
-- 4. KIHONS
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
-- 5. KATAS
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
-- 6. TREINOS REGISTRADOS
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

-- treinos ↔ exercícios
CREATE TABLE IF NOT EXISTS treino_exercicios (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    treino_id    INT UNSIGNED NOT NULL,
    exercicio_id INT DEFAULT NULL,
    descricao    VARCHAR(255) NOT NULL,
    series       TINYINT UNSIGNED DEFAULT 3,
    repeticoes   TINYINT UNSIGNED DEFAULT 15,

    FOREIGN KEY (treino_id) REFERENCES treinos(id)
        ON DELETE CASCADE
);

-- ───────────────────────────────────────────────────────────────
-- 7. PROGRESSO
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
-- 8. EXERCÍCIOS KYOKUSHIN
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
-- 9. LOGS DE ATIVIDADE DO SISTEMA
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
('Branca (10º Kyu)',              1),
('Laranja (9º Kyu)',               2),
('Azul (8º Kyu)',                   3),
('Amarela (6º Kyu)',                4),
('Verde (4º Kyu)',                  5),
('Marrom (2º Kyu)',                 6),
('Marrom com Ponta Preta (1º Kyu)', 7),
('Preta (1º Dan)',                  8);


-- ═══════════════════════════════════════════════════════════════
--  KIHONS
-- ═══════════════════════════════════════════════════════════════

INSERT INTO kihons
(categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem)
VALUES
(1, 'Soco médio', 'Seiken Chudan Tsuki', 'せいけんちゅうだんづき',
 'Soco direto direcionado à região média do corpo.',
 'https://www.youtube.com/watch?v=JcEJXgudhmE', 'iniciante', 1),

(1, 'Soco alto', 'Seiken Jodan Tsuki', 'せいけんじょうだんづき',
 'Soco direto direcionado à região superior do corpo.',
 'https://www.youtube.com/watch?v=575l-u9rbB0', 'iniciante', 2),

(2, 'Chute frontal', 'Mae Geri', 'まえげり',
 'Chute frontal direto direcionado ao alvo à frente.',
 'https://www.youtube.com/watch?v=D2fdO_QepKs', 'iniciante', 3),

(2, 'Chute frontal ascendente', 'Mae Keage', 'まえけあげ',
 'Chute frontal ascendente realizado com a perna estendida.',
 'https://www.youtube.com/watch?v=V2e_adl_qCM', 'iniciante', 4),

(2, 'Joelhada', 'Hiza Geri', 'ひざげり',
 'Golpe realizado com o joelho.',
 'https://www.youtube.com/watch?v=5mOmOXZ-FNU', 'iniciante', 5),

(2, 'Chute à virilha', 'Kin Geri / Kinteki Geri', 'きんてきげり',
 'Chute direcionado à região da virilha.',
 'https://www.youtube.com/watch?v=JntaVndHl5U', 'iniciante', 6),

(3, 'Bloqueio alto', 'Seiken Jodan Uke', 'せいけんじょうだんうけ',
 'Bloqueio alto utilizado para proteger a região superior do corpo.',
 'https://www.youtube.com/watch?v=WS7ys0uxyMU', 'iniciante', 7),

(3, 'Bloqueio interno médio', 'Seiken Chudan Uchi Uke', 'せいけんちゅうだんうちうけ',
 'Bloqueio interno realizado na altura média do corpo.',
 'https://www.youtube.com/watch?v=ZjRI_Abs1UQ', 'iniciante', 8),

(3, 'Bloqueio externo médio', 'Seiken Chudan Soto Uke', 'せいけんちゅうだんそとうけ',
 'Bloqueio externo realizado na altura média do corpo.',
 'https://www.youtube.com/watch?v=JXOtx6aqeg0', 'iniciante', 9),

(3, 'Bloqueio baixo', 'Seiken Gedan Barai', 'せいけんげだんばらい',
 'Bloqueio descendente utilizado para proteger a região inferior.',
 'https://www.youtube.com/watch?v=f2Xdk6__7Ts', 'iniciante', 10),

(4, 'Posição natural/firme', 'Fudo Dachi', 'ふどうだち',
 'Posição firme utilizada como base para execução das técnicas.',
 'https://www.youtube.com/watch?v=rtM5H0lpe20', 'iniciante', 11),

(4, 'Posição frontal', 'Zenkutsu Dachi', 'ぜんくつだち',
 'Base frontal com distribuição de peso voltada para a perna dianteira.',
 'https://www.youtube.com/watch?v=VLohuq5fPr0', 'iniciante', 12),

(4, 'Posição do cavaleiro', 'Kiba Dachi', 'きばだち',
 'Base ampla com os pés paralelos e joelhos flexionados.',
 'https://www.youtube.com/watch?v=b04SKpx7-H4', 'iniciante', 13),

(4, 'Posição traseira', 'Kokutsu Dachi', 'こうくつだち',
 'Base com maior distribuição de peso sobre a perna traseira.',
 'https://www.youtube.com/watch?v=7wtD_8d9JgM', 'iniciante', 14);
 
 -- ───────────────────────────────────────────────────────────────
-- INSERT DOS KATAS POR FAIXA (KYOKUSHIN)
-- ───────────────────────────────────────────────────────────────

INSERT INTO katas (nome, descricao, video_url, categoria, nivel, ordem) VALUES

-- ⚪ FAIXA BRANCA (Iniciante)
('Taikyoku Sono Ichi', 'Primeiro kata básico do Kyokushin. Foco em posições Zenkutsu Dachi e socos Chudan.', 'https://www.youtube.com/watch?v=NCS6QB3ODnM', 'Taikyoku', 'iniciante', 1),
('Taikyoku Sono Ni', 'Segundo kata básico do Kyokushin, similar ao Sono Ichi, porém aplicando socos Jodan.', 'https://www.youtube.com/watch?v=W-y0Myy8i9Q', 'Taikyoku', 'iniciante', 2),

-- 🟠 FAIXA LARANJA (10º / 9º Kyu)
('Taikyoku Sono San', 'Terceiro kata básico, introduzindo bloqueios Uchi Uke e posições Kiba Dachi.', 'https://www.youtube.com/watch?v=5j1i4LHSet0', 'Taikyoku', 'iniciante', 3),
('Sokugi Taikyoku Sono Ichi', 'Primeiro kata focado exclusivamente em técnicas de chutes (Sokugi).', 'https://www.youtube.com/watch?v=WfEYcnmT7M4', 'Sokugi', 'iniciante', 4),

-- 🔵 FAIXA AZUL (8º / 7º Kyu)
('Pinan Sono Ichi', 'Primeiro kata da série Pinan. Trabalha esquivas, defesas e ataques variados.', 'https://www.youtube.com/watch?v=TEa5SPNTCLg', 'Pinan', 'iniciante', 5),
('Pinan Sono Ni', 'Segundo kata da série Pinan, introduzindo defesas Kokutsu Dachi e chutes Mae Geri.', 'https://www.youtube.com/watch?v=6JLhRJdzJyA', 'Pinan', 'iniciante', 6),
('Sokugi Taikyoku Sono Ni', 'Segundo kata da série Sokugi, trabalhando chutes em novas direções e ângulos.', 'https://www.youtube.com/watch?v=Ce-C_X1v1CQ', 'Sokugi', 'iniciante', 7),

-- 🟡 FAIXA AMARELA (6º / 5º Kyu)
('Pinan Sono San', 'Terceiro kata da série Pinan, focado em defesas duplas, cotoveladas e giros.', 'https://www.youtube.com/watch?v=HqxrJFDqCbw', 'Pinan', 'intermediario', 8),
('Pinan Sono Yon', 'Quarto kata da série Pinan, extremamente dinâmico, com chutes e combinações rápidas.', 'https://www.youtube.com/watch?v=WrFtKCmgdwU', 'Pinan', 'intermediario', 9),
('Pinan Sono Go', 'Quinto kata da série Pinan, introduzindo saltos, defesas baixas e ataques sequenciais.', 'https://www.youtube.com/watch?v=R6ZB1dYoPXY', 'Pinan', 'intermediario', 10),
('Sokugi Taikyoku Sono San', 'Terceiro kata da série Sokugi, avançando em variações de chutes combinados.', 'https://www.youtube.com/watch?v=xH0yLnndYJw', 'Sokugi', 'intermediario', 11),

-- 🟢 FAIXA VERDE (4º / 3º Kyu)
('Yantsu', 'Kata avançado focado na manutenção da energia interna e fortalecimento de golpes curtos.', 'https://www.youtube.com/watch?v=Pb0xsTDKrjo', 'Avançado', 'intermediario', 12),
('Sanchin no Kata', 'Kata tradicional isométrico focado no fortalecimento corporal e respiração profunda Ibuki.', 'https://www.youtube.com/watch?v=QPGLyHwtepA', 'Avançado', 'intermediario', 13),
('Tsuki no Kata', 'Kata focado inteiramente na precisão, potência e sequências de socos e perfurações.', 'https://www.youtube.com/watch?v=wcieC2HfnLY', 'Avançado', 'intermediario', 14),

-- 🟤 FAIXA MARROM — 2º Kyu
('Saifa', 'Kata de origem Goju-Ryu que significa "destruir e esmagar". Rápido, ágil e explosivo.', 'https://www.youtube.com/watch?v=LNtxEnEmRUw', 'Goju-Ryu', 'avancado', 15),
('Gekisai Sono Ichi', 'Kata projetado para combate real, enfatizando ataques diretos e contundentes.', 'https://www.youtube.com/watch?v=lv43m1liVCs', 'Goju-Ryu', 'avancado', 16),
('Tekki Sono Ichi', 'Kata executado totalmente na posição Kiba Dachi (posição do cavalo), simulando combate em locais estreitos.', 'https://www.youtube.com/watch?v=_5w-JLSsLU8', 'Shorei-Ryu', 'avancado', 17),

-- 🟤 FAIXA MARROM — 1º Kyu
('Gekisai Sono Ni', 'Segunda variação do Gekisai, adicionando esquivas fluidas e ataques com as mãos abertas (Shuto).', 'https://www.youtube.com/watch?v=6xmHr9ZhbH0', 'Goju-Ryu', 'avancado', 18);

-- treinos

INSERT INTO exercicios_kyokushin
(nome, categoria, tipo, descricao, quantidade, video_url)
VALUES

-- FORÇA
('Flexão de braço', 'Força', 'Força',
 'Flexão tradicional para fortalecimento de peito, ombros e tríceps.',
 '3 x 15', NULL),

('Flexão fechada', 'Força', 'Força',
 'Flexão com as mãos mais próximas, enfatizando tríceps e estabilidade.',
 '3 x 10', NULL),

('Agachamento livre', 'Força', 'Força',
 'Agachamento com o peso corporal para fortalecimento das pernas.',
 '3 x 20', NULL),

('Agachamento sumô', 'Força', 'Força',
 'Agachamento com base ampla para trabalhar pernas e quadril.',
 '3 x 15', NULL),

('Afundo', 'Força', 'Força',
 'Exercício unilateral para fortalecimento das pernas e estabilidade.',
 '3 x 10 cada perna', NULL),

('Elevação de panturrilha', 'Força', 'Força',
 'Elevação do corpo sobre a ponta dos pés para fortalecer as panturrilhas.',
 '3 x 20', NULL),

('Abdominal tradicional', 'Força', 'Força',
 'Abdominal para fortalecimento da musculatura do tronco.',
 '3 x 20', NULL),

('Abdominal bicicleta', 'Força', 'Força',
 'Exercício abdominal com movimento alternado de pernas e tronco.',
 '3 x 20', NULL),

-- CORE / RESISTÊNCIA
('Prancha', 'Core', 'Resistência',
 'Isometria para fortalecimento do abdômen e estabilização do tronco.',
 '3 x 30 segundos', NULL),

('Prancha lateral', 'Core', 'Resistência',
 'Isometria lateral para fortalecimento do core e estabilidade do quadril.',
 '3 x 20 segundos cada lado', NULL),

('Mountain climber', 'Resistência', 'Resistência',
 'Movimento dinâmico para trabalhar core e condicionamento cardiovascular.',
 '3 x 30 segundos', NULL),

('Burpee', 'Resistência', 'Resistência',
 'Exercício de corpo inteiro para desenvolver resistência e explosão.',
 '3 x 10', NULL),

('Polichinelo', 'Resistência', 'Resistência',
 'Exercício cardiovascular utilizado para aquecimento e condicionamento.',
 '3 x 30', NULL),

('Corrida estacionária', 'Resistência', 'Resistência',
 'Corrida no lugar para elevar a frequência cardíaca e melhorar o condicionamento.',
 '3 x 1 minuto', NULL),

('Pular corda', 'Resistência', 'Resistência',
 'Exercício cardiovascular para coordenação, resistência e agilidade.',
 '3 x 2 minutos', NULL),

-- TÉCNICA
('Kihon de socos', 'Técnica', 'Técnica',
 'Sequência de socos básicos do Kyokushin executados com controle e técnica.',
 '3 x 20', NULL),

('Kihon de chutes', 'Técnica', 'Chute',
 'Sequência de chutes básicos do Kyokushin executados com controle técnico.',
 '3 x 10 cada perna', NULL),

('Kihon de bloqueios', 'Técnica', 'Defesa',
 'Sequência de bloqueios básicos do Kyokushin.',
 '3 x 10', NULL),

('Ido Kihon', 'Técnica', 'Técnica',
 'Execução de técnicas básicas em deslocamento.',
 '3 séries', NULL),

-- SOCO
('Seiken Tsuki no Makiwara', 'Condicionamento', 'Soco',
 'Prática controlada de socos no makiwara para desenvolver técnica e condicionamento.',
 '3 x 20', NULL),

('Soco direto no ar', 'Técnica', 'Soco',
 'Execução repetida de Seiken Tsuki com foco em velocidade e retorno da guarda.',
 '3 x 30', NULL),

-- CHUTE
('Mae Geri no ar', 'Técnica', 'Chute',
 'Repetição de Mae Geri com foco em equilíbrio, velocidade e técnica.',
 '3 x 10 cada perna', NULL),

('Mawashi Geri no ar', 'Técnica', 'Chute',
 'Repetição de Mawashi Geri com foco em controle e mobilidade do quadril.',
 '3 x 10 cada perna', NULL),

('Mae Keage', 'Técnica', 'Chute',
 'Repetição do chute frontal ascendente.',
 '3 x 10 cada perna', NULL),

-- AGILIDADE / EXPLOSÃO
('Saltos verticais', 'Explosão', 'Força',
 'Saltos explosivos para desenvolvimento de potência das pernas.',
 '3 x 10', NULL),

('Salto agachado', 'Explosão', 'Força',
 'Agachamento seguido de salto explosivo para desenvolver potência.',
 '3 x 10', NULL),

('Deslocamento lateral', 'Agilidade', 'Mobilidade',
 'Deslocamentos laterais rápidos para desenvolver agilidade e movimentação.',
 '3 x 30 segundos', NULL),

('Sprawl', 'Agilidade', 'Resistência',
 'Movimento rápido de queda e recuperação utilizado para condicionamento.',
 '3 x 10', NULL),

-- MOBILIDADE
('Mobilidade de quadril', 'Mobilidade', 'Mobilidade',
 'Sequência de movimentos para melhorar a mobilidade do quadril.',
 '3 x 30 segundos', NULL),

('Alongamento posterior de coxa', 'Mobilidade', 'Mobilidade',
 'Alongamento para melhorar a flexibilidade da cadeia posterior das pernas.',
 '3 x 30 segundos', NULL),

('Alongamento de borboleta', 'Mobilidade', 'Mobilidade',
 'Alongamento para adutores e mobilidade do quadril.',
 '3 x 30 segundos', NULL),

('Alongamento de quadríceps', 'Mobilidade', 'Mobilidade',
 'Alongamento dos músculos anteriores da coxa.',
 '3 x 30 segundos cada perna', NULL),

-- CONDICIONAMENTO KYOKUSHIN
('Flexão com socos', 'Condicionamento', 'Força',
 'Sequência combinando flexão de braço e socos para condicionamento geral.',
 '3 x 10', NULL),

('Agachamento com Mae Geri', 'Condicionamento', 'Chute',
 'Agachamento seguido de Mae Geri para combinar força e técnica.',
 '3 x 10 cada perna', NULL),

('Burpee com salto', 'Condicionamento', 'Resistência',
 'Burpee realizado com salto explosivo ao final do movimento.',
 '3 x 10', NULL),

('Abdominal com socos', 'Condicionamento', 'Soco',
 'Exercício combinando trabalho abdominal com golpes de punho.',
 '3 x 20', NULL),

('Chute alternado', 'Condicionamento', 'Chute',
 'Execução alternada de chutes para desenvolver resistência específica.',
 '3 x 20', NULL);