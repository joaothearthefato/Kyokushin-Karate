<?php
include_once 'config.php';

// Ler o arquivo SQL
$sql = file_get_contents('../sql/sql.sql');

// Dividir em statements individuais
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            $success++;
        } else {
            echo "Erro: " . $conn->error . "\n";
            $errors++;
        }
    }
}

echo "Executado: $success statements com sucesso, $errors erros.\n";
?>