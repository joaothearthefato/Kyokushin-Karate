        </div> <!-- /.admin-content -->
    </main> <!-- /.admin-main -->
</div> <!-- /.admin-layout -->

<!-- GLOBAL DELETE CONFIRM MODAL -->
<div class="delete-confirm-overlay" id="globalDeleteConfirm">
    <div class="delete-confirm-box">
        <div class="delete-confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
        </div>
        <div class="delete-confirm-body">
            <h4 id="deleteConfirmTitle">Confirmar Exclusão</h4>
            <p id="deleteConfirmMessage">Esta ação não pode ser desfeita.</p>
        </div>
        <div class="delete-confirm-footer">
            <button class="btn-cancel-delete" id="deleteConfirmCancel">Cancelar</button>
            <button class="btn-confirm-delete" id="deleteConfirmOk">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Excluir
            </button>
        </div>
    </div>
</div>

<script>
    // =========================================================================
    // THEME TOGGLE
    // =========================================================================
    document.getElementById('themeToggleBtn')?.addEventListener('click', function() {
        const isLight = document.documentElement.classList.toggle('light-mode');
        localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    });

    // =========================================================================
    // ADMIN API — Centralized REST fetch helper
    // =========================================================================
    async function adminApi(url, method = 'GET', payload = null) {
        const options = { method: method, headers: { 'Accept': 'application/json' } };

        if (payload !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        let res;
        try {
            res = await fetch(url, options);
        } catch (err) {
            throw new Error('Falha de conexão com o servidor. Verifique se o Apache/MySQL está ativo.');
        }

        const text = await res.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : null;
        } catch (err) {
            data = null;
        }

        if (!data) {
            if (res.status === 401 || res.status === 403) {
                throw new Error('Sessão expirada ou sem permissão de administrador. Faça login novamente.');
            }
            throw new Error(`Resposta inválida do servidor (HTTP ${res.status}). Verifique o log de erros do PHP.`);
        }

        if (!res.ok || data.success === false) {
            throw new Error(data.error || `Erro HTTP ${res.status}`);
        }

        return data;
    }

    // =========================================================================
    // TOAST NOTIFICATION — Premium version with icon + progress bar
    // =========================================================================
    const TOAST_ICONS = {
        success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
        error:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
        warning: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>`
    };

    function showNotification(msg, type = 'success', duration = 3800) {
        const toast = document.createElement('div');
        toast.className = `admin-toast admin-toast-${type}`;
        toast.style.setProperty('--toast-duration', duration + 'ms');

        toast.innerHTML = `
            <div class="admin-toast-inner">
                <div class="admin-toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.success}</div>
                <span class="admin-toast-msg">${msg}</span>
                <button class="admin-toast-close" title="Fechar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="admin-toast-progress"></div>
        `;

        document.body.appendChild(toast);

        // Stack multiple toasts
        const existing = document.querySelectorAll('.admin-toast');
        let offset = 28;
        existing.forEach(t => {
            if (t !== toast) {
                offset += t.offsetHeight + 10;
            }
        });
        toast.style.bottom = offset + 'px';

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        const close = () => {
            toast.classList.add('hide');
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        };

        toast.querySelector('.admin-toast-close').addEventListener('click', close);
        setTimeout(close, duration);
    }

    // =========================================================================
    // DELETE CONFIRM MODAL — Global custom confirmation dialog
    // =========================================================================
    (function() {
        const overlay  = document.getElementById('globalDeleteConfirm');
        const titleEl  = document.getElementById('deleteConfirmTitle');
        const msgEl    = document.getElementById('deleteConfirmMessage');
        const btnOk    = document.getElementById('deleteConfirmOk');
        const btnCancel= document.getElementById('deleteConfirmCancel');

        let _callback = null;

        window.showDeleteConfirm = function(title, message, callback) {
            titleEl.textContent = title || 'Confirmar Exclusão';
            msgEl.innerHTML = message || 'Esta ação <strong>não pode ser desfeita</strong>.';
            _callback = callback;
            overlay.classList.add('active');
            btnCancel.focus();
        };

        function closeConfirm() {
            overlay.classList.remove('active');
            _callback = null;
        }

        btnOk.addEventListener('click', function() {
            if (typeof _callback === 'function') {
                _callback();
            }
            closeConfirm();
        });

        btnCancel.addEventListener('click', closeConfirm);

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeConfirm();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeConfirm();
            }
        });
    })();

    // =========================================================================
    // SKELETON LOADING — Render placeholder rows while API loads
    // =========================================================================
    function renderSkeleton(tbody, cols, rows = 5) {
        const widths = ['short', 'medium', 'long', 'medium', 'short', 'medium', 'long'];
        let html = '';
        for (let r = 0; r < rows; r++) {
            html += '<tr class="skeleton-row">';
            for (let c = 0; c < cols; c++) {
                const w = widths[(r + c) % widths.length];
                html += `<td><div class="skeleton-cell ${w}"></div></td>`;
            }
            html += '</tr>';
        }
        tbody.innerHTML = html;
    }

    // =========================================================================
    // YOUTUBE URL HELPER — Extract video ID and build embed URL
    // =========================================================================
    function getYoutubeEmbedUrl(url) {
        if (!url) return null;
        let videoId = null;
        try {
            const u = new URL(url);
            if (u.hostname.includes('youtu.be')) {
                videoId = u.pathname.slice(1);
            } else if (u.hostname.includes('youtube.com')) {
                videoId = u.searchParams.get('v');
            }
        } catch (e) {
            const match = url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (match) videoId = match[1];
        }
        if (!videoId) return null;
        return `https://www.youtube-nocookie.com/embed/${videoId}?rel=0&modestbranding=1`;
    }

    // =========================================================================
    // EXPANDABLE ROWS — Toggle description detail row
    // =========================================================================
    function initExpandableRows(tbody) {
        tbody.addEventListener('click', function(e) {
            const row = e.target.closest('tr.expandable-row');
            if (!row) return;
            // Don't expand if clicking action buttons
            if (e.target.closest('.btn-action') || e.target.closest('a')) return;

            const isExpanded = row.classList.contains('expanded');

            // Collapse all others
            tbody.querySelectorAll('tr.expandable-row.expanded').forEach(r => {
                if (r !== row) {
                    r.classList.remove('expanded');
                    const sibling = r.nextElementSibling;
                    if (sibling && sibling.classList.contains('detail-row')) {
                        sibling.remove();
                    }
                }
            });

            if (isExpanded) {
                row.classList.remove('expanded');
                const next = row.nextElementSibling;
                if (next && next.classList.contains('detail-row')) next.remove();
            } else {
                row.classList.add('expanded');
                const detailHtml = row.dataset.detail || '';
                const videoUrl = row.dataset.video || '';
                const detailRow = document.createElement('tr');
                detailRow.className = 'detail-row';

                let videoSection = '';
                if (videoUrl) {
                    videoSection = `<a href="${videoUrl}" target="_blank" class="detail-video-link">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Ver Vídeo
                    </a>`;
                }

                detailRow.innerHTML = `<td colspan="100">
                    <div class="detail-inner">
                        <div>
                            <div class="detail-label">Descrição Técnica</div>
                            <div class="detail-text">${detailHtml || '<em>Sem descrição cadastrada.</em>'}</div>
                        </div>
                        ${videoSection ? `<div>${videoSection}</div>` : ''}
                    </div>
                </td>`;

                row.insertAdjacentElement('afterend', detailRow);
            }
        });
    }

    // =========================================================================
    // TIPO BADGE CSS CLASS — Map exercise type to CSS class
    // =========================================================================
    function getTipoBadgeClass(tipo) {
        const map = {
            'Força': 'badge-tipo-forca',
            'Resistência': 'badge-tipo-resistencia',
            'Técnica': 'badge-tipo-tecnica',
            'Mobilidade': 'badge-tipo-mobilidade',
            'Soco': 'badge-tipo-soco',
            'Chute': 'badge-tipo-chute',
            'Defesa': 'badge-tipo-defesa',
            'Cotovelada': 'badge-tipo-cotovelada',
            'Joelhada': 'badge-tipo-joelhada'
        };
        return map[tipo] || 'badge-intermediario';
    }

    // =========================================================================
    // ESCAPE HTML — Security helper (globally available)
    // =========================================================================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
</body>
</html>
