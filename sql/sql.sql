CREATE DATABASE oyama_hub;
USE oyama_hub;

CREATE TABLE faixas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(50) NOT NULL,
  ordem INT NOT NULL
);

CREATE TABLE usuarios_oyama (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('aluno', 'administrador') NOT NULL,
  idade INT NULL,
  altura DECIMAL(5,2) NULL,
  peso DECIMAL(5,2) NULL,
  faixa_id INT NULL,
  FOREIGN KEY (faixa_id) REFERENCES faixas(id)
);

CREATE TABLE katas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NOT NULL,
  video VARCHAR(255) NULL,
  foto VARCHAR(255) NULL,
  nivel_dificuldade ENUM('iniciante', 'intermediario', 'avancado') NOT NULL
);

CREATE TABLE treinos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data_treino DATE NOT NULL,
  descricao TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios_oyama(id) ON DELETE CASCADE
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
