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
    senha_hash  VARCHAR(255)    NOT NULL,        
    nascimento  DATE            NOT NULL,           
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
(1, 'Soco Direto',        'Seiken Tsuki', '正拳',   'Soco básico com os dois primeiros nós dos dedos. O punho gira no final do movimento para potencializar o impacto. Base de todos os socos do Kyokushin.',                                                          'https://www.youtube.com/watch?v=C88wANMHb0Q&pp=ygUVc2Vpa2VuIHRzdWtpIHR1dG9yaWFs',  'iniciante',     1),
(1, 'Soco Reverso',       'Gyaku Tsuki',  '逆突き',  'Soco com a mão oposta à perna da frente. Usa a rotação completa do quadril — o golpe de maior potência no karate. Muito usado em kumite.',                                                                       'https://www.youtube.com/watch?v=DBzOc2_ETEA&pp=ygULZ3lha3UgdHN1a2nSBwkJ2goBhyohjO8%3D',   'iniciante',     2),
(1, 'Soco com Avanço',    'Oi Tsuki',     '追い突き', 'Soco executado enquanto se avança um passo. A perna da frente lidera o movimento e o soco é desferido com a mão do mesmo lado.',                                                                                'https://www.youtube.com/watch?v=43iXcMfl5aE&pp=ygUIb2kgdHN1a2k%3D',      'iniciante',     3),
(1, 'Soco Alto',          'Jodan Tsuki',  '上段突き', 'Soco direcionado à cabeça/queixo do adversário. No Kyokushin full-contact, socos à cabeça são proibidos no kumite — mas o kihon os pratica para desenvolver controle de nível.',                               'https://www.youtube.com/watch?v=NZEkDYGsgJY&pp=ygUSSk9EQU4gVFNVS0kga2FyYXRl',   'iniciante',     4),
(1, 'Soco Médio',         'Chudan Tsuki', '中段突き', 'Soco ao nível do solar plexus ou costelas. É o alvo principal no kumite do Kyokushin, onde socos ao corpo são permitidos e muito efetivos.',                                                                    'https://www.youtube.com/watch?v=C88wANMHb0Q&pp=ygUTY2h1ZGFuIFRTVUtJIGthcmF0ZQ%3D%3D',  'iniciante',     5),
(1, 'Socos em Sequência', 'Ren Tsuki',    '連突き',  'Combinação rápida de socos alternados — geralmente dois ou três. Treina a velocidade de recuperação do punho e a manutenção da posição do corpo durante combinações.',                                            'https://www.youtube.com/watch?v=XddFZONffYs&pp=ygUQcmVuIFRTVUtJIGthcmF0ZQ%3D%3D',     'intermediario', 6);

-- ── GERI ───────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(2, 'Chute Frontal',    'Mae Geri',          '前蹴り',   'Chute em linha reta para frente com a base da planta do pé (koshi). Usado para manter distância e atingir o abdômen. A joelho sobe antes de estender a perna.',                                             'http://youtube.com/watch?v=hYChPGOfzHU',       'iniciante',     1),
(2, 'Chute Circular',   'Mawashi Geri',      '回し蹴り',  'Chute em arco horizontal com o peito do pé ou canela. Usado para atingir as costelas lateralmente. No Kyokushin a versão jodan é um dos golpes de maior pontuação.',                                        'http://youtube.com/watch?v=hYChPGOfzHU',   'iniciante',     2),
(2, 'Low Kick',         'Gedan Mawashi Geri','下段回し',  'Chute circular baixo direcionado à coxa ou panturrilha do adversário com a canela. Muito utilizado no Kyokushin para desgastar e desequilibrar. Golpe de alta frequência em competição.',                   'https://www.youtube.com/watch?v=IDOvfrQwBcQ&pp=ygUjZ2VkYW4gbWF3YXNoaSBnZXJpIHR1dG9yaWFsIEtPIERPSk8%3D',     'iniciante',     3),
(2, 'Chute de Costas',  'Ushiro Geri',       '後ろ蹴り',  'Chute para trás com o calcanhar, executado após girar o quadril. Extremamente poderoso pela linha reta e peso corporal envolvido. Requer boa consciência espacial.',                                        'https://www.youtube.com/watch?v=JcEJXgudhmE',    'intermediario', 4),
(2, 'Joelhada',         'Hiza Geri',         '膝蹴り',   'Ataque com o joelho ao corpo do adversário em distância curta. Muito efetivo no clinch. A mão puxa o adversário para baixo enquanto o joelho sobe para o abdômen.',                                         'https://www.youtube.com/watch?v=LKfVBzQg9eQ',      'iniciante',     5),
(2, 'Chute Voador',     'Tobi Geri',         '飛び蹴り',  'Chute executado no ar após um salto. Combina potência e alcance inesperado. Treinado para desenvolver explosão muscular e coordenação. Exige grande habilidade técnica.',                                   'https://www.youtube.com/watch?v=tUFvvza3HWY&pp=ugMICgJwdBABGAHKBRJ0b2JpIGdlcmkgdHV0b3JpYWw%3D',      'avancado',      6),
(2, 'Chute Lateral',    'Yoko Geri',         '横蹴り',   'Chute em linha reta para o lado com o lado do pé (sokuto). O quadril abre completamente e o corpo inclina. Eficiente para criar ângulo e quebrar a guarda lateral.',                                         'https://www.youtube.com/watch?v=cHZ1P3h8Xg4',      'intermediario', 7);

