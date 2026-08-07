<?php
// admin/api/bootstrap.php - Base comum das APIs REST administrativas
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Envia uma resposta de erro em JSON e encerra a execução
 */
function api_error($mensagem, $status = 400) {
    if (!headers_sent()) {
        http_response_code($status);
    }
    echo json_encode(['success' => false, 'error' => $mensagem]);
    exit;
}

/**
 * Traduz erros do MySQL em mensagens compreensíveis para o administrador
 */
function db_error($conn, $fallback) {
    $errno = mysqli_errno($conn);
    $erro = mysqli_error($conn);

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

    return $fallback . ($erro ? ' (' . $erro . ')' : '');
}

/**
 * Lê o corpo da requisição, aceitando JSON ou formulário
 */
function api_input() {
    $raw = file_get_contents('php://input');
    $json = $raw !== '' ? json_decode($raw, true) : null;

    return is_array($json) ? $json : $_POST;
}

// Qualquer exceção ou erro fatal ainda precisa devolver JSON — respostas vazias
// fazem o painel falhar silenciosamente, sem mensagem para o administrador.
set_exception_handler(function ($e) {
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'error' => 'Erro interno no servidor: ' . $e->getMessage()]);
});

register_shutdown_function(function () {
    $erro = error_get_last();
    if ($erro && in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'error' => 'Erro fatal no servidor: ' . $erro['message']]);
    }
});

require_admin();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$input = api_input();

if ($id <= 0 && isset($input['id'])) {
    $id = intval($input['id']);
}
