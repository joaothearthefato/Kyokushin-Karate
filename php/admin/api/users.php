<?php
// admin/api/users.php - REST API for User Management
require_once __DIR__ . '/bootstrap.php';

function nascimento_valido($nascimento) {
    $data = DateTime::createFromFormat('!Y-m-d', $nascimento);
    return $data && $data->format('Y-m-d') === $nascimento && $data <= new DateTime('today');
}

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "SELECT u.id, u.nome, u.email, u.nascimento, u.tipo, u.faixa_id, u.ativo, u.foto_perfil, u.criado_em, f.nome AS faixa_nome, f.cor AS faixa_cor 
                                           FROM usuarios u 
                                           LEFT JOIN faixas f ON u.faixa_id = f.id 
                                           WHERE u.id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
            if ($user) {
                api_success($user, 'Usuário carregado com sucesso');
            } else {
                api_error('Usuário não encontrado', 404);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $tipo = trim($_GET['tipo'] ?? '');
            $faixa_id = intval($_GET['faixa_id'] ?? 0);
            $page = intval($_GET['page'] ?? 0);
            $limit = intval($_GET['limit'] ?? 15);
            if ($limit <= 0 || $limit > 100) $limit = 15;

            $where = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($q)) {
                $where[] = "(u.nome LIKE ? OR u.email LIKE ?)";
                $paramQ = "%$q%";
                $params[] = $paramQ;
                $params[] = $paramQ;
                $types .= "ss";
            }
            if (!empty($tipo)) {
                $where[] = "u.tipo = ?";
                $params[] = $tipo;
                $types .= "s";
            }
            if ($faixa_id > 0) {
                $where[] = "u.faixa_id = ?";
                $params[] = $faixa_id;
                $types .= "i";
            }

            // Contagem total para paginação
            $totalCount = 0;
            if ($page > 0) {
                $sqlCount = "SELECT COUNT(*) c FROM usuarios u WHERE " . implode(" AND ", $where);
                $stmtCount = mysqli_prepare($conn, $sqlCount);
                if (!empty($params)) {
                    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
                }
                mysqli_stmt_execute($stmtCount);
                $resCount = mysqli_stmt_get_result($stmtCount);
                $totalCount = ($resCount && $rowC = mysqli_fetch_assoc($resCount)) ? intval($rowC['c']) : 0;
            }

            $sql = "SELECT u.id, u.nome, u.email, u.nascimento, u.tipo, u.faixa_id, u.ativo, u.foto_perfil, u.criado_em, f.nome AS faixa_nome, f.cor AS faixa_cor 
                    FROM usuarios u 
                    LEFT JOIN faixas f ON u.faixa_id = f.id 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY u.id DESC";

            if ($page > 0) {
                $offset = ($page - 1) * $limit;
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                $types .= "ii";
            }

            $stmt = mysqli_prepare($conn, $sql);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $users = mysqli_fetch_all($res, MYSQLI_ASSOC);

            // Fetch faixas for user edit modal dropdown
            $resFaixas = mysqli_query($conn, "SELECT id, nome, cor FROM faixas ORDER BY ordem ASC");
            $faixas = mysqli_fetch_all($resFaixas, MYSQLI_ASSOC);

            $responseData = $users;
            $pagination = $page > 0 ? [
                'page'  => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ] : null;

            // Enviar resposta mantendo compatibilidade com res.data e res.faixas
            if (!headers_sent()) {
                http_response_code(200);
            }
            echo json_encode([
                'status'     => 'success',
                'code'       => 200,
                'data'       => $responseData,
                'faixas'     => $faixas,
                'count'      => count($users),
                'pagination' => $pagination,
                'message'    => 'Usuários listados com sucesso',
                'success'    => true
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        break;

    case 'POST':
        $nome = trim($input['nome'] ?? '');
        $email = trim($input['email'] ?? '');
        $senha = trim($input['senha'] ?? '');
        $nascimento = trim($input['nascimento'] ?? '');
        $tipo = trim($input['tipo'] ?? 'aluno');
        $faixa_id = intval($input['faixa_id'] ?? 0);
        $ativo = isset($input['ativo']) ? (intval($input['ativo']) ? 1 : 0) : 1;

        if (empty($nome) || empty($email) || empty($senha) || empty($nascimento)) {
            api_error('Nome, Email, Senha e Data de Nascimento são obrigatórios', 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            api_error('Email inválido', 400);
        }
        if (strlen($senha) < 6) {
            api_error('A senha deve ter no mínimo 6 caracteres', 400);
        }

        if (!nascimento_valido($nascimento)) {
            api_error('A data de nascimento não pode ser futura.', 400);
        }

        $passHash = password_hash($senha, PASSWORD_DEFAULT);
        $faixaParam = $faixa_id > 0 ? $faixa_id : null;

        $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nome, email, senha_hash, nascimento, tipo, faixa_id, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssii", $nome, $email, $passHash, $nascimento, $tipo, $faixaParam, $ativo);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            log_activity($conn, 'users_create', "Usuário '$nome' criado (ID $newId)");
            api_success(['id' => $newId], 'Usuário cadastrado com sucesso!', 201);
        } else {
            api_error(db_error($conn, 'Erro ao cadastrar usuário'), 500);
        }
        break;

    case 'PUT':
        if ($id <= 0) {
            api_error('ID do usuário é necessário', 400);
        }

        $nome = trim($input['nome'] ?? '');
        $email = trim($input['email'] ?? '');
        $nascimento = trim($input['nascimento'] ?? '');
        $tipo = trim($input['tipo'] ?? 'aluno');
        $faixa_id = intval($input['faixa_id'] ?? 0);
        $ativo = isset($input['ativo']) ? (intval($input['ativo']) ? 1 : 0) : 1;
        $nova_senha = trim($input['nova_senha'] ?? '');

        if (empty($nome) || empty($email)) {
            api_error('Nome e Email são obrigatórios', 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            api_error('Email inválido', 400);
        }
        if (!empty($nova_senha) && strlen($nova_senha) < 6) {
            api_error('A nova senha deve ter no mínimo 6 caracteres', 400);
        }

        if (!nascimento_valido($nascimento)) {
            api_error('Data de nascimento inválida ou futura.', 400);
        }

        $faixaParam = $faixa_id > 0 ? $faixa_id : null;

        if (!empty($nova_senha)) {
            $passHash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome = ?, email = ?, nascimento = ?, tipo = ?, faixa_id = ?, ativo = ?, senha_hash = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssiisi", $nome, $email, $nascimento, $tipo, $faixaParam, $ativo, $passHash, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome = ?, email = ?, nascimento = ?, tipo = ?, faixa_id = ?, ativo = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssiii", $nome, $email, $nascimento, $tipo, $faixaParam, $ativo, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'users_update', "Usuário '$nome' (ID $id) atualizado");
            api_success(['id' => $id], 'Dados do usuário atualizados com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao atualizar usuário'), 500);
        }
        break;

    case 'DELETE':
        if ($id <= 0) {
            api_error('ID do usuário é necessário', 400);
        }

        if ($id === intval($_SESSION['id'] ?? 0)) {
            api_error('Você não pode excluir sua própria conta de administrador.', 403);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'users_delete', "Usuário ID $id excluído");
            api_success(['id' => $id], 'Usuário excluído com sucesso!');
        } else {
            api_error(db_error($conn, 'Erro ao excluir usuário'), 500);
        }
        break;

    default:
        api_error('Método HTTP não suportado', 405);
        break;
}
?>
