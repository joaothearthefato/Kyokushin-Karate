<?php
// admin/api/kihons.php - REST API for Kihons Management
require_once __DIR__ . '/bootstrap.php';

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT k.*, c.nome AS categoria_nome, c.kanji, c.slug 
                                           FROM kihons k 
                                           JOIN kihon_categorias c ON k.categoria_id = c.id 
                                           WHERE k.id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $kihon = mysqli_fetch_assoc($res);
            if ($kihon) {
                echo json_encode(['success' => true, 'data' => $kihon]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Kihon não encontrado']);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $categoria_id = intval($_GET['categoria_id'] ?? 0);
            $nivel = trim($_GET['nivel'] ?? '');

            $where = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($q)) {
                $where[] = "(k.nome LIKE ? OR k.romaji LIKE ? OR k.descricao LIKE ?)";
                $paramQ = "%$q%";
                $params[] = $paramQ;
                $params[] = $paramQ;
                $params[] = $paramQ;
                $types .= "sss";
            }
            if ($categoria_id > 0) {
                $where[] = "k.categoria_id = ?";
                $params[] = $categoria_id;
                $types .= "i";
            }
            if (!empty($nivel)) {
                $where[] = "k.nivel = ?";
                $params[] = $nivel;
                $types .= "s";
            }

            $sql = "SELECT k.*, c.nome AS categoria_nome, c.kanji, c.slug, c.cor AS categoria_cor 
                    FROM kihons k 
                    JOIN kihon_categorias c ON k.categoria_id = c.id 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY c.numero ASC, k.ordem ASC, k.id ASC";
            
            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $kihons = mysqli_fetch_all($res, MYSQLI_ASSOC);

            // Fetch list of categories for drop-down filters
            $resCat = mysqli_query($conn, "SELECT * FROM kihon_categorias ORDER BY numero ASC");
            $categorias = mysqli_fetch_all($resCat, MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'count' => count($kihons), 'data' => $kihons, 'categorias' => $categorias]);
        }
        break;

    case 'POST':
        $categoria_id = intval($input['categoria_id'] ?? 1);
        $nome = trim($input['nome'] ?? '');
        $romaji = trim($input['romaji'] ?? '');
        $kana = trim($input['kana'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $video_url = trim($input['video_url'] ?? '');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $ordem = intval($input['ordem'] ?? 0);

        if (empty($nome) || empty($descricao) || empty($romaji)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nome, Romaji e Descrição são obrigatórios']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO kihons (categoria_id, nome, romaji, kana, descricao, video_url, nivel, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssssi", $categoria_id, $nome, $romaji, $kana, $descricao, $video_url, $nivel, $ordem);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'kihons_create', "Kihon '$nome' criado (ID $newId)");
            echo json_encode(['success' => true, 'message' => 'Kihon cadastrado com sucesso!', 'id' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao cadastrar Kihon')]);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do Kihon é necessário']);
            exit;
        }

        $categoria_id = intval($input['categoria_id'] ?? 1);
        $nome = trim($input['nome'] ?? '');
        $romaji = trim($input['romaji'] ?? '');
        $kana = trim($input['kana'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $video_url = trim($input['video_url'] ?? '');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $ordem = intval($input['ordem'] ?? 0);

        $stmt = mysqli_prepare($conn, "UPDATE kihons SET categoria_id = ?, nome = ?, romaji = ?, kana = ?, descricao = ?, video_url = ?, nivel = ?, ordem = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "issssssii", $categoria_id, $nome, $romaji, $kana, $descricao, $video_url, $nivel, $ordem, $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'kihons_update', "Kihon '$nome' atualizado (ID $id)");
            echo json_encode(['success' => true, 'message' => 'Kihon atualizado com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao atualizar Kihon')]);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do Kihon é necessário']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM kihons WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'kihons_delete', "Kihon ID $id removido");
            echo json_encode(['success' => true, 'message' => 'Kihon removido com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao excluir Kihon')]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP não suportado']);
        break;
}
?>
