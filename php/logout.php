<?php
session_start();
require 'config.php';
require_once 'auth_check.php';

// Registrar logout antes de destruir sessão
if (isset($_SESSION['id'])) {
    $nome = $_SESSION['nome'] ?? 'Desconhecido';
    log_activity($conn, 'logout', "Usuário '{$nome}' fez logout");
}

session_unset();
session_destroy();

header("Location: login.php");
exit;