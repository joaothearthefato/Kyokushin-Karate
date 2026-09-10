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
      ♿
    </button>

    <div id="a11y-panel"
      role="dialog"
      aria-modal="false"
      aria-label="Configurações de Acessibilidade"
      aria-hidden="true">

      <h3>Acessibilidade</h3>

      <div class="a11y-control-group">
        <span>Tamanho do Texto</span>
        <button class="a11y-btn" id="a11y-txt-normal">A — Normal (100%)</button>
        <button class="a11y-btn" id="a11y-txt-large">A+ — Grande (110%)</button>
        <button class="a11y-btn" id="a11y-txt-xl">A++ — Extra Grande (125%)</button>
      </div>

      <div class="a11y-control-group">
        <span>Visualização</span>
        <button class="a11y-btn" id="a11y-contrast">◑ Alto Contraste</button>
        <button class="a11y-btn" id="a11y-motion">⏸ Reduzir Animações</button>
        <button class="a11y-btn" id="a11y-links">🔗 Destacar Links</button>
        <button class="a11y-btn" id="a11y-spacing">↕ Espaçamento Extra</button>
      </div>

      <div class="a11y-control-group">
        <span>Libras</span>
        <button class="a11y-btn" id="a11y-vlibras">🤟 Abrir Tradutor VLibras</button>
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
  const panel     = document.getElementById('a11y-panel');
  const html      = document.documentElement;

  const btnTxtNormal = document.getElementById('a11y-txt-normal');
  const btnTxtLarge  = document.getElementById('a11y-txt-large');
  const btnTxtXl     = document.getElementById('a11y-txt-xl');
  const btnContrast  = document.getElementById('a11y-contrast');
  const btnMotion    = document.getElementById('a11y-motion');
  const btnLinks     = document.getElementById('a11y-links');
  const btnSpacing   = document.getElementById('a11y-spacing');
  const btnVlibras   = document.getElementById('a11y-vlibras');
  const btnReset     = document.getElementById('a11y-reset');


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
    motion:   false,
    links:    false,
    spacing:  false,
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

    // Contraste
    html.classList.toggle('a11y-high-contrast', state.contrast);
    btnContrast.classList.toggle('active', state.contrast);

    // Animações
    html.classList.toggle('a11y-reduced-motion', state.motion);
    btnMotion.classList.toggle('active', state.motion);

    // Links destacados
    html.classList.toggle('a11y-highlight-links', state.links);
    btnLinks.classList.toggle('active', state.links);

    // Espaçamento
    html.classList.toggle('a11y-extra-spacing', state.spacing);
    btnSpacing.classList.toggle('active', state.spacing);
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

  // Fechar ao clicar fora
  document.addEventListener('click', (e) => {
    if (!panel.contains(e.target) && e.target !== toggleBtn) {
      panel.classList.remove('open');
      toggleBtn.setAttribute('aria-expanded', 'false');
      panel.setAttribute('aria-hidden', 'true');
    }
  });

  // Fechar com Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && panel.classList.contains('open')) {
      panel.classList.remove('open');
      toggleBtn.setAttribute('aria-expanded', 'false');
      panel.setAttribute('aria-hidden', 'true');
      toggleBtn.focus();
    }
  });


  /* ─── 7. EVENT LISTENERS DOS BOTÕES ─── */
  btnTxtNormal.addEventListener('click', () => { state.textSize = 'normal'; saveAndApply(); });
  btnTxtLarge.addEventListener('click',  () => { state.textSize = 'large';  saveAndApply(); });
  btnTxtXl.addEventListener('click',    () => { state.textSize = 'xl';     saveAndApply(); });

  btnContrast.addEventListener('click', () => { state.contrast = !state.contrast; saveAndApply(); });
  btnMotion.addEventListener('click',   () => { state.motion   = !state.motion;   saveAndApply(); });
  btnLinks.addEventListener('click',    () => { state.links    = !state.links;    saveAndApply(); });
  btnSpacing.addEventListener('click',  () => { state.spacing  = !state.spacing;  saveAndApply(); });

  // VLibras — localiza o botão injetado pelo widget e simula um clique
  btnVlibras.addEventListener('click', () => {
    // O VLibras injeta .vw-access-button dentro do [vw] container
    const vwBtn = document.querySelector('[vw-access-button]') ||
                  document.querySelector('.vw-access-button');
    if (vwBtn) {
      vwBtn.click();
      // Fecha o painel de acessibilidade para não sobrepor o VLibras
      panel.classList.remove('open');
      toggleBtn.setAttribute('aria-expanded', 'false');
      panel.setAttribute('aria-hidden', 'true');
    } else {
      // VLibras não está disponível nesta página
      btnVlibras.textContent = '⚠️ VLibras indisponível aqui';
      setTimeout(() => { btnVlibras.textContent = '🤟 Abrir Tradutor VLibras'; }, 2500);
    }
  });

  btnReset.addEventListener('click', () => {
    state = { textSize: 'normal', contrast: false, motion: false, links: false, spacing: false };
    saveAndApply();
  });


  /* ─── 8. INICIALIZAR ─── */
  applyState();

})();
