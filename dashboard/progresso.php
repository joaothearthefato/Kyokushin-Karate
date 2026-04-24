<?php
session_start();
require '../php/config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id   = $_SESSION['id'];
$usuario_nome = $_SESSION['nome'] ?? 'Praticante';

// RF14 – Total de treinos
$sql_total = "SELECT COUNT(*) as total FROM treinos WHERE usuario_id = $usuario_id";
$r_total   = mysqli_query($conn, $sql_total);
$total_treinos = mysqli_fetch_assoc($r_total)['total'] ?? 0;

// RF15 – Tempo total treinado
$sql_tempo = "SELECT SUM(duracao_min) as total_min FROM treinos WHERE usuario_id = $usuario_id";
$r_tempo   = mysqli_query($conn, $sql_tempo);
$total_min = mysqli_fetch_assoc($r_tempo)['total_min'] ?? 0;
$total_horas = floor($total_min / 60);
$restante_min = $total_min % 60;

// Média por treino
$media_duracao = $total_treinos > 0 ? round($total_min / $total_treinos) : 0;

// RF16 – Frequência: treinos por mês (últimos 6 meses)
$sql_frequencia = "SELECT DATE_FORMAT(data_treino, '%Y-%m') AS mes,
                          COUNT(*) AS quantidade,
                          SUM(duracao_min) AS minutos
                   FROM treinos
                   WHERE usuario_id = $usuario_id
                     AND data_treino >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                   GROUP BY mes
                   ORDER BY mes ASC";
$r_freq = mysqli_query($conn, $sql_frequencia);
$freq_data = [];
if ($r_freq) {
    while ($row = mysqli_fetch_assoc($r_freq)) $freq_data[] = $row;
}

// RF17 – Dados para gráfico de evolução (minutos por mês)
// Garantir que todos os 6 meses apareçam mesmo sem treinos
$meses_labels  = [];
$meses_valores = [];
$meses_minutos = [];
for ($i = 5; $i >= 0; $i--) {
    $mes_key  = date('Y-m', strtotime("-$i months"));
    $mes_nome = ucfirst(strftime('%b/%y', strtotime("-$i months")));
    // Fallback para sistemas sem strftime pt-BR
    $meses_pt = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    $mes_num  = intval(date('m', strtotime("-$i months")));
    $mes_ano  = date('y', strtotime("-$i months"));
    $mes_nome = $meses_pt[$mes_num - 1] . "/{$mes_ano}";
    $meses_labels[]  = $mes_nome;
    $found = array_filter($freq_data, fn($f) => $f['mes'] === $mes_key);
    $found = array_values($found);
    $meses_valores[] = count($found) ? (int)$found[0]['quantidade'] : 0;
    $meses_minutos[] = count($found) ? (int)$found[0]['minutos']    : 0;
}

// Últimos 10 treinos para lista
$sql_hist = "SELECT observacoes, data_treino, duracao_min FROM treinos
             WHERE usuario_id = $usuario_id
             ORDER BY data_treino DESC LIMIT 10";
$r_hist = mysqli_query($conn, $sql_hist);
$hist = [];
if ($r_hist) {
    while ($row = mysqli_fetch_assoc($r_hist)) $hist[] = $row;
}

// Faixa atual
$sql_faixa = "SELECT f.nome AS faixa_nome, f.ordem
              FROM usuarios u LEFT JOIN faixas f ON u.faixa_id = f.id
              WHERE u.id = $usuario_id";
$r_faixa = mysqli_query($conn, $sql_faixa);
$faixa_data = mysqli_fetch_assoc($r_faixa);
$faixa_nome = $faixa_data['faixa_nome'] ?? '—';

// Katas e Kihons concluídos
$sql_katas  = "SELECT COUNT(*) as c FROM progresso WHERE usuario_id=$usuario_id AND tipo='kata' AND concluido=1";
$sql_kihons = "SELECT COUNT(*) as c FROM progresso WHERE usuario_id=$usuario_id AND tipo='kihon' AND concluido=1";
$katas_feitos  = mysqli_fetch_assoc(mysqli_query($conn, $sql_katas))['c']  ?? 0;
$kihons_feitos = mysqli_fetch_assoc(mysqli_query($conn, $sql_kihons))['c'] ?? 0;

