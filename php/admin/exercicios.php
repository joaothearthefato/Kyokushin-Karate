<?php
$page_title = 'Gerenciamento de Exercícios';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5h11M6.5 17.5h11M4 12h16"/></svg>
            <h3>Catálogo de Exercícios Kyokushin</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="exSearch" placeholder="Buscar Exercício...">
            </div>
            <select class="select-filter" id="exFilterTipo">
                <option value="">Todos os Tipos</option>
                <option value="Técnica">Técnica</option>
                <option value="Força">Força</option>
                <option value="Resistência">Resistência</option>
                <option value="Mobilidade">Mobilidade</option>
                <option value="Soco">Soco</option>
                <option value="Chute">Chute</option>
                <option value="Defesa">Defesa</option>
                <option value="Cotovelada">Cotovelada</option>
                <option value="Joelhada">Joelhada</option>
            </select>
            <button class="btn-primary" id="btnOpenAddEx">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Cadastrar Exercício
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="exTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Exercício</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Quantidade / Séries</th>
                    <th>Vídeo Explicativo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="exTbody">
                <tr>
                    <td colspan="7" style="text-align:center; padding: 40px;">Carregando Exercícios...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD / EDIT EXERCICIO -->
<div class="modal-overlay" id="exModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="exModalTitle">Cadastrar Novo Exercício</h3>
            <button class="btn-close-modal" id="btnCloseExModal">&times;</button>
        </div>
        <form id="exForm">
            <div class="modal-body">
                <input type="hidden" id="exId" name="id" value="0">

                <div class="form-group">
                    <label for="exNome">Nome do Exercício *</label>
                    <input type="text" id="exNome" name="nome" class="form-control" placeholder="Ex: Flexão de Punhos Seiken" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="exCategoria">Categoria *</label>
                        <input type="text" id="exCategoria" name="categoria" class="form-control" placeholder="Ex: Força / Soco" required>
                    </div>
                    <div class="form-group">
                        <label for="exTipo">Tipo *</label>
                        <select id="exTipo" name="tipo" class="form-control" required>
                            <option value="Força">Força</option>
                            <option value="Resistência">Resistência</option>
                            <option value="Técnica">Técnica</option>
                            <option value="Mobilidade">Mobilidade</option>
                            <option value="Soco">Soco</option>
                            <option value="Chute">Chute</option>
                            <option value="Defesa">Defesa</option>
                            <option value="Cotovelada">Cotovelada</option>
                            <option value="Joelhada">Joelhada</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="exQuantidade">Quantidade / Repetições Padrão</label>
                        <input type="text" id="exQuantidade" name="quantidade" class="form-control" placeholder="Ex: 3x 20 repetições">
                    </div>
                    <div class="form-group">
                        <label for="exVideo">URL do Vídeo Explicativo</label>
                        <input type="url" id="exVideo" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="exDescricao">Descrição Técnica & Execução</label>
                    <textarea id="exDescricao" name="descricao" class="form-control" placeholder="Descreva como executar corretamente o movimento..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelExModal">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Exercício</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let exData = [];

    const tbody = document.getElementById('exTbody');
    const searchInput = document.getElementById('exSearch');
    const filterTipo = document.getElementById('exFilterTipo');

    const modal = document.getElementById('exModal');
    const form = document.getElementById('exForm');
    const modalTitle = document.getElementById('exModalTitle');

    function loadExercicios() {
        adminApi('api/exercicios.php')
            .then(res => {
                exData = res.data;
                renderExercicios();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-red);">${escapeHtml(err.message)}</td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function renderExercicios() {
        const q = searchInput.value.toLowerCase().trim();
        const tipoVal = filterTipo.value;

        const filtered = exData.filter(e => {
            const matchesQ = e.nome.toLowerCase().includes(q) || e.categoria.toLowerCase().includes(q);
            const matchesTipo = !tipoVal || e.tipo === tipoVal;
            return matchesQ && matchesTipo;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum Exercício encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(e => `
            <tr>
                <td>#${e.id}</td>
                <td><strong>${escapeHtml(e.nome)}</strong></td>
                <td>${escapeHtml(e.categoria)}</td>
                <td><span class="badge badge-intermediario">${escapeHtml(e.tipo || 'Técnica')}</span></td>
                <td>${escapeHtml(e.quantidade || 'Livre')}</td>
                <td>
                    ${e.video_url ? `<a href="${escapeHtml(e.video_url)}" target="_blank" style="color: var(--admin-red); font-weight: 600;">Assistir</a>` : '<span style="color: var(--admin-text-dim);">-</span>'}
                </td>
                <td>
                    <button class="btn-action edit" onclick="editEx(${e.id})" title="Editar Exercício">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteEx(${e.id}, '${escapeHtml(e.nome)}')" title="Excluir Exercício">
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

    searchInput.addEventListener('input', renderExercicios);
    filterTipo.addEventListener('change', renderExercicios);

    document.getElementById('btnOpenAddEx').addEventListener('click', () => {
        form.reset();
        document.getElementById('exId').value = '0';
        modalTitle.textContent = 'Cadastrar Novo Exercício';
        modal.classList.add('active');
    });

    document.getElementById('btnCloseExModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelExModal').addEventListener('click', () => modal.classList.remove('active'));

    window.editEx = function(id) {
        const e = exData.find(x => x.id == id);
        if (!e) return;

        document.getElementById('exId').value = e.id;
        document.getElementById('exNome').value = e.nome;
        document.getElementById('exCategoria').value = e.categoria;
        document.getElementById('exTipo').value = e.tipo || 'Técnica';
        document.getElementById('exQuantidade').value = e.quantidade || '';
        document.getElementById('exVideo').value = e.video_url || '';
        document.getElementById('exDescricao').value = e.descricao || '';

        modalTitle.textContent = 'Editar Exercício: ' + e.nome;
        modal.classList.add('active');
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('exId').value, 10);
        const payload = {
            id: id,
            nome: document.getElementById('exNome').value,
            categoria: document.getElementById('exCategoria').value,
            tipo: document.getElementById('exTipo').value,
            quantidade: document.getElementById('exQuantidade').value,
            video_url: document.getElementById('exVideo').value,
            descricao: document.getElementById('exDescricao').value
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/exercicios.php' + (id > 0 ? '?id=' + id : '');

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Exercício salvo com sucesso!', 'success');
                modal.classList.remove('active');
                loadExercicios();
            })
            .catch(err => showNotification(err.message, 'error'));
        });
    });

    window.deleteEx = function(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o Exercício "${nome}"?`)) return;

        adminApi('api/exercicios.php?id=' + id, 'DELETE')
            .then(res => {
                showNotification(res.message || 'Exercício excluído com sucesso!', 'success');
                loadExercicios();
            })
            .catch(err => showNotification(err.message, 'error'));
    };

    loadExercicios();
});
</script>

<?php require_once 'footer.php'; ?>
