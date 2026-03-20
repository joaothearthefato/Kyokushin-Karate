<?php
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