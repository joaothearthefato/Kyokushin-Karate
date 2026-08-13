<?php
$page_title = 'Gerenciamento de Katas';
require_once 'header.php';
?>

<div class="panel-box">
    <div class="panel-header">
        <div class="panel-title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            <h3>Katas — Formas e Sequências</h3>
        </div>
        <div class="panel-controls">
            <div class="results-counter">
                <strong id="kataCount">—</strong>
                <span>katas</span>
            </div>
            <div class="search-input-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="kataSearch" placeholder="Buscar por nome ou categoria...">
            </div>
            <button class="btn-primary" id="btnOpenAddKata">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo Kata
            </button>
        </div>
    </div>

    <!-- Quick filter pills -->
    <div class="panel-subheader">
        <div class="pill-tabs" id="kataLevelPills">
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
        <table class="data-table" id="katasTable">
            <thead>
                <tr>
                    <th>Capa</th>
                    <th>Ordem</th>
                    <th>Nome do Kata</th>
                    <th>Categoria</th>
                    <th>Nível</th>
                    <th>Vídeo</th>
                    <th style="text-align:right; padding-right: 40px;">Ações</th>
                </tr>
            </thead>
            <tbody id="katasTbody">
                <!-- Skeleton populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================
     MODAL: ADD / EDIT KATA
     ======================================================================== -->
