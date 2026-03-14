
create database Oyama_Hub;
-- Criação da tabela de usuários
CREATE TABLE usuarios_oyama (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('aluno', 'administrador') NOT NULL
);

-- Criação da tabela de katas
CREATE TABLE katas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NOT NULL,
  video VARCHAR(255),
  nivel_dificuldade ENUM('iniciante', 'intermediario', 'avancado') NOT NULL
);

CREATE TABLE informacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(100) NOT NULL,
  conteudo TEXT NOT NULL,
  tipo ENUM('tecnica', 'historia', 'filosofia', 'outros') NOT NULL
);
