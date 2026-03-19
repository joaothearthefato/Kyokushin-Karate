<?php
session_start();

// ── Proteção de rota (RF17 / RNF08) ──────────────────────────────────────────
if (!isset($_SESSION['usuario']) || $_SESSION['perfil'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

require_once '../includes/config.php';   // conexão PDO em $pdo
require_once '../includes/auth.php'; // funções de sessão

// ── Estatísticas principais (RF15) ───────────────────────────────────────────
$totalAlunos    = $pdo->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'")->fetchColumn();
$totalSenseis   = $pdo->query("SELECT COUNT(*) FROM senseis WHERE status = 'ativo'")->fetchColumn();
$totalTurmas    = $pdo->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
$totalCamp      = $pdo->query("SELECT COUNT(*) FROM campeonatos WHERE YEAR(data) = YEAR(CURDATE())")->fetchColumn();

// Financeiro (RF12 / RF15)
$receitaPaga    = $pdo->query("SELECT COALESCE(SUM(valor),0) FROM mensalidades WHERE status = 'pago'")->fetchColumn();
$totalPendentes = $pdo->query("SELECT COUNT(*) FROM mensalidades WHERE status IN ('pendente','atrasado')")->fetchColumn();
$totalAtrasados = $pdo->query("SELECT COUNT(*) FROM mensalidades WHERE status = 'atrasado'")->fetchColumn();

// Distribuição de faixas (RF09)
$faixasQuery = $pdo->query("
    SELECT faixa, COUNT(*) as total
    FROM alunos WHERE status = 'ativo'
    GROUP BY faixa ORDER BY FIELD(faixa,'branca','laranja','azul','amarela','verde','marrom','preta')
");
$faixas = $faixasQuery->fetchAll(PDO::FETCH_ASSOC);

// Últimos alunos cadastrados (RF09)
$recentAlunos = $pdo->query("
    SELECT a.nome, a.faixa, a.matricula, a.status, t.nome AS turma
    FROM alunos a
    LEFT JOIN turmas t ON a.turma_id = t.id
    ORDER BY a.id DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Mensalidades pendentes / atrasadas (RF12 / RF19)
$mensRecentes = $pdo->query("
    SELECT m.mes_ano, m.valor, m.vencimento, m.status, a.nome AS aluno
    FROM mensalidades m
    JOIN alunos a ON m.aluno_id = a.id
    WHERE m.status IN ('pendente','atrasado')
    ORDER BY m.vencimento ASC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Próximos campeonatos (RF13)
$proxCamp = $pdo->query("
    SELECT nome, data, local, categoria, status
    FROM campeonatos
    WHERE data >= CURDATE()
    ORDER BY data ASC LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);

// Turmas com contagem de alunos (RF11)
$turmasDetalhe = $pdo->query("
    SELECT t.nome, t.horario, t.nivel, s.nome AS sensei,
           COUNT(a.id) AS qtd
    FROM turmas t
    LEFT JOIN senseis s ON t.sensei_id = s.id
    LEFT JOIN alunos a ON a.turma_id = t.id AND a.status = 'ativo'
    GROUP BY t.id ORDER BY qtd DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Admin logado
$adminNome = htmlspecialchars($_SESSION['usuario'] ?? 'Admin');

// Helper: badge de faixa
function beltBadge(string $faixa): string {
    $map = [
        'branca'  => ['#e8e4df','rgba(255,255,255,0.12)'],
        'laranja' => ['#e67e22','rgba(230,126,34,0.18)'],
        'azul'    => ['#3498db','rgba(52,152,219,0.18)'],
        'amarela' => ['#f1c40f','rgba(241,196,15,0.18)'],
        'verde'   => ['#2ecc71','rgba(46,204,113,0.18)'],
        'marrom'  => ['#a0622a','rgba(149,88,25,0.18)'],
        'preta'   => ['#aaaaaa','rgba(255,255,255,0.07)'],
    ];
    [$cor, $bg] = $map[$faixa] ?? $map['branca'];
    $label = ucfirst($faixa);
    return "<span class='belt' style='color:{$cor};background:{$bg}'>{$label}</span>";
}

// Helper: badge de status
function statusBadge(string $s): string {
    $map = [
        'ativo'    => ['Ativo',    'badge-green'],
        'inativo'  => ['Inativo',  'badge-gray'],
        'pendente' => ['Pendente', 'badge-yellow'],
        'pago'     => ['Pago',     'badge-green'],
        'atrasado' => ['Atrasado', 'badge-red'],
    ];
    [$label, $cls] = $map[$s] ?? [ucfirst($s), 'badge-gray'];
    return "<span class='badge {$cls}'>{$label}</span>";
}

function fmtData(?string $d): string {
    if (!$d) return '—';
    return date('d/m/Y', strtotime($d));
}
function fmtMes(?string $m): string {
    if (!$m) return '—';
    [$y,$mo] = explode('-', $m);
    $meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    return ($meses[(int)$mo - 1] ?? $mo) . '/' . $y;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oyama Hub · Dashboard</title>
    <link rel="icon" href="../img/kyokushinicon.png">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@300;400;500;600&family=Barlow+Condensed:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>

<!-- ═══════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="../img/kyokushinicon.png" alt="Kyokushin" class="brand-icon">
        <div>
            <div class="brand-name">OYAMA HUB</div>
            <div class="brand-sub">ADMIN · OSS</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">GERAL</span>
        <a href="dashboard.php" class="nav-item active">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>

        <span class="nav-label">GESTÃO</span>
        <a href="alunos.php" class="nav-item">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2"/><path d="M16 11a4 4 0 1 1 5 3.87V21h-2v-2h-2v-2.13A4 4 0 0 1 16 11z"/></svg>
            Alunos
        </a>
        <a href="senseis.php" class="nav-item">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            Senseis
        </a>
        <a href="turmas.php" class="nav-item">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="3" rx="1"/><rect x="3" y="10" width="12" height="3" rx="1"/><rect x="3" y="16" width="8" height="3" rx="1"/></svg>
            Turmas
        </a>

        <span class="nav-label">EVENTOS</span>
        <a href="campeonatos.php" class="nav-item">
            <svg viewBox="0 0 24 24"><path d="M6 9H4V5h16v4h-2a6 6 0 0 1-12 0z"/><path d="M12 15v4m-4 2h8"/></svg>
            Campeonatos
        </a>
        <a href="agenda.php" class="nav-item">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Agenda
        </a>

        <span class="nav-label">FINANCEIRO</span>
        <a href="mensalidades.php" class="nav-item">
            <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            Mensalidades
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar"><?= strtoupper(substr($adminNome, 0, 2)) ?></div>
            <div>
                <div class="admin-name"><?= $adminNome ?></div>
                <div class="admin-role">Administrador</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn" title="Sair">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</aside>

<!-- ═══════════════════════════════════════════
     MAIN
════════════════════════════════════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="topbar-title">DASHBOARD</div>
        </div>
        <div class="topbar-right">
            <span class="topbar-date"><?= date('d/m/Y') ?></span>
            <span class="topbar-dojo">Dojo Central · OSS</span>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- ── STATS CARDS (RF15) ─────────────────── -->
        <div class="stats-grid">

            <div class="stat-card accent-red" style="animation-delay:.05s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Alunos Ativos</div>
                    <div class="stat-value"><?= $totalAlunos ?></div>
                    <div class="stat-sub">matriculados</div>
                </div>
            </div>

            <div class="stat-card accent-gold" style="animation-delay:.10s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Senseis</div>
                    <div class="stat-value"><?= $totalSenseis ?></div>
                    <div class="stat-sub">instrutores ativos</div>
                </div>
            </div>

            <div class="stat-card accent-blue" style="animation-delay:.15s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="3" rx="1"/><rect x="3" y="10" width="12" height="3" rx="1"/><rect x="3" y="16" width="8" height="3" rx="1"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Turmas</div>
                    <div class="stat-value"><?= $totalTurmas ?></div>
                    <div class="stat-sub">em andamento</div>
                </div>
            </div>

            <div class="stat-card accent-green" style="animation-delay:.20s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M6 9H4V5h16v4h-2a6 6 0 0 1-12 0z"/><path d="M12 15v4m-4 2h8"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Campeonatos</div>
                    <div class="stat-value"><?= $totalCamp ?></div>
                    <div class="stat-sub">este ano</div>
                </div>
            </div>

            <div class="stat-card accent-green wide" style="animation-delay:.25s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Receita Recebida</div>
                    <div class="stat-value">R$&nbsp;<?= number_format($receitaPaga, 2, ',', '.') ?></div>
                    <div class="stat-sub">mensalidades pagas</div>
                </div>
            </div>

            <div class="stat-card accent-red wide" style="animation-delay:.30s">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Pendências</div>
                    <div class="stat-value"><?= $totalPendentes ?></div>
                    <div class="stat-sub"><?= $totalAtrasados ?> em atraso</div>
                </div>
            </div>

        </div><!-- /stats-grid -->

        <!-- ── ROW 2: Últimos alunos + Mensalidades pendentes ── -->
        <div class="row-2">

            <!-- Últimos Alunos (RF09 / RF18) -->
            <section class="card" style="animation-delay:.35s">
                <div class="card-header">
                    <h2 class="card-title">Últimos Alunos</h2>
                    <a href="alunos.php" class="card-link">Ver todos →</a>
                </div>
                <div class="card-body nopd">
                    <?php if (empty($recentAlunos)): ?>
                        <div class="empty">Nenhum aluno cadastrado.</div>
                    <?php else: foreach ($recentAlunos as $a): ?>
                        <div class="list-row">
                            <div class="list-left">
                                <div class="list-avatar">
                                    <?= strtoupper(substr($a['nome'],0,1) . (strpos($a['nome'],' ') !== false ? substr($a['nome'], strpos($a['nome'],' ')+1, 1) : '')) ?>
                                </div>
                                <div>
                                    <div class="list-name"><?= htmlspecialchars($a['nome']) ?></div>
                                    <div class="list-meta"><?= htmlspecialchars($a['matricula']) ?> · <?= htmlspecialchars($a['turma'] ?? '—') ?></div>
                                </div>
                            </div>
                            <div class="list-right">
                                <?= beltBadge($a['faixa']) ?>
                                <?= statusBadge($a['status']) ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- Mensalidades Pendentes (RF12 / RF19 / RF20) -->
            <section class="card" style="animation-delay:.40s">
                <div class="card-header">
                    <h2 class="card-title">Cobranças Pendentes</h2>
                    <a href="mensalidades.php" class="card-link">Ver todas →</a>
                </div>
                <div class="card-body nopd">
                    <?php if (empty($mensRecentes)): ?>
                        <div class="empty success-msg">✓ Tudo em dia!</div>
                    <?php else: foreach ($mensRecentes as $m): ?>
                        <div class="list-row">
                            <div class="list-left">
                                <div class="list-dot <?= $m['status'] === 'atrasado' ? 'dot-red' : 'dot-yellow' ?>"></div>
                                <div>
                                    <div class="list-name"><?= htmlspecialchars($m['aluno']) ?></div>
                                    <div class="list-meta"><?= fmtMes($m['mes_ano']) ?> · Vence <?= fmtData($m['vencimento']) ?></div>
                                </div>
                            </div>
                            <div class="list-right">
                                <span class="valor <?= $m['status'] === 'atrasado' ? 'valor-red' : 'valor-yellow' ?>">
                                    R$ <?= number_format($m['valor'], 2, ',', '.') ?>
                                </span>
                                <?= statusBadge($m['status']) ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

        </div><!-- /row-2 -->

        <!-- ── ROW 3: Turmas + Faixas + Campeonatos ── -->
        <div class="row-3">

            <!-- Turmas (RF11) -->
            <section class="card" style="animation-delay:.45s">
                <div class="card-header">
                    <h2 class="card-title">Turmas Ativas</h2>
                    <a href="turmas.php" class="card-link">Gerenciar →</a>
                </div>
                <div class="card-body nopd">
                    <?php if (empty($turmasDetalhe)): ?>
                        <div class="empty">Nenhuma turma.</div>
                    <?php else: foreach ($turmasDetalhe as $t): ?>
                        <div class="turma-row">
                            <div class="turma-info">
                                <div class="turma-nome"><?= htmlspecialchars($t['nome']) ?></div>
                                <div class="turma-meta"><?= htmlspecialchars($t['sensei'] ?? '—') ?> · <?= htmlspecialchars($t['horario']) ?></div>
                            </div>
                            <div class="turma-qtd">
                                <span class="qtd-num"><?= $t['qtd'] ?></span>
                                <span class="qtd-label">alunos</span>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- Distribuição de Faixas (RF07 / RF09) -->
            <section class="card" style="animation-delay:.50s">
                <div class="card-header">
                    <h2 class="card-title">Distribuição por Faixa</h2>
                </div>
                <div class="card-body">
                    <?php
                    $faixaMap = [
                        'branca'  => ['Branca',  '#e8e4df'],
                        'laranja' => ['Laranja', '#e67e22'],
                        'azul'    => ['Azul',    '#3498db'],
                        'amarela' => ['Amarela', '#f1c40f'],
                        'verde'   => ['Verde',   '#2ecc71'],
                        'marrom'  => ['Marrom',  '#a0622a'],
                        'preta'   => ['Preta',   '#999999'],
                    ];
                    $totalFaixas = array_sum(array_column($faixas, 'total')) ?: 1;
                    foreach ($faixas as $f):
                        $info  = $faixaMap[$f['faixa']] ?? [ucfirst($f['faixa']), '#888'];
                        $pct   = round($f['total'] / $totalFaixas * 100);
                    ?>
                        <div class="faixa-bar">
                            <div class="faixa-bar-top">
                                <span class="faixa-label" style="color:<?= $info[1] ?>"><?= $info[0] ?></span>
                                <span class="faixa-count"><?= $f['total'] ?> <small>(<?= $pct ?>%)</small></span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $info[1] ?>"></div>
                            </div>
                        </div>
                    <?php endforeach;
                    if (empty($faixas)): ?><div class="empty">Sem dados.</div><?php endif; ?>
                </div>
            </section>

            <!-- Próximos Campeonatos (RF13) -->
            <section class="card" style="animation-delay:.55s">
                <div class="card-header">
                    <h2 class="card-title">Próximos Campeonatos</h2>
                    <a href="campeonatos.php" class="card-link">Ver todos →</a>
                </div>
                <div class="card-body nopd">
                    <?php if (empty($proxCamp)): ?>
                        <div class="empty">Nenhum campeonato agendado.</div>
                    <?php else: foreach ($proxCamp as $c): ?>
                        <div class="camp-row">
                            <div class="camp-data">
                                <div class="camp-day"><?= date('d', strtotime($c['data'])) ?></div>
                                <div class="camp-month"><?= strtoupper(date('M', strtotime($c['data']))) ?></div>
                            </div>
                            <div class="camp-info">
                                <div class="camp-nome"><?= htmlspecialchars($c['nome']) ?></div>
                                <div class="camp-local"><?= htmlspecialchars($c['local']) ?> · <em><?= htmlspecialchars($c['categoria']) ?></em></div>
                            </div>
                            <?= statusBadge($c['status']) ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

        </div><!-- /row-3 -->

    </div><!-- /content -->
</div><!-- /main -->

<script>
// Toggle sidebar mobile
document.getElementById('menuToggle').addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});
</script>
</body>
</html>
