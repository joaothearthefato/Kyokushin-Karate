<?php
// admin/api/bootstrap.php - Base comum das APIs REST administrativas
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Envia uma resposta padronizada de SUCESSO em JSON conforme especificação:
 * {
 *   "status": "success",
 *   "code": 200,
 *   "data": {},
 *   "message": "..."
 * }
 */
function api_success($data = null, string $message = 'Operação realizada com sucesso', int $code = 200) {
    if (!headers_sent()) {
        http_response_code($code);
    }
    echo json_encode([
        'status'  => 'success',
        'code'    => $code,
        'data'    => $data,
        'message' => $message,
        'success' => true // retrocompatibilidade com UI existente
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Envia uma resposta padronizada de ERRO em JSON conforme especificação:
 * {
 *   "status": "error",
 *   "code": 400,
 *   "data": null,
 *   "message": "..."
 * }
 */
function api_error($mensagem, int $status = 400, $data = null) {
    if (!headers_sent()) {
        http_response_code($status);
    }
    echo json_encode([
        'status'  => 'error',
        'code'    => $status,
        'data'    => $data,
        'message' => $mensagem,
        'success' => false, // retrocompatibilidade
        'error'   => $mensagem // retrocompatibilidade
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Traduz erros do MySQL em mensagens compreensíveis para o administrador
 */
function db_error($conn, $fallback) {
    $errno = mysqli_errno($conn);

    switch ($errno) {
        case 1062: // duplicidade em índice UNIQUE
            return 'Já existe um registro com esse valor único (nome, email ou ordem). Utilize outro valor.';
        case 1451: // registro referenciado por outra tabela
            return 'Não é possível excluir: existem registros vinculados a este item.';
        case 1452: // referência inexistente
            return 'Referência inválida: verifique a categoria/faixa/usuário selecionado.';
        case 1264:
        case 1265:
            return 'Valor inválido para um dos campos enviados.';
        case 1146: // tabela ausente
            return 'Tabela ausente no banco de dados. Execute a migração em Configurações > Executar Migração DB.';
    }

    return $fallback;
}

/**
 * Lê o corpo da requisição, aceitando JSON ou formulário
 */
function api_input() {
    $raw = file_get_contents('php://input');
    $json = $raw !== '' ? json_decode($raw, true) : null;

    return is_array($json) ? $json : $_POST;
}

// Qualquer exceção ou erro fatal devolve JSON padronizado sem expor dados internos
set_exception_handler(function ($e) {
    error_log("Erro de exceção na API admin: " . $e->getMessage());
    api_error('Erro interno no servidor ao processar a requisição', 500);
});

register_shutdown_function(function () {
    $erro = error_get_last();
    if ($erro && in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("Erro fatal na API admin: " . $erro['message']);
        api_error('Erro fatal no servidor ao processar a requisição', 500);
    }
});

require_admin();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$input = api_input();

if ($id <= 0 && isset($input['id'])) {
    $id = intval($input['id']);
}
