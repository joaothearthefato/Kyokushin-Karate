-- ═══════════════════════════════════════════════════════════════
--  OYAMA HUB — Schema v2.0
--  Ordem: sem FK quebrada, tipos enxutos, índices úteis,
--  video_url em kihons para o PHP consumir direto.
-- ═══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS oyama_hub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE oyama_hub;

-- ───────────────────────────────────────────────────────────────
-- 1. FAIXAS  (referenciada por usuarios → criada primeiro)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE faixas (
    id     TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome   VARCHAR(70)      NOT NULL,
    ordem  TINYINT UNSIGNED NOT NULL UNIQUE   -- seq. sem duplicata
);

-- ───────────────────────────────────────────────────────────────
-- 2. USUÁRIOS
-- ───────────────────────────────────────────────────────────────
CREATE TABLE usuarios (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    senha_hash  VARCHAR(255)    NOT NULL,          -- bcrypt/argon2 only
    nascimento  DATE            NOT NULL,           -- idade = TIMESTAMPDIFF no SELECT
    tipo        ENUM('aluno','professor','admin')
                NOT NULL DEFAULT 'aluno',
    faixa_id    TINYINT UNSIGNED            DEFAULT NULL,
    ativo       BOOLEAN         NOT NULL    DEFAULT TRUE,
    criado_em   TIMESTAMP       NOT NULL    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (faixa_id) REFERENCES faixas(id)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_tipo    (tipo),
    INDEX idx_faixa   (faixa_id)
);

-- ───────────────────────────────────────────────────────────────
-- 3. CATEGORIAS DE KIHON  (Tsuki, Geri, Uke, Dachi, Uchi)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE kihon_categorias (
    id     TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug   VARCHAR(20)      NOT NULL UNIQUE,  -- 'tsuki' | 'geri' | ...
    nome   VARCHAR(60)      NOT NULL,          -- 'Socos'
    kanji  VARCHAR(30)      NOT NULL,          -- '突き · Tsuki'
    cor    VARCHAR(7)       NOT NULL,          -- hex accent do card
    numero TINYINT UNSIGNED NOT NULL UNIQUE    -- 01..05 para exibição
);

-- ───────────────────────────────────────────────────────────────
-- 4. KIHONS
-- ───────────────────────────────────────────────────────────────
CREATE TABLE kihons (
    id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id TINYINT UNSIGNED  NOT NULL,
    nome         VARCHAR(100)      NOT NULL,          -- 'Soco Direto'
    romaji       VARCHAR(100)      NOT NULL,          -- 'Seiken Tsuki'
    kana         VARCHAR(30)       NOT NULL,          -- '正拳'
    descricao    TEXT              NOT NULL,
    video_url    VARCHAR(255)      DEFAULT NULL,       -- URL completa do YouTube
    nivel        ENUM('iniciante','intermediario','avancado')
                 NOT NULL DEFAULT 'iniciante',
    ordem        TINYINT UNSIGNED  NOT NULL DEFAULT 0, -- ordem no grid

    FOREIGN KEY (categoria_id) REFERENCES kihon_categorias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_categoria (categoria_id),
    INDEX idx_nivel     (nivel)
);

-- ───────────────────────────────────────────────────────────────
-- 5. KATAS
-- ───────────────────────────────────────────────────────────────
CREATE TABLE katas (
    id        SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    descricao TEXT         NOT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    nivel     ENUM('iniciante','intermediario','avancado') NOT NULL,
    ordem     TINYINT UNSIGNED NOT NULL DEFAULT 0
);

-- ───────────────────────────────────────────────────────────────
-- 6. TREINOS REGISTRADOS
-- ───────────────────────────────────────────────────────────────
CREATE TABLE treinos (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT UNSIGNED  NOT NULL,
    duracao_min  SMALLINT UNSIGNED NOT NULL,
    observacoes  TEXT          DEFAULT NULL,
    data_treino  DATE          NOT NULL,
    criado_em    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_usuario_data (usuario_id, data_treino)
);

-- treinos ↔ exercícios (relação N:N, substitui a coluna VARCHAR genérica)
CREATE TABLE treino_exercicios (
    treino_id    INT UNSIGNED    NOT NULL,
    descricao    VARCHAR(255)    NOT NULL,
    series       TINYINT UNSIGNED,
    repeticoes   TINYINT UNSIGNED,

    PRIMARY KEY (treino_id, descricao(100)),
    FOREIGN KEY (treino_id) REFERENCES treinos(id)
        ON DELETE CASCADE
);

