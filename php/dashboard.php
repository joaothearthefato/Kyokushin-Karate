<?php
session_start();
require 'config.php';
require_once '../includes/icons.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$uid  = $_SESSION['id'];
$nome = $_SESSION['nome'] ?? 'Praticante';

// Helper para Prepared Statements
function query_prep($conn, $query, $types, ...$params) {
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// ── KPIs reais ───────────────────────────────────────────────────
$r1 = query_prep($conn, "SELECT COUNT(*) c, COALESCE(SUM(duracao_min),0) m FROM treinos WHERE usuario_id=?", "i", $uid);
$kpi = mysqli_fetch_assoc($r1);
$total_treinos  = $kpi['c'];
$total_horas    = floor($kpi['m'] / 60);
$total_min_rest = $kpi['m'] % 60;

// Treinos esta semana
$r2 = query_prep($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=? AND YEARWEEK(data_treino,1)=YEARWEEK(CURDATE(),1)", "i", $uid);
$semana = mysqli_fetch_assoc($r2)['c'] ?? 0;

// Treinos este mês
$r3 = query_prep($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=? AND MONTH(data_treino)=MONTH(CURDATE()) AND YEAR(data_treino)=YEAR(CURDATE())", "i", $uid);
$mes = mysqli_fetch_assoc($r3)['c'] ?? 0;

// Último treino
$r4 = query_prep($conn, "SELECT data_treino, duracao_min, observacoes FROM treinos WHERE usuario_id=? ORDER BY data_treino DESC LIMIT 1", "i", $uid);
$ultimo = $r4 ? mysqli_fetch_assoc($r4) : null;

// Faixa atual
$r5 = query_prep($conn, "SELECT f.nome FROM usuarios u LEFT JOIN faixas f ON u.faixa_id=f.id WHERE u.id=?", "i", $uid);
$faixa_nome = mysqli_fetch_assoc($r5)['nome'] ?? 'Sem faixa';

// Katas e kihons concluídos
$r6 = query_prep($conn, "SELECT tipo, COUNT(*) c FROM progresso WHERE usuario_id=? AND concluido=1 GROUP BY tipo", "i", $uid);
$concluidos = ['kata' => 0, 'kihon' => 0];
if ($r6) while ($row = mysqli_fetch_assoc($r6)) $concluidos[$row['tipo']] = $row['c'];

// Frequência últimos 6 meses para mini-gráfico
$freq = [];
for ($i = 5; $i >= 0; $i--) {
    $mes_key  = date('Y-m', strtotime("-$i months"));
    $mes_num  = intval(date('m', strtotime("-$i months")));
    $mes_ano  = date('Y', strtotime("-$i months"));
    $meses_pt = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    $r = query_prep($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=? AND DATE_FORMAT(data_treino,'%Y-%m')=?", "is", $uid, $mes_key);
    $freq[] = [
        'label' => $meses_pt[$mes_num - 1],
        'valor' => mysqli_fetch_assoc($r)['c'] ?? 0,
    ];
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Oyama Hub</title>
<link rel="icon" href="../img/kyokushinicon.png">
<meta name="description" content="Seu pai
nel principal no Oyama Hub — Kyokushin Karate.">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="../css/dash_home.css">
<script>
    (function(){
        const t=localStorage.getItem('oyama-theme');
        if(t==='light'){ document.documentElement.classList.add('light'); }
    })();
</script>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<main class="dash-main">

    <!-- ── HERO ── -->
    <section class="dash-hero">
        <div class="dash-hero-text">
            <p class="dash-hero-tag">OSU!</p>
            <h1>BEM-VINDO,<br><em><?= strtoupper(htmlspecialchars($nome)) ?></em></h1>
            <p class="dash-hero-sub">
                Faixa <strong><?= htmlspecialchars($faixa_nome) ?></strong>
                <?php if ($ultimo): ?>
                    · Último treino em <strong><?= date('d/m', strtotime($ultimo['data_treino'])) ?></strong>
                <?php else: ?>
                    · Nenhum treino registrado ainda
                <?php endif; ?>
            </p>
        </div>
        <a href="../dashboard/treinos.php" class="dash-hero-cta">
            <?= render_icon('plus', 18) ?>
            Registrar Treino
        </a>
    </section>

    <!-- ── KPIs ── -->
    <section class="dash-kpis">
        <div class="dash-kpi">
            <div class="kpi-ico red"><?= render_icon('treinos', 22) ?></div>
            <div>
                <span class="kpi-val"><?= $total_treinos ?></span>
                <span class="kpi-lbl">Total de Treinos</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico gold"><?= render_icon('horas', 22) ?></div>
            <div>
                <span class="kpi-val"><?= $total_horas ?><small>h</small><?= $total_min_rest ?><small>m</small></span>
                <span class="kpi-lbl">Horas Treinadas</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico green"><?= render_icon('atividade', 22) ?></div>
            <div>
                <span class="kpi-val"><?= $semana ?></span>
                <span class="kpi-lbl">Treinos Esta Semana</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico blue"><?= render_icon('zap', 22) ?></div>
            <div>
                <span class="kpi-val"><?= $mes ?></span>
                <span class="kpi-lbl">Treinos Este Mês</span>
            </div>
        </div>
    </section>

    <!-- ── GRID PRINCIPAL ── -->
    <div class="dash-grid">

        <!-- Mini-gráfico de frequência -->
        <section class="dash-card dash-chart-card">
            <div class="dash-card-header">
                <h2>FREQUÊNCIA DE TREINOS</h2>
                <span class="dash-card-sub">Últimos 6 meses</span>
            </div>
            <div class="mini-chart">
                <?php
                $max_v = max(array_column($freq, 'valor') + [1]);
                foreach ($freq as $f):
                    $pct = $max_v > 0 ? round(($f['valor'] / $max_v) * 100) : 0;
                ?>
                    <div class="mini-bar-group">
                        <div class="mini-bar-wrap">
                            <span class="mini-bar-tip"><?= $f['valor'] ?></span>
                            <div class="mini-bar" style="height: <?= max($pct, 4) ?>%"></div>
                        </div>
                        <span class="mini-bar-label"><?= $f['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Progresso de técnicas -->
        <section class="dash-card">
            <div class="dash-card-header">
                <h2>TÉCNICAS DOMINADAS</h2>
                <a href="../dashboard/progresso.php" class="dash-card-link">Ver tudo →</a>
            </div>
            <div class="tecnicas-stats">
                <div class="tecnica-row">
                    <div class="tecnica-info">
                        <span class="tecnica-nome">Katas</span>
                        <span class="tecnica-count"><?= $concluidos['kata'] ?> concluídos</span>
                    </div>
                    <?php $pct_k = min(round($concluidos['kata'] / max(15,1) * 100), 100); ?>
                    <div class="tecnica-bar-bg">
                        <div class="tecnica-bar red" style="width: <?= $pct_k ?>%"></div>
                    </div>
                </div>
                <div class="tecnica-row">
                    <div class="tecnica-info">
                        <span class="tecnica-nome">Kihons</span>
                        <span class="tecnica-count"><?= $concluidos['kihon'] ?> dominados</span>
                    </div>
                    <?php $pct_kh = min(round($concluidos['kihon'] / max(30,1) * 100), 100); ?>
                    <div class="tecnica-bar-bg">
                        <div class="tecnica-bar gold" style="width: <?= $pct_kh ?>%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Acesso rápido -->
        <section class="dash-card dash-quick-card">
            <div class="dash-card-header">
                <h2>ACESSO RÁPIDO</h2>
            </div>
            <div class="quick-links">
                <a href="../dashboard/treinos.php" class="quick-link">
                    <div class="quick-icon red-bg">
                        <?= render_icon('treinos', 20) ?>
                    </div>
                    <div>
                        <strong>Treinos</strong>
                        <span>Registrar e historiar</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
                <a href="../dashboard/katas.php" class="quick-link">
                    <div class="quick-icon gold-bg">
                        <?= render_icon('katas', 20) ?>
                    </div>
                    <div>
                        <strong>Katas</strong>
                        <span>Formas codificadas</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
                <a href="../dashboard/kihons.php" class="quick-link">
                    <div class="quick-icon green-bg">
                        <?= render_icon('kihons', 20) ?>
                    </div>
                    <div>
                        <strong>Kihons</strong>
                        <span>Fundamentos técnicos</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
                <a href="../dashboard/progresso.php" class="quick-link">
                    <div class="quick-icon blue-bg">
                        <?= render_icon('progresso', 20) ?>
                    </div>
                    <div>
                        <strong>Progresso</strong>
                        <span>Estatísticas e conquistas</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
                <a href="../dashboard/anotacoes.php" class="quick-link">
                    <div class="quick-icon purple-bg">
                        <?= render_icon('anotacoes', 20) ?>
                    </div>
                    <div>
                        <strong>Anotações</strong>
                        <span>Notas pessoais</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
                <a href="../dashboard/perfil.php" class="quick-link">
                    <div class="quick-icon gray-bg">
                        <?= render_icon('perfil', 20) ?>
                    </div>
                    <div>
                        <strong>Perfil</strong>
                        <span>Dados e configurações</span>
                    </div>
                    <?= render_icon('arrow-right', 16, 'quick-arrow') ?>
                </a>
            </div>
        </section>

        <!-- Último treino -->
        <?php if ($ultimo): ?>
        <section class="dash-card dash-ultimo-card">
            <div class="dash-card-header">
                <h2>ÚLTIMO TREINO</h2>
                <a href="../dashboard/treinos.php" class="dash-card-link">Ver histórico →</a>
            </div>
            <div class="ultimo-treino">
                <div class="ultimo-data">
                    <span class="ultimo-dia"><?= date('d', strtotime($ultimo['data_treino'])) ?></span>
                    <span class="ultimo-mes"><?php
                        $meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                        echo $meses[intval(date('m', strtotime($ultimo['data_treino']))) - 1];
                    ?></span>
                </div>
                <div class="ultimo-info">
                    <p class="ultimo-obs"><?= htmlspecialchars($ultimo['observacoes'] ?: 'Treino registrado') ?></p>
                    <span class="ultimo-dur">⏱ <?= intval($ultimo['duracao_min']) ?> minutos</span>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>
<?php include '../includes/toast.php'; ?>
</body>
</html>
