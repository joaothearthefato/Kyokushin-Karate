<?php
/**
 * toast.php — Sistema centralizado de notificações Toast para o Oyama Hub
 *
 * Como usar em PHP (via parâmetro GET):
 *   Redirecionar com ?toast_type=sucesso&toast_msg=Operação+realizada
 *
 * Como usar em JS (para AJAX):
 *   OyamaToast.show('Operação realizada!', 'sucesso');
 *   OyamaToast.show('Erro ao salvar', 'erro');
 *   OyamaToast.show('Atenção: revise os dados', 'aviso');
 *   OyamaToast.show('Nova informação disponível', 'info');
 *
 * Incluir este arquivo uma vez por página (preferencialmente via include no final do body).
 */
?>

<!-- TOAST CONTAINER -->
<div id="oyama-toast-container" role="region" aria-label="Notificações" aria-live="polite" aria-atomic="false"></div>

<style>
/* ─── TOAST CONTAINER ───────────────────────────────── */
#oyama-toast-container {
    position: fixed;
    bottom: var(--space-6, 24px);
    right: var(--space-6, 24px);
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: var(--space-3, 12px);
    pointer-events: none;
}

/* ─── TOAST BASE ────────────────────────────────────── */
.oyama-toast {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    gap: var(--space-3, 12px);
    min-width: 300px;
    max-width: 420px;
    padding: var(--space-4, 16px);
    border: 1px solid var(--color-border, rgba(255, 255, 255, 0.08));
    border-left-width: 4px;
    border-radius: var(--radius-md, 6px);
    background: var(--color-surface, #17171b);
    color: var(--color-text, #e6e3df);
    font-family: var(--font-sans, 'Inter', system-ui, sans-serif);
    font-size: var(--text-sm, 13px);
    line-height: var(--leading-normal, 1.5);
    box-shadow: var(--shadow-lg, 0 12px 32px rgba(0, 0, 0, 0.45));
    pointer-events: all;
    cursor: pointer;
    opacity: 0;
    transform: translateY(12px) scale(0.98);
    transition: opacity var(--duration-base, 200ms) var(--ease-default, cubic-bezier(0.4, 0, 0.2, 1)),
                transform var(--duration-base, 200ms) var(--ease-spring, cubic-bezier(0.22, 1, 0.36, 1));
}

.oyama-toast.toast-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.oyama-toast.toast-hide {
    opacity: 0;
    transform: translateY(8px) scale(0.96);
}

/* ─── TIPOS ─────────────────────────────────────────── */
.oyama-toast.toast-sucesso  { border-left-color: var(--color-success, #16a34a); }
.oyama-toast.toast-sucesso .oyama-toast-icon { color: var(--color-success, #16a34a); }

.oyama-toast.toast-erro     { border-left-color: var(--color-accent, #c8000a); }
.oyama-toast.toast-erro .oyama-toast-icon { color: var(--color-accent, #c8000a); }

.oyama-toast.toast-aviso    { border-left-color: var(--color-warning, #d97706); }
.oyama-toast.toast-aviso .oyama-toast-icon { color: var(--color-warning, #d97706); }

.oyama-toast.toast-info     { border-left-color: var(--color-info, #2563eb); }
.oyama-toast.toast-info .oyama-toast-icon { color: var(--color-info, #2563eb); }

/* ─── ÍCONE ─────────────────────────────────────────── */
.oyama-toast-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.oyama-toast-icon svg {
    width: 18px;
    height: 18px;
}

/* ─── MENSAGEM ──────────────────────────────────────── */
.oyama-toast-msg {
    flex: 1;
    color: var(--color-text, #e6e3df);
    font-weight: var(--weight-medium, 500);
}

/* ─── BOTÃO FECHAR ──────────────────────────────────── */
.oyama-toast-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: var(--color-text-3, #6a6763);
    cursor: pointer;
    padding: 2px;
    margin-top: -2px;
    margin-right: -4px;
    border-radius: var(--radius-sm, 4px);
    transition: color var(--duration-fast, 150ms) var(--ease-default, cubic-bezier(0.4, 0, 0.2, 1));
}

.oyama-toast-close:hover {
    color: var(--color-text, #e6e3df);
}

.oyama-toast-close svg {
    width: 14px;
    height: 14px;
}

/* ─── BARRA DE PROGRESSO ────────────────────────────── */
.oyama-toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    background: currentColor;
    opacity: 0.35;
    width: 100%;
    transform-origin: left;
}

/* ─── LIGHT MODE ────────────────────────────────────── */
html.light .oyama-toast {
    background: var(--color-surface, #ffffff);
    color: var(--color-text, #1a1917);
    border-color: var(--color-border, rgba(0, 0, 0, 0.08));
    box-shadow: var(--shadow-lg, 0 12px 32px rgba(0, 0, 0, 0.12));
}

html.light .oyama-toast-msg {
    color: var(--color-text, #1a1917);
}

html.light .oyama-toast-close {
    color: var(--color-text-3, #8a8884);
}

html.light .oyama-toast-close:hover {
    color: var(--color-text, #1a1917);
}

/* ─── MOBILE ────────────────────────────────────────── */
@media (max-width: 500px) {
    #oyama-toast-container {
        bottom: var(--space-4, 16px);
        right: var(--space-4, 16px);
        left: var(--space-4, 16px);
    }
    .oyama-toast {
        min-width: unset;
        max-width: 100%;
    }
}
</style>

<script>
/**
 * OyamaToast — API pública de notificações toast.
 *
 * @param {string} mensagem - Texto a exibir
 * @param {string} tipo     - 'sucesso' | 'erro' | 'aviso' | 'info'
 * @param {number} duracao  - Duração em ms (padrão: 4000)
 */
const OyamaToast = (() => {
    const container = document.getElementById('oyama-toast-container');

    const iconesSvg = {
        sucesso: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
        erro:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        aviso:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    };

    function show(mensagem, tipo = 'info', duracao = 4000) {
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `oyama-toast toast-${tipo}`;
        toast.setAttribute('role', 'alert');

        const svgIcon = iconesSvg[tipo] || iconesSvg.info;

        toast.innerHTML = `
            <span class="oyama-toast-icon">${svgIcon}</span>
            <span class="oyama-toast-msg">${mensagem}</span>
            <button class="oyama-toast-close" aria-label="Fechar notificação">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="oyama-toast-progress"></div>
        `;

        const closeBtn = toast.querySelector('.oyama-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismiss(toast);
            });
        }

        // Fechar ao clicar no próprio toast também
        toast.addEventListener('click', () => dismiss(toast));

        container.appendChild(toast);

        // Animar entrada
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('toast-visible'));
        });

        // Animar barra de progresso
        const bar = toast.querySelector('.oyama-toast-progress');
        if (bar) {
            bar.style.transition = `transform ${duracao}ms linear`;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { bar.style.transform = 'scaleX(0)'; });
            });
        }

        // Auto-dismiss
        const timer = setTimeout(() => dismiss(toast), duracao);
        toast._timer = timer;
    }

    function dismiss(toast) {
        clearTimeout(toast._timer);
        toast.classList.remove('toast-visible');
        toast.classList.add('toast-hide');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }

    return { show, dismiss };
})();

// ─── Leitura de toasts vindos de redirecionamentos PHP ───────
<?php
$toastType = htmlspecialchars($_GET['toast_type'] ?? '', ENT_QUOTES, 'UTF-8');
$toastMsg  = htmlspecialchars($_GET['toast_msg']  ?? '', ENT_QUOTES, 'UTF-8');
if ($toastType && $toastMsg): ?>
document.addEventListener('DOMContentLoaded', () => {
    OyamaToast.show('<?= $toastMsg ?>', '<?= $toastType ?>');
});
<?php endif; ?>
</script>