-- ───────────────────────────────────────────────────────────────
-- 7. PROGRESSO (katas e kihons unificados em uma tabela)
-- ───────────────────────────────────────────────────────────────
CREATE TABLE progresso (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED     NOT NULL,
    tipo        ENUM('kata','kihon') NOT NULL,
    referencia_id SMALLINT UNSIGNED NOT NULL,   -- kata.id ou kihon.id
    concluido   BOOLEAN          NOT NULL DEFAULT FALSE,
    atualizado  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_progresso (usuario_id, tipo, referencia_id),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);

-- ═══════════════════════════════════════════════════════════════
--  SEED DATA
-- ═══════════════════════════════════════════════════════════════

-- Faixas
INSERT INTO faixas (nome, ordem) VALUES
('Branca (Iniciante)',              1),
('Laranja (10º Kyu)',               2),
('Azul (8º Kyu)',                   3),
('Amarela (6º Kyu)',                4),
('Verde (4º Kyu)',                  5),
('Marrom (2º Kyu)',                 6),
('Marrom com Ponta Preta (1º Kyu)', 7),
('Preta (1º Dan)',                  8);

-- Categorias de Kihon
INSERT INTO kihon_categorias (slug, nome, kanji, cor, numero) VALUES
('tsuki', 'Socos',           '突き · Tsuki', '#c0392b', 1),
('geri',  'Chutes',          '蹴り · Geri',  '#d4af37', 2),
('uke',   'Bloqueios',       '受け · Uke',   '#2980b9', 3),
('dachi', 'Posições',        '立ち · Dachi', '#27ae60', 4),
('uchi',  'Golpes Especiais','打ち · Uchi',  '#8e44ad', 5);

-- ── TSUKI ──────────────────────────────────────────────────────
-- video_url: troque pelos IDs reais quando tiver. Formato: https://www.youtube.com/watch?v=VIDEO_ID
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(1, 'Soco Direto',        'Seiken Tsuki', '正拳',   'Soco básico com os dois primeiros nós dos dedos. O punho gira no final do movimento para potencializar o impacto. Base de todos os socos do Kyokushin.',                                                          'https://www.youtube.com/watch?v=example_seiken',  'iniciante',     1),
(1, 'Soco Reverso',       'Gyaku Tsuki',  '逆突き',  'Soco com a mão oposta à perna da frente. Usa a rotação completa do quadril — o golpe de maior potência no karate. Muito usado em kumite.',                                                                       'https://www.youtube.com/watch?v=example_gyaku',   'iniciante',     2),
(1, 'Soco com Avanço',    'Oi Tsuki',     '追い突き', 'Soco executado enquanto se avança um passo. A perna da frente lidera o movimento e o soco é desferido com a mão do mesmo lado.',                                                                                'https://www.youtube.com/watch?v=example_oi',      'iniciante',     3),
(1, 'Soco Alto',          'Jodan Tsuki',  '上段突き', 'Soco direcionado à cabeça/queixo do adversário. No Kyokushin full-contact, socos à cabeça são proibidos no kumite — mas o kihon os pratica para desenvolver controle de nível.',                               'https://www.youtube.com/watch?v=example_jodan',   'iniciante',     4),
(1, 'Soco Médio',         'Chudan Tsuki', '中段突き', 'Soco ao nível do solar plexus ou costelas. É o alvo principal no kumite do Kyokushin, onde socos ao corpo são permitidos e muito efetivos.',                                                                    'https://www.youtube.com/watch?v=example_chudan',  'iniciante',     5),
(1, 'Socos em Sequência', 'Ren Tsuki',    '連突き',  'Combinação rápida de socos alternados — geralmente dois ou três. Treina a velocidade de recuperação do punho e a manutenção da posição do corpo durante combinações.',                                            'https://www.youtube.com/watch?v=example_ren',     'intermediario', 6);

