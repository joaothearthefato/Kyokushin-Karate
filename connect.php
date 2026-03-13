<?php
// Configurações do banco de dados
$host = "localhost";   // Servidor
$usuario = "root";     // Usuário do banco
$senha = "Home@spSENAI2025!";           // Senha do banco
$banco = "meu_banco";  // Nome do banco

// Criar conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

echo "Conexão realizada com sucesso!";

// Fechar conexão
$conn->close();
?>