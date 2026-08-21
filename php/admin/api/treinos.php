<?php
// admin/api/treinos.php - REST API for Workouts Management
require_once __DIR__ . '/bootstrap.php';

function treino_data_valida($data) {
    $dt = DateTime::createFromFormat('!Y-m-d', $data);
    return $dt && $dt->format('Y-m-d') === $data;
}

function validar_dados_treino($conn, $usuario_id, $nome, $nivel, $duracao_min, $data_treino, $exercicios) {
    if ($usuario_id <= 0) api_error('Selecione um usuário válido.');
    if ($nome === '' || mb_strlen($nome) > 100) api_error('Informe um nome de treino de até 100 caracteres.');
    if (!in_array($nivel, ['iniciante', 'intermediario', 'avancado'], true)) api_error('Nível de treino inválido.');
    if ($duracao_min < 1 || $duracao_min > 1440) api_error('A duração deve estar entre 1 e 1440 minutos.');
    if (!treino_data_valida($data_treino)) api_error('Data do treino inválida.');
    if (!is_array($exercicios)) api_error('Lista de exercícios inválida.');

    $stmt = mysqli_prepare($conn, 'SELECT id FROM usuarios WHERE id = ? AND ativo = 1');
    mysqli_stmt_bind_param($stmt, 'i', $usuario_id);
    mysqli_stmt_execute($stmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
        api_error('Usuário selecionado não existe ou está inativo.');
    }
}

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT t.*, u.nome AS usuario_nome 
                                           FROM treinos t 
                                           JOIN usuarios u ON t.usuario_id = u.id 
                                           WHERE t.id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $treino = mysqli_fetch_assoc($res);
            if ($treino) {
                // Get linked exercises
                $resEx = mysqli_query($conn, "SELECT * FROM treino_exercicios WHERE treino_id = $id");
                $treino['exercicios'] = mysqli_fetch_all($resEx, MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $treino]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Treino não encontrado']);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $nivel = trim($_GET['nivel'] ?? '');
            $usuario_id = intval($_GET['usuario_id'] ?? 0);

            $where = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($q)) {
                $where[] = "(t.nome LIKE ? OR u.nome LIKE ? OR t.observacoes LIKE ?)";
                $paramQ = "%$q%";
                $params[] = $paramQ;
                $params[] = $paramQ;
                $params[] = $paramQ;
                $types .= "sss";
            }
            if (!empty($nivel)) {
                $where[] = "t.nivel = ?";
                $params[] = $nivel;
                $types .= "s";
            }
            if ($usuario_id > 0) {
                $where[] = "t.usuario_id = ?";
                $params[] = $usuario_id;
                $types .= "i";
            }

            $sql = "SELECT t.*, u.nome AS usuario_nome, f.nome AS faixa_nome 
                    FROM treinos t 
                    JOIN usuarios u ON t.usuario_id = u.id 
                    LEFT JOIN faixas f ON u.faixa_id = f.id 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY t.data_treino DESC, t.id DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $treinos = mysqli_fetch_all($res, MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'count' => count($treinos), 'data' => $treinos]);
        }
        break;

    case 'POST':
        $usuario_id = intval($input['usuario_id'] ?? $_SESSION['id']);
        $nome = trim($input['nome'] ?? 'Treino Kyokushin');
        $descricao = trim($input['descricao'] ?? '');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $duracao_min = intval($input['duracao_min'] ?? 60);
        $observacoes = trim($input['observacoes'] ?? '');
        $data_treino = trim($input['data_treino'] ?? date('Y-m-d'));
        $exercicios = $input['exercicios'] ?? [];

        validar_dados_treino($conn, $usuario_id, $nome, $nivel, $duracao_min, $data_treino, $exercicios);

        $stmt = mysqli_prepare($conn, "INSERT INTO treinos (usuario_id, nome, descricao, nivel, duracao_min, observacoes, data_treino) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssiss", $usuario_id, $nome, $descricao, $nivel, $duracao_min, $observacoes, $data_treino);

        if (mysqli_stmt_execute($stmt)) {
            $treinoId = mysqli_insert_id($conn);
            
            if (is_array($exercicios)) {
                foreach ($exercicios as $ex) {
                    $exDesc = is_array($ex) ? trim($ex['descricao'] ?? '') : trim($ex);
                    $series = is_array($ex) ? intval($ex['series'] ?? 3) : 3;
                    $repeticoes = is_array($ex) ? intval($ex['repeticoes'] ?? 15) : 15;

                    if (!empty($exDesc)) {
                        $stmtEx = mysqli_prepare($conn, "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes) VALUES (?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmtEx, "isii", $treinoId, $exDesc, $series, $repeticoes);
                        mysqli_stmt_execute($stmtEx);
                    }
                }
            }

            log_activity($conn, 'treinos_create', "Treino '$nome' cadastrado (ID $treinoId)");
            echo json_encode(['success' => true, 'message' => 'Treino criado com sucesso!', 'id' => $treinoId]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao cadastrar Treino')]);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do Treino é necessário']);
            exit;
        }

        $nome = trim($input['nome'] ?? 'Treino Kyokushin');
        $descricao = trim($input['descricao'] ?? '');
        $nivel = trim($input['nivel'] ?? 'iniciante');
        $duracao_min = intval($input['duracao_min'] ?? 60);
        $observacoes = trim($input['observacoes'] ?? '');
        $data_treino = trim($input['data_treino'] ?? date('Y-m-d'));
        $exercicios = $input['exercicios'] ?? [];

        $current = mysqli_prepare($conn, 'SELECT usuario_id FROM treinos WHERE id = ?');
        mysqli_stmt_bind_param($current, 'i', $id);
        mysqli_stmt_execute($current);
        $treinoAtual = mysqli_fetch_assoc(mysqli_stmt_get_result($current));
        if (!$treinoAtual) api_error('Treino não encontrado.', 404);
        $usuario_id = intval($input['usuario_id'] ?? $treinoAtual['usuario_id']);
        validar_dados_treino($conn, $usuario_id, $nome, $nivel, $duracao_min, $data_treino, $exercicios);

        $stmt = mysqli_prepare($conn, "UPDATE treinos SET usuario_id = ?, nome = ?, descricao = ?, nivel = ?, duracao_min = ?, observacoes = ?, data_treino = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "isssissi", $usuario_id, $nome, $descricao, $nivel, $duracao_min, $observacoes, $data_treino, $id);

        if (mysqli_stmt_execute($stmt)) {
            $exerciciosValidos = array_filter($exercicios, function ($ex) {
                return trim(is_array($ex) ? ($ex['descricao'] ?? '') : (string) $ex) !== '';
            });
            if (!empty($exerciciosValidos)) {
                $deleteExercises = mysqli_prepare($conn, 'DELETE FROM treino_exercicios WHERE treino_id = ?');
                mysqli_stmt_bind_param($deleteExercises, 'i', $id);
                mysqli_stmt_execute($deleteExercises);
            }
            foreach ($exerciciosValidos as $ex) {
                $exDesc = is_array($ex) ? trim($ex['descricao'] ?? '') : trim((string) $ex);
                if ($exDesc === '') continue;
                $series = is_array($ex) ? max(0, min(255, intval($ex['series'] ?? 3))) : 3;
                $repeticoes = is_array($ex) ? max(0, min(255, intval($ex['repeticoes'] ?? 15))) : 15;
                if (mb_strlen($exDesc) > 255) api_error('A descrição de um exercício excede 255 caracteres.');
                $stmtEx = mysqli_prepare($conn, "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmtEx, "isii", $id, $exDesc, $series, $repeticoes);
                if (!mysqli_stmt_execute($stmtEx)) api_error(db_error($conn, 'Erro ao salvar exercícios do treino'), 500);
            }
            log_activity($conn, 'treinos_update', "Treino '$nome' atualizado (ID $id)");
            echo json_encode(['success' => true, 'message' => 'Treino atualizado com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao atualizar Treino')]);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do Treino é necessário']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM treinos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'treinos_delete', "Treino ID $id excluído");
            echo json_encode(['success' => true, 'message' => 'Treino excluído com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => db_error($conn, 'Erro ao excluir Treino')]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP não suportado']);
        break;
}
?>
