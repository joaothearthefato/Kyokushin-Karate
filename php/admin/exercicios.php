<?php
$page_title = 'Gerenciamento de Exercícios';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v8H2z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="6" y1="20" x2="6" y2="23"/><line x1="10" y1="20" x2="10" y2="23"/></svg>
            <h3>Exercícios — Catálogo Kyokushin</h3>
        </div>
        <div class="panel-controls">
            <div class="results-counter">
                <strong id="exCount">—</strong>
                <span>exercícios</span>
            </div>
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="exSearch" placeholder="Buscar exercício ou categoria...">
            </div>
            <button class="btn-primary" id="btnOpenAddEx">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo Exercício
            </button>
        </div>
    </div>

    <!-- Quick filter pills by type -->
    <div class="panel-subheader">
        <div class="pill-tabs" id="exTypePills">
            <button class="pill-tab active" data-tipo="">Todos</button>
            <button class="pill-tab" data-tipo="Técnica">Técnica</button>
            <button class="pill-tab" data-tipo="Força">Força</button>
            <button class="pill-tab" data-tipo="Resistência">Resistência</button>
            <button class="pill-tab" data-tipo="Mobilidade">Mobilidade</button>
            <button class="pill-tab" data-tipo="Soco">Soco</button>
            <button class="pill-tab" data-tipo="Chute">Chute</button>
            <button class="pill-tab" data-tipo="Defesa">Defesa</button>
            <button class="pill-tab" data-tipo="Cotovelada">Cotovelada</button>
            <button class="pill-tab" data-tipo="Joelhada">Joelhada</button>
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
                    <th>Qtd. / Séries</th>
                    <th>Vídeo</th>
                    <th style="text-align:right; padding-right: 40px;">Ações</th>
                </tr>
            </thead>
            <tbody id="exTbody">
                <!-- Skeleton populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================
     MODAL: ADD / EDIT EXERCÍCIO
     ======================================================================== -->
