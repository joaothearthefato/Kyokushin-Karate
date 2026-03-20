
create database Oyama_Hub;
-- Criação da tabela de usuários
CREATE TABLE usuarios_oyama (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
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

CREATE TABLE faixas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    ordem INT NOT NULL -- Ex: 1 para Branca, 2 para Amarela, etc.
);

INSERT INTO faixas (nome, ordem) VALUES 
('Branca (Iniciante)', 1),
('Laranja (10º Kyu)', 2),
('Laranja com Tarja (9º Kyu)', 3),
('Azul (8º Kyu)', 4),
('Azul com Tarja (7º Kyu)', 5),
('Amarela (6º Kyu)', 6),
('Amarela com Tarja (5º Kyu)', 7),
('Verde (4º Kyu)', 8),
('Verde com Tarja (3º Kyu)', 9),
('Marrom (2º Kyu)', 10),
('Marrom com Tarja (1º Kyu)', 11),
('Preta (1º Dan)', 12);

select * from usuarios_oyama;
select * FROM FAIXAS;
	