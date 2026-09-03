<?php
session_start();
require '../php/config.php';
require_once '../php/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Não autenticado']);
    exit();
}

$usuario_id    = intval($_SESSION['id']);
$tipo          = $_POST['tipo']          ?? '';
$referencia_id = intval($_POST['referencia_id'] ?? 0);

if (!in_array($tipo, ['kata', 'kihon'], true) || $referencia_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Dados inválidos']);
    exit();
}

// Verificar se já existe registro — usando prepared statement
$stmt_check = mysqli_prepare($conn, "SELECT id, concluido FROM progresso WHERE usuario_id = ? AND tipo = ? AND referencia_id = ?");
mysqli_stmt_bind_param($stmt_check, "isi", $usuario_id, $tipo, $referencia_id);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$row    = $result ? mysqli_fetch_assoc($result) : null;

if ($row) {
    $novo = $row['concluido'] ? 0 : 1;
    $stmt_update = mysqli_prepare($conn, "UPDATE progresso SET concluido = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "ii", $novo, $row['id']);
    mysqli_stmt_execute($stmt_update);
    echo json_encode(['ok' => true, 'concluido' => (bool)$novo]);
} else {
    $concluido = 1;
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO progresso (usuario_id, tipo, referencia_id, concluido) VALUES (?, ?, ?, 1)");
    mysqli_stmt_bind_param($stmt_insert, "isi", $usuario_id, $tipo, $referencia_id);
    mysqli_stmt_execute($stmt_insert);
    echo json_encode(['ok' => true, 'concluido' => true]);
}

mysqli_close($conn);
