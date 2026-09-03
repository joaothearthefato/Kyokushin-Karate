<?php
/**
 * ATUALIZAR_TREINO.PHP
 * Handler seguro para atualizar treinos registrados
 */

session_start();
require '../php/config.php';
require_once '../php/auth_check.php';
require_once '../php/csrf.php';

// RNF04 – Validação de Sessão
if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../php/login.php");
    exit();
}

validar_csrf();

$usuario_id  = intval($_SESSION['id']);
$treino_id   = intval($_POST['treino_id'] ?? 0);
$data_treino = trim($_POST['data_treino'] ?? '');
$duracao_min = intval($_POST['duracao_min'] ?? 0);
$observacoes = trim($_POST['observacoes'] ?? '');

// Validar ID do treino
if ($treino_id <= 0) {
    header("Location: treinos.php?erro=treino_invalido");
    exit();
}

// Verificar se o treino pertence ao usuário usando prepared statement
$stmt_check = mysqli_prepare($conn, "SELECT id FROM treinos WHERE id = ? AND usuario_id = ?");
mysqli_stmt_bind_param($stmt_check, "ii", $treino_id, $usuario_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (!$result_check || mysqli_num_rows($result_check) === 0) {
    header("Location: treinos.php?erro=acesso_negado");
    exit();
}

// Validações de data e duração
$data_atual = date('Y-m-d');
if ($data_treino > $data_atual) {
    header("Location: editar_treino.php?id=$treino_id&erro=data_futura");
    exit();
}

if (empty($data_treino) || $duracao_min < 5 || empty($observacoes)) {
    header("Location: editar_treino.php?id=$treino_id&erro=campos_obrigatorios");
    exit();
}

// Atualizar treino com prepared statement
$stmt_update = mysqli_prepare($conn, "UPDATE treinos SET data_treino = ?, duracao_min = ?, observacoes = ? WHERE id = ? AND usuario_id = ?");
mysqli_stmt_bind_param($stmt_update, "sisii", $data_treino, $duracao_min, $observacoes, $treino_id, $usuario_id);

if (!mysqli_stmt_execute($stmt_update)) {
    error_log("Erro ao atualizar treino: " . mysqli_error($conn));
    header("Location: editar_treino.php?id=$treino_id&erro=banco_dados");
    exit();
}

// Deletar exercícios antigos (prepared)
$stmt_del_ex = mysqli_prepare($conn, "DELETE FROM treino_exercicios WHERE treino_id = ?");
mysqli_stmt_bind_param($stmt_del_ex, "i", $treino_id);
mysqli_stmt_execute($stmt_del_ex);

$total_exercicios = 0;

// Inserir novos exercícios com prepared statement
if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
    $stmt_in_ex = mysqli_prepare($conn, "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes) VALUES (?, ?, ?, ?)");
    foreach ($_POST['exercicios'] as $exercicio) {
        $descricao  = trim($exercicio['descricao'] ?? '');
        $series     = intval($exercicio['series'] ?? 0);
        $repeticoes = intval($exercicio['repeticoes'] ?? 0);

        if (!empty($descricao)) {
            mysqli_stmt_bind_param($stmt_in_ex, "isii", $treino_id, $descricao, $series, $repeticoes);
            if (mysqli_stmt_execute($stmt_in_ex)) {
                $total_exercicios++;
            }
        }
    }
}

// Validação: deve ter pelo menos 1 exercício
if ($total_exercicios === 0) {
    $stmt_del = mysqli_prepare($conn, "DELETE FROM treinos WHERE id = ? AND usuario_id = ?");
    mysqli_stmt_bind_param($stmt_del, "ii", $treino_id, $usuario_id);
    mysqli_stmt_execute($stmt_del);
    header("Location: treinos.php?erro=sem_exercicios");
    exit();
}

log_activity($conn, 'treino_atualizado', "Treino ID $treino_id atualizado pelo usuário ID $usuario_id");

mysqli_close($conn);

// Mensagem de confirmação
header("Location: treinos.php?sucesso=treino_atualizado");
exit();
