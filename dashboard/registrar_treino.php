<?php
/**
 * REGISTRAR_TREINO.PHP
 * Handler para registrar novos treinos com exercícios
 * Requisitos: RF01-RF07, RNF03-RNF04
 */

session_start();
require '../php/config.php';
require_once '../php/csrf.php';
require_once '../php/auth_check.php';

// RNF04 – Validação de Sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

// Validar CSRF em todas as submissões POST
validar_csrf();

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

// Sanitizar e inserir com prepared statement
$stmt_treino = mysqli_prepare($conn, "INSERT INTO treinos (usuario_id, data_treino, duracao_min, observacoes) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_treino, "isis", $usuario_id, $data_treino, $duracao_min, $observacoes);

if (!mysqli_stmt_execute($stmt_treino)) {
    error_log("Erro ao inserir treino: " . mysqli_error($conn));
    header("Location: treinos.php?erro=banco_dados");
    exit();
}

$treino_id = mysqli_insert_id($conn);

$total_exercicios = 0;

// RF02 e RF03 – Processar exercícios com prepared statements
if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
    $stmt_ex = mysqli_prepare($conn, "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes) VALUES (?, ?, ?, ?)");
    foreach ($_POST['exercicios'] as $exercicio) {
        $descricao  = trim($exercicio['descricao'] ?? '');
        $series     = intval($exercicio['series'] ?? 0);
        $repeticoes = intval($exercicio['repeticoes'] ?? 0);

        if (!empty($descricao)) {
            mysqli_stmt_bind_param($stmt_ex, "isii", $treino_id, $descricao, $series, $repeticoes);
            if (mysqli_stmt_execute($stmt_ex)) {
                $total_exercicios++;
            } else {
                error_log("Erro ao inserir exercício: " . mysqli_error($conn));
            }
        }
    }
}

// Validação: deve ter pelo menos 1 exercício
if ($total_exercicios === 0) {
    // Deletar o treino já inserido (prepared)
    $stmt_del = mysqli_prepare($conn, "DELETE FROM treinos WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $treino_id);
    mysqli_stmt_execute($stmt_del);
    header("Location: treinos.php?erro=sem_exercicios");
    exit();
}

mysqli_close($conn);

// RF07 – Mensagem de confirmação
header("Location: treinos.php?sucesso=treino_registrado");
exit();
?>
