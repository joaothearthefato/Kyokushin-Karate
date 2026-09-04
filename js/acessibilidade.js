/**
 * ACESSIBILIDADE - WIDGET E LÓGICA (WCAG 2.2)
 * Injeta o painel flutuante e gerencia o LocalStorage
 */

(function initA11y() {
    // 1. Injetar o CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    // Caminho base adaptável se estiver numa subpasta (ex: /php/)
    const basePath = window.location.pathname.includes('/php/') || window.location.pathname.includes('/dashboard/') ? '../' : './';
    link.href = basePath + 'css/acessibilidade.css';
    document.head.appendChild(link);
  
    // 2. Injetar o HTML do Painel
    const panelHTML = `
      <button id="a11y-toggle-btn" aria-label="Abrir Painel de Acessibilidade" aria-expanded="false">
        ♿
      </button>
  
      <div id="a11y-panel" aria-hidden="true" role="dialog" aria-label="Configurações de Acessibilidade">
        <h3>ACESSIBILIDADE</h3>
        
        <div class="a11y-control-group">
          <span>Tamanho do Texto</span>
          <button class="a11y-btn" id="a11y-txt-normal">A Normal (100%)</button>
          <button class="a11y-btn" id="a11y-txt-large">A+ Grande (110%)</button>
          <button class="a11y-btn" id="a11y-txt-xl">A++ Extra Grande (125%)</button>
        </div>
  
        <div class="a11y-control-group">
          <span>Visualização</span>
          <button class="a11y-btn" id="a11y-contrast">◑ Alto Contraste</button>
          <button class="a11y-btn" id="a11y-motion">⏸ Reduzir Animações</button>
        </div>
  
        <div class="a11y-control-group">
          <span>Leitura (TTS)</span>
          <button class="a11y-btn" id="a11y-read">🔊 Ler Conteúdo Principal</button>
        </div>
  
        <button class="a11y-btn" id="a11y-reset">↺ Restaurar Padrões</button>
      </div>
    `;
  
    const wrapper = document.createElement('div');
    wrapper.innerHTML = panelHTML;
    document.body.appendChild(wrapper);
  
    // 3. Referências DOM
    const toggleBtn = document.getElementById('a11y-toggle-btn');
    const panel = document.getElementById('a11y-panel');
    const html = document.documentElement;
  
    // Botões
    const btnTxtNormal = document.getElementById('a11y-txt-normal');
    const btnTxtLarge = document.getElementById('a11y-txt-large');
    const btnTxtXl = document.getElementById('a11y-txt-xl');
    const btnContrast = document.getElementById('a11y-contrast');
    const btnMotion = document.getElementById('a11y-motion');
    const btnRead = document.getElementById('a11y-read');
    const btnReset = document.getElementById('a11y-reset');
  
    // 4. Lógica de Estado (LocalStorage)
    const state = {
      textSize: localStorage.getItem('a11y-textSize') || 'normal', // normal, large, xl
      contrast: localStorage.getItem('a11y-contrast') === 'true',
      motion: localStorage.getItem('a11y-motion') === 'true'
    };
  
    function applyState() {
      // Limpa classes
      html.classList.remove('a11y-text-large', 'a11y-text-xl', 'a11y-high-contrast', 'a11y-reduced-motion');
      
      // Limpa active dos botões de texto
      [btnTxtNormal, btnTxtLarge, btnTxtXl].forEach(b => b.classList.remove('active'));
  
      // Aplica Texto
      if (state.textSize === 'large') {
        html.classList.add('a11y-text-large');
        btnTxtLarge.classList.add('active');
      } else if (state.textSize === 'xl') {
        html.classList.add('a11y-text-xl');
        btnTxtXl.classList.add('active');
      } else {
        btnTxtNormal.classList.add('active');
      }
  
      // Aplica Contraste
      if (state.contrast) {
        html.classList.add('a11y-high-contrast');
        btnContrast.classList.add('active');
      } else {
        btnContrast.classList.remove('active');
      }
  
      // Aplica Motion
      if (state.motion) {
        html.classList.add('a11y-reduced-motion');
        btnMotion.classList.add('active');
      } else {
        btnMotion.classList.remove('active');
      }
    }
  
    // 5. Event Listeners
    toggleBtn.addEventListener('click', () => {
      const isOpen = panel.classList.contains('open');
      panel.classList.toggle('open');
      toggleBtn.setAttribute('aria-expanded', !isOpen);
      panel.setAttribute('aria-hidden', isOpen);
    });
  
    // Fechar ao clicar fora
    document.addEventListener('click', (e) => {
      if (!panel.contains(e.target) && e.target !== toggleBtn) {
        panel.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
      }
    });
  
    // Tamanho do Texto
    btnTxtNormal.addEventListener('click', () => { state.textSize = 'normal'; saveAndApply(); });
    btnTxtLarge.addEventListener('click', () => { state.textSize = 'large'; saveAndApply(); });
    btnTxtXl.addEventListener('click', () => { state.textSize = 'xl'; saveAndApply(); });
  
    // Toggles
    btnContrast.addEventListener('click', () => { state.contrast = !state.contrast; saveAndApply(); });
    btnMotion.addEventListener('click', () => { state.motion = !state.motion; saveAndApply(); });
  
    // Reset
    btnReset.addEventListener('click', () => {
      state.textSize = 'normal';
      state.contrast = false;
      state.motion = false;
      saveAndApply();
      if(window.speechSynthesis) window.speechSynthesis.cancel();
    });
  
    function saveAndApply() {
      localStorage.setItem('a11y-textSize', state.textSize);
      localStorage.setItem('a11y-contrast', state.contrast);
      localStorage.setItem('a11y-motion', state.motion);
      applyState();
    }
  
    // 6. TTS (Text to Speech)
    let isReading = false;
    btnRead.addEventListener('click', () => {
      if (!window.speechSynthesis) {
        alert("Seu navegador não suporta leitura por voz.");
        return;
      }
  
      if (isReading) {
        window.speechSynthesis.cancel();
        btnRead.innerHTML = "🔊 Ler Conteúdo Principal";
        btnRead.classList.remove('active');
        isReading = false;
        return;
      }
  
      // Procura tag <main> primeiro, senão pega body
      const mainContent = document.querySelector('main') || document.body;
      // Pega texto visível básico ignorando scripts e estilos
      const texto = mainContent.innerText || mainContent.textContent;
  
      const fala = new SpeechSynthesisUtterance(texto);
      fala.lang = 'pt-BR';
      fala.rate = 1.0;
  
      fala.onend = () => {
        btnRead.innerHTML = "🔊 Ler Conteúdo Principal";
        btnRead.classList.remove('active');
        isReading = false;
      };
  
      window.speechSynthesis.speak(fala);
      btnRead.innerHTML = "⏹ Parar Leitura";
      btnRead.classList.add('active');
      isReading = true;
    });
  
    // Init
    applyState();
  
  })();
