<?php
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

// Verificar conexão
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}
?>