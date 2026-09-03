<?php
// admin/api/progresso.php - Global Progression Data Endpoint
require_once __DIR__ . '/bootstrap.php';

// Fetch summary of all users' progress
$sql = "SELECT u.id, u.nome, u.email, f.nome AS faixa_nome, f.cor AS faixa_cor,
               (SELECT COUNT(*) FROM treinos t WHERE t.usuario_id = u.id) AS total_treinos,
               (SELECT COALESCE(SUM(duracao_min), 0) FROM treinos t WHERE t.usuario_id = u.id) AS total_minutos,
               (SELECT COUNT(*) FROM progresso p WHERE p.usuario_id = u.id AND p.tipo = 'kata' AND p.concluido = 1) AS katas_concluidos,
               (SELECT COUNT(*) FROM progresso p WHERE p.usuario_id = u.id AND p.tipo = 'kihon' AND p.concluido = 1) AS kihons_concluidos
        FROM usuarios u
        LEFT JOIN faixas f ON u.faixa_id = f.id
        ORDER BY total_minutos DESC, katas_concluidos DESC, u.id ASC";

$res = mysqli_query($conn, $sql);
$progresso = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Total Katas count and Total Kihons count in database
$totalKatas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM katas"))['c'] ?? 0;
$totalKihons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM kihons"))['c'] ?? 0;

api_success([
    'total_katas_db' => $totalKatas,
    'total_kihons_db' => $totalKihons,
    'ranking' => $progresso
], 'Progresso global carregado com sucesso');
?>
