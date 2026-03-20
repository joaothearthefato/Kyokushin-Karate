-- Script para atualizar as faixas no banco de dados
-- Execute este arquivo no phpMyAdmin para atualizar as faixas existentes

-- Atualizar faixas existentes
UPDATE faixas SET nome = 'Branca (Iniciante)', ordem = 1 WHERE id = 1;
UPDATE faixas SET nome = 'Laranja (10º Kyu)', ordem = 2 WHERE id = 2;
UPDATE faixas SET nome = 'Azul (8º Kyu)', ordem = 3 WHERE id = 3;
UPDATE faixas SET nome = 'Amarela (6º Kyu)', ordem = 4 WHERE id = 4;
UPDATE faixas SET nome = 'Verde (4º Kyu)', ordem = 5 WHERE id = 5;
UPDATE faixas SET nome = 'Marrom (2º Kyu)', ordem = 6 WHERE id = 6;
UPDATE faixas SET nome = 'Marrom com Ponta Preta (1º Kyu)', ordem = 7 WHERE id = 7;
UPDATE faixas SET nome = 'Preta (1º Dan)', ordem = 8 WHERE id = 8;
