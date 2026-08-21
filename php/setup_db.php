<?php
// setup_db.php - Dynamic DB Migration & Seed script for Oyama Hub
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== OYAMA HUB - DATABASE SETUP & MIGRATION ===\n\n";

// Disable throwing exceptions for non-fatal SQL statements during migration
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Execute SQL schema file
$sqlFile = __DIR__ . '/../database/schema.sql';
    $sqlContent = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        if (stristr($stmt, 'CREATE DATABASE') || stristr($stmt, 'USE oyama_hub')) {
            @mysqli_query($conn, $stmt);
            continue;
        }
        if (@mysqli_query($conn, $stmt)) {
            $success++;
        } else {
            $errors++;
        }
    }
    echo "✔ SQL Schema processado: $success comandos executados, $errors avisos/ignorados.\n";
} else {
    echo "⚠ Arquivo sql/sql.sql não encontrado.\n";
}

// Helper to safely add column if missing
function addColumnIfMissing($conn, $table, $column, $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check && mysqli_num_rows($check) == 0) {
        $alter = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (mysqli_query($conn, $alter)) {
            echo "  + Coluna '$column' adicionada em '$table'.\n";
        } else {
            echo "  x Erro ao adicionar '$column' em '$table': " . mysqli_error($conn) . "\n";
        }
    }
}

// Legacy column checks removed; schema.sql now defines all columns.


// Admin user creation moved to schema.sql; no longer needed here.


// 3. Log initial setup activity
@mysqli_query($conn, "INSERT INTO atividades (acao, detalhes, ip) VALUES ('system_setup', 'Migração de banco realizada com sucesso', '127.0.0.1')");

echo "\n=== CONFIGURAÇÃO CONCLUÍDA COM SUCESSO! ===\n";
?>