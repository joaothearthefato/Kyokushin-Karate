/**
 * OYAMA HUB — Universal AppModal & Toast System
 * Substitui alerts e confirms nativos por modais modernos Kyokushin.
 */
window.AppModal = (function () {
  let modalOverlay = null;
  let toastContainer = null;
  let lastFocusedElement = null;
  let modalKeydownHandler = null;

  function openModal(initialFocus) {
    lastFocusedElement = document.activeElement;
    modalOverlay.classList.add('open');
    modalOverlay.setAttribute('aria-hidden', 'false');
    modalKeydownHandler = (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        const cancel = document.getElementById('btnAppModalCancel') || document.getElementById('btnAppModalOk');
        cancel?.click();
        return;
      }
      if (event.key !== 'Tab') return;
      const focusable = modalOverlay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };
    document.addEventListener('keydown', modalKeydownHandler);
    initialFocus?.focus();
  }

  function closeModal() {
    modalOverlay.classList.remove('open');
    modalOverlay.setAttribute('aria-hidden', 'true');
    if (modalKeydownHandler) document.removeEventListener('keydown', modalKeydownHandler);
    modalKeydownHandler = null;
    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') lastFocusedElement.focus();
    lastFocusedElement = null;
  }

  function ensureElements() {
    if (!modalOverlay) {
      modalOverlay = document.createElement('div');
      modalOverlay.className = 'app-modal-overlay';
      modalOverlay.id = 'universalAppModal';
      modalOverlay.setAttribute('aria-hidden', 'true');
      modalOverlay.innerHTML = `
          <div class="app-modal-box" role="dialog" aria-modal="true" aria-labelledby="appModalTitle" aria-describedby="appModalText">
          <div class="app-modal-icon-wrap" id="appModalIcon"></div>
          <h3 class="app-modal-title" id="appModalTitle">Aviso</h3>
          <p class="app-modal-text" id="appModalText"></p>
          <div class="app-modal-actions" id="appModalActions"></div>
        </div>
      `;
      document.body.appendChild(modalOverlay);
    }

    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'app-toast-container';
      document.body.appendChild(toastContainer);
    }
  }

  function getIconSvg(type) {
    switch (type) {
      case 'success':
        return '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none"><polyline points="20 6 9 17 4 12"/></svg>';
      case 'error':
        return '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
      case 'info':
        return '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
      case 'warning':
      default:
        return '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
    }
  }

  function alert(opts) {
    ensureElements();
    return new Promise((resolve) => {
      const options = typeof opts === 'string' ? { message: opts } : (opts || {});
      const title = options.title || 'Aviso';
      const message = options.message || '';
      const type = options.type || options.icon || 'warning';
      const buttonText = options.buttonText || 'Entendido';

      const iconWrap = document.getElementById('appModalIcon');
      const titleEl = document.getElementById('appModalTitle');
      const textEl = document.getElementById('appModalText');
      const actionsEl = document.getElementById('appModalActions');

      iconWrap.className = 'app-modal-icon-wrap ' + type;
      iconWrap.innerHTML = getIconSvg(type);
      titleEl.textContent = title;
      textEl.textContent = message;

      actionsEl.innerHTML = `<button type="button" class="btn-app-modal-primary" id="btnAppModalOk">${buttonText}</button>`;

      const okBtn = document.getElementById('btnAppModalOk');
      const close = () => {
        closeModal();
        okBtn.removeEventListener('click', close);
        resolve(true);
      };

      okBtn.addEventListener('click', close);
      openModal(okBtn);
    });
  }

  function confirm(opts) {
    ensureElements();
    return new Promise((resolve) => {
      const options = typeof opts === 'string' ? { message: opts } : (opts || {});
      const title = options.title || 'Confirmação';
      const message = options.message || '';
      const type = options.type || options.icon || 'warning';
      const confirmText = options.confirmText || 'Confirmar';
      const cancelText = options.cancelText || 'Cancelar';

      const iconWrap = document.getElementById('appModalIcon');
      const titleEl = document.getElementById('appModalTitle');
      const textEl = document.getElementById('appModalText');
      const actionsEl = document.getElementById('appModalActions');

      iconWrap.className = 'app-modal-icon-wrap ' + type;
      iconWrap.innerHTML = getIconSvg(type);
      titleEl.textContent = title;
      textEl.textContent = message;

      actionsEl.innerHTML = `
        <button type="button" class="btn-app-modal-cancel" id="btnAppModalCancel">${cancelText}</button>
        <button type="button" class="btn-app-modal-primary" id="btnAppModalConfirm">${confirmText}</button>
      `;

      const confirmBtn = document.getElementById('btnAppModalConfirm');
      const cancelBtn = document.getElementById('btnAppModalCancel');

      const onConfirm = () => {
        cleanup();
        resolve(true);
      };

      const onCancel = () => {
        cleanup();
        resolve(false);
      };

      const cleanup = () => {
        closeModal();
        confirmBtn.removeEventListener('click', onConfirm);
        cancelBtn.removeEventListener('click', onCancel);
      };

      confirmBtn.addEventListener('click', onConfirm);
      cancelBtn.addEventListener('click', onCancel);
      openModal(confirmBtn);
    });
  }

  function toast(opts) {
    ensureElements();
    const options = typeof opts === 'string' ? { message: opts } : (opts || {});
    const message = options.message || '';
    const type = options.type || 'success';
    const duration = options.duration || 3500;

    const t = document.createElement('div');
    t.className = `app-toast ${type}`;
    t.innerHTML = `
      <div class="app-toast-icon">${getIconSvg(type)}</div>
      <span></span>
    `;
    t.querySelector('span').textContent = String(message);

    toastContainer.appendChild(t);

    // Animar entrada
    requestAnimationFrame(() => {
      t.classList.add('visible');
    });

    // Saída automática
    setTimeout(() => {
      t.classList.remove('visible');
      setTimeout(() => {
        if (t.parentNode) t.parentNode.removeChild(t);
      }, 350);
    }, duration);
  }

  return {
    alert,
    confirm,
    toast,
  };
})();
