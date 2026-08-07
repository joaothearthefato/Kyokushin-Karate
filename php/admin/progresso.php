<?php
$page_title = 'Acompanhamento de Progresso Geral';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <h3>Matriz de Evolução Técnica dos Praticantes</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="progSearch" placeholder="Buscar Aluno...">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="progTable">
            <thead>
                <tr>
                    <th>Classificação</th>
                    <th>Praticante</th>
                    <th>Faixa Atual</th>
                    <th>Treinos Realizados</th>
                    <th>Horas Acumuladas</th>
                    <th>Katas Dominados</th>
                    <th>Kihons Dominados</th>
                    <th>Pontuação XP</th>
                </tr>
            </thead>
            <tbody id="progTbody">
                <tr>
                    <td colspan="8" style="text-align:center; padding: 40px;">Carregando dados de progresso...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let progData = [];
    let totalKatasDB = 1;
    let totalKihonsDB = 1;

    const tbody = document.getElementById('progTbody');
    const searchInput = document.getElementById('progSearch');

    function loadProgress() {
        fetch('api/progresso.php')
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    progData = res.ranking || [];
                    totalKatasDB = res.total_katas_db || 1;
                    totalKihonsDB = res.total_kihons_db || 1;
                    renderProgress();
                } else {
                    showNotification(res.error || 'Erro ao carregar Progresso', 'error');
                }
            })
            .catch(() => showNotification('Erro na conexão com API de Progresso', 'error'));
    }

    function renderProgress() {
        const q = searchInput.value.toLowerCase().trim();

        const filtered = progData.filter(p => p.nome.toLowerCase().includes(q) || p.email.toLowerCase().includes(q));

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum praticante encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map((p, idx) => {
            const horas = Math.floor((p.total_minutos || 0) / 60);
            const minRest = (p.total_minutos || 0) % 60;
            
            const kataPct = Math.round(((p.katas_concluidos || 0) / totalKatasDB) * 100);
            const kihonPct = Math.round(((p.kihons_concluidos || 0) / totalKihonsDB) * 100);

            // Calculate XP Score
            const xp = ((p.total_treinos || 0) * 50) + ((p.katas_concluidos || 0) * 100) + ((p.kihons_concluidos || 0) * 30) + (horas * 20);

            return `
                <tr>
                    <td><strong>#${idx + 1}</strong></td>
                    <td><strong>${escapeHtml(p.nome)}</strong><br><small style="color:var(--admin-text-muted);">${escapeHtml(p.email)}</small></td>
                    <td>
                        <span class="badge" style="background: rgba(255,255,255,0.06); color: ${p.faixa_cor || 'var(--admin-gold)'}; border: 1px solid rgba(255,255,255,0.1);">
                            ${escapeHtml(p.faixa_nome || 'Branca')}
                        </span>
                    </td>
                    <td><span class="badge badge-iniciante">${p.total_treinos || 0} treinos</span></td>
                    <td><strong>${horas}h ${minRest}m</strong></td>
                    <td>
                        <strong>${p.katas_concluidos || 0} / ${totalKatasDB}</strong>
                        <div style="width: 100px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; margin-top: 4px; overflow: hidden;">
                            <div style="width: ${kataPct}%; height: 100%; background: var(--admin-red);"></div>
                        </div>
                    </td>
                    <td>
                        <strong>${p.kihons_concluidos || 0} / ${totalKihonsDB}</strong>
                        <div style="width: 100px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; margin-top: 4px; overflow: hidden;">
                            <div style="width: ${kihonPct}%; height: 100%; background: var(--admin-gold);"></div>
                        </div>
                    </td>
                    <td><span class="badge badge-avancado">${xp} XP</span></td>
                </tr>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    searchInput.addEventListener('input', renderProgress);
    loadProgress();
});
</script>

<?php require_once 'footer.php'; ?>
