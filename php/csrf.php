<?php
/**
 * csrf.php — Sistema centralizado de proteção CSRF para o Oyama Hub
 *
 * Uso:
 *   require_once 'csrf.php';
 *
 *   // Em qualquer formulário HTML:
 *   echo csrf_input();
 *
 *   // No handler do formulário (POST):
 *   validar_csrf();  // encerra com 403 se inválido
 *
 *   // Para AJAX (enviar token no header X-CSRF-Token):
 *   $token = csrf_token();
 *   // Na resposta ao JS: echo json_encode(['csrf' => $token]);
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna o token CSRF da sessão atual, criando um se não existir.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retorna o campo hidden HTML com o token CSRF.
 */
function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida o token CSRF vindo de um formulário POST ou header AJAX.
 * Em caso de falha, encerra a execução com HTTP 403.
 *
 * @param bool $isAjax  Quando true, busca o token no header X-CSRF-Token além do POST.
 */
function validar_csrf(bool $isAjax = false): void {
    $tokenEnviado = '';

    if ($isAjax) {
        // Suporte a AJAX via header HTTP
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headerKey = 'X-Csrf-Token';
        foreach ($headers as $k => $v) {
            if (strcasecmp($k, 'X-CSRF-Token') === 0) {
                $tokenEnviado = $v;
                break;
            }
        }
    }

    // Fallback para POST (formulários e AJAX via body)
    if (empty($tokenEnviado)) {
        $tokenEnviado = $_POST['csrf_token'] ?? '';
    }

    // Também aceita do input JSON (APIs internas)
    if (empty($tokenEnviado)) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            $tokenEnviado = $json['csrf_token'] ?? '';
        }
    }

    $tokenEsperado = $_SESSION['csrf_token'] ?? '';

    if (empty($tokenEnviado) || empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenEnviado)) {
        $isApiRequest = !empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
            || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');

        http_response_code(403);

        if ($isApiRequest || $isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'code'    => 403,
                'data'    => null,
                'message' => 'Token de segurança inválido ou ausente. Recarregue a página e tente novamente.',
            ]);
        } else {
            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Acesso Negado</title></head>'
               . '<body style="background:#0a0a0a;color:#f2ede8;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;">'
               . '<div style="text-align:center;"><h1 style="color:#c8000a;">403 — Token Inválido</h1>'
               . '<p>A requisição foi rejeitada por segurança. <a href="javascript:history.back()" style="color:#c8000a;">Voltar</a></p>'
               . '</div></body></html>';
        }
        exit;
    }
}

/**
 * Regenera o token CSRF (útil após login ou ações críticas).
 */
function regenerar_csrf(): void {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