-- ── UKE ────────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(3, 'Bloqueio Alto',     'Jodan Uke',   '上段受け', 'Bloqueio ascendente do antebraço para proteger a cabeça. O braço vai de baixo para cima, desviando golpes altos para cima e para fora. Mão defensora termina acima da cabeça.',                                    'https://www.youtube.com/watch?v=WS7ys0uxyMU&pp=0gcJCdoKAYcqIYzv', 'iniciante', 1),
(3, 'Bloqueio Médio',    'Chudan Uke',  '中段受け', 'Bloqueio externo do antebraço ao nível do corpo. Desvia socos e chutes dirigidos ao abdômen para o lado. O antebraço roda no impacto para redirecionar a força.',                                                 'https://www.youtube.com/watch?v=VikdV4S2b70&pp=ygUKY2h1ZGFuIHVrZQ%3D%3D','iniciante', 2),
(3, 'Bloqueio Baixo',    'Gedan Barai', '下段払い', 'Varredura descendente do antebraço para bloquear chutes baixos e socos ao abdômen inferior. Movimento de cima para baixo e para fora. Um dos bloqueios mais praticados no kihon.',                                 'https://www.youtube.com/watch?v=rOgfT21eoGQ', 'iniciante', 3),
(3, 'Bloqueio Interno',  'Uchi Uke',    '内受け',  'Bloqueio de dentro para fora com o antebraço. Ideal contra socos retos ao corpo — redireciona a força para o lado externo do atacante, abrindo contra-ataque imediato.',                                           'https://www.youtube.com/watch?v=ZjRI_Abs1UQ',  'iniciante', 4),
(3, 'Bloqueio Mão-Faca', 'Shuto Uke',   '手刀受け', 'Bloqueio com a lateral da mão aberta (shuto). Pode interceptar socos e também ser usado como ataque. A mão não-defensora fica na cintura em posição de câmara (hikite).',                                         'https://www.youtube.com/watch?v=3Po67EodOdM&pp=ygUJc2hvdG8gdWtl0gcJCdoKAYcqIYzv', 'intermediario', 5),
(3, 'Bloqueio Circular', 'Mawas hi Uke', '回し受け', 'Bloqueio em movimento circular que redireciona o ataque. Ambas as mãos participam do movimento — uma guia e outra bloqueia. Eficiente para neutralizar chutes circulares.',                                       'https://www.youtube.com/watch?v=O6lmZ8IkyUM&pp=ygUM5Zue44GX5Y-X44GR',   'intermediario', 6);

