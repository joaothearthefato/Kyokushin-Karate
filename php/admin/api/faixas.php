<?php
// admin/api/faixas.php - REST API for Belts Management
require_once __DIR__ . '/bootstrap.php';

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM faixas WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $faixa = mysqli_fetch_assoc($res);
            if ($faixa) {
                api_success($faixa, 'Faixa carregada com sucesso');
            } else {
                api_error('Faixa não encontrada', 404);
            }
        } else {
            $res = mysqli_query($conn, "SELECT f.*, (SELECT COUNT(*) FROM usuarios u WHERE u.faixa_id = f.id) AS total_alunos FROM faixas f ORDER BY f.ordem ASC");
            $faixas = mysqli_fetch_all($res, MYSQLI_ASSOC);
            api_success($faixas, 'Faixas listadas com sucesso');
        }
        break;

    case 'POST':
        $nome = trim($input['nome'] ?? '');
        $ordem = intval($input['ordem'] ?? 1);
        $cor = trim($input['cor'] ?? '#d4af37');
        $requisitos = trim($input['requisitos'] ?? '');

        if (empty($nome)) {
            api_error('Nome da faixa é obrigatório', 400);
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO faixas (nome, ordem, cor, requisitos) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siss", $nome, $ordem, $cor, $requisitos);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'faixas_create', "Faixa '$nome' cadastrada (ID $newId)");
            api_success(['id' => $newId], 'Faixa cadastrada com sucesso!', 201);
        } else {
            api_error(db_error($conn, 'Erro ao cadastrar Faixa'), 500);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            api_error('ID da Faixa é necessário', 400);
        }

        $nome = trim($input['nome'] ?? '');
        $ordem = intval($input['ordem'] ?? 1);
        $cor = trim($input['cor'] ?? '#d4af37');
        $requisitos = trim($input['requisitos'] ?? '');

        $stmt = mysqli_prepare($conn, "UPDATE faixas SET nome = ?, ordem = ?, cor = ?, requisitos = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sissi", $nome, $ordem, $cor, $requisitos, $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'faixas_update', "Faixa '$nome' atualizada (ID $id)");
            api_success(['id' => $id], 'Faixa atualizada com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao atualizar Faixa'), 500);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            api_error('ID da Faixa é necessário', 400);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM faixas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'faixas_delete', "Faixa ID $id removida");
            api_success(['id' => $id], 'Faixa excluída com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao excluir Faixa'), 500);
        }
        break;

    default:
        api_error('Método HTTP não suportado', 405);
        break;
}
?>
