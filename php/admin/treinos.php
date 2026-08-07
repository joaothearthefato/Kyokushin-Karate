<?php
$page_title = 'Gerenciamento de Treinos';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v8H2z"/><path d="M6 8v8"/><path d="M14 8v8"/></svg>
            <h3>Administração de Treinos e Rotinas</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="treinoSearch" placeholder="Buscar Treino ou Praticante...">
            </div>
            <button class="btn-primary" id="btnOpenAddTreino">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Criar Treino
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="treinosTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Treino</th>
                    <th>Praticante</th>
                    <th>Nível</th>
                    <th>Duração</th>
                    <th>Data do Treino</th>
                    <th>Observações</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="treinosTbody">
                <tr>
                    <td colspan="8" style="text-align:center; padding: 40px;">Carregando Treinos...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD / EDIT TREINO -->
<div class="modal-overlay" id="treinoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="treinoModalTitle">Cadastrar Novo Treino</h3>
            <button class="btn-close-modal" id="btnCloseTreinoModal">&times;</button>
        </div>
        <form id="treinoForm">
            <div class="modal-body">
                <input type="hidden" id="treinoId" name="id" value="0">

                <div class="form-group">
                    <label for="treinoNome">Nome do Treino *</label>
                    <input type="text" id="treinoNome" name="nome" class="form-control" placeholder="Ex: Treino Iniciante de Condicionamento" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="treinoNivel">Nível *</label>
                        <select id="treinoNivel" name="nivel" class="form-control" required>
                            <option value="iniciante">Iniciante</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="treinoDuracao">Duração (Minutos) *</label>
                        <input type="number" id="treinoDuracao" name="duracao_min" class="form-control" value="60" min="10" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="treinoData">Data do Treino *</label>
                        <input type="date" id="treinoData" name="data_treino" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="treinoUsuario">Atribuir a Usuário (Opcional)</label>
                        <select id="treinoUsuario" name="usuario_id" class="form-control">
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="treinoExerciciosTxt">Exercícios Relacionados (1 por linha ou sep. por vírgula)</label>
                    <textarea id="treinoExerciciosTxt" class="form-control" placeholder="Ex:&#10;20 flexões de braço&#10;50 golpes básicos (Seiken Tsuki)&#10;Kata Taikyoku Sono Ichi"></textarea>
                </div>

                <div class="form-group">
                    <label for="treinoObservacoes">Observações Técnicas</label>
                    <textarea id="treinoObservacoes" name="observacoes" class="form-control" placeholder="Instruções para o aluno ou detalhes do treino..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelTreinoModal">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Treino</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let treinosData = [];
    let usersList = [];

    const tbody = document.getElementById('treinosTbody');
    const searchInput = document.getElementById('treinoSearch');
    const selectUser = document.getElementById('treinoUsuario');

    const modal = document.getElementById('treinoModal');
    const form = document.getElementById('treinoForm');
    const modalTitle = document.getElementById('treinoModalTitle');

    document.getElementById('treinoData').value = new Date().toISOString().split('T')[0];

    function loadData() {
        Promise.all([
            fetch('api/treinos.php').then(r => r.json()),
            fetch('api/users.php').then(r => r.json())
        ])
        .then(([resT, resU]) => {
            if (resT.success) treinosData = resT.data;
            if (resU.success) usersList = resU.data;

            selectUser.innerHTML = usersList.map(u => `<option value="${u.id}">${u.nome} (${u.email})</option>`).join('');
            renderTreinos();
        })
        .catch(() => showNotification('Erro na conexão com API de Treinos', 'error'));
    }

    function renderTreinos() {
        const q = searchInput.value.toLowerCase().trim();

        const filtered = treinosData.filter(t => {
            return t.nome.toLowerCase().includes(q) || (t.usuario_nome && t.usuario_nome.toLowerCase().includes(q)) || (t.observacoes && t.observacoes.toLowerCase().includes(q));
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum Treino encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(t => `
            <tr>
                <td>#${t.id}</td>
                <td><strong>${escapeHtml(t.nome || 'Treino Kyokushin')}</strong></td>
                <td>${escapeHtml(t.usuario_nome || 'Desconhecido')}</td>
                <td><span class="badge badge-${t.nivel || 'iniciante'}">${(t.nivel || 'iniciante').toUpperCase()}</span></td>
                <td>${t.duracao_min} min</td>
                <td>${t.data_treino ? new Date(t.data_treino + 'T00:00:00').toLocaleDateString('pt-BR') : '-'}</td>
                <td>${escapeHtml(t.observacoes || '-')}</td>
                <td>
                    <button class="btn-action edit" onclick="editTreino(${t.id})" title="Editar Treino">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteTreino(${t.id}, '${escapeHtml(t.nome)}')" title="Excluir Treino">
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

    searchInput.addEventListener('input', renderTreinos);

    document.getElementById('btnOpenAddTreino').addEventListener('click', () => {
        form.reset();
        document.getElementById('treinoId').value = '0';
        document.getElementById('treinoData').value = new Date().toISOString().split('T')[0];
        modalTitle.textContent = 'Cadastrar Novo Treino';
        modal.classList.add('active');
    });

    document.getElementById('btnCloseTreinoModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelTreinoModal').addEventListener('click', () => modal.classList.remove('active'));

    window.editTreino = function(id) {
        const t = treinosData.find(x => x.id == id);
        if (!t) return;

        document.getElementById('treinoId').value = t.id;
        document.getElementById('treinoNome').value = t.nome || '';
        document.getElementById('treinoNivel').value = t.nivel || 'iniciante';
        document.getElementById('treinoDuracao').value = t.duracao_min || 60;
        document.getElementById('treinoData').value = t.data_treino || '';
        document.getElementById('treinoUsuario').value = t.usuario_id || 0;
        document.getElementById('treinoObservacoes').value = t.observacoes || '';

        modalTitle.textContent = 'Editar Treino: ' + (t.nome || 'Treino');
        modal.classList.add('active');
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('treinoId').value, 10);
        
        const rawEx = document.getElementById('treinoExerciciosTxt').value.split('\n').filter(s => s.trim() !== '');
        const exercicios = rawEx.map(line => ({ descricao: line.trim(), series: 3, repeticoes: 15 }));

        const payload = {
            id: id,
            nome: document.getElementById('treinoNome').value,
            nivel: document.getElementById('treinoNivel').value,
            duracao_min: document.getElementById('treinoDuracao').value,
            data_treino: document.getElementById('treinoData').value,
            usuario_id: document.getElementById('treinoUsuario').value,
            observacoes: document.getElementById('treinoObservacoes').value,
            exercicios: exercicios
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/treinos.php' + (id > 0 ? '?id=' + id : '');

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showNotification(res.message || 'Treino salvo com sucesso!', 'success');
                modal.classList.remove('active');
                loadData();
            } else {
                showNotification(res.error || 'Erro ao salvar Treino', 'error');
            }
        });
    });

    window.deleteTreino = function(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o Treino "${nome}"?`)) return;

        fetch('api/treinos.php?id=' + id, { method: 'DELETE' })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showNotification(res.message || 'Treino excluído com sucesso!', 'success');
                    loadData();
                } else {
                    showNotification(res.error || 'Erro ao excluir Treino', 'error');
                }
            });
    };

    loadData();
});
</script>

<?php require_once 'footer.php'; ?>