-- ── GERI ───────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(2, 'Chute Frontal',    'Mae Geri',          '前蹴り',   'Chute em linha reta para frente com a base da planta do pé (koshi). Usado para manter distância e atingir o abdômen. A joelho sobe antes de estender a perna.',                                             'https://www.youtube.com/watch?v=example_mae',       'iniciante',     1),
(2, 'Chute Circular',   'Mawashi Geri',      '回し蹴り',  'Chute em arco horizontal com o peito do pé ou canela. Usado para atingir as costelas lateralmente. No Kyokushin a versão jodan é um dos golpes de maior pontuação.',                                        'https://www.youtube.com/watch?v=example_mawashi',   'iniciante',     2),
(2, 'Low Kick',         'Gedan Mawashi Geri','下段回し',  'Chute circular baixo direcionado à coxa ou panturrilha do adversário com a canela. Muito utilizado no Kyokushin para desgastar e desequilibrar. Golpe de alta frequência em competição.',                   'https://www.youtube.com/watch?v=example_gedan',     'iniciante',     3),
(2, 'Chute de Costas',  'Ushiro Geri',       '後ろ蹴り',  'Chute para trás com o calcanhar, executado após girar o quadril. Extremamente poderoso pela linha reta e peso corporal envolvido. Requer boa consciência espacial.',                                        'https://www.youtube.com/watch?v=example_ushiro',    'intermediario', 4),
(2, 'Joelhada',         'Hiza Geri',         '膝蹴り',   'Ataque com o joelho ao corpo do adversário em distância curta. Muito efetivo no clinch. A mão puxa o adversário para baixo enquanto o joelho sobe para o abdômen.',                                         'https://www.youtube.com/watch?v=example_hiza',      'iniciante',     5),
(2, 'Chute Voador',     'Tobi Geri',         '飛び蹴り',  'Chute executado no ar após um salto. Combina potência e alcance inesperado. Treinado para desenvolver explosão muscular e coordenação. Exige grande habilidade técnica.',                                   'https://www.youtube.com/watch?v=example_tobi',      'avancado',      6),
(2, 'Chute Lateral',    'Yoko Geri',         '横蹴り',   'Chute em linha reta para o lado com o lado do pé (sokuto). O quadril abre completamente e o corpo inclina. Eficiente para criar ângulo e quebrar a guarda lateral.',                                         'https://www.youtube.com/watch?v=example_yoko',      'intermediario', 7);

-- ── UKE ────────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(3, 'Bloqueio Alto',     'Jodan Uke',   '上段受け', 'Bloqueio ascendente do antebraço para proteger a cabeça. O braço vai de baixo para cima, desviando golpes altos para cima e para fora. Mão defensora termina acima da cabeça.',                                    'https://www.youtube.com/watch?v=example_jodan_uke', 'iniciante', 1),
(3, 'Bloqueio Médio',    'Chudan Uke',  '中段受け', 'Bloqueio externo do antebraço ao nível do corpo. Desvia socos e chutes dirigidos ao abdômen para o lado. O antebraço roda no impacto para redirecionar a força.',                                                 'https://www.youtube.com/watch?v=example_chudan_uke','iniciante', 2),
(3, 'Bloqueio Baixo',    'Gedan Barai', '下段払い', 'Varredura descendente do antebraço para bloquear chutes baixos e socos ao abdômen inferior. Movimento de cima para baixo e para fora. Um dos bloqueios mais praticados no kihon.',                                 'https://www.youtube.com/watch?v=example_gedan_bar', 'iniciante', 3),
(3, 'Bloqueio Interno',  'Uchi Uke',    '内受け',  'Bloqueio de dentro para fora com o antebraço. Ideal contra socos retos ao corpo — redireciona a força para o lado externo do atacante, abrindo contra-ataque imediato.',                                           'https://www.youtube.com/watch?v=example_uchi_uke',  'iniciante', 4),
(3, 'Bloqueio Mão-Faca', 'Shuto Uke',   '手刀受け', 'Bloqueio com a lateral da mão aberta (shuto). Pode interceptar socos e também ser usado como ataque. A mão não-defensora fica na cintura em posição de câmara (hikite).',                                         'https://www.youtube.com/watch?v=example_shuto_uke', 'intermediario', 5),
(3, 'Bloqueio Circular', 'Mawashi Uke', '回し受け', 'Bloqueio em movimento circular que redireciona o ataque. Ambas as mãos participam do movimento — uma guia e outra bloqueia. Eficiente para neutralizar chutes circulares.',                                       'https://www.youtube.com/watch?v=example_maw_uke',   'intermediario', 6);

