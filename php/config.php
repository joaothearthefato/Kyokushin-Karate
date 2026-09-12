<?php
date_default_timezone_set('America/Sao_Paulo');
// Erros do mysqli são tratados pelos retornos das funções (mysqli_query,
// mysqli_stmt_execute...) e não por exceções, que a partir do PHP 8.1 são
// lançadas por padrão e interrompem as respostas JSON da área administrativa.
mysqli_report(MYSQLI_REPORT_OFF);

// Carrega configuração local sem versionar credenciais.
function carregar_env_local(): void {
    $arquivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($arquivo)) return;

    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) continue;
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim(trim($valor), "\"'");
        if ($chave !== '' && getenv($chave) === false) putenv($chave . '=' . $valor);
    }
}

carregar_env_local();

$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') !== false ? (string) getenv('DB_USER') : 'oyama_app';
$senha = getenv('DB_PASSWORD') !== false ? (string) getenv('DB_PASSWORD') : '';
$banco = getenv('DB_NAME') ?: 'oyama_hub';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

// Criar conexão
$conn = mysqli_connect($host, $usuario, $senha, $banco);

if ($conn) {
    mysqli_set_charset($conn, $charset);
    mysqli_query($conn, "SET time_zone = '-03:00'");
}

// Verificar conexão mysqli
if (!$conn) {
    die("Serviço temporariamente indisponível. Por favor, tente novamente mais tarde.");
}

// Inicializar Singleton PDO (Disponibiliza $pdo para toda a aplicação)
require_once __DIR__ . '/Database.php';
Database::configure($host, $banco, $usuario, $senha, $charset);
$pdo = Database::getConnection();
?>
