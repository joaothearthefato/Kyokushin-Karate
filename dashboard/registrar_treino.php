<?php
/**
 * REGISTRAR_TREINO.PHP
 * Handler para registrar novos treinos com exercícios
 * Requisitos: RF01-RF07, RNF03-RNF04
 */

session_start();
require '../php/config.php';

// RNF04 – Validação de Sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

// RF06 – Associar ao usuário logado
$usuario_id = $_SESSION['id'];

// Validações de entrada
$data_treino = trim($_POST['data_treino'] ?? '');
$duracao_min = intval($_POST['duracao_min'] ?? 0);
$observacoes = trim($_POST['observacoes'] ?? '');

// RF05 – Validar data (não permitir data futura)
$data_atual = date('Y-m-d');
if ($data_treino > $data_atual) {
    header("Location: treinos.php?erro=data_futura");
    exit();
}

// Validações básicas
if (empty($data_treino) || $duracao_min < 5 || empty($observacoes)) {
    header("Location: treinos.php?erro=campos_obrigatorios");
    exit();
}

// Sanitizar dados
$data_treino = mysqli_real_escape_string($conn, $data_treino);
$observacoes = mysqli_real_escape_string($conn, $observacoes);

// RF01 – Inserir novo treino no banco
$sql_treino = "INSERT INTO treinos (usuario_id, data_treino, duracao_min, observacoes)
               VALUES ('$usuario_id', '$data_treino', '$duracao_min', '$observacoes')";

if (!mysqli_query($conn, $sql_treino)) {
    error_log("Erro ao inserir treino: " . mysqli_error($conn));
    header("Location: treinos.php?erro=banco_dados");
    exit();
}

$treino_id = mysqli_insert_id($conn);

// Garantir que a tabela treino_exercicios exista antes de inserir os exercícios
$sql_cria_exercicios = "CREATE TABLE IF NOT EXISTS treino_exercicios (
    treino_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    series TINYINT UNSIGNED,
    repeticoes TINYINT UNSIGNED,
    PRIMARY KEY (treino_id, descricao(100)),
    FOREIGN KEY (treino_id) REFERENCES treinos(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $sql_cria_exercicios)) {
    error_log("Erro ao criar tabela treino_exercicios: " . mysqli_error($conn));
    header("Location: treinos.php?erro=banco_dados");
    exit();
}

$total_exercicios = 0;

// RF02 e RF03 – Processar exercícios
if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
    foreach ($_POST['exercicios'] as $exercicio) {
        $descricao = trim($exercicio['descricao'] ?? '');
        $series = intval($exercicio['series'] ?? 0);
        $repeticoes = intval($exercicio['repeticoes'] ?? 0);

        // Validar que pelo menos descricao existe
        if (!empty($descricao)) {
            $descricao = mysqli_real_escape_string($conn, $descricao);

            // Inserir exercício na tabela treino_exercicios
            $sql_exercicio = "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes)
                              VALUES ('$treino_id', '$descricao', '$series', '$repeticoes')";

            if (mysqli_query($conn, $sql_exercicio)) {
                $total_exercicios++;
            } else {
                error_log("Erro ao inserir exercício: " . mysqli_error($conn));
            }
        }
    }
}

// Validação: deve ter pelo menos 1 exercício
if ($total_exercicios === 0) {
    // Deletar o treino já inserido
    $sql_delete = "DELETE FROM treinos WHERE id = '$treino_id'";
    mysqli_query($conn, $sql_delete);
    header("Location: treinos.php?erro=sem_exercicios");
    exit();
}

mysqli_close($conn);

// RF07 – Mensagem de confirmação
header("Location: treinos.php?sucesso=treino_registrado");
exit();
?>
