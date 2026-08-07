<?php
// auth_check.php - Auth & Role-Based Access Control Middleware for Oyama Hub

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if current user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

/**
 * Check if logged-in user is an Administrator
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
}

/**
 * Require general user authentication
 */
function require_login() {
    if (!is_logged_in()) {
        $isApi = stristr($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false || 
                 (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['error' => 'Autenticação necessária', 'success' => false]);
            exit;
        } else {
            header("Location: ../php/login.php?error=access_denied");
            exit;
        }
    }
}

/**
 * Require Administrator privileges
 */
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        $isApi = stristr($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false || 
                 (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado. Apenas administradores possuem permissão.', 'success' => false]);
            exit;
        } else {
            // Render friendly Access Denied page for HTML browser requests
            http_response_code(403);
            ?>
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <title>Acesso Negado | Oyama Hub</title>
                <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
                <style>
                    body { background: #0a0a0a; color: #fff; font-family: 'Oswald', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                    .card { background: #111; border: 1px solid #222; border-left: 4px solid #c0392b; padding: 2.5rem; border-radius: 8px; max-width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); }
                    h1 { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: #c0392b; margin: 0 0 10px; letter-spacing: 2px; }
                    p { color: #aaa; margin-bottom: 20px; font-size: 1.1rem; }
                    a { display: inline-block; background: #c0392b; color: #fff; padding: 10px 24px; text-decoration: none; border-radius: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: background .2s; }
                    a:hover { background: #e74c3c; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>403 - ACESSO RESTRITO</h1>
                    <p>Esta área é exclusiva para Administradores da plataforma Oyama Hub.</p>
                    <a href="../php/dashboard.php">Voltar para o Painel</a>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
}

/**
 * Log activity helper
 */
function log_activity($conn, $acao, $detalhes = null) {
    if (!$conn) return;

    $usuario_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // O log é auxiliar: nenhuma falha aqui deve interromper a operação principal
    try {
        $stmt = mysqli_prepare($conn, "INSERT INTO atividades (usuario_id, acao, detalhes, ip) VALUES (?, ?, ?, ?)");
        if (!$stmt) return;
        mysqli_stmt_bind_param($stmt, "isss", $usuario_id, $acao, $detalhes, $ip);
        mysqli_stmt_execute($stmt);
    } catch (Throwable $e) {
        // ignorado de propósito
    }
}
?>
