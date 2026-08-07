<?php
// admin/api/users.php - REST API for User Management
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
            $stmt = mysqli_prepare($conn, "SELECT u.id, u.nome, u.email, u.nascimento, u.tipo, u.faixa_id, u.ativo, u.foto_perfil, u.criado_em, f.nome AS faixa_nome, f.cor AS faixa_cor 
                                           FROM usuarios u 
                                           LEFT JOIN faixas f ON u.faixa_id = f.id 
                                           WHERE u.id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
            }
        } else {
            $q = trim($_GET['q'] ?? '');
            $tipo = trim($_GET['tipo'] ?? '');
            $faixa_id = intval($_GET['faixa_id'] ?? 0);

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

            $sql = "SELECT u.id, u.nome, u.email, u.nascimento, u.tipo, u.faixa_id, u.ativo, u.foto_perfil, u.criado_em, f.nome AS faixa_nome, f.cor AS faixa_cor 
                    FROM usuarios u 
                    LEFT JOIN faixas f ON u.faixa_id = f.id 
                    WHERE " . implode(" AND ", $where) . " 
                    ORDER BY u.id DESC";

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

            echo json_encode(['success' => true, 'count' => count($users), 'data' => $users, 'faixas' => $faixas]);
        }
        break;

    case 'PUT':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do usuário é necessário']);
            exit;
        }

        $nome = trim($input['nome'] ?? '');
        $email = trim($input['email'] ?? '');
        $tipo = trim($input['tipo'] ?? 'aluno');
        $faixa_id = intval($input['faixa_id'] ?? 0);
        $ativo = isset($input['ativo']) ? (intval($input['ativo']) ? 1 : 0) : 1;
        $nova_senha = trim($input['nova_senha'] ?? '');

        if (empty($nome) || empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nome e Email são obrigatórios']);
            exit;
        }

        if (!empty($nova_senha)) {
            $passHash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome = ?, email = ?, tipo = ?, faixa_id = ?, ativo = ?, senha_hash = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssiisi", $nome, $email, $tipo, $faixa_id, $ativo, $passHash, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET nome = ?, email = ?, tipo = ?, faixa_id = ?, ativo = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssiii", $nome, $email, $tipo, $faixa_id, $ativo, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'users_update', "Usuário '$nome' (ID $id) atualizado");
            echo json_encode(['success' => true, 'message' => 'Dados do usuário atualizados com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar usuário: ' . mysqli_error($conn)]);
        }
        break;

    case 'DELETE':
        if ($id <= 0 && isset($input['id'])) {
            $id = intval($input['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID do usuário é necessário']);
            exit;
        }

        if ($id === $_SESSION['id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Você não pode excluir sua própria conta de administrador.']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'users_delete', "Usuário ID $id excluído");
            echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao excluir usuário: ' . mysqli_error($conn)]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP não suportado']);
        break;
}
?>
