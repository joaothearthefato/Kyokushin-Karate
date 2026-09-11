/**
 * ACESSIBILIDADE.JS — Painel Flutuante de Acessibilidade (WCAG 2.2)
 * Injeção dinâmica de CSS + HTML do painel + lógica de localStorage
 *
 * Funcionalidades:
 *  1. Aumentar / diminuir / restaurar tamanho do texto
 *  2. Alto contraste
 *  3. Reduzir animações
 *  4. Leitura por voz (TTS nativo)
 *  5. Escala de cursor
 *  6. Destacar links
 *  7. Espaçamento de linhas aumentado
 */

(function initA11y() {

  /* ─── 1. INJETAR CSS ─── */
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  // Detecta se está numa subpasta (ex: /php/, /php/admin/)
  const depth = (window.location.pathname.match(/\//g) || []).length - 1;
  const basePath = depth >= 2 ? '../'.repeat(depth - 1) : './';
  link.href = basePath + 'css/acessibilidade.css';
  document.head.appendChild(link);


  /* ─── 2. INJETAR HTML DO PAINEL ─── */
  const panelHTML = `
    <button id="a11y-toggle-btn"
      aria-label="Abrir Painel de Acessibilidade"
      aria-expanded="false"
      aria-controls="a11y-panel"
      title="Acessibilidade">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="4" r="2"/><path d="M5 8h14M12 8v11M8 21l4-6 4 6M8 8l-2 6M16 8l2 6"/></svg>
      <span class="a11y-toggle-label">A11Y</span>
    </button>

    <div id="a11y-panel"
      role="dialog"
      aria-modal="false"
      aria-label="Configurações de Acessibilidade"
      aria-hidden="true">

      <div class="a11y-panel-header">
        <div>
          <span class="a11y-panel-kicker">OYAMA HUB</span>
          <h3>Acessibilidade</h3>
        </div>
        <button class="a11y-panel-close" id="a11y-close" type="button" aria-label="Fechar painel">&times;</button>
      </div>

      <div class="a11y-control-group">
        <span>Tamanho do Texto</span>
        <button class="a11y-btn" id="a11y-txt-normal" aria-pressed="false">A — Normal (100%)</button>
        <button class="a11y-btn" id="a11y-txt-large" aria-pressed="false">A+ — Grande (110%)</button>
        <button class="a11y-btn" id="a11y-txt-xl" aria-pressed="false">A++ — Extra Grande (125%)</button>
      </div>

      <div class="a11y-control-group">
        <span>Visualização</span>
        <button class="a11y-btn" id="a11y-contrast" aria-pressed="false">◑ Alto Contraste</button>
        <button class="a11y-btn" id="a11y-motion" aria-pressed="false">⏸ Reduzir Animações</button>
        <button class="a11y-btn" id="a11y-links" aria-pressed="false">🔗 Destacar Links</button>
        <button class="a11y-btn" id="a11y-spacing" aria-pressed="false">↕ Espaçamento Extra</button>
      </div>

      <div class="a11y-control-group">
        <span>Leitura</span>
        <button class="a11y-btn a11y-btn-feature" id="a11y-speech">◉ <span>Ler conteúdo da página</span></button>
      </div>

      <button class="a11y-btn" id="a11y-reset">↺ Restaurar Padrões</button>
    </div>
  `;

  const wrapper = document.createElement('div');
  wrapper.id = 'a11y-root';
  wrapper.innerHTML = panelHTML;
  document.body.appendChild(wrapper);


  /* ─── 3. REFERÊNCIAS DOM ─── */
  const toggleBtn = document.getElementById('a11y-toggle-btn');
  const panel = document.getElementById('a11y-panel');
  const html = document.documentElement;

  const btnTxtNormal = document.getElementById('a11y-txt-normal');
  const btnTxtLarge = document.getElementById('a11y-txt-large');
  const btnTxtXl = document.getElementById('a11y-txt-xl');
  const btnContrast = document.getElementById('a11y-contrast');
  const btnMotion = document.getElementById('a11y-motion');
  const btnLinks = document.getElementById('a11y-links');
  const btnSpacing = document.getElementById('a11y-spacing');
  const btnSpeech = document.getElementById('a11y-speech');
  const btnReset = document.getElementById('a11y-reset');
  const btnClose = document.getElementById('a11y-close');


  /* ─── 4. ESTADO (localStorageç) ─── */
  const STORE_KEY = 'oyama-a11y';

  function loadState() {
    try {
      return JSON.parse(localStorage.getItem(STORE_KEY)) || {};
    } catch { return {}; }
  }

  function saveState(state) {
    localStorage.setItem(STORE_KEY, JSON.stringify(state));
  }

  let state = {
    textSize: 'normal',    // 'normal' | 'large' | 'xl'
    contrast: false,
    motion: false,
    links: false,
    spacing: false,
    ...loadState()
  };


  /* ─── 5. APLICAR ESTADO ─── */
  function applyState() {
    // Remove todas as classes de a11y antes de reaplicar
    html.classList.remove(
      'a11y-text-large', 'a11y-text-xl',
      'a11y-high-contrast',
      'a11y-reduced-motion',
      'a11y-highlight-links',
      'a11y-extra-spacing'
    );

    // Limpa active dos botões de texto
    [btnTxtNormal, btnTxtLarge, btnTxtXl].forEach(b => b.classList.remove('active'));

    // Texto
    if (state.textSize === 'large') {
      html.classList.add('a11y-text-large');
      btnTxtLarge.classList.add('active');
    } else if (state.textSize === 'xl') {
      html.classList.add('a11y-text-xl');
      btnTxtXl.classList.add('active');
    } else {
      btnTxtNormal.classList.add('active');
    }

    [btnTxtNormal, btnTxtLarge, btnTxtXl].forEach(button => {
      button.setAttribute('aria-pressed', String(button.classList.contains('active')));
    });

    // Contraste
    html.classList.toggle('a11y-high-contrast', state.contrast);
    btnContrast.classList.toggle('active', state.contrast);
    btnContrast.setAttribute('aria-pressed', String(state.contrast));

    // Animações
    html.classList.toggle('a11y-reduced-motion', state.motion);
    btnMotion.classList.toggle('active', state.motion);
    btnMotion.setAttribute('aria-pressed', String(state.motion));

    // Links destacados
    html.classList.toggle('a11y-highlight-links', state.links);
    btnLinks.classList.toggle('active', state.links);
    btnLinks.setAttribute('aria-pressed', String(state.links));

    // Espaçamento
    html.classList.toggle('a11y-extra-spacing', state.spacing);
    btnSpacing.classList.toggle('active', state.spacing);
    btnSpacing.setAttribute('aria-pressed', String(state.spacing));
  }

  function saveAndApply() {
    saveState(state);
    applyState();
  }


  /* ─── 6. ABRIR / FECHAR PAINEL ─── */
  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = panel.classList.contains('open');
    panel.classList.toggle('open', !isOpen);
    toggleBtn.setAttribute('aria-expanded', String(!isOpen));
    panel.setAttribute('aria-hidden', String(isOpen));

    if (!isOpen) {
      // Foco no primeiro botão ao abrir
      setTimeout(() => btnTxtNormal.focus(), 50);
    }
  });

  function closePanel(restoreFocus = false) {
    panel.classList.remove('open');
    toggleBtn.setAttribute('aria-expanded', 'false');
    panel.setAttribute('aria-hidden', 'true');
    if (restoreFocus) toggleBtn.focus();
  }

  btnClose.addEventListener('click', () => closePanel(true));

  // Fechar ao clicar fora
  document.addEventListener('click', (e) => {
    if (!panel.contains(e.target) && e.target !== toggleBtn) {
      closePanel();
    }
  });

  // Fechar com Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && panel.classList.contains('open')) {
      closePanel(true);
    }
  });


  /* ─── 7. EVENT LISTENERS DOS BOTÕES ─── */
  btnTxtNormal.addEventListener('click', () => { state.textSize = 'normal'; saveAndApply(); });
  btnTxtLarge.addEventListener('click', () => { state.textSize = 'large'; saveAndApply(); });
  btnTxtXl.addEventListener('click', () => { state.textSize = 'xl'; saveAndApply(); });

  btnContrast.addEventListener('click', () => { state.contrast = !state.contrast; saveAndApply(); });
  btnMotion.addEventListener('click', () => { state.motion = !state.motion; saveAndApply(); });
  btnLinks.addEventListener('click', () => { state.links = !state.links; saveAndApply(); });
  btnSpacing.addEventListener('click', () => { state.spacing = !state.spacing; saveAndApply(); });

  btnSpeech.addEventListener('click', () => {
    if (!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const content = document.querySelector('main')?.innerText || document.body.innerText;
    const utterance = new SpeechSynthesisUtterance(content.slice(0, 5000));
    utterance.lang = 'pt-BR';
    utterance.rate = 0.95;
    window.speechSynthesis.speak(utterance);
    closePanel();
  });

  btnReset.addEventListener('click', () => {
    state = { textSize: 'normal', contrast: false, motion: false, links: false, spacing: false };
    saveAndApply();
  });


  /* ─── 8. INICIALIZAR ─── */
  applyState();

})();
