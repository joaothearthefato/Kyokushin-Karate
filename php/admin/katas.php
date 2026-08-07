<?php
$page_title = 'Gerenciamento de Katas';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            <h3>Tabela Administrativa de Katas</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="kataSearch" placeholder="Buscar Kata...">
            </div>
            <select class="select-filter" id="kataFilterNivel">
                <option value="">Todos os Níveis</option>
                <option value="iniciante">Iniciante</option>
                <option value="intermediario">Intermediário</option>
                <option value="avancado">Avançado</option>
            </select>
            <button class="btn-primary" id="btnOpenAddKata">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Cadastrar Kata
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="katasTable">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Nome do Kata</th>
                    <th>Categoria</th>
                    <th>Nível</th>
                    <th>Vídeo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="katasTbody">
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px;">Carregando Katas...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD / EDIT KATA -->
<div class="modal-overlay" id="kataModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="kataModalTitle">Cadastrar Novo Kata</h3>
            <button class="btn-close-modal" id="btnCloseKataModal">&times;</button>
        </div>
        <form id="kataForm">
            <div class="modal-body">
                <input type="hidden" id="kataId" name="id" value="0">
                
                <div class="form-group">
                    <label for="kataNome">Nome do Kata *</label>
                    <input type="text" id="kataNome" name="nome" class="form-control" placeholder="Ex: Heian Shodan" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kataCategoria">Categoria</label>
                        <input type="text" id="kataCategoria" name="categoria" class="form-control" placeholder="Ex: Norte (Shotokan)">
                    </div>
                    <div class="form-group">
                        <label for="kataNivel">Nível de Dificuldade *</label>
                        <select id="kataNivel" name="nivel" class="form-control" required>
                            <option value="iniciante">Iniciante</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kataVideo">URL do Vídeo (YouTube)</label>
                        <input type="url" id="kataVideo" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    </div>
                    <div class="form-group">
                        <label for="kataImagem">Imagem de Capa (URL)</label>
                        <input type="text" id="kataImagem" name="imagem_url" class="form-control" placeholder="https://exemplo.com/imagem.jpg">
                    </div>
                </div>

                <div class="form-group">
                    <label for="kataOrdem">Ordem de Exibição</label>
                    <input type="number" id="kataOrdem" name="ordem" class="form-control" value="0" min="0">
                </div>

                <div class="form-group">
                    <label for="kataDescricao">Descrição Técnica *</label>
                    <textarea id="kataDescricao" name="descricao" class="form-control" placeholder="Primeiro kata básico do estilo..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelKataModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveKata">Salvar Kata</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let katasData = [];

    const tbody = document.getElementById('katasTbody');
    const searchInput = document.getElementById('kataSearch');
    const filterNivel = document.getElementById('kataFilterNivel');
    
    const modal = document.getElementById('kataModal');
    const form = document.getElementById('kataForm');
    const modalTitle = document.getElementById('kataModalTitle');

    // Fetch Katas from REST API
    function loadKatas() {
        adminApi('api/katas.php')
            .then(res => {
                katasData = res.data;
                renderKatas();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-red);">${escapeHtml(err.message)}</td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function renderKatas() {
        const q = searchInput.value.toLowerCase().trim();
        const nivelVal = filterNivel.value;

        const filtered = katasData.filter(k => {
            const matchesQ = k.nome.toLowerCase().includes(q) || (k.descricao && k.descricao.toLowerCase().includes(q));
            const matchesNivel = !nivelVal || k.nivel === nivelVal;
            return matchesQ && matchesNivel;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum Kata encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(k => `
            <tr>
                <td>#${k.ordem || k.id}</td>
                <td><strong>${escapeHtml(k.nome)}</strong></td>
                <td><span class="badge" style="background: rgba(255,255,255,0.06); color: var(--admin-text-main);">${escapeHtml(k.categoria || 'Geral')}</span></td>
                <td><span class="badge badge-${k.nivel}">${k.nivel.toUpperCase()}</span></td>
                <td>
                    ${k.video_url ? `<a href="${escapeHtml(k.video_url)}" target="_blank" style="color: var(--admin-red); font-weight: 600;">Assistir</a>` : '<span style="color: var(--admin-text-dim);">-</span>'}
                </td>
                <td>
                    <button class="btn-action edit" onclick="editKata(${k.id})" title="Editar Kata">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteKata(${k.id}, '${escapeHtml(k.nome)}')" title="Excluir Kata">
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

    // Filters & Search
    searchInput.addEventListener('input', renderKatas);
    filterNivel.addEventListener('change', renderKatas);

    // Modal Handlers
    document.getElementById('btnOpenAddKata').addEventListener('click', () => {
        form.reset();
        document.getElementById('kataId').value = '0';
        modalTitle.textContent = 'Cadastrar Novo Kata';
        modal.classList.add('active');
    });

    document.getElementById('btnCloseKataModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelKataModal').addEventListener('click', () => modal.classList.remove('active'));

    // Edit Kata
    window.editKata = function(id) {
        const kata = katasData.find(k => k.id == id);
        if (!kata) return;

        document.getElementById('kataId').value = kata.id;
        document.getElementById('kataNome').value = kata.nome;
        document.getElementById('kataCategoria').value = kata.categoria || '';
        document.getElementById('kataNivel').value = kata.nivel;
        document.getElementById('kataVideo').value = kata.video_url || '';
        document.getElementById('kataImagem').value = kata.imagem_url || '';
        document.getElementById('kataOrdem').value = kata.ordem || 0;
        document.getElementById('kataDescricao').value = kata.descricao || '';

        modalTitle.textContent = 'Editar Kata: ' + kata.nome;
        modal.classList.add('active');
    };

    // Save Kata Form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('kataId').value, 10) || 0;
        const payload = {
            id: id,
            nome: document.getElementById('kataNome').value,
            categoria: document.getElementById('kataCategoria').value,
            nivel: document.getElementById('kataNivel').value,
            video_url: document.getElementById('kataVideo').value,
            imagem_url: document.getElementById('kataImagem').value,
            ordem: document.getElementById('kataOrdem').value,
            descricao: document.getElementById('kataDescricao').value
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/katas.php' + (id > 0 ? '?id=' + id : '');

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Operação realizada com sucesso!', 'success');
                modal.classList.remove('active');
                loadKatas();
            })
            .catch(err => showNotification(err.message, 'error'));
    });

    // Delete Kata
    window.deleteKata = function(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o Kata "${nome}"?`)) return;

        adminApi('api/katas.php?id=' + id, 'DELETE')
            .then(res => {
                showNotification(res.message || 'Kata removido com sucesso!', 'success');
                loadKatas();
            })
            .catch(err => showNotification(err.message, 'error'));
    };

    loadKatas();
});
</script>

<?php require_once 'footer.php'; ?>
