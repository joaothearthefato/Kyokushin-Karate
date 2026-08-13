<?php
$page_title = 'Gerenciamento de Kihons';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            <h3>Kihons — Técnicas Fundamentais</h3>
        </div>
        <div class="panel-controls">
            <div class="results-counter">
                <strong id="kihonCount">—</strong>
                <span>kihons</span>
            </div>
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="kihonSearch" placeholder="Buscar por nome, romaji...">
            </div>
            <select class="select-filter" id="kihonFilterCategoria">
                <option value="0">Todas as Categorias</option>
                <!-- Populated via API -->
            </select>
            <button class="btn-primary" id="btnOpenAddKihon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo Kihon
            </button>
        </div>
    </div>

    <!-- Quick filter pills -->
    <div class="panel-subheader">
        <div class="pill-tabs" id="kihonLevelPills">
            <button class="pill-tab active" data-nivel="">Todos os Níveis</button>
            <button class="pill-tab" data-nivel="iniciante">Iniciante</button>
            <button class="pill-tab" data-nivel="intermediario">Intermediário</button>
            <button class="pill-tab" data-nivel="avancado">Avançado</button>
        </div>
        <span style="font-family: var(--admin-font-body); font-size: 0.85rem; color: var(--admin-text-dim); letter-spacing: 1px;">
            Clique na linha para ver a descrição completa
        </span>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="kihonsTable">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Movimento (PT)</th>
                    <th>Romaji / Kanji</th>
                    <th>Categoria</th>
                    <th>Nível</th>
                    <th>Vídeo</th>
                    <th style="text-align:right; padding-right: 40px;">Ações</th>
                </tr>
            </thead>
            <tbody id="kihonsTbody">
                <!-- Skeleton populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================
     MODAL: ADD / EDIT KIHON
     ======================================================================== -->