<div class="modal-overlay" id="kataModal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="kataModalTitle">Cadastrar Novo Kata</h3>
            <button class="btn-close-modal" id="btnCloseKataModal">&times;</button>
        </div>
        <form id="kataForm" novalidate>
            <div class="modal-body">
                <input type="hidden" id="kataId" name="id" value="0">

                <div class="form-row">
                    <div class="form-group">
                        <label for="kataNome">Nome do Kata <span class="form-required">*</span></label>
                        <input type="text" id="kataNome" name="nome" class="form-control"
                               placeholder="Ex: Taikyoku Sono Ichi" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="kataCategoria">Categoria / Estilo</label>
                        <input type="text" id="kataCategoria" name="categoria" class="form-control"
                               placeholder="Ex: Kihon Kata, Norte (Shotokan)">
                        <div class="form-hint">Classificação ou escola de origem.</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kataNivel">Nível de Dificuldade <span class="form-required">*</span></label>
                        <select id="kataNivel" name="nivel" class="form-control" required>
                            <option value="iniciante">🔵 Iniciante</option>
                            <option value="intermediario">🟡 Intermediário</option>
                            <option value="avancado">🔴 Avançado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kataOrdem">Ordem de Exibição</label>
                        <input type="number" id="kataOrdem" name="ordem" class="form-control"
                               value="0" min="0" placeholder="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kataVideo">URL do Vídeo (YouTube)</label>
                        <input type="url" id="kataVideo" name="video_url" class="form-control"
                               placeholder="https://youtube.com/watch?v=..." autocomplete="off">
                        <div class="form-hint">Cole o link do YouTube para pré-visualizar.</div>
                        <div class="video-preview-container" id="kataVideoPreview">
                            <span class="video-preview-label">YouTube Preview</span>
                            <iframe id="kataVideoFrame" src="" allowfullscreen
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="kataImagem">URL da Imagem de Capa</label>
                        <input type="text" id="kataImagem" name="imagem_url" class="form-control"
                               placeholder="https://exemplo.com/imagem.jpg" autocomplete="off">
                        <div class="form-hint">URL de imagem para exibir na lista.</div>
                        <div class="image-preview-container" id="kataImagePreview">
                            <img id="kataImagePreviewImg" src="" alt="Pré-visualização">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kataDescricao">
                        Descrição Técnica <span class="form-required">*</span>
                        <span class="char-counter" id="kataDescCounter">0 / 1200</span>
                    </label>
                    <textarea id="kataDescricao" name="descricao" class="form-control" rows="5"
                              maxlength="1200"
                              placeholder="Descreva a sequência de movimentos, origem histórica e objetivo do kata..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelKataModal">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSaveKata">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Salvar Kata
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let katasData  = [];
    let activeNivel= '';

    const tbody    = document.getElementById('katasTbody');
    const countEl  = document.getElementById('kataCount');
    const search   = document.getElementById('kataSearch');
    const pillsBox = document.getElementById('kataLevelPills');

    const modal      = document.getElementById('kataModal');
    const form       = document.getElementById('kataForm');
    const modalTitle = document.getElementById('kataModalTitle');
    const btnSave    = document.getElementById('btnSaveKata');

    // --- LOAD ---
    function loadKatas() {
        renderSkeleton(tbody, 7, 5);
        adminApi('api/katas.php')
            .then(res => {
                katasData = res.data;
                renderKatas();
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

    function renderKatas() {
        const q = search.value.toLowerCase().trim();

        const filtered = katasData.filter(k => {
            const matchQ   = k.nome.toLowerCase().includes(q) || (k.categoria && k.categoria.toLowerCase().includes(q)) || (k.descricao && k.descricao.toLowerCase().includes(q));
            const matchNiv = !activeNivel || k.nivel === activeNivel;
            return matchQ && matchNiv;
        });

        countEl.textContent = filtered.length;

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                <h4>Nenhum Kata Encontrado</h4>
                <p>Tente ajustar o filtro ou cadastre um novo kata.</p>
            </div></td></tr>`;
            return;
        }

        const nivelBadge = { iniciante: 'badge-iniciante', intermediario: 'badge-intermediario', avancado: 'badge-avancado' };

        tbody.innerHTML = filtered.map(k => `
            <tr class="expandable-row"
                data-id="${k.id}"
                data-detail="${escapeHtml(k.descricao || '')}"
                data-video="${escapeHtml(k.video_url || '')}">
                <td>
                    ${k.imagem_url
                        ? `<img src="${escapeHtml(k.imagem_url)}" alt="${escapeHtml(k.nome)}" class="table-thumb" onerror="this.parentNode.innerHTML='<div class=\\'table-thumb-placeholder\\'><svg viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\'><rect x=\\'3\\' y=\\'3\\' width=\\'18\\' height=\\'18\\' rx=\\'2\\'/><circle cx=\\'8.5\\' cy=\\'8.5\\' r=\\'1.5\\'/><polyline points=\\'21 15 16 10 5 21\\'/></svg></div>'">`
                        : `<div class="table-thumb-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`}
                </td>
                <td>#${k.ordem != null ? k.ordem : k.id}</td>
                <td><strong>${escapeHtml(k.nome)}</strong></td>
                <td>
                    <span class="badge" style="background:rgba(255,255,255,0.06); color:var(--admin-text-main); border-left:3px solid var(--admin-border);">
                        ${escapeHtml(k.categoria || 'Geral')}
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
                        <button class="btn-action edit" onclick="event.stopPropagation(); editKata(${k.id})" title="Editar Kata">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-action delete" onclick="event.stopPropagation(); deleteKata(${k.id}, '${escapeHtml(k.nome)}')" title="Excluir Kata">
                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        initExpandableRows(tbody);
    }

    // --- FILTERS ---
    search.addEventListener('input', renderKatas);

    pillsBox.addEventListener('click', function(e) {
        const pill = e.target.closest('.pill-tab');
        if (!pill) return;
        pillsBox.querySelectorAll('.pill-tab').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        activeNivel = pill.dataset.nivel;
        renderKatas();
    });

    // --- MODAL OPEN (NEW) ---
    document.getElementById('btnOpenAddKata').addEventListener('click', () => {
        form.reset();
        document.getElementById('kataId').value = '0';
        document.getElementById('kataDescCounter').textContent = '0 / 1200';
        modalTitle.textContent = 'Cadastrar Novo Kata';
        hideVideoPreview();
        hideImagePreview();
        modal.classList.add('active');
        document.getElementById('kataNome').focus();
    });

    document.getElementById('btnCloseKataModal').addEventListener('click', closeModal);
    document.getElementById('btnCancelKataModal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });

    function closeModal() {
        modal.classList.remove('active');
        hideVideoPreview();
        hideImagePreview();
    }

    // --- VIDEO PREVIEW ---
    function hideVideoPreview() {
        document.getElementById('kataVideoPreview').classList.remove('visible');
        document.getElementById('kataVideoFrame').src = '';
    }

    document.getElementById('kataVideo').addEventListener('input', function() {
        const embedUrl = getYoutubeEmbedUrl(this.value.trim());
        const previewEl = document.getElementById('kataVideoPreview');
        if (embedUrl) {
            document.getElementById('kataVideoFrame').src = embedUrl;
            previewEl.classList.add('visible');
        } else {
            previewEl.classList.remove('visible');
            document.getElementById('kataVideoFrame').src = '';
        }
    });

    // --- IMAGE PREVIEW ---
    function hideImagePreview() {
        document.getElementById('kataImagePreview').classList.remove('visible');
        document.getElementById('kataImagePreviewImg').src = '';
    }

    document.getElementById('kataImagem').addEventListener('input', function() {
        const url = this.value.trim();
        const previewEl = document.getElementById('kataImagePreview');
        const img       = document.getElementById('kataImagePreviewImg');
        if (url) {
            img.src = url;
            img.onload  = () => previewEl.classList.add('visible');
            img.onerror = () => previewEl.classList.remove('visible');
        } else {
            previewEl.classList.remove('visible');
            img.src = '';
        }
    });

    // --- CHAR COUNTER ---
    document.getElementById('kataDescricao').addEventListener('input', function() {
        const len = this.value.length;
        const max = parseInt(this.getAttribute('maxlength'));
        const counter = document.getElementById('kataDescCounter');
        counter.textContent = `${len} / ${max}`;
        counter.className = 'char-counter' + (len >= max ? ' at-limit' : len >= max * 0.85 ? ' near-limit' : '');
    });

    // --- EDIT ---
    window.editKata = function(id) {
        const k = katasData.find(x => x.id == id);
        if (!k) return;

        document.getElementById('kataId').value       = k.id;
        document.getElementById('kataNome').value     = k.nome;
        document.getElementById('kataCategoria').value= k.categoria || '';
        document.getElementById('kataNivel').value    = k.nivel;
        document.getElementById('kataVideo').value    = k.video_url || '';
        document.getElementById('kataImagem').value   = k.imagem_url || '';
        document.getElementById('kataOrdem').value    = k.ordem != null ? k.ordem : 0;
        document.getElementById('kataDescricao').value= k.descricao || '';

        // Trigger char counter
        document.getElementById('kataDescricao').dispatchEvent(new Event('input'));

        // Trigger video preview
        document.getElementById('kataVideo').dispatchEvent(new Event('input'));

        // Trigger image preview
        document.getElementById('kataImagem').dispatchEvent(new Event('input'));

        modalTitle.textContent = `Editar: ${k.nome}`;
        modal.classList.add('active');
    };

    // --- SAVE (POST/PUT) ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = parseInt(document.getElementById('kataId').value, 10) || 0;
        const payload = {
            id:        id,
            nome:      document.getElementById('kataNome').value.trim(),
            categoria: document.getElementById('kataCategoria').value.trim(),
            nivel:     document.getElementById('kataNivel').value,
            video_url: document.getElementById('kataVideo').value.trim(),
            imagem_url:document.getElementById('kataImagem').value.trim(),
            ordem:     parseInt(document.getElementById('kataOrdem').value) || 0,
            descricao: document.getElementById('kataDescricao').value.trim()
        };

        if (!payload.nome || !payload.descricao) {
            showNotification('Nome e Descrição são obrigatórios.', 'warning');
            return;
        }

        const method = id > 0 ? 'PUT' : 'POST';
        const url    = 'api/katas.php' + (id > 0 ? '?id=' + id : '');

        btnSave.classList.add('btn-loading');
        btnSave.disabled = true;

        adminApi(url, method, payload)
            .then(res => {
                showNotification(res.message || 'Kata salvo com sucesso!', 'success');
                closeModal();
                loadKatas();
            })
            .catch(err => showNotification(err.message, 'error'))
            .finally(() => {
                btnSave.classList.remove('btn-loading');
                btnSave.disabled = false;
            });
    });

    // --- DELETE ---
    window.deleteKata = function(id, nome) {
        showDeleteConfirm(
            'Excluir Kata',
            `Tem certeza que deseja excluir o kata <strong>${escapeHtml(nome)}</strong>? Esta ação não pode ser desfeita.`,
            function() {
                adminApi('api/katas.php?id=' + id, 'DELETE')
                    .then(res => {
                        showNotification(res.message || 'Kata removido!', 'success');
                        loadKatas();
                    })
                    .catch(err => showNotification(err.message, 'error'));
            }
        );
    };

    loadKatas();
});
</script>

<?php require_once 'footer.php'; ?>
