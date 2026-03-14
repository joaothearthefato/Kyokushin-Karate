<?php
// Configurações do banco de dados
$host = "localhost";   // Servidor
$usuario = "root";     // Usuário do banco
$senha = "";           // Senha do banco
$banco = "oyama_hub";  // Nome do banco

// Criar conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>