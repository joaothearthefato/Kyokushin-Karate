-- Adicionar colunas de dados do aluno à tabela usuarios_oyama
-- Execute este arquivo no phpMyAdmin

ALTER TABLE usuarios_oyama 
ADD COLUMN idade INT,
ADD COLUMN altura DECIMAL(5, 2),
ADD COLUMN peso DECIMAL(5, 2);
