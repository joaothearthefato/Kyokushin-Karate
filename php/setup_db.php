<?php
// setup_db.php - Dynamic DB Migration & Seed script for Oyama Hub
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== OYAMA HUB - DATABASE SETUP & MIGRATION ===\n\n";

// Disable throwing exceptions for non-fatal SQL statements during migration
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Execute SQL schema file
$sqlFile = __DIR__ . '/../sql/sql.sql';
if (file_exists($sqlFile)) {
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

echo "\n--- Verificando Estrutura de Colunas ---\n";
addColumnIfMissing($conn, 'usuarios', 'foto_perfil', "VARCHAR(255) DEFAULT 'default_avatar.png'");
addColumnIfMissing($conn, 'faixas', 'cor', "VARCHAR(20) NOT NULL DEFAULT '#d4af37'");
addColumnIfMissing($conn, 'faixas', 'requisitos', "TEXT DEFAULT NULL");
addColumnIfMissing($conn, 'katas', 'imagem_url', "VARCHAR(255) DEFAULT NULL");
addColumnIfMissing($conn, 'katas', 'categoria', "VARCHAR(50) NOT NULL DEFAULT 'Shotokan/Norte'");
addColumnIfMissing($conn, 'exercicios_kyokushin', 'tipo', "ENUM('Força','Resistência','Técnica','Mobilidade','Soco','Chute','Defesa','Cotovelada','Joelhada') DEFAULT 'Técnica'");
addColumnIfMissing($conn, 'exercicios_kyokushin', 'descricao', "TEXT DEFAULT NULL");
addColumnIfMissing($conn, 'exercicios_kyokushin', 'quantidade', "VARCHAR(50) DEFAULT NULL");
addColumnIfMissing($conn, 'exercicios_kyokushin', 'video_url', "VARCHAR(255) DEFAULT NULL");
addColumnIfMissing($conn, 'treinos', 'nome', "VARCHAR(100) NOT NULL DEFAULT 'Treino Kyokushin'");
addColumnIfMissing($conn, 'treinos', 'descricao', "TEXT DEFAULT NULL");
addColumnIfMissing($conn, 'treinos', 'nivel', "ENUM('iniciante','intermediario','avancado') DEFAULT 'iniciante'");

// 2. Ensure Admin User exists
echo "\n--- Verificando Usuário Administrador ---\n";
$adminEmail = 'reidokyokushin@kyokushin.com';
$checkAdmin = mysqli_query($conn, "SELECT id, tipo FROM usuarios WHERE email = '$adminEmail'");
if ($checkAdmin && mysqli_num_rows($checkAdmin) > 0) {
    $userRow = mysqli_fetch_assoc($checkAdmin);
    if ($userRow['tipo'] !== 'admin') {
        mysqli_query($conn, "UPDATE usuarios SET tipo = 'admin' WHERE email = '$adminEmail'");
        echo "✔ Usuário $adminEmail atualizado para administrador!\n";
    } else {
        echo "✔ Usuário Admin já é administrador ($adminEmail).\n";
    }
} else {
    $passHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Find Preta belt ID or default 8
    $resFaixa = mysqli_query($conn, "SELECT id FROM faixas ORDER BY ordem DESC LIMIT 1");
    $faixaId = ($resFaixa && $rowF = mysqli_fetch_assoc($resFaixa)) ? $rowF['id'] : 8;

    $sqlInsertAdmin = "INSERT INTO usuarios (nome, email, senha_hash, nascimento, tipo, faixa_id, ativo, foto_perfil)
                       VALUES ('Administrador Oyama', '$adminEmail', '$passHash', '1990-01-01', 'admin', $faixaId, 1, 'default_avatar.png')";
    if (mysqli_query($conn, $sqlInsertAdmin)) {
        echo "✔ Usuário Admin criado com sucesso!\n";
        echo "  Email: $adminEmail\n";
        echo "  Senha: admin123\n";
    } else {
        echo "x Erro ao criar admin: " . mysqli_error($conn) . "\n";
    }
}

// 3. Log initial setup activity
@mysqli_query($conn, "INSERT INTO atividades (acao, detalhes, ip) VALUES ('system_setup', 'Migração de banco realizada com sucesso', '127.0.0.1')");

echo "\n=== CONFIGURAÇÃO CONCLUÍDA COM SUCESSO! ===\n";
?>