-- ── DACHI ──────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(4, 'Posição Paralela',            'Heiko Dachi',   '平行立ち', 'Pés paralelos na largura dos ombros. Posição natural de repouso e ponto de partida para muitos movimentos. Usada no Yoi antes de executar sequências de kihon.',                                    'https://www.youtube.com/watch?v=vV3L5iQ8CiU&pp=ygULaGVpa28gZGFjaGk%3D',   'iniciante', 1),
(4, 'Posição de Combate Frontal',  'Zenkutsu Dachi','前屈立ち', 'Perna da frente dobrada a 90°, perna de trás estendida. Peso ~70% na frente. Excelente para socos com avanço. Proporciona grande força de impulso para frente.',                                     'https://www.youtube.com/watch?v=b93Pmv1b44U&pp=ygUOemVua3V0c3UgZGFjaGk%3D',    'iniciante', 2),
(4, 'Posição do Cavaleiro',        'Kiba Dachi',    '騎馬立ち', 'Pés afastados, joelhos dobrados e para fora, como se montasse um cavalo. Base muito estável e baixa. Excelente para treinar força de pernas e golpes laterais.',                                      'https://www.youtube.com/watch?v=7uDQK908dKA&pp=ygUKa2liYSBkYWNoaQ%3D%3D',    'iniciante', 3),
(4, 'Posição Recuada',             'Kokutsu Dachi', '後屈立ち', 'Peso ~70% na perna de trás, joelho traseiro dobrado. Posição defensiva que mantém distância e facilita chutes rápidos com a perna da frente.',                                                        'https://www.youtube.com/watch?v=smbTru64xrA&pp=ygUNa29rdXRzdSBkYWNoaQ%3D%3D',    'iniciante', 4),
(4, 'Posição Imóvel',              'Fudo Dachi',    '不動立ち', 'Posição natural de combate do Kyokushin — similar ao zenkutsu mas mais natural. Base do kumite, equilibra mobilidade e estabilidade.',                                                                'https://www.youtube.com/watch?v=rtM5H0lpe20&pp=ygUKZnVkbyBkYWNoaQ%3D%3D',    'intermediario', 5),
(4, 'Posição dos Três Conflitos',  'Sanchin Dachi', '三戦立ち', 'Posição fechada e tensa onde os pés se cruzam levemente. Base do kata Sanchin — treina contração muscular total, respiração e tensão corporal. Fundamental no Kyokushin.',                           'https://www.youtube.com/watch?v=ryziGsaoOFU&pp=ygUNc2FuY2hpbiBkYWNoaQ%3D%3D', 'intermediario', 6);



-- ── UCHI ───────────────────────────────────────────────────────
INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES
(5, 'Golpe Mão-Faca',       'Shuto Uchi',  '手刀打ち', 'Ataque com a lateral da mão aberta em movimento circular. Pode ser executado de dentro para fora ou de fora para dentro. Alvo clássico: pescoço ou têmpora do adversário.',                                   'https://www.youtube.com/watch?v=AbDz5GKXlU0&pp=ygUKc2h1dG8gdWNoaQ%3D%3D', 'intermediario', 1),
(5, 'Golpe Martelo',        'Tetsui Uchi', '鉄槌打ち', 'Golpe com a parte inferior do punho fechado (lado do mindinho), como um martelo. Movimento descendente ou circular. Útil contra alvos duros como o topo da cabeça ou costelas.',                              'https://www.youtube.com/watch?v=ulj1_TjFM8o',  'intermediario', 2),
(5, 'Golpe Lança-Dedos',    'Nukite',      '貫手',    'Ataque com as pontas dos dedos estendidos, como uma lança. Alvo: garganta, abdômen ou pontos vitais. Exige dedos muito fortalecidos — treinados com makiwara e areia.',                                        'https://www.youtube.com/watch?v=28--Q0F0ojQ&pp=ygURbnVraXRlICBreW9rdXNoaW4%3D',  'avancado',      3),
(5, 'Cotovelada',           'Empi Uchi',   '肘打ち',  'Golpe com a ponta do cotovelo em curta distância. Devastador quando executado corretamente. Pode ser horizontal, ascendente ou descendente.',                                                                   'https://www.youtube.com/watch?v=w9ChXYYeAXw&pp=ygUJZW1waSB1Y2hp',    'intermediario', 4),
(5, 'Soco Um Nó',           'Ippon Ken',   '一本拳',  'Soco com o nó do dedo indicador projetado à frente. Penetra em alvos pequenos e pontos de pressão como têmpora, philtrum ou costelas. Exige condicionamento dos dedos.',                                        'https://www.youtube.com/watch?v=0mT37QYsRyg&pp=ygUJaXBwb24ga2Vu',   'avancado',      5),
(5, 'Golpe Dorso do Punho', 'Uraken Uchi', '裏拳打ち', 'Golpe com o dorso (costas) do punho fechado. Movimento rápido de chicote lateral ou circular. Eficiente para atingir a têmpora com velocidade surpreendente.',                                                'https://www.youtube.com/watch?v=sHyfuXHtpyQ&pp=ygULdXJha2VuIHVjaGk%3D',  'intermediario', 6);

