<?php
/**
 * DELETAR_TREINO.PHP
 * Handler para deletar um treino registrado
 */

session_start();
require '../php/config.php';

// Validar autenticação
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$treino_id = intval($_GET['id'] ?? 0);

// Validar ID do treino
if ($treino_id <= 0) {
    header("Location: treinos.php?erro=treino_invalido");
    exit();
}

// Verificar se o treino pertence ao usuário
$sql_check = "SELECT id FROM treinos WHERE id = '$treino_id' AND usuario_id = '$usuario_id'";
$result_check = mysqli_query($conn, $sql_check);

if (!$result_check || mysqli_num_rows($result_check) === 0) {
    header("Location: treinos.php?erro=acesso_negado");
    exit();
}

// Deletar exercícios do treino (cascata)
$sql_delete_exercicios = "DELETE FROM treino_exercicios WHERE treino_id = '$treino_id'";
mysqli_query($conn, $sql_delete_exercicios);

// Deletar o treino
$sql_delete_treino = "DELETE FROM treinos WHERE id = '$treino_id'";

if (mysqli_query($conn, $sql_delete_treino)) {
    mysqli_close($conn);
    header("Location: treinos.php?sucesso=treino_deletado");
    exit();
} else {
    error_log("Erro ao deletar treino: " . mysqli_error($conn));
    header("Location: treinos.php?erro=banco_dados");
    exit();
}
?>
