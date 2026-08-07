<?php
$page_title = 'Gerenciamento de Kihons';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            <h3>Tabela Administrativa de Kihons</h3>
        </div>
        <div class="panel-controls">
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="kihonSearch" placeholder="Buscar Kihon (Romaji / Português)...">
            </div>
            <select class="select-filter" id="kihonFilterCategoria">
                <option value="0">Todas as Categorias</option>
                <!-- Populated via API -->
            </select>
            <button class="btn-primary" id="btnOpenAddKihon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Cadastrar Kihon
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="kihonsTable">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Movimento</th>
                    <th>Romaji / Kanji</th>
                    <th>Categoria</th>
                    <th>Nível</th>
                    <th>Vídeo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="kihonsTbody">
                <tr>
                    <td colspan="7" style="text-align:center; padding: 40px;">Carregando Kihons...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD / EDIT KIHON -->
<div class="modal-overlay" id="kihonModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="kihonModalTitle">Cadastrar Novo Kihon</h3>
            <button class="btn-close-modal" id="btnCloseKihonModal">&times;</button>
        </div>
        <form id="kihonForm">
            <div class="modal-body">
                <input type="hidden" id="kihonId" name="id" value="0">

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonNome">Nome do Movimento (Português) *</label>
                        <input type="text" id="kihonNome" name="nome" class="form-control" placeholder="Ex: Soco Direto" required>
                    </div>
                    <div class="form-group">
                        <label for="kihonRomaji">Romaji (Japonês) *</label>
                        <input type="text" id="kihonRomaji" name="romaji" class="form-control" placeholder="Ex: Seiken Tsuki" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonKana">Kanji / Ideograma</label>
                        <input type="text" id="kihonKana" name="kana" class="form-control" placeholder="Ex: 正拳">
                    </div>
                    <div class="form-group">
                        <label for="kihonCategoria">Categoria *</label>
                        <select id="kihonCategoria" name="categoria_id" class="form-control" required>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonNivel">Nível de Dificuldade *</label>
                        <select id="kihonNivel" name="nivel" class="form-control" required>
                            <option value="iniciante">Iniciante</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kihonOrdem">Ordem</label>
                        <input type="number" id="kihonOrdem" name="ordem" class="form-control" value="1" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="kihonVideo">URL do Vídeo (YouTube)</label>
                    <input type="url" id="kihonVideo" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                </div>

                <div class="form-group">
                    <label for="kihonDescricao">Descrição Técnica & Aplicação *</label>
                    <textarea id="kihonDescricao" name="descricao" class="form-control" placeholder="Descreva os pontos de impacto, rotação do quadril e aplicação de combate..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelKihonModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveKihon">Salvar Kihon</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let kihonsData = [];
    let categoriasData = [];

    const tbody = document.getElementById('kihonsTbody');
    const searchInput = document.getElementById('kihonSearch');
    const filterCat = document.getElementById('kihonFilterCategoria');
    const selectCatForm = document.getElementById('kihonCategoria');

    const modal = document.getElementById('kihonModal');
    const form = document.getElementById('kihonForm');
    const modalTitle = document.getElementById('kihonModalTitle');

    function loadKihons() {
        adminApi('api/kihons.php')
            .then(res => {
                kihonsData = res.data;
                categoriasData = res.categorias || [];
                populateCategoryDropdowns();
                renderKihons();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--admin-red);">${escapeHtml(err.message)}</td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function populateCategoryDropdowns() {
        filterCat.innerHTML = '<option value="0">Todas as Categorias</option>' + 
            categoriasData.map(c => `<option value="${c.id}">${c.nome} (${c.kanji})</option>`).join('');

        selectCatForm.innerHTML = categoriasData.map(c => `<option value="${c.id}">${c.nome} (${c.kanji})</option>`).join('');
    }

    function renderKihons() {
        const q = searchInput.value.toLowerCase().trim();
        const catId = parseInt(filterCat.value, 10);

        const filtered = kihonsData.filter(k => {
            const matchesQ = k.nome.toLowerCase().includes(q) || k.romaji.toLowerCase().includes(q) || (k.descricao && k.descricao.toLowerCase().includes(q));
            const matchesCat = catId === 0 || k.categoria_id == catId;
            return matchesQ && matchesCat;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">Nenhum Kihon encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(k => `
            <tr>
                <td>#${k.ordem || k.id}</td>
                <td><strong>${escapeHtml(k.nome)}</strong></td>
                <td><em>${escapeHtml(k.romaji)}</em> ${k.kana ? `<span style="color:var(--admin-gold); font-size:0.9rem; margin-left:4px;">${escapeHtml(k.kana)}</span>` : ''}</td>
                <td><span class="badge" style="background: rgba(192, 57, 43, 0.15); color: ${k.categoria_cor || 'var(--admin-gold)'}; border: 1px solid rgba(255,255,255,0.1);">${escapeHtml(k.categoria_nome)}</span></td>
                <td><span class="badge badge-${k.nivel}">${k.nivel.toUpperCase()}</span></td>
                <td>
                    ${k.video_url ? `<a href="${escapeHtml(k.video_url)}" target="_blank" style="color: var(--admin-red); font-weight: 600;">Assistir</a>` : '<span style="color: var(--admin-text-dim);">-</span>'}
                </td>
                <td>
                    <button class="btn-action edit" onclick="editKihon(${k.id})" title="Editar Kihon">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action delete" onclick="deleteKihon(${k.id}, '${escapeHtml(k.romaji)}')" title="Excluir Kihon">
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

    searchInput.addEventListener('input', renderKihons);
    filterCat.addEventListener('change', renderKihons);

    document.getElementById('btnOpenAddKihon').addEventListener('click', () => {
        form.reset();
        document.getElementById('kihonId').value = '0';
        modalTitle.textContent = 'Cadastrar Novo Kihon';
        modal.classList.add('active');
    });

    document.getElementById('btnCloseKihonModal').addEventListener('click', () => modal.classList.remove('active'));
    document.getElementById('btnCancelKihonModal').addEventListener('click', () => modal.classList.remove('active'));

    window.editKihon = function(id) {
        const k = kihonsData.find(x => x.id == id);
        if (!k) return;

        document.getElementById('kihonId').value = k.id;
        document.getElementById('kihonNome').value = k.nome;
        document.getElementById('kihonRomaji').value = k.romaji;
        document.getElementById('kihonKana').value = k.kana || '';
        document.getElementById('kihonCategoria').value = k.categoria_id;
        document.getElementById('kihonNivel').value = k.nivel;
        document.getElementById('kihonOrdem').value = k.ordem || 0;
        document.getElementById('kihonVideo').value = k.video_url || '';
        document.getElementById('kihonDescricao').value = k.descricao || '';

        modalTitle.textContent = 'Editar Kihon: ' + k.romaji;
        modal.classList.add('active');
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('kihonId').value, 10);
        const payload = {
            id: id,
            nome: document.getElementById('kihonNome').value,
            romaji: document.getElementById('kihonRomaji').value,
            kana: document.getElementById('kihonKana').value,
            categoria_id: document.getElementById('kihonCategoria').value,
            nivel: document.getElementById('kihonNivel').value,
            ordem: document.getElementById('kihonOrdem').value,
            video_url: document.getElementById('kihonVideo').value,
            descricao: document.getElementById('kihonDescricao').value
        };

        const method = id > 0 ? 'PUT' : 'POST';
        const url = 'api/kihons.php' + (id > 0 ? '?id=' + id : '');

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Operação realizada com sucesso!', 'success');
                modal.classList.remove('active');
                loadKihons();
            })
            .catch(err => showNotification(err.message, 'error'));
    });

    window.deleteKihon = function(id, romaji) {
        if (!confirm(`Tem certeza que deseja excluir o Kihon "${romaji}"?`)) return;

        adminApi('api/kihons.php?id=' + id, 'DELETE')
            .then(res => {
                showNotification(res.message || 'Kihon removido com sucesso!', 'success');
                loadKihons();
            })
            .catch(err => showNotification(err.message, 'error'));
    };

    loadKihons();
});
</script>

<?php require_once 'footer.php'; ?>
