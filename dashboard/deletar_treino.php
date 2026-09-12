<?php
/**
 * DELETAR_TREINO.PHP
 * Handler seguro para deletar um treino registrado
 */

require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/auth_check.php';
require_once __DIR__ . '/../php/csrf.php';

// Validar autenticação
if (!is_logged_in()) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = intval($_SESSION['id']);
$treino_id  = intval($_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Location: treinos.php?erro=metodo_nao_permitido');
    exit();
}

// Validar ID do treino
if ($treino_id <= 0) {
    header("Location: treinos.php?erro=treino_invalido");
    exit();
}

validar_csrf();

// Verificar se o treino pertence ao usuário usando prepared statement
$stmt_check = mysqli_prepare($conn, "SELECT id FROM treinos WHERE id = ? AND usuario_id = ?");
mysqli_stmt_bind_param($stmt_check, "ii", $treino_id, $usuario_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (!$result_check || mysqli_num_rows($result_check) === 0) {
    header("Location: treinos.php?erro=acesso_negado");
    exit();
}

// Deletar exercícios do treino (prepared)
$stmt_del_ex = mysqli_prepare($conn, "DELETE FROM treino_exercicios WHERE treino_id = ?");
mysqli_stmt_bind_param($stmt_del_ex, "i", $treino_id);
mysqli_stmt_execute($stmt_del_ex);

// Deletar o treino (prepared)
$stmt_del = mysqli_prepare($conn, "DELETE FROM treinos WHERE id = ? AND usuario_id = ?");
mysqli_stmt_bind_param($stmt_del, "ii", $treino_id, $usuario_id);

if (mysqli_stmt_execute($stmt_del)) {
    log_activity($conn, 'treino_deletado', "Treino ID $treino_id excluído pelo usuário ID $usuario_id");
    mysqli_close($conn);
    header("Location: treinos.php?sucesso=treino_deletado");
    exit();
} else {
    error_log("Erro ao deletar treino: " . mysqli_error($conn));
    mysqli_close($conn);
    header("Location: treinos.php?erro=banco_dados");
    exit();
}
