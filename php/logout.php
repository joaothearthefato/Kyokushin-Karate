<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

validar_csrf();

// Registrar logout antes de destruir sessão
if (isset($_SESSION['id'])) {
    $nome = $_SESSION['nome'] ?? 'Desconhecido';
    log_activity($conn, 'logout', "Usuário '{$nome}' fez logout");
}

session_unset();
session_destroy();

header("Location: login.php");
exit;