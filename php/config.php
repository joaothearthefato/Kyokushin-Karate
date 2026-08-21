<?php
date_default_timezone_set('America/Sao_Paulo');
// Erros do mysqli são tratados pelos retornos das funções (mysqli_query,
// mysqli_stmt_execute...) e não por exceções, que a partir do PHP 8.1 são
// lançadas por padrão e interrompem as respostas JSON da área administrativa.
mysqli_report(MYSQLI_REPORT_OFF);

// Configurações do banco de dados
$host = "localhost";   // Servidor
$usuario = "root";     // Usuário do banco
$senha = "Home@spSENAI2025!";           // Senha do banco (vazia por padrão no XAMPP)
$banco = "oyama_hub";  // Nome do banco

// Criar conexão
$conn = mysqli_connect($host, $usuario, $senha, $banco);

if ($conn) {
    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '-03:00'");
}

// Verificar conexão
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}
?>
