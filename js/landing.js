/**
 * landing.js — Interatividade da Landing Page do Oyama Hub
 * Inclui:
 * 1. Filtro dinâmico de Técnicas (Tabs com transição suave)
 * 2. Explorador Interativo de Faixas do Kyokushin (com dados reais de graduação)
 * 3. Módulo Interativo do Dojo Kun (Os 7 Preceitos de Mas Oyama)
 * 4. Scrollspy na Navbar para destacar seção ativa
 * 5. Menu Hamburger para dispositivos móveis
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ─────────────────────────────────────────────────────────────
     1. MENU HAMBURGER MOBILE
     ───────────────────────────────────────────────────────────── */
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const navLinks = document.getElementById('navLinks');

  if (hamburgerBtn && navLinks) {
    hamburgerBtn.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('nav-open');
      hamburgerBtn.classList.toggle('is-open', isOpen);
      hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
    });

    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('nav-open');
        hamburgerBtn.classList.remove('is-open');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('click', e => {
      if (!hamburgerBtn.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('nav-open');
        hamburgerBtn.classList.remove('is-open');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }


  /* ─────────────────────────────────────────────────────────────
     2. NAVBAR SCROLLSPY
     ───────────────────────────────────────────────────────────── */
  const sections = document.querySelectorAll('section[id]');
  const navItems = document.querySelectorAll('.nav-links .nav-link');

  function updateScrollspy() {
    const scrollY = window.scrollY + 120;

    sections.forEach(current => {
      const sectionHeight = current.offsetHeight;
      const sectionTop = current.offsetTop;
      const sectionId = current.getAttribute('id');

      if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
        navItems.forEach(item => {
          item.classList.remove('active');
          if (item.getAttribute('href') === `#${sectionId}`) {
            item.classList.add('active');
          }
        });
      }
    });
  }

  window.addEventListener('scroll', updateScrollspy, { passive: true });
  updateScrollspy();


  /* ─────────────────────────────────────────────────────────────
     3. FILTRO DE TÉCNICAS (TABS)
     ───────────────────────────────────────────────────────────── */
  const techFilterBtns = document.querySelectorAll('.tech-filter-btn');
  const techCards = document.querySelectorAll('.technique-card');

  techFilterBtns.forEach((btn, index) => {
    btn.addEventListener('keydown', event => {
      if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % techFilterBtns.length;
      if (event.key === 'ArrowLeft') next = (index - 1 + techFilterBtns.length) % techFilterBtns.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = techFilterBtns.length - 1;
      techFilterBtns[next].focus();
    });
  });

  techFilterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      techFilterBtns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');

      const category = btn.dataset.category;

      techCards.forEach(card => {
        const cardCat = card.dataset.cat;
        if (category === 'todos' || cardCat === category) {
          card.style.display = 'flex';
          requestAnimationFrame(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0) scale(1)';
          });
        } else {
          card.style.opacity = '0';
          card.style.transform = 'translateY(10px) scale(0.98)';
          setTimeout(() => {
            if (btn.dataset.category !== 'todos' && card.dataset.cat !== btn.dataset.category) {
              card.style.display = 'none';
            }
          }, 200);
        }
      });
    });
  });


  /* ─────────────────────────────────────────────────────────────
     4. EXPLORADOR INTERATIVO DE FAIXAS (BELT EXPLORER)
     ───────────────────────────────────────────────────────────── */
  const beltData = {
    branca: {
      title: 'Faixa Branca (白帯)',
      kyu: '11º Kyu (Mu-Kyu)',
      element: 'Base: começo e adaptação',
      philosophy: 'É o começo da prática. O aluno aprende a etiqueta do dojo, a postura de respeito (Rei), a base dos deslocamentos e a forma correta de executar os movimentos.',
      katas: 'Taikyoku Sono Ichi, Taikyoku Sono Ni',
      time: '3 a 6 meses de treino constante',
      focus: 'Posturas fundamentais (Zenkutsu Dachi), socos diretos (Seiken Tsuki) e chutes frontais (Mae Geri).',
      conditioning: 'Mobilidade básica, fortalecimento geral e condicionamento gradual.',
      cssClass: 'belt-branca',
      stripes: 0
    },
    laranja: {
      title: 'Faixa Laranja (橙帯)',
      kyu: '10º e 9º Kyu',
      element: 'Base: equilíbrio e estabilidade',
      philosophy: 'Nesta etapa, o aluno consolida a base, aprende a deslocar-se com mais segurança e começa a combinar defesa, postura e ataque sem perder o controle.',
      katas: 'Taikyoku Sono San, Pinan Sono Ichi',
      time: '6 a 9 meses de prática',
      focus: 'Bloqueios (Jodan Uke, Gedan Barai), chutes circulares baixos (Gedan Mawashi Geri) e postura Kokutsu Dachi.',
      conditioning: 'Resistência geral, agachamentos, exercícios de base e Kumite orientado.',
      cssClass: 'belt-laranja',
      stripes: 0
    },
    azul: {
      title: 'Faixa Azul (青帯)',
      kyu: '8º e 7º Kyu',
      element: 'Base: movimentação e fluidez',
      philosophy: 'O praticante passa a trabalhar deslocamento, esquiva e mudança de direção, aprendendo a responder sem ficar preso a uma única posição.',
      katas: 'Pinan Sono Ni, Yantsu',
      time: '9 a 12 meses após a Laranja',
      focus: 'Esquivas (Tai Sabaki), chute circular médio (Mawashi Geri Chudan) e defesas circulares (Mawashi Uke).',
      conditioning: 'Resistência, mobilidade e Kumite com contato controlado.',
      cssClass: 'belt-azul',
      stripes: 0
    },
    amarela: {
      title: 'Faixa Amarela (黄帯)',
      kyu: '6º e 5º Kyu',
      element: 'Base: foco e tempo de execução',
      philosophy: 'O aluno refina o Kime, aprende a escolher melhor o momento da técnica e começa a sustentar ritmo e concentração durante o treino.',
      katas: 'Pinan Sono San, Tsuki No Kata',
      time: '1 ano de treino após a Azul',
      focus: 'Golpes com cotovelo (Hiji Ate), chutes altos (Jodan Geri) e controle da distância e tempo de reação.',
      conditioning: 'Resistência de braços e tronco, além de rounds de Kumite com orientação.',
      cssClass: 'belt-amarela',
      stripes: 0
    },
    verde: {
      title: 'Faixa Verde (緑帯)',
      kyu: '4º e 3º Kyu',
      element: 'Base: coordenação e controle',
      philosophy: 'A prática passa a exigir mais coordenação entre combinações, respiração (Ibuki), deslocamento e leitura da distância durante o Kumite.',
      katas: 'Pinan Sono Shi, Pinan Sono Go, Gekisai Dai',
      time: '1 ano e meio de prática dedicada',
      focus: 'Combinações complexas (Renraku), chutes giratórios (Ushiro Geri) e estratégias táticas de combate livre.',
      conditioning: 'Resistência específica e rounds de Kumite conforme a orientação do dojo.',
      cssClass: 'belt-verde',
      stripes: 0
    },
    marrom: {
      title: 'Faixa Marrom (茶帯)',
      kyu: '2º Kyu',
      element: 'Base: refinamento e responsabilidade',
      philosophy: 'O praticante começa a ajudar os mais novos e a revisar suas próprias lacunas. O foco é dar qualidade ao Kihon, ao Kata e ao Kumite, sem pular etapas.',
      katas: 'Saifa, Seienchin, Garyu',
      time: '2 a 3 anos de prática consistente',
      focus: 'Refinamento de Katas superiores, controle da distância e Kumite com intensidade progressiva.',
      conditioning: 'Resistência específica e sequência de rounds conforme o programa do dojo.',
      cssClass: 'belt-marrom',
      stripes: 0,
      tipBlack: false
    },
    'marrom-preta': {
      title: 'Faixa Marrom com Ponta Preta (茶帯黒先)',
      kyu: '1º Kyu',
      element: 'Base: preparação para o Shodan',
      philosophy: 'Última etapa antes da Faixa Preta. O praticante revisa fundamentos, assume mais responsabilidade no dojo e se prepara para demonstrar consistência técnica.',
      katas: 'Saifa, Seienchin, Garyu, Kanku',
      time: '2 a 3 anos após a Faixa Marrom comum',
      focus: 'Katas avançados, aplicação dos fundamentos e liderança responsável no dojo.',
      conditioning: 'Condicionamento e rounds de Kumite de acordo com o programa do exame.',
      cssClass: 'belt-marrom',
      stripes: 1,
      tipBlack: false
    },
    preta: {
      title: 'Faixa Preta (黒帯 - Shodan)',
      kyu: '1º Dan (Yudansha - Senpai)',
      element: 'Base: continuidade e serviço',
      philosophy: 'O Shodan não encerra o aprendizado. É uma nova responsabilidade: continuar estudando, treinar com regularidade e contribuir para a evolução do dojo.',
      katas: 'Kanku Dai, Seipai, Sushiho, Bassai Dai',
      time: 'Mínimo de 4 a 6 anos de dedicação ininterrupta',
      focus: 'Kihon, Kata, Bunkai, aplicação técnica e preparação para o exame de Dan.',
      conditioning: 'Condicionamento e Kumite conforme as exigências da organização e do dojo.',
      cssClass: 'belt-preta',
      stripes: 1,
      tipBlack: false
    }
  };

  const beltNavItems = document.querySelectorAll('.belt-nav-item');
  const beltGraphic = document.getElementById('beltGraphic');
  const beltStripes = document.getElementById('beltStripes');
  const beltElement = document.getElementById('beltElement');
  const beltKyu = document.getElementById('beltKyu');
  const beltTitle = document.getElementById('beltTitle');
  const beltPhilosophy = document.getElementById('beltPhilosophy');
  const beltKatas = document.getElementById('beltKatas');
  const beltTime = document.getElementById('beltTime');
  const beltFocus = document.getElementById('beltFocus');
  const beltConditioning = document.getElementById('beltConditioning');

  beltNavItems.forEach((btn, index) => {
    btn.setAttribute('aria-controls', 'beltShowcase');
    btn.addEventListener('keydown', event => {
      if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % beltNavItems.length;
      if (event.key === 'ArrowLeft') next = (index - 1 + beltNavItems.length) % beltNavItems.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = beltNavItems.length - 1;
      beltNavItems[next].focus();
    });
  });

  beltNavItems.forEach(btn => {
    btn.addEventListener('click', () => {
      beltNavItems.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');

      const beltKey = btn.dataset.belt;
      const data = beltData[beltKey];
      if (!data) return;

      // Animar transição
      const showcaseCard = document.getElementById('beltShowcase');
      if (showcaseCard) {
        showcaseCard.style.opacity = '0.7';
        showcaseCard.style.transform = 'scale(0.995)';
      }

      setTimeout(() => {
        // Atualiza visual da faixa
        beltGraphic.className = 'belt-strap ' + data.cssClass;
        beltElement.textContent = data.element;
        beltKyu.textContent = data.kyu;
        beltTitle.textContent = data.title;
        beltPhilosophy.textContent = data.philosophy;
        beltKatas.textContent = data.katas;
        beltTime.textContent = data.time;
        beltFocus.textContent = data.focus;
        beltConditioning.textContent = data.conditioning;

        // Controle da ponta da faixa (sem bloco sólido de ponta)
        const beltTipWrap = document.getElementById('beltTipWrap');
        if (beltTipWrap) {
          beltTipWrap.className = 'belt-tip-wrap';
        }

        // Adicionar apenas a listra preta na Marrom/Preta e o risco amarelo na Preta
        beltStripes.innerHTML = '';
        if (beltKey === 'marrom-preta') {
          const stripe = document.createElement('div');
          stripe.className = 'belt-stripe-bar stripe-black';
          beltStripes.appendChild(stripe);
        } else if (beltKey === 'preta') {
          const stripe = document.createElement('div');
          stripe.className = 'belt-stripe-bar stripe-gold';
          beltStripes.appendChild(stripe);
        }

        if (showcaseCard) {
          showcaseCard.style.opacity = '1';
          showcaseCard.style.transform = 'scale(1)';
        }
      }, 150);
    });
  });

});
