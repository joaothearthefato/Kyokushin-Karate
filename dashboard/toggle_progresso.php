<?php
session_start();
require '../php/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Não autenticado']);
    exit();
}

$usuario_id   = intval($_SESSION['id']);
$tipo         = $_POST['tipo']         ?? '';
$referencia_id = intval($_POST['referencia_id'] ?? 0);

if (!in_array($tipo, ['kata', 'kihon']) || $referencia_id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Dados inválidos']);
    exit();
}

// Verificar se já existe registro
$sql_check = "SELECT id, concluido FROM progresso WHERE usuario_id = $usuario_id AND tipo = '$tipo' AND referencia_id = $referencia_id";
$result = mysqli_query($conn, $sql_check);
$row    = $result ? mysqli_fetch_assoc($result) : null;

if ($row) {
    // Alternar estado
    $novo = $row['concluido'] ? 0 : 1;
    $sql_update = "UPDATE progresso SET concluido = $novo WHERE id = {$row['id']}";
    mysqli_query($conn, $sql_update);
    echo json_encode(['ok' => true, 'concluido' => (bool)$novo]);
} else {
    // Criar novo como concluído
    $sql_insert = "INSERT INTO progresso (usuario_id, tipo, referencia_id, concluido) VALUES ($usuario_id, '$tipo', $referencia_id, 1)";
    mysqli_query($conn, $sql_insert);
    echo json_encode(['ok' => true, 'concluido' => true]);
}

mysqli_close($conn);
?>
