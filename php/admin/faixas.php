<?php
$page_title = 'Gerenciamento de Faixas';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 0 0 0 14 7 7 0 0 0 0-14z"/></svg>
            <h3>Sistema de Controle de Faixas & Graduações</h3>
        </div>
        <div class="panel-controls">
            <button class="btn-primary" id="btnOpenAddFaixa">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Cadastrar Faixa
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="faixasTable">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Cor</th>
                    <th>Nome da Faixa</th>
                    <th>Alunos Graduados</th>
                    <th>Requisitos Técnicos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="faixasTbody">
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px;">Carregando Faixas...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD / EDIT FAIXA -->
<div class="modal-overlay" id="faixaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="faixaModalTitle">Cadastrar Nova Faixa</h3>
            <button class="btn-close-modal" id="btnCloseFaixaModal">&times;</button>
        </div>
        <form id="faixaForm">
            <div class="modal-body">
                <input type="hidden" id="faixaId" name="id" value="0">

                <div class="form-group">
                    <label for="faixaNome">Nome da Faixa *</label>
                    <input type="text" id="faixaNome" name="nome" class="form-control" placeholder="Ex: Laranja (10º Kyu)" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="faixaOrdem">Ordem Sequencial *</label>
                        <input type="number" id="faixaOrdem" name="ordem" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="faixaCor">Cor da Faixa (HEX / CSS) *</label>
                        <input type="color" id="faixaCor" name="cor" class="form-control" value="#d4af37" style="height: 42px; padding: 4px;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="faixaRequisitos">Requisitos para Exame de Faixa</label>
                    <textarea id="faixaRequisitos" name="requisitos" class="form-control" placeholder="Descreva os Katas, Kihons, Flexões e tempo de treino mínimos necessários..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelFaixaModal">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Faixa</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let faixasData = [];

    const tbody = document.getElementById('faixasTbody');
    const modal = document.getElementById('faixaModal');
    const form = document.getElementById('faixaForm');
    const modalTitle = document.getElementById('faixaModalTitle');

    function loadFaixas() {
        adminApi('api/faixas.php')
            .then(res => {
                faixasData = res.data;
                renderFaixas();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-red);">${escapeHtml(err.message)}</td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function renderFaixas() {
        if (faixasData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhuma Faixa encontrada.</td></tr>`;
            return;
        }

        tbody.innerHTML = faixasData.map(f => `
            <tr>
                <td><strong>#${f.ordem}</strong></td>
                <td>
                    <span style="display:inline-block; width: 20px; height: 20px; border-radius: 50%; background: ${f.cor || '#d4af37'}; border: 1px solid rgba(255,255,255,0.3); vertical-align: middle;"></span>
                    <code style="margin-left: 8px;">${f.cor || '#d4af37'}</code>
                </td>
                <td><strong>${escapeHtml(f.nome)}</strong></td>
                <td><span class="badge badge-iniciante">${f.total_alunos || 0} praticante(s)</span></td>
                <td>${escapeHtml(f.requisitos || 'Sem requisitos cadastrados')}</td>
                <td>
                    <button class="btn-action edit" onclick="editFaixa(${f.id})" title="Editar Faixa">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteFaixa(${f.id}, '${escapeHtml(f.nome)}')" title="Excluir Faixa">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.getElementById('btnOpenAddFaixa').addEventListener('click', () => {
        form.reset();
        document.getElementById('faixaId').value = '0';
        document.getElementById('faixaCor').value = '#d4af37';
        modalTitle.textContent = 'Cadastrar Nova Faixa';
        modal.classList.add('active');
    });

    document.getElementById('btnCloseFaixaModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelFaixaModal').addEventListener('click', () => modal.classList.remove('active'));

    window.editFaixa = function(id) {
        const f = faixasData.find(x => x.id == id);
        if (!f) return;

        document.getElementById('faixaId').value = f.id;
        document.getElementById('faixaNome').value = f.nome;
        document.getElementById('faixaOrdem').value = f.ordem;
        document.getElementById('faixaCor').value = f.cor || '#d4af37';
        document.getElementById('faixaRequisitos').value = f.requisitos || '';

        modalTitle.textContent = 'Editar Faixa: ' + f.nome;
        modal.classList.add('active');
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('faixaId').value, 10);
        const payload = {
            id: id,
            nome: document.getElementById('faixaNome').value,
            ordem: document.getElementById('faixaOrdem').value,
            cor: document.getElementById('faixaCor').value,
            requisitos: document.getElementById('faixaRequisitos').value
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/faixas.php' + (id > 0 ? '?id=' + id : '');

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Faixa salva com sucesso!', 'success');
                modal.classList.remove('active');
                loadFaixas();
            })
            .catch(err => showNotification(err.message, 'error'));
    });

    window.deleteFaixa = function(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir a Faixa "${nome}"?`)) return;

        fetch('api/faixas.php?id=' + id, { method: 'DELETE' })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showNotification(res.message || 'Faixa excluída com sucesso!', 'success');
                    loadFaixas();
                } else {
                    showNotification(res.error || 'Erro ao excluir Faixa', 'error');
                }
            });
    };

    loadFaixas();
});
</script>

<?php require_once 'footer.php'; ?>
