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
<div id="oyama-toast-container" role="alert" aria-live="polite" aria-atomic="false"></div>

<style>
/* ─── TOAST CONTAINER ───────────────────────────────── */
#oyama-toast-container {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
}

/* ─── TOAST BASE ────────────────────────────────────── */
.oyama-toast {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-width: 280px;
    max-width: 400px;
    padding: 14px 18px;
    border-left: 4px solid #c8000a;
    background: #1a1a1a;
    color: #f2ede8;
    font-family: 'Oswald', 'Barlow Condensed', sans-serif;
    font-size: 14px;
    letter-spacing: 0.5px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.55);
    pointer-events: all;
    cursor: pointer;
    opacity: 0;
    transform: translateX(30px);
    transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.22,1,0.36,1);
}

.oyama-toast.toast-visible {
    opacity: 1;
    transform: translateX(0);
}

.oyama-toast.toast-hide {
    opacity: 0;
    transform: translateX(40px);
}

/* ─── TIPOS ─────────────────────────────────────────── */
.oyama-toast.toast-sucesso  { border-left-color: #27ae60; }
.oyama-toast.toast-erro     { border-left-color: #c8000a; }
.oyama-toast.toast-aviso    { border-left-color: #f39c12; }
.oyama-toast.toast-info     { border-left-color: #2980b9; }

/* ─── ICONE ─────────────────────────────────────────── */
.oyama-toast-icon {
    font-size: 18px;
    flex-shrink: 0;
    line-height: 1.2;
}

/* ─── MENSAGEM ──────────────────────────────────────── */
.oyama-toast-msg {
    flex: 1;
    line-height: 1.5;
}

/* ─── BARRA DE PROGRESSO ────────────────────────────── */
.oyama-toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    background: rgba(255,255,255,0.25);
    width: 100%;
    transform-origin: left;
}

/* ─── LIGHT MODE ────────────────────────────────────── */
html.light .oyama-toast,
body.light-mode .oyama-toast {
    background: #ffffff;
    color: #1f1c1a;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

/* ─── MOBILE ────────────────────────────────────────── */
@media (max-width: 500px) {
    #oyama-toast-container {
        bottom: 16px;
        right: 16px;
        left: 16px;
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

    const icones = {
        sucesso: '✅',
        erro:    '❌',
        aviso:   '⚠️',
        info:    'ℹ️',
    };

    function show(mensagem, tipo = 'info', duracao = 4000) {
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `oyama-toast toast-${tipo}`;
        toast.setAttribute('role', 'alert');
        toast.style.position = 'relative';
        toast.innerHTML = `
            <span class="oyama-toast-icon">${icones[tipo] ?? 'ℹ️'}</span>
            <span class="oyama-toast-msg">${mensagem}</span>
            <div class="oyama-toast-progress"></div>
        `;

        // Fechar ao clicar
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