-- Katas
INSERT INTO katas (nome, descricao, video_url, nivel, ordem) VALUES
('Taikyoku Sono Ichi', 'Kata básico 1 — movimentos fundamentais em linha reta com bloqueios baixos e socos.', 'https://www.youtube.com/watch?v=jbwuhibM3Uc&pp=ygUcdGFpa3lva3Ugc29ubyBpY2hpIGt5b2t1c2hpbtIHCQnaCgGHKiGM7w%3D%3D', 'iniciante', 1),
('Taikyoku Sono Ni',   'Kata básico 2 — variação com socos médios.',                                          'https://www.youtube.com/watch?v=W-y0Myy8i9Q&pp=ygUaVGFpa3lva3UgU29ubyBOaSBreW9rdXNoaW4%3D', 'iniciante', 2),
('Taikyoku Sono San',  'Kata básico 3 — introdução de bloqueios internos.',                                   'https://www.youtube.com/watch?v=5j1i4LHSet0&pp=ygUbVGFpa3lva3UgU29ubyBzYW4ga3lva3VzaGlu0gcJCdoKAYcqIYzv', 'iniciante', 3),
('Pinan Sono Ichi',    'Kata intermediário 1 — sequências mais longas com giros.',                             'https://www.youtube.com/watch?v=WejnMH3Q21w&pp=ugMGCgJwdBABugUEEgJwdMoFD3BpbmFuIHNvbm8gaWNoadgHAQ%3D%3D', 'intermediario', 4),
('Sanchin',            'Kata de respiração e tensão — fundamental no Kyokushin.',                              'https://www.youtube.com/watch?v=QPGLyHwtepA&pp=ygUWc2FuY2hpbiBrYXRhIGt5b2t1c2hpbg%3D%3D', 'intermediario', 5);

-- Exercicios
CREATE TABLE exercicios_kyokushin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL
);

INSERT INTO exercicios_kyokushin (nome,categoria) VALUES

-- SOCOS
('Seiken Choku Tsuki','Soco'),
('Seiken Ago Uchi','Soco'),
('Seiken Shita Tsuki','Soco'),
('Seiken Mawashi Uchi','Soco'),
('Uraken Shomen Uchi','Soco'),
('Uraken Sayu Uchi','Soco'),
('Uraken Hizo Uchi','Soco'),
('Tettsui Oroshi Ganmen Uchi','Soco'),

-- CHUTES
('Mae Geri','Chute'),
('Yoko Geri','Chute'),
('Mawashi Geri','Chute'),
('Ushiro Geri','Chute'),
('Ushiro Mawashi Geri','Chute'),
('Hiza Geri','Chute'),
('Kansetsu Geri','Chute'),

-- DEFESAS
('Jodan Uke','Defesa'),
('Chudan Soto Uke','Defesa'),
('Chudan Uchi Uke','Defesa'),
('Gedan Barai','Defesa'),
('Shuto Uke','Defesa'),

-- COTOVELADAS
('Empi Uchi Jodan','Cotovelada'),
('Empi Uchi Mawashi','Cotovelada'),
('Empi Uchi Oroshi','Cotovelada'),

-- JOELHADAS
('Hiza Geri Jodan','Joelhada'),
('Hiza Geri Chudan','Joelhada');

select * from kihons;