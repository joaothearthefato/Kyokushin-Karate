<?php
$page_title = 'Configurações do Sistema';
require_once 'header.php';

$php_version = phpversion();
$db_version = mysqli_get_server_info($conn);
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <h3>Configurações Globais & Manutenção</h3>
        </div>
    </div>

    <div style="padding: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <div style="background: var(--admin-surface); padding: 20px; border-radius: 8px; border: 1px solid var(--admin-border);">
                <h4 style="font-family: var(--admin-font-heading); font-size: 1.3rem; color: var(--admin-gold); margin-bottom: 10px;">Ambiente de Execução</h4>
                <p><strong>Plataforma:</strong> Oyama Hub v2.5</p>
                <p><strong>Versão PHP:</strong> <?= $php_version ?></p>
                <p><strong>Servidor MySQL:</strong> <?= $db_version ?></p>
                <p><strong>Banco de Dados:</strong> <code>oyama_hub</code></p>
            </div>

            <div style="background: var(--admin-surface); padding: 20px; border-radius: 8px; border: 1px solid var(--admin-border);">
                <h4 style="font-family: var(--admin-font-heading); font-size: 1.3rem; color: var(--admin-red); margin-bottom: 10px;">Manutenção & Migração</h4>
                <p style="font-size: 0.9rem; color: var(--admin-text-muted); margin-bottom: 16px;">Re-executa scripts de verificação e migração de colunas no banco de dados.</p>
                <button class="btn-primary" id="btnRunMigration">Executar Migração DB</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnRunMigration')?.addEventListener('click', function() {
    if (!confirm('Deseja re-executar a migração de banco de dados?')) return;
    
    fetch('../setup_db.php')
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        })
        .catch(err => showNotification('Erro ao executar migração', 'error'));
});
</script>

<?php require_once 'footer.php'; ?>
