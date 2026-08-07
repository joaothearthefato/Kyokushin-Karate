<?php
// admin/api/exercicios.php - REST API for Kyokushin Exercises
require_once __DIR__ . '/bootstrap.php';

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM exercicios_kyokushin WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $ex = mysqli_fetch_assoc($res);
            if ($ex) {
                echo json_encode(['success' => true, 'data' => $ex]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Exercício não encontrado']);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $tipo = trim($_GET['tipo'] ?? '');

            $where = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($q)) {
                $where[] = "(nome LIKE ? OR categoria LIKE ? OR descricao LIKE ?)";
                $paramQ = "%$q%";
                $params[] = $paramQ;
                $params[] = $paramQ;
                $params[] = $paramQ;
                $types .= "sss";
            }
            if (!empty($tipo)) {
                $where[] = "tipo = ?";
                $params[] = $tipo;
                $types .= "s";
            }

            $sql = "SELECT * FROM exercicios_kyokushin WHERE " . implode(" AND ", $where) . " ORDER BY categoria ASC, nome ASC";
            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exercicios = mysqli_fetch_all($res, MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'count' => count($exercicios), 'data' => $exercicios]);
        }
        break;

    case 'POST':
        $nome = trim($input['nome'] ?? '');
        $categoria = trim($input['categoria'] ?? 'Técnica');
        $tipo = trim($input['tipo'] ?? 'Técnica');
        $descricao = trim($input['descricao'] ?? '');
        $quantidade = trim($input['quantidade'] ?? '');
        $video_url = trim($input['video_url'] ?? '');

        if (empty($nome)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nome do exercício é obrigatório']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO exercicios_kyokushin (nome, categoria, tipo, descricao, quantidade, video_url) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $nome, $categoria, $tipo, $descricao, $quantidade, $video_url);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'exercicios_create', "Exercício '$nome' cadastrado (ID $newId)");
            echo json_encode(['success' => true, 'message' => 'Exercício cadastrado com sucesso!', 'id' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao cadastrar exercício')]);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do exercício é necessário']);
            exit;
        }

        $nome = trim($input['nome'] ?? '');
        $categoria = trim($input['categoria'] ?? 'Técnica');
        $tipo = trim($input['tipo'] ?? 'Técnica');
        $descricao = trim($input['descricao'] ?? '');
        $quantidade = trim($input['quantidade'] ?? '');
        $video_url = trim($input['video_url'] ?? '');

        $stmt = mysqli_prepare($conn, "UPDATE exercicios_kyokushin SET nome = ?, categoria = ?, tipo = ?, descricao = ?, quantidade = ?, video_url = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssssi", $nome, $categoria, $tipo, $descricao, $quantidade, $video_url, $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'exercicios_update', "Exercício '$nome' atualizado (ID $id)");
            echo json_encode(['success' => true, 'message' => 'Exercício atualizado com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao atualizar exercício')]);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do exercício é necessário']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM exercicios_kyokushin WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'exercicios_delete', "Exercício ID $id removido");
            echo json_encode(['success' => true, 'message' => 'Exercício excluído com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao excluir exercício')]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP não suportado']);
        break;
}
?>