<div class="modal-overlay" id="kihonModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="kihonModalTitle">Cadastrar Novo Kihon</h3>
            <button class="btn-close-modal" id="btnCloseKihonModal">&times;</button>
        </div>
        <form id="kihonForm" novalidate>
            <div class="modal-body">
                <input type="hidden" id="kihonId" name="id" value="0">

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonNome">Nome em Português <span class="form-required">*</span></label>
                        <input type="text" id="kihonNome" name="nome" class="form-control"
                               placeholder="Ex: Soco Direto" autocomplete="off" required>
                        <div class="form-hint">Nome do movimento em português.</div>
                    </div>
                    <div class="form-group">
                        <label for="kihonRomaji">Romaji (Japonês) <span class="form-required">*</span></label>
                        <input type="text" id="kihonRomaji" name="romaji" class="form-control"
                               placeholder="Ex: Seiken Tsuki" autocomplete="off" required>
                        <div class="form-hint">Transliteração fonética japonesa.</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonKana">Kanji / Ideograma</label>
                        <input type="text" id="kihonKana" name="kana" class="form-control"
                               placeholder="Ex: 正拳突き" autocomplete="off">
                        <div class="form-hint">Caracteres japoneses (opcional).</div>
                    </div>
                    <div class="form-group">
                        <label for="kihonCategoria">Categoria <span class="form-required">*</span></label>
                        <select id="kihonCategoria" name="categoria_id" class="form-control" required>
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kihonNivel">Nível de Dificuldade <span class="form-required">*</span></label>
                        <select id="kihonNivel" name="nivel" class="form-control" required>
                            <option value="iniciante">🔵 Iniciante</option>
                            <option value="intermediario">🟡 Intermediário</option>
                            <option value="avancado">🔴 Avançado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kihonOrdem">Ordem de Exibição</label>
                        <input type="number" id="kihonOrdem" name="ordem" class="form-control"
                               value="1" min="0" placeholder="0">
                        <div class="form-hint">Número para ordenação na lista.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kihonVideo">URL do Vídeo (YouTube)</label>
                    <input type="url" id="kihonVideo" name="video_url" class="form-control"
                           placeholder="https://youtube.com/watch?v=..." autocomplete="off">
                    <div class="form-hint">Cole o link do YouTube para pré-visualizar abaixo.</div>
                    <div class="video-preview-container" id="kihonVideoPreview">
                        <span class="video-preview-label">Pré-visualização</span>
                        <iframe id="kihonVideoFrame" src="" allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kihonDescricao">
                        Descrição Técnica <span class="form-required">*</span>
                        <span class="char-counter" id="kihonDescCounter">0 / 1000</span>
                    </label>
                    <textarea id="kihonDescricao" name="descricao" class="form-control" rows="5"
                              maxlength="1000"
                              placeholder="Descreva os pontos de impacto, rotação do quadril, aplicação em combate..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelKihonModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveKihon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Salvar Kihon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let kihonsData = [];
    let categoriasData = [];
    let activeNivel = '';

    const tbody    = document.getElementById('kihonsTbody');
    const countEl  = document.getElementById('kihonCount');
    const search   = document.getElementById('kihonSearch');
    const filterCat= document.getElementById('kihonFilterCategoria');
    const pillsBox = document.getElementById('kihonLevelPills');

    const modal      = document.getElementById('kihonModal');
    const form       = document.getElementById('kihonForm');
    const modalTitle = document.getElementById('kihonModalTitle');
    const btnSave    = document.getElementById('btnSaveKihon');

    // --- LOAD ---
    function loadKihons() {
        renderSkeleton(tbody, 7, 6);
        adminApi('api/kihons.php')
            .then(res => {
                kihonsData     = res.data;
                categoriasData = res.categorias || [];
                populateCategoryDropdowns();
                renderKihons();
                initExpandableRows(tbody);
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

    function populateCategoryDropdowns() {
        filterCat.innerHTML = '<option value="0">Todas as Categorias</option>' +
            categoriasData.map(c => `<option value="${c.id}">${escapeHtml(c.nome)} (${escapeHtml(c.kanji)})</option>`).join('');

        const catForm = document.getElementById('kihonCategoria');
        catForm.innerHTML = '<option value="">Selecione uma categoria...</option>' +
            categoriasData.map(c => `<option value="${c.id}">${escapeHtml(c.nome)} (${escapeHtml(c.kanji)})</option>`).join('');
    }

    function renderKihons() {
        const q      = search.value.toLowerCase().trim();
        const catId  = parseInt(filterCat.value, 10);

        const filtered = kihonsData.filter(k => {
            const matchQ   = k.nome.toLowerCase().includes(q) || k.romaji.toLowerCase().includes(q) || (k.descricao && k.descricao.toLowerCase().includes(q));
            const matchCat = catId === 0 || k.categoria_id == catId;
            const matchNiv = !activeNivel || k.nivel === activeNivel;
            return matchQ && matchCat && matchNiv;
        });

        countEl.textContent = filtered.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <h4>Nenhum Kihon Encontrado</h4>
                <p>Tente ajustar os filtros ou cadastre um novo kihon.</p>
            </div></td></tr>`;
            return;
        }

        const nivelBadge = { iniciante: 'badge-iniciante', intermediario: 'badge-intermediario', avancado: 'badge-avancado' };

        tbody.innerHTML = filtered.map(k => `
            <tr class="expandable-row"
                data-id="${k.id}"
                data-detail="${escapeHtml(k.descricao || '')}"
                data-video="${escapeHtml(k.video_url || '')}">
                <td>#${k.ordem != null ? k.ordem : k.id}</td>
                <td><strong>${escapeHtml(k.nome)}</strong></td>
                <td>
                    <em style="color: var(--admin-text-main);">${escapeHtml(k.romaji)}</em>
                    ${k.kana ? `<span style="color:var(--admin-gold); font-size:1rem; margin-left:6px;">${escapeHtml(k.kana)}</span>` : ''}
                </td>
                <td>
                    <span class="badge" style="background: rgba(184,150,46,0.12); color: ${escapeHtml(k.categoria_cor || 'var(--admin-gold)')}; border-left: 3px solid ${escapeHtml(k.categoria_cor || 'var(--admin-gold)')};">
                        ${escapeHtml(k.categoria_nome || '—')}
                    </span>
                </td>
                <td><span class="badge ${nivelBadge[k.nivel] || 'badge-iniciante'}">${k.nivel.toUpperCase()}</span></td>
                <td>
                    ${k.video_url
                        ? `<span style="display:inline-flex;align-items:center;gap:5px;color:var(--admin-red);font-weight:600;font-size:0.85rem;">
                               <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                               Tem vídeo
                           </span>`
                        : `<span style="color:var(--admin-text-dim);">—</span>`}
                </td>
                <td style="text-align:right; padding-right:32px;">
                    <div style="display:inline-flex; gap:8px;">
                        <button class="btn-action edit" onclick="event.stopPropagation(); editKihon(${k.id})" title="Editar Kihon">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-action delete" onclick="event.stopPropagation(); deleteKihon(${k.id}, '${escapeHtml(k.romaji)}')" title="Excluir Kihon">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        initExpandableRows(tbody);
    }

    // --- FILTERS ---
    search.addEventListener('input', renderKihons);
    filterCat.addEventListener('change', renderKihons);

    pillsBox.addEventListener('click', function(e) {
        const pill = e.target.closest('.pill-tab');
        if (!pill) return;
        pillsBox.querySelectorAll('.pill-tab').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        activeNivel = pill.dataset.nivel;
        renderKihons();
    });

    // --- MODAL OPEN (NEW) ---
    document.getElementById('btnOpenAddKihon').addEventListener('click', () => {
        form.reset();
        document.getElementById('kihonId').value = '0';
        document.getElementById('kihonDescCounter').textContent = '0 / 1000';
        modalTitle.textContent = 'Cadastrar Novo Kihon';
        hideVideoPreview();
        modal.classList.add('active');
        document.getElementById('kihonNome').focus();
    });

    document.getElementById('btnCloseKihonModal').addEventListener('click', closeModal);
    document.getElementById('btnCancelKihonModal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    function closeModal() {
        modal.classList.remove('active');
        hideVideoPreview();
    }

    // --- VIDEO PREVIEW ---
    function hideVideoPreview() {
        document.getElementById('kihonVideoPreview').classList.remove('visible');
        document.getElementById('kihonVideoFrame').src = '';
    }

    document.getElementById('kihonVideo').addEventListener('input', function() {
        const embedUrl = getYoutubeEmbedUrl(this.value.trim());
        const previewEl = document.getElementById('kihonVideoPreview');
        if (embedUrl) {
            document.getElementById('kihonVideoFrame').src = embedUrl;
            previewEl.classList.add('visible');
        } else {
            previewEl.classList.remove('visible');
            document.getElementById('kihonVideoFrame').src = '';
        }
    });

    // --- CHAR COUNTER ---
    document.getElementById('kihonDescricao').addEventListener('input', function() {
        const len = this.value.length;
        const max = parseInt(this.getAttribute('maxlength'));
        const counter = document.getElementById('kihonDescCounter');
        counter.textContent = `${len} / ${max}`;
        counter.className = 'char-counter' + (len >= max ? ' at-limit' : len >= max * 0.85 ? ' near-limit' : '');
    });

    // --- EDIT ---
    window.editKihon = function(id) {
        const k = kihonsData.find(x => x.id == id);
        if (!k) return;

        document.getElementById('kihonId').value       = k.id;
        document.getElementById('kihonNome').value     = k.nome;
        document.getElementById('kihonRomaji').value   = k.romaji;
        document.getElementById('kihonKana').value     = k.kana || '';
        document.getElementById('kihonCategoria').value= k.categoria_id;
        document.getElementById('kihonNivel').value    = k.nivel;
        document.getElementById('kihonOrdem').value    = k.ordem != null ? k.ordem : 0;
        document.getElementById('kihonVideo').value    = k.video_url || '';
        document.getElementById('kihonDescricao').value= k.descricao || '';

        // Trigger char counter
        document.getElementById('kihonDescricao').dispatchEvent(new Event('input'));

        // Trigger video preview
        document.getElementById('kihonVideo').dispatchEvent(new Event('input'));

        modalTitle.textContent = `Editar: ${k.romaji}`;
        modal.classList.add('active');
    };

    // --- SAVE (POST/PUT) ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = parseInt(document.getElementById('kihonId').value, 10);
        const payload = {
            id:          id,
            nome:        document.getElementById('kihonNome').value.trim(),
            romaji:      document.getElementById('kihonRomaji').value.trim(),
            kana:        document.getElementById('kihonKana').value.trim(),
            categoria_id:document.getElementById('kihonCategoria').value,
            nivel:       document.getElementById('kihonNivel').value,
            ordem:       parseInt(document.getElementById('kihonOrdem').value) || 0,
            video_url:   document.getElementById('kihonVideo').value.trim(),
            descricao:   document.getElementById('kihonDescricao').value.trim()
        };

        if (!payload.nome || !payload.romaji || !payload.descricao || !payload.categoria_id) {
            showNotification('Preencha todos os campos obrigatórios (*).', 'warning');
            return;
        }

        const method = id > 0 ? 'PUT' : 'POST';
        const url    = 'api/kihons.php' + (id > 0 ? '?id=' + id : '');

        btnSave.classList.add('btn-loading');
        btnSave.disabled = true;

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Kihon salvo com sucesso!', 'success');
                closeModal();
                loadKihons();
            })
            .catch(err => showNotification(err.message, 'error'))
            .finally(() => {
                btnSave.classList.remove('btn-loading');
                btnSave.disabled = false;
            });
    });

    // --- DELETE ---
    window.deleteKihon = function(id, romaji) {
        showDeleteConfirm(
            'Excluir Kihon',
            `Tem certeza que deseja excluir o kihon <strong>${escapeHtml(romaji)}</strong>? Esta ação não pode ser desfeita.`,
            function() {
                adminApi('api/kihons.php?id=' + id, 'DELETE')
                    .then(res => {
                        showNotification(res.message || 'Kihon removido!', 'success');
                        loadKihons();
                    })
                    .catch(err => showNotification(err.message, 'error'));
            }
        );
    };

    // Keyboard shortcut: Escape closes modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    loadKihons();
});
</script>

<?php require_once 'footer.php'; ?>
