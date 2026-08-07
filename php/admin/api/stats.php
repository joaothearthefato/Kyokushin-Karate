<?php
// admin/api/stats.php - Admin Dashboard KPIs & Activity API
require_once __DIR__ . '/bootstrap.php';

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM usuarios"))['c'] ?? 0;
$totalTreinos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM treinos"))['c'] ?? 0;
$totalKatas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM katas"))['c'] ?? 0;
$totalKihons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM kihons"))['c'] ?? 0;
$totalExercicios = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM exercicios_kyokushin"))['c'] ?? 0;
$totalFaixas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM faixas"))['c'] ?? 0;

// Access / Activity stats (last 10 system activities)
$resAct = mysqli_query($conn, "SELECT a.*, u.nome AS usuario_nome 
                               FROM atividades a 
                               LEFT JOIN usuarios u ON a.usuario_id = u.id 
                               ORDER BY a.id DESC LIMIT 10");
$atividades = $resAct ? mysqli_fetch_all($resAct, MYSQLI_ASSOC) : [];

// Monthly training stats for past 6 months
$chartData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M/Y', strtotime("-$i months"));
    $resMonth = mysqli_query($conn, "SELECT COUNT(*) c FROM treinos WHERE DATE_FORMAT(data_treino, '%Y-%m') = '$monthKey'");
    $countMonth = ($resMonth && $rowM = mysqli_fetch_assoc($resMonth)) ? $rowM['c'] : 0;
    $chartData[] = [
        'month' => $monthLabel,
        'count' => $countMonth
    ];
}

echo json_encode([
    'success' => true,
    'kpis' => [
        'users' => $totalUsers,
        'treinos' => $totalTreinos,
        'katas' => $totalKatas,
        'kihons' => $totalKihons,
        'exercicios' => $totalExercicios,
        'faixas' => $totalFaixas
    ],
    'chart' => $chartData,
    'atividades' => $atividades
]);
?>
