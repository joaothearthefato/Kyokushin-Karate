<?php
// admin/api/katas.php - REST API for Katas Management
require_once __DIR__ . '/bootstrap.php';

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM katas WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $kata = mysqli_fetch_assoc($res);
            if ($kata) {
                api_success($kata, 'Kata carregado com sucesso');
            } else {
                api_error('Kata não encontrado', 404);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $nivel = trim($_GET['nivel'] ?? '');
            $categoria = trim($_GET['categoria'] ?? '');
            
            $where = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($q)) {
                $where[] = "(nome LIKE ? OR descricao LIKE ?)";
                $paramQ = "%$q%";
                $params[] = $paramQ;
                $params[] = $paramQ;
                $types .= "ss";
            }
            if (!empty($nivel)) {
                $where[] = "nivel = ?";
                $params[] = $nivel;
                $types .= "s";
            }
            if (!empty($categoria)) {
                $where[] = "categoria = ?";
                $params[] = $categoria;
                $types .= "s";
            }

            $sql = "SELECT * FROM katas WHERE " . implode(" AND ", $where) . " ORDER BY ordem ASC, id ASC";
            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $katas = mysqli_fetch_all($res, MYSQLI_ASSOC);

            api_success($katas, 'Katas listados com sucesso');
        }
        break;

    case 'POST':
        $nome = trim($input['nome'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $video_url = trim($input['video_url'] ?? '');
        $imagem_url = trim($input['imagem_url'] ?? '');
        $categoria = trim($input['categoria'] ?? 'Norte (Shotokan)');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $ordem = intval($input['ordem'] ?? 0);

        if (empty($nome) || empty($descricao)) {
            api_error('Nome e Descrição são obrigatórios', 400);
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO katas (nome, descricao, video_url, imagem_url, categoria, nivel, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssssi", $nome, $descricao, $video_url, $imagem_url, $categoria, $nivel, $ordem);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'katas_create', "Kata '$nome' criado (ID $newId)");
            api_success(['id' => $newId], 'Kata cadastrado com sucesso!', 201);
        } else {
            api_error(db_error($conn, 'Erro ao cadastrar Kata'), 500);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            api_error('ID do Kata é necessário', 400);
        }

        $nome = trim($input['nome'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $video_url = trim($input['video_url'] ?? '');
        $imagem_url = trim($input['imagem_url'] ?? '');
        $categoria = trim($input['categoria'] ?? 'Norte (Shotokan)');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $ordem = intval($input['ordem'] ?? 0);

        $stmt = mysqli_prepare($conn, "UPDATE katas SET nome = ?, descricao = ?, video_url = ?, imagem_url = ?, categoria = ?, nivel = ?, ordem = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssssii", $nome, $descricao, $video_url, $imagem_url, $categoria, $nivel, $ordem, $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'katas_update', "Kata '$nome' atualizado (ID $id)");
            api_success(['id' => $id], 'Kata atualizado com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao atualizar Kata'), 500);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            api_error('ID do Kata é necessário', 400);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM katas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'katas_delete', "Kata ID $id removido");
            api_success(['id' => $id], 'Kata removido com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao excluir Kata'), 500);
        }
        break;

    default:
        api_error('Método HTTP não suportado', 405);
        break;
}
?>
