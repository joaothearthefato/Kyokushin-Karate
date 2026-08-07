<?php
$page_title = 'Dashboard Administrativo';
require_once 'header.php';

// Fetch stats directly or via query
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM usuarios"))['c'] ?? 0;
$totalTreinos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM treinos"))['c'] ?? 0;
$totalKatas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM katas"))['c'] ?? 0;
$totalKihons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM kihons"))['c'] ?? 0;
$totalExercicios = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM exercicios_kyokushin"))['c'] ?? 0;
$totalFaixas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM faixas"))['c'] ?? 0;

// Fetch last 8 activity logs
$resAct = mysqli_query($conn, "SELECT a.*, u.nome AS usuario_nome 
                               FROM atividades a 
                               LEFT JOIN usuarios u ON a.usuario_id = u.id 
                               ORDER BY a.id DESC LIMIT 8");
$atividades = $resAct ? mysqli_fetch_all($resAct, MYSQLI_ASSOC) : [];
?>

<!-- KPI STATS CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-info">
            <p>Usuários Cadastrados</p>
            <h3><?= number_format($totalUsers) ?></h3>
        </div>
        <div class="stat-card-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-info">
            <p>Treinos Registrados</p>
            <h3><?= number_format($totalTreinos) ?></h3>
        </div>
        <div class="stat-card-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v8H2z"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-info">
            <p>Katas Cadastrados</p>
            <h3><?= number_format($totalKatas) ?></h3>
        </div>
        <div class="stat-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 2 7 12 12 22 7 12 2"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-info">
            <p>Kihons Cadastrados</p>
            <h3><?= number_format($totalKihons) ?></h3>
        </div>
        <div class="stat-card-icon gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
    </div>
</div>

<!-- RECENT ACTIVITIES & SYSTEM SUMMARY -->
<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <h3>Últimas Atividades & Logs de Acesso</h3>
        </div>
        <div class="panel-controls">
            <span class="badge badge-iniciante"><?= count($atividades) ?> registros recentes</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Detalhes</th>
                    <th>IP</th>
                    <th>Data / Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($atividades)): ?>
                    <?php foreach ($atividades as $act): ?>
                        <tr>
                            <td>#<?= $act['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($act['usuario_nome'] ?? 'Sistema / Visitante') ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-intermediario"><?= htmlspecialchars($act['acao']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($act['detalhes'] ?? '-') ?></td>
                            <td><code><?= htmlspecialchars($act['ip'] ?? '127.0.0.1') ?></code></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($act['criado_em'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--admin-text-muted); padding: 30px;">
                            Nenhuma atividade registrada no momento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