// Dias consecutivos (streak)
$sql_streak = "SELECT data_treino FROM treinos WHERE usuario_id=$usuario_id ORDER BY data_treino DESC";
$r_streak = mysqli_query($conn, $sql_streak);
$streak = 0;
if ($r_streak && mysqli_num_rows($r_streak) > 0) {
    $datas = [];
    while ($row = mysqli_fetch_assoc($r_streak)) $datas[] = $row['data_treino'];
    $datas = array_unique($datas);
    $hoje = new DateTime();
    $streak = 0;
    foreach ($datas as $d) {
        $dt   = new DateTime($d);
        $diff = $hoje->diff($dt)->days;
        if ($diff === $streak) { $streak++; } else { break; }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progresso | Oyama Hub</title>
    <meta name="description" content="Acompanhe sua evolução no Kyokushin Karate com gráficos e estatísticas detalhadas.">
    <link rel="icon" href="../img/kyokushinicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/progresso.css">
    <script>
        (function () {
            const t = localStorage.getItem('oyama-theme');
            if (t === 'light') {
                document.documentElement.classList.add('light');
                document.body && document.body.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="progresso-container">

    <!-- ── Header ── -->
    <section class="progresso-hero">
        <div>
            <h1>MEU<br><em>PROGRESSO</em></h1>
            <p>Osu, <strong><?= htmlspecialchars($usuario_nome) ?></strong>! Cada treino é um passo rumo à faixa preta.</p>
        </div>
        <div class="faixa-badge-hero">
            <small>Faixa Atual</small>
            <span><?= htmlspecialchars($faixa_nome) ?></span>
        </div>
    </section>

    <!-- ── KPIs (RF14, RF15) ── -->
    <section class="kpi-grid">
        <div class="kpi-card red-accent">
            <div class="kpi-icon">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-text">
                <span class="kpi-number"><?= $total_treinos ?></span>
                <span class="kpi-label">Total de Treinos</span>
            </div>
        </div>
        <div class="kpi-card gold-accent">
            <div class="kpi-icon">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="kpi-text">
                <span class="kpi-number"><?= $total_horas ?><small>h</small><?= $restante_min ?><small>m</small></span>
                <span class="kpi-label">Tempo Total Treinado</span>
            </div>
        </div>
        <div class="kpi-card green-accent">
            <div class="kpi-icon">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div class="kpi-text">
                <span class="kpi-number"><?= $streak ?></span>
                <span class="kpi-label">Dias Consecutivos</span>
            </div>
        </div>
        <div class="kpi-card blue-accent">
            <div class="kpi-icon">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div class="kpi-text">
                <span class="kpi-number"><?= $media_duracao ?><small>min</small></span>
                <span class="kpi-label">Média por Treino</span>
            </div>
        </div>
    </section>

    <!-- ── Gráficos (RF16, RF17) ── -->
    <div class="graficos-grid">

        <!-- Gráfico de barras: Treinos por mês -->
        <section class="grafico-card">
            <div class="grafico-header">
                <h2>FREQUÊNCIA DE TREINOS</h2>
                <span class="grafico-sub">Últimos 6 meses</span>
            </div>
            <div class="chart-container">
                <?php
                $max_val = max(array_merge($meses_valores, [1]));
                foreach ($meses_valores as $i => $val):
                    $pct = $max_val > 0 ? round(($val / $max_val) * 100) : 0;
                ?>
                    <div class="bar-group">
                        <div class="bar-wrap">
                            <div class="bar-tooltip"><?= $val ?> treino<?= $val !== 1 ? 's' : '' ?></div>
                            <div class="bar" style="height: <?= max($pct, 4) ?>%; background: <?= $val > 0 ? 'var(--red)' : 'var(--border)' ?>"></div>
                        </div>
                        <span class="bar-label"><?= $meses_labels[$i] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Gráfico de linha: Minutos por mês -->
        <section class="grafico-card">
            <div class="grafico-header">
                <h2>EVOLUÇÃO DE TEMPO</h2>
                <span class="grafico-sub">Minutos treinados por mês</span>
            </div>
            <div class="line-chart-container" id="lineChart">
                <canvas id="lineChartCanvas"></canvas>
            </div>
        </section>

    </div>

    <!-- ── Conquistas ── -->
    <section class="conquistas-section">
        <div class="section-title-bar">
            <h2>Conquistas</h2>
            <div class="title-line"></div>
        </div>
        <div class="conquistas-grid">
            <div class="conquista-card <?= $total_treinos >= 1 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">🥊</div>
                <div class="conquista-info">
                    <strong>Primeiro Treino</strong>
                    <span>Complete seu primeiro treino</span>
                </div>
                <?php if ($total_treinos >= 1): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
            <div class="conquista-card <?= $total_treinos >= 10 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">🔥</div>
                <div class="conquista-info">
                    <strong>10 Treinos</strong>
                    <span>Persistência em construção</span>
                </div>
                <?php if ($total_treinos >= 10): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
            <div class="conquista-card <?= $total_treinos >= 50 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">⚡</div>
                <div class="conquista-info">
                    <strong>50 Treinos</strong>
                    <span>Guerreiro do Dojo</span>
                </div>
                <?php if ($total_treinos >= 50): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
            <div class="conquista-card <?= $total_min >= 1000 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">⏱</div>
                <div class="conquista-info">
                    <strong>1.000 Minutos</strong>
                    <span>Mais de 16h treinadas</span>
                </div>
                <?php if ($total_min >= 1000): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
            <div class="conquista-card <?= $katas_feitos >= 5 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">🥋</div>
                <div class="conquista-info">
                    <strong>5 Katas</strong>
                    <span>Mestre das formas</span>
                </div>
                <?php if ($katas_feitos >= 5): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
            <div class="conquista-card <?= $streak >= 7 ? 'unlocked' : 'locked' ?>">
                <div class="conquista-icon">📅</div>
                <div class="conquista-info">
                    <strong>7 Dias Seguidos</strong>
                    <span>Consistência é rei</span>
                </div>
                <?php if ($streak >= 7): ?>
                    <div class="conquista-check">✓</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ── Histórico Recente (RF16) ── -->
    <section class="historico-recente">
        <div class="section-title-bar">
            <h2>Histórico Recente</h2>
            <div class="title-line"></div>
        </div>
        <?php if (count($hist) > 0): ?>
            <div class="hist-list">
                <?php foreach ($hist as $h): ?>
                    <div class="hist-item">
                        <div class="hist-date">
                            <span class="hist-dia"><?= date('d', strtotime($h['data_treino'])) ?></span>
                            <span class="hist-mes"><?php
                                $meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                                echo $meses[intval(date('m', strtotime($h['data_treino']))) - 1];
                            ?></span>
                        </div>
                        <div class="hist-info">
                            <strong><?= htmlspecialchars($h['observacoes'] ?: 'Treino registrado') ?></strong>
                            <span>⏱ <?= intval($h['duracao_min']) ?> minutos</span>
                        </div>
                        <div class="hist-bar-wrap">
                            <?php $barpct = min(round(($h['duracao_min'] / 120) * 100), 100); ?>
                            <div class="hist-bar" style="width: <?= $barpct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="vazio-progresso">
                <p>Nenhum treino registrado ainda.</p>
                <a href="treinos.php">Registre seu primeiro treino →</a>
            </div>
        <?php endif; ?>
    </section>

</main>

<script>
// ── Gráfico de linha com Canvas (RF17) ──
(function () {
    const canvas = document.getElementById('lineChartCanvas');
    if (!canvas) return;

    const dados   = <?= json_encode($meses_minutos) ?>;
    const labels  = <?= json_encode($meses_labels) ?>;
    const isDark  = !document.documentElement.classList.contains('light');

    function drawChart() {
        const dpr    = window.devicePixelRatio || 1;
        const parent = canvas.parentElement;
        const W      = parent.clientWidth;
        const H      = parent.clientHeight || 220;

        canvas.width  = W * dpr;
        canvas.height = H * dpr;
        canvas.style.width  = W + 'px';
        canvas.style.height = H + 'px';

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        ctx.clearRect(0, 0, W, H);

        const pad    = { top: 20, right: 20, bottom: 32, left: 44 };
        const cW     = W - pad.left - pad.right;
        const cH     = H - pad.top - pad.bottom;
        const maxVal = Math.max(...dados, 60);
        const n      = dados.length;

        const isLight = document.documentElement.classList.contains('light');
        const colorLine   = '#c8000a';
        const colorGrid   = isLight ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.06)';
        const colorText   = isLight ? '#7a7570' : '#888880';
        const colorFill   = isLight ? 'rgba(200,0,10,0.08)' : 'rgba(200,0,10,0.12)';

        // Grid lines
        const gridCount = 4;
        ctx.strokeStyle = colorGrid;
        ctx.lineWidth = 1;
        for (let i = 0; i <= gridCount; i++) {
            const y = pad.top + (cH / gridCount) * i;
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(pad.left + cW, y);
            ctx.stroke();

            const val = Math.round(maxVal - (maxVal / gridCount) * i);
            ctx.fillStyle = colorText;
            ctx.font = '11px Barlow Condensed, sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(val + 'm', pad.left - 6, y + 4);
        }

        // Pontos
        const pts = dados.map((v, i) => ({
            x: pad.left + (cW / (n - 1)) * i,
            y: pad.top + cH - (v / maxVal) * cH
        }));

        // Área de preenchimento
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pad.top + cH);
        pts.forEach(p => ctx.lineTo(p.x, p.y));
        ctx.lineTo(pts[pts.length - 1].x, pad.top + cH);
        ctx.closePath();
        ctx.fillStyle = colorFill;
        ctx.fill();

        // Linha
        ctx.beginPath();
        ctx.strokeStyle = colorLine;
        ctx.lineWidth = 2.5;
        ctx.lineJoin = 'round';
        pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
        ctx.stroke();

        // Pontos e labels
        pts.forEach((p, i) => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = dados[i] > 0 ? colorLine : colorGrid;
            ctx.fill();
            ctx.strokeStyle = isLight ? '#e8e3de' : '#181818';
            ctx.lineWidth = 2;
            ctx.stroke();

            // label do mês
            ctx.fillStyle = colorText;
            ctx.font = '10px Barlow Condensed, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i], p.x, pad.top + cH + 20);
        });
    }

    drawChart();
    window.addEventListener('resize', drawChart);
    // Re-draw em mudança de tema
    document.addEventListener('click', function (e) {
        if (e.target.closest('#themeToggle') || e.target.closest('#theme-toggle')) {
            setTimeout(drawChart, 50);
        }
    });
})();
</script>
</body>
</html>