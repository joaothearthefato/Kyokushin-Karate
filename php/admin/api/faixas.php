<?php
// admin/api/faixas.php - REST API for Belts Management
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

header('Content-Type: application/json; charset=utf-8');
require_admin();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM faixas WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $faixa = mysqli_fetch_assoc($res);
            if ($faixa) {
                echo json_encode(['success' => true, 'data' => $faixa]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Faixa não encontrada']);
            }
        } else {
            $res = mysqli_query($conn, "SELECT f.*, (SELECT COUNT(*) FROM usuarios u WHERE u.faixa_id = f.id) AS total_alunos FROM faixas f ORDER BY f.ordem ASC");
            $faixas = mysqli_fetch_all($res, MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'count' => count($faixas), 'data' => $faixas]);
        }
        break;

    case 'POST':
        $nome = trim($input['nome'] ?? '');
        $ordem = intval($input['ordem'] ?? 1);
        $cor = trim($input['cor'] ?? '#d4af37');
        $requisitos = trim($input['requisitos'] ?? '');

        if (empty($nome)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nome da faixa é obrigatório']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO faixas (nome, ordem, cor, requisitos) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siss", $nome, $ordem, $cor, $requisitos);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'faixas_create', "Faixa '$nome' cadastrada (ID $newId)");
            echo json_encode(['success' => true, 'message' => 'Faixa cadastrada com sucesso!', 'id' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao cadastrar Faixa: ' . mysqli_error($conn)]);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID da Faixa é necessário']);
            exit;
        }

        $nome = trim($input['nome'] ?? '');
        $ordem = intval($input['ordem'] ?? 1);
        $cor = trim($input['cor'] ?? '#d4af37');
        $requisitos = trim($input['requisitos'] ?? '');

        $stmt = mysqli_prepare($conn, "UPDATE faixas SET nome = ?, ordem = ?, cor = ?, requisitos = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sissi", $nome, $ordem, $cor, $requisitos, $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'faixas_update', "Faixa '$nome' atualizada (ID $id)");
            echo json_encode(['success' => true, 'message' => 'Faixa atualizada com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar Faixa: ' . mysqli_error($conn)]);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID da Faixa é necessário']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM faixas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'faixas_delete', "Faixa ID $id removida");
            echo json_encode(['success' => true, 'message' => 'Faixa excluída com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir Faixa: ' . mysqli_error($conn)]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP não suportado']);
        break;
}
?>