-- ── DACHI ──────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(4, 'Posição Paralela',            'Heiko Dachi',   '平行立ち', 'Pés paralelos na largura dos ombros. Posição natural de repouso e ponto de partida para muitos movimentos. Usada no Yoi antes de executar sequências de kihon.',                                    'https://www.youtube.com/watch?v=example_heiko',   'iniciante', 1),
(4, 'Posição de Combate Frontal',  'Zenkutsu Dachi','前屈立ち', 'Perna da frente dobrada a 90°, perna de trás estendida. Peso ~70% na frente. Excelente para socos com avanço. Proporciona grande força de impulso para frente.',                                     'https://www.youtube.com/watch?v=example_zenk',    'iniciante', 2),
(4, 'Posição do Cavaleiro',        'Kiba Dachi',    '騎馬立ち', 'Pés afastados, joelhos dobrados e para fora, como se montasse um cavalo. Base muito estável e baixa. Excelente para treinar força de pernas e golpes laterais.',                                      'https://www.youtube.com/watch?v=example_kiba',    'iniciante', 3),
(4, 'Posição Recuada',             'Kokutsu Dachi', '後屈立ち', 'Peso ~70% na perna de trás, joelho traseiro dobrado. Posição defensiva que mantém distância e facilita chutes rápidos com a perna da frente.',                                                        'https://www.youtube.com/watch?v=example_koku',    'iniciante', 4),
(4, 'Posição Imóvel',              'Fudo Dachi',    '不動立ち', 'Posição natural de combate do Kyokushin — similar ao zenkutsu mas mais natural. Base do kumite, equilibra mobilidade e estabilidade.',                                                                'https://www.youtube.com/watch?v=example_fudo',    'intermediario', 5),
(4, 'Posição dos Três Conflitos',  'Sanchin Dachi', '三戦立ち', 'Posição fechada e tensa onde os pés se cruzam levemente. Base do kata Sanchin — treina contração muscular total, respiração e tensão corporal. Fundamental no Kyokushin.',                           'https://www.youtube.com/watch?v=example_sanchin', 'intermediario', 6);

-- ── UCHI ───────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(5, 'Golpe Mão-Faca',       'Shuto Uchi',  '手刀打ち', 'Ataque com a lateral da mão aberta em movimento circular. Pode ser executado de dentro para fora ou de fora para dentro. Alvo clássico: pescoço ou têmpora do adversário.',                                   'https://www.youtube.com/watch?v=example_shuto_u', 'intermediario', 1),
(5, 'Golpe Martelo',        'Tetsui Uchi', '鉄槌打ち', 'Golpe com a parte inferior do punho fechado (lado do mindinho), como um martelo. Movimento descendente ou circular. Útil contra alvos duros como o topo da cabeça ou costelas.',                              'https://www.youtube.com/watch?v=example_tetsui',  'intermediario', 2),
(5, 'Golpe Lança-Dedos',    'Nukite',      '貫手',    'Ataque com as pontas dos dedos estendidos, como uma lança. Alvo: garganta, abdômen ou pontos vitais. Exige dedos muito fortalecidos — treinados com makiwara e areia.',                                        'https://www.youtube.com/watch?v=example_nukite',  'avancado',      3),
(5, 'Cotovelada',           'Empi Uchi',   '肘打ち',  'Golpe com a ponta do cotovelo em curta distância. Devastador quando executado corretamente. Pode ser horizontal, ascendente ou descendente.',                                                                   'https://www.youtube.com/watch?v=example_empi',    'intermediario', 4),
(5, 'Soco Um Nó',           'Ippon Ken',   '一本拳',  'Soco com o nó do dedo indicador projetado à frente. Penetra em alvos pequenos e pontos de pressão como têmpora, philtrum ou costelas. Exige condicionamento dos dedos.',                                        'https://www.youtube.com/watch?v=example_ippon',   'avancado',      5),
(5, 'Golpe Dorso do Punho', 'Uraken Uchi', '裏拳打ち', 'Golpe com o dorso (costas) do punho fechado. Movimento rápido de chicote lateral ou circular. Eficiente para atingir a têmpora com velocidade surpreendente.',                                                'https://www.youtube.com/watch?v=example_uraken',  'intermediario', 6);

-- Katas
INSERT INTO katas (nome, descricao, video_url, nivel, ordem) VALUES
('Taikyoku Sono Ichi', 'Kata básico 1 — movimentos fundamentais em linha reta com bloqueios baixos e socos.', NULL, 'iniciante', 1),
('Taikyoku Sono Ni',   'Kata básico 2 — variação com socos médios.',                                          NULL, 'iniciante', 2),
('Taikyoku Sono San',  'Kata básico 3 — introdução de bloqueios internos.',                                   NULL, 'iniciante', 3),
('Pinan Sono Ichi',    'Kata intermediário 1 — sequências mais longas com giros.',                             NULL, 'intermediario', 4),
('Sanchin',            'Kata de respiração e tensão — fundamental no Kyokushin.',                              NULL, 'intermediario', 5);