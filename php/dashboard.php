<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$uid  = $_SESSION['id'];
$nome = $_SESSION['nome'] ?? 'Praticante';

// ── KPIs reais ───────────────────────────────────────────────────
$r1 = mysqli_query($conn, "SELECT COUNT(*) c, COALESCE(SUM(duracao_min),0) m FROM treinos WHERE usuario_id=$uid");
$kpi = mysqli_fetch_assoc($r1);
$total_treinos  = $kpi['c'];
$total_horas    = floor($kpi['m'] / 60);
$total_min_rest = $kpi['m'] % 60;

// Treinos esta semana
$r2 = mysqli_query($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=$uid AND YEARWEEK(data_treino,1)=YEARWEEK(CURDATE(),1)");
$semana = mysqli_fetch_assoc($r2)['c'] ?? 0;

// Treinos este mês
$r3 = mysqli_query($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=$uid AND MONTH(data_treino)=MONTH(CURDATE()) AND YEAR(data_treino)=YEAR(CURDATE())");
$mes = mysqli_fetch_assoc($r3)['c'] ?? 0;

// Último treino
$r4 = mysqli_query($conn, "SELECT data_treino, duracao_min, observacoes FROM treinos WHERE usuario_id=$uid ORDER BY data_treino DESC LIMIT 1");
$ultimo = $r4 ? mysqli_fetch_assoc($r4) : null;

// Faixa atual
$r5 = mysqli_query($conn, "SELECT f.nome FROM usuarios u LEFT JOIN faixas f ON u.faixa_id=f.id WHERE u.id=$uid");
$faixa_nome = mysqli_fetch_assoc($r5)['nome'] ?? 'Sem faixa';

// Katas e kihons concluídos
$r6 = mysqli_query($conn, "SELECT tipo, COUNT(*) c FROM progresso WHERE usuario_id=$uid AND concluido=1 GROUP BY tipo");
$concluidos = ['kata' => 0, 'kihon' => 0];
if ($r6) while ($row = mysqli_fetch_assoc($r6)) $concluidos[$row['tipo']] = $row['c'];

// Frequência últimos 6 meses para mini-gráfico
$freq = [];
for ($i = 5; $i >= 0; $i--) {
    $mes_key  = date('Y-m', strtotime("-$i months"));
    $mes_num  = intval(date('m', strtotime("-$i months")));
    $mes_ano  = date('Y', strtotime("-$i months"));
    $meses_pt = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    $r = mysqli_query($conn, "SELECT COUNT(*) c FROM treinos WHERE usuario_id=$uid AND DATE_FORMAT(data_treino,'%Y-%m')='$mes_key'");
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
<meta name="description" content="Seu painel principal no Oyama Hub — Kyokushin Karate.">
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
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Registrar Treino
        </a>
    </section>

    <!-- ── KPIs ── -->
    <section class="dash-kpis">
        <div class="dash-kpi">
            <div class="kpi-ico red"><svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div>
                <span class="kpi-val"><?= $total_treinos ?></span>
                <span class="kpi-lbl">Total de Treinos</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico gold"><svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div>
                <span class="kpi-val"><?= $total_horas ?><small>h</small><?= $total_min_rest ?><small>m</small></span>
                <span class="kpi-lbl">Horas Treinadas</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico green"><svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
            <div>
                <span class="kpi-val"><?= $semana ?></span>
                <span class="kpi-lbl">Treinos Esta Semana</span>
            </div>
        </div>
        <div class="dash-kpi">
            <div class="kpi-ico blue"><svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
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
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <strong>Treinos</strong>
                        <span>Registrar e historiar</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="../dashboard/katas.php" class="quick-link">
                    <div class="quick-icon gold-bg">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                    </div>
                    <div>
                        <strong>Katas</strong>
                        <span>Formas codificadas</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="../dashboard/kihons.php" class="quick-link">
                    <div class="quick-icon green-bg">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                    </div>
                    <div>
                        <strong>Kihons</strong>
                        <span>Fundamentos técnicos</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="../dashboard/progresso.php" class="quick-link">
                    <div class="quick-icon blue-bg">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <div>
                        <strong>Progresso</strong>
                        <span>Estatísticas e conquistas</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="../dashboard/anotacoes.php" class="quick-link">
                    <div class="quick-icon purple-bg">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <div>
                        <strong>Anotações</strong>
                        <span>Notas pessoais</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="../dashboard/perfil.php" class="quick-link">
                    <div class="quick-icon gray-bg">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <strong>Perfil</strong>
                        <span>Dados e configurações</span>
                    </div>
                    <svg class="quick-arrow" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polyline points="9 18 15 12 9 6"/></svg>
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
</body>
</html>
