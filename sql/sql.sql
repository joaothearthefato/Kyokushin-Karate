
create database Oyama_Hub;
-- Criação da tabela de usuários
CREATE TABLE usuarios_oyama (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    idade VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'administrador') NOT NULL,
    faixa_id INT,
    FOREIGN KEY (faixa_id) REFERENCES faixas(id)
);

-- Criação da tabela de katas
CREATE TABLE katas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NOT NULL,
  video VARCHAR(255),
  nivel_dificuldade ENUM('iniciante', 'intermediario', 'avancado') NOT NULL
);

-- Criação da tabela de kihons
CREATE TABLE kihons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NOT NULL,
  nivel_dificuldade ENUM('iniciante', 'intermediario', 'avancado') NOT NULL
);

CREATE TABLE faixas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    ordem INT NOT NULL -- Ex: 1 para Branca, 2 para Amarela, etc.
);

-- Tabela de progresso katas
CREATE TABLE progresso_katas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    kata_id INT,
    concluido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios_oyama(id),
    FOREIGN KEY (kata_id) REFERENCES katas(id)
);

-- Tabela de progresso kihons
CREATE TABLE progresso_kihons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    kihon_id INT,
    concluido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios_oyama(id),
    FOREIGN KEY (kihon_id) REFERENCES kihons(id)
);

INSERT INTO faixas (nome, ordem) VALUES 
('Branca (Iniciante)', 1),
('Laranja (10º Kyu)', 2),
('Azul (8º Kyu)', 3),
('Amarela (6º Kyu)', 4),
('Verde (4º Kyu)', 5),
('Marrom (2º Kyu)', 6),
('Marrom com Ponta Preta (1º Kyu)', 7),
('Preta (1º Dan)', 8);

INSERT INTO katas (nome, descricao, video, nivel_dificuldade) VALUES
('Taikyoku Sono Ichi', 'Kata básico 1', '', 'iniciante'),
('Taikyoku Sono Ni', 'Kata básico 2', '', 'iniciante'),
('Taikyoku Sono San', 'Kata básico 3', '', 'iniciante');

INSERT INTO kihons (nome, descricao, nivel_dificuldade) VALUES
('Chudan-zuki', 'Soco médio ao corpo', 'iniciante'),
('Jodan-uke', 'Bloqueio alto', 'iniciante'),
('Mae-geri', 'Chute frontal', 'iniciante');

select * from usuarios_oyama;
select * FROM FAIXAS;