<div class="modal-overlay" id="exModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="exModalTitle">Cadastrar Novo Exercício</h3>
            <button class="btn-close-modal" id="btnCloseExModal">&times;</button>
        </div>
        <form id="exForm" novalidate>
            <div class="modal-body">
                <input type="hidden" id="exId" name="id" value="0">

                <div class="form-group">
                    <label for="exNome">Nome do Exercício <span class="form-required">*</span></label>
                    <input type="text" id="exNome" name="nome" class="form-control"
                           placeholder="Ex: Flexão de Punhos Seiken" autocomplete="off" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="exCategoria">Categoria <span class="form-required">*</span></label>
                        <input type="text" id="exCategoria" name="categoria" class="form-control"
                               placeholder="Ex: Condicionamento / Soco" autocomplete="off" required>
                        <div class="form-hint">Agrupa exercícios do mesmo grupo muscular ou técnico.</div>
                    </div>
                    <div class="form-group">
                        <label for="exTipo">Tipo <span class="form-required">*</span></label>
                        <select id="exTipo" name="tipo" class="form-control" required>
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
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="exQuantidade">Quantidade / Séries Padrão</label>
                        <input type="text" id="exQuantidade" name="quantidade" class="form-control"
                               placeholder="Ex: 3x 20 repetições, 5 min contínuo">
                        <div class="form-hint">Volume de treino recomendado para iniciantes.</div>
                    </div>
                    <div class="form-group">
                        <label for="exVideo">URL do Vídeo Explicativo</label>
                        <input type="url" id="exVideo" name="video_url" class="form-control"
                               placeholder="https://youtube.com/watch?v=..." autocomplete="off">
                        <div class="form-hint">Link YouTube com demonstração do exercício.</div>
                        <div class="video-preview-container" id="exVideoPreview">
                            <span class="video-preview-label">YouTube Preview</span>
                            <iframe id="exVideoFrame" src="" allowfullscreen
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="exDescricao">
                        Descrição Técnica &amp; Execução
                        <span class="char-counter" id="exDescCounter">0 / 1000</span>
                    </label>
                    <textarea id="exDescricao" name="descricao" class="form-control" rows="5"
                              maxlength="1000"
                              placeholder="Descreva como executar o exercício corretamente, posição, respiração, erros comuns..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelExModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveEx">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Salvar Exercício
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let exData     = [];
    let activeTipo = '';

    const tbody    = document.getElementById('exTbody');
    const countEl  = document.getElementById('exCount');
    const search   = document.getElementById('exSearch');
    const pillsBox = document.getElementById('exTypePills');

    const modal      = document.getElementById('exModal');
    const form       = document.getElementById('exForm');
    const modalTitle = document.getElementById('exModalTitle');
    const btnSave    = document.getElementById('btnSaveEx');

    // --- LOAD ---
    function loadExercicios() {
        renderSkeleton(tbody, 7, 6);
        adminApi('api/exercicios.php')
            .then(res => {
                exData = res.data;
                renderExercicios();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <h4>Erro ao Carregar</h4>
                    <p>${escapeHtml(err.message)}</p>
                </div></td></tr>`;
                showNotification(err.message, 'error');
            });
    }

    function renderExercicios() {
        const q = search.value.toLowerCase().trim();

        const filtered = exData.filter(e => {
            const matchQ    = e.nome.toLowerCase().includes(q) || (e.categoria && e.categoria.toLowerCase().includes(q)) || (e.descricao && e.descricao.toLowerCase().includes(q));
            const matchTipo = !activeTipo || e.tipo === activeTipo;
            return matchQ && matchTipo;
        });

        countEl.textContent = filtered.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v8H2z"/></svg>
                <h4>Nenhum Exercício Encontrado</h4>
                <p>Ajuste os filtros ou cadastre um novo exercício.</p>
            </div></td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(e => `
            <tr class="expandable-row"
                data-id="${e.id}"
                data-detail="${escapeHtml(e.descricao || '')}"
                data-video="${escapeHtml(e.video_url || '')}">
                <td>#${e.id}</td>
                <td><strong>${escapeHtml(e.nome)}</strong></td>
                <td style="color:var(--admin-text-muted);">${escapeHtml(e.categoria || '—')}</td>
                <td>
                    <span class="badge ${getTipoBadgeClass(e.tipo)}">${escapeHtml(e.tipo || 'Técnica')}</span>
                </td>
                <td style="color:var(--admin-text-muted); font-size:0.95rem;">${escapeHtml(e.quantidade || 'Livre')}</td>
                <td>
                    ${e.video_url
                        ? `<span style="display:inline-flex;align-items:center;gap:5px;color:var(--admin-red);font-weight:600;font-size:0.85rem;">
                               <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                               Tem vídeo
                           </span>`
                        : `<span style="color:var(--admin-text-dim);">—</span>`}
                </td>
                <td style="text-align:right; padding-right:32px;">
                    <div style="display:inline-flex; gap:8px;">
                        <button class="btn-action edit" onclick="event.stopPropagation(); editEx(${e.id})" title="Editar Exercício">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-action delete" onclick="event.stopPropagation(); deleteEx(${e.id}, '${escapeHtml(e.nome)}')" title="Excluir Exercício">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        initExpandableRows(tbody);
    }

    // --- FILTERS ---
    search.addEventListener('input', renderExercicios);

    pillsBox.addEventListener('click', function(e) {
        const pill = e.target.closest('.pill-tab');
        if (!pill) return;
        pillsBox.querySelectorAll('.pill-tab').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        activeTipo = pill.dataset.tipo;
        renderExercicios();
    });

    // --- MODAL OPEN (NEW) ---
    document.getElementById('btnOpenAddEx').addEventListener('click', () => {
        form.reset();
        document.getElementById('exId').value = '0';
        document.getElementById('exDescCounter').textContent = '0 / 1000';
        modalTitle.textContent = 'Cadastrar Novo Exercício';
        hideVideoPreview();
        modal.classList.add('active');
        document.getElementById('exNome').focus();
    });

    document.getElementById('btnCloseExModal').addEventListener('click', closeModal);
    document.getElementById('btnCancelExModal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });

    function closeModal() {
        modal.classList.remove('active');
        hideVideoPreview();
    }

    // --- VIDEO PREVIEW ---
    function hideVideoPreview() {
        document.getElementById('exVideoPreview').classList.remove('visible');
        document.getElementById('exVideoFrame').src = '';
    }

    document.getElementById('exVideo').addEventListener('input', function() {
        const embedUrl = getYoutubeEmbedUrl(this.value.trim());
        const previewEl = document.getElementById('exVideoPreview');
        if (embedUrl) {
            document.getElementById('exVideoFrame').src = embedUrl;
            previewEl.classList.add('visible');
        } else {
            previewEl.classList.remove('visible');
            document.getElementById('exVideoFrame').src = '';
        }
    });

    // --- CHAR COUNTER ---
    document.getElementById('exDescricao').addEventListener('input', function() {
        const len = this.value.length;
        const max = parseInt(this.getAttribute('maxlength'));
        const counter = document.getElementById('exDescCounter');
        counter.textContent = `${len} / ${max}`;
        counter.className = 'char-counter' + (len >= max ? ' at-limit' : len >= max * 0.85 ? ' near-limit' : '');
    });

    // --- EDIT ---
    window.editEx = function(id) {
        const e = exData.find(x => x.id == id);
        if (!e) return;

        document.getElementById('exId').value        = e.id;
        document.getElementById('exNome').value      = e.nome;
        document.getElementById('exCategoria').value = e.categoria || '';
        document.getElementById('exTipo').value      = e.tipo || 'Técnica';
        document.getElementById('exQuantidade').value= e.quantidade || '';
        document.getElementById('exVideo').value     = e.video_url || '';
        document.getElementById('exDescricao').value = e.descricao || '';

        // Trigger char counter
        document.getElementById('exDescricao').dispatchEvent(new Event('input'));

        // Trigger video preview
        document.getElementById('exVideo').dispatchEvent(new Event('input'));

        modalTitle.textContent = `Editar: ${e.nome}`;
        modal.classList.add('active');
    };

    // --- SAVE (POST/PUT) --- BUG FIXED: removed extra });
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = parseInt(document.getElementById('exId').value, 10) || 0;
        const payload = {
            id:        id,
            nome:      document.getElementById('exNome').value.trim(),
            categoria: document.getElementById('exCategoria').value.trim(),
            tipo:      document.getElementById('exTipo').value,
            quantidade:document.getElementById('exQuantidade').value.trim(),
            video_url: document.getElementById('exVideo').value.trim(),
            descricao: document.getElementById('exDescricao').value.trim()
        };

        if (!payload.nome || !payload.categoria) {
            showNotification('Nome e Categoria são obrigatórios.', 'warning');
            return;
        }

        const method = id > 0 ? 'PUT' : 'POST';
        const url    = 'api/exercicios.php' + (id > 0 ? '?id=' + id : '');

        btnSave.classList.add('btn-loading');
        btnSave.disabled = true;

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Exercício salvo com sucesso!', 'success');
                closeModal();
                loadExercicios();
            })
            .catch(err => showNotification(err.message, 'error'))
            .finally(() => {
                btnSave.classList.remove('btn-loading');
                btnSave.disabled = false;
            });
    });

    // --- DELETE ---
    window.deleteEx = function(id, nome) {
        showDeleteConfirm(
            'Excluir Exercício',
            `Tem certeza que deseja excluir <strong>${escapeHtml(nome)}</strong>? Esta ação não pode ser desfeita.`,
            function() {
                adminApi('api/exercicios.php?id=' + id, 'DELETE')
                    .then(res => {
                        showNotification(res.message || 'Exercício excluído!', 'success');
                        loadExercicios();
                    })
                    .catch(err => showNotification(err.message, 'error'));
            }
        );
    };

    loadExercicios();
});
</script>

<?php require_once 'footer.php'; ?>
