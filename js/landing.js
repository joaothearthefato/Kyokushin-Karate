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
      element: 'Elemento: Pureza & Potencial',
      philosophy: 'A pureza e o início da busca. A folha em branco onde tudo será gravado. O praticante aprende a etiqueta do dojo, a postura correta e a respiração básica.',
      katas: 'Taikyoku Sono Ichi, Taikyoku Sono Ni',
      time: '3 a 6 meses de treino constante',
      focus: 'Posturas fundamentais (Zenkutsu Dachi), socos diretos (Seiken Tsuki) e chutes frontais (Mae Geri).',
      conditioning: 'Flexibilidade de base, 20 flexões no punho, 25 abdominais.',
      cssClass: 'belt-branca',
      stripes: 0
    },
    laranja: {
      title: 'Faixa Laranja (橙帯)',
      kyu: '10º e 9º Kyu',
      element: 'Elemento: Terra & Estabilidade',
      philosophy: 'Simboliza a terra onde a semente foi plantada. O aluno começa a criar raízes sólidas, entendendo o equilíbrio físico e a disciplina no combate.',
      katas: 'Taikyoku Sono San, Pinan Sono Ichi',
      time: '6 a 9 meses de prática',
      focus: 'Bloqueios (Jodan Uke, Gedan Barai), chutes circulares baixos (Gedan Mawashi Geri) e postura Kokutsu Dachi.',
      conditioning: '30 flexões, 35 abdominais, 30 agachamentos, kumite leve.',
      cssClass: 'belt-laranja',
      stripes: 0
    },
    azul: {
      title: 'Faixa Azul (青帯)',
      kyu: '8º e 7º Kyu',
      element: 'Elemento: Água & Fluidez',
      philosophy: 'A água adapta-se a qualquer recipiente. O karateka aprende a mover-se com soltura, esquivar sem rigidez e fluir entre ataque e defesa.',
      katas: 'Pinan Sono Ni, Yantsu',
      time: '9 a 12 meses após a Laranja',
      focus: 'Esquivas (Tai Sabaki), chute circular médio (Mawashi Geri Chudan) e defesas circulares (Mawashi Uke).',
      conditioning: '40 flexões, 45 abdominais, kumite com contato moderado.',
      cssClass: 'belt-azul',
      stripes: 0
    },
    amarela: {
      title: 'Faixa Amarela (黄帯)',
      kyu: '6º e 5º Kyu',
      element: 'Elemento: Fogo & Determinação',
      philosophy: 'O fogo que queima as dúvidas e acende o poder interior. O karateka descobre o kime (foco de energia explosiva) em cada impacto.',
      katas: 'Pinan Sono San, Tsuki No Kata',
      time: '1 ano de treino após a Azul',
      focus: 'Golpes com cotovelo (Hiji Ate), chutes altos (Jodan Geri) e controle da distância e tempo de reação.',
      conditioning: '50 flexões nos punhos (Seiken), 50 abdominais, 5 rounds de kumite.',
      cssClass: 'belt-amarela',
      stripes: 0
    },
    verde: {
      title: 'Faixa Verde (緑帯)',
      kyu: '4º e 3º Kyu',
      element: 'Elemento: Ar & Equilíbrio',
      philosophy: 'A árvore ganha copa e folhagem verde. Representa a maturidade técnica intermediária. O karateka domina a respiração profunda (Ibuki) e a serenidade sob pressão.',
      katas: 'Pinan Sono Shi, Pinan Sono Go, Gekisai Dai',
      time: '1 ano e meio de prática dedicada',
      focus: 'Combinações complexas (Renraku), chutes giratórios (Ushiro Geri) e estratégias táticas de combate livre.',
      conditioning: '60 flexões, 60 abdominais, 10 rounds de kumite com contato pleno.',
      cssClass: 'belt-verde',
      stripes: 0
    },
    marrom: {
      title: 'Faixa Marrom (茶帯)',
      kyu: '2º Kyu',
      element: 'Elemento: Fruto & Criatividade',
      philosophy: 'O fruto pronto para colheita. O praticante torna-se exemplo aos mais novos, refinando suas fraquezas e preparando corpo e alma para o desafio da Faixa Preta.',
      katas: 'Saifa, Seienchin, Garyu',
      time: '2 a 3 anos de treinamento rigoroso',
      focus: 'Refinamento milimétrico de katas superiores, absorção de impacto corporal e kumite contínuo de alta intensidade.',
      conditioning: '75 flexões, 80 abdominais, 15 a 20 lutas consecutivas.',
      cssClass: 'belt-marrom',
      stripes: 0,
      tipBlack: false
    },
    'marrom-preta': {
      title: 'Faixa Marrom com Ponta Preta (茶帯黒先)',
      kyu: '1º Kyu',
      element: 'Elemento: Maturidade & Transição',
      philosophy: 'O ponto culminante antes da Faixa Preta. A ponta negra simboliza que o guerreiro já toca a escuridão da maestria. É o último rito de purificação antes do verdadeiro recomeço.',
      katas: 'Saifa, Seienchin, Garyu, Kanku',
      time: '2 a 3 anos após a Faixa Marrom comum',
      focus: 'Maestria dos katas avançados (Kanku), kumite de alta intensidade e demonstração de liderança no dojo.',
      conditioning: '80 flexões nos punhos, 90 abdominais, 20 a 25 lutas consecutivas.',
      cssClass: 'belt-marrom',
      stripes: 1,
      tipBlack: false
    },
    preta: {
      title: 'Faixa Preta (黒帯 - Shodan)',
      kyu: '1º Dan (Yudansha - Senpai)',
      element: 'Elemento: Sabedoria & Renascimento',
      philosophy: 'O negro que absorve todas as cores da jornada. Não é o fim, mas o verdadeiro recomeço. O Shodan alcança a mente vazia (Mushin), calma na tempestade e respeito absoluto.',
      katas: 'Kanku Dai, Seipai, Sushiho, Bassai Dai',
      time: 'Mínimo de 4 a 6 anos de dedicação ininterrupta',
      focus: 'Maestria completa de Kihon, Kata, Bunkai e teste físico do Kumite de 30 a 50 lutadores.',
      conditioning: '100 flexões nos nós dos dedos, 100 abdominais, teste de quebramento (Tameshiwari) e Kumite brutal.',
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


  /* ─────────────────────────────────────────────────────────────
     5. MÓDULO INTERATIVO DO DOJO KUN
     ───────────────────────────────────────────────────────────── */
  const dojoKunData = {
    1: {
      badge: 'PRIMEIRO PRECEITO · 一、我々は心身を錬磨し',
      quote: '"Treinaremos firmemente nosso coração e nosso corpo para obter um espírito firme e inabalável."',
      explanation: 'O karateka do Kyokushin não treina apenas músculos e ossos. A repetição exaustiva visa forjar a resiliência psíquica que não se dobra diante do cansaço, do medo ou do sofrimento cotidiano.',
      kanji: '心身錬磨'
    },
    2: {
      badge: 'SEGUNDO PRECEITO · 一、我々は武の真髄を極め',
      quote: '"Almejaremos o verdadeiro significado do caminho marcial, para que a todo momento nossos sentidos estejam alerta."',
      explanation: 'O verdadeiro significado (Kyoku) reside na sinceridade do combate real. É manter a presença plena no presente (Zanshin), estando pronto para reagir a qualquer adversidade com clareza mental.',
      kanji: '武道真髄'
    },
    3: {
      badge: 'TERCEIRO PRECEITO · 一、我々は質実剛健を以て',
      quote: '"Com vigor e coragem, cultivaremos o espírito de abnegação e lealdade."',
      explanation: 'O Kyokushin rejeita o orgulho fútil. Treina-se na simplicidade (Shitsujitsu), desenvolvendo a capacidade de sacrificar o ego em prol do bem comum, dos companheiros de treino e dos mestres.',
      kanji: '質実剛健'
    },
    4: {
      badge: 'QUARTO PRECEITO · 一、我々は礼節を重んじ',
      quote: '"Observaremos as regras de cortesia, honraremos nossos superiores e nos absteremos de violência desnecessária."',
      explanation: 'A força suprema só se justifica acompanhada de extremo respeito (Rei). Quem domina a capacidade de nocautear tem o dever sagrado de agir com humildade, cortesia e pacificação.',
      kanji: '礼節尊重'
    },
    5: {
      badge: 'QUINTO PRECEITO · 一、我々は神仏を尊び',
      quote: '"Honraremos nossos princípios morais e jamais esqueceremos a verdadeira virtude da humildade."',
      explanation: 'Quanto mais alta a graduação, mais inclinado o corpo deve se curvar no cumprimento. A vaidade é o veneno de qualquer artista marcial; a humildade é sua couraça.',
      kanji: '謙譲之美'
    },
    6: {
      badge: 'SEXTO PRECEITO · 一、我々は知性と体力とを向上させ',
      quote: '"Cultivaremos a sabedoria e a força física, sem nos desviarmos em desejos supérfluos."',
      explanation: 'Força sem intelecto é barbárie; intelecto sem força é impotência. O praticante busca o equilíbrio perfeito (Bunbu Ryodo — a pena e a espada) através do estudo e da disciplina física.',
      kanji: '文武両道'
    },
    7: {
      badge: 'SÉTIMO PRECEITO · 一、我々は生涯の修行を空手の道に通じ',
      quote: '"Dedicaremos toda a vida ao cumprimento da vocação do Kyokushin, realizando o verdadeiro caminho marcial."',
      explanation: 'O Kyokushin não termina quando se sai do dojo. É uma filosofia que orienta as decisões, o caráter ético no trabalho, a honra com a família e a persistência até o último suspiro de vida.',
      kanji: '生涯修行'
    }
  };

  const dojoKunItems = document.querySelectorAll('.dojokun-item');
  const dkBadge = document.getElementById('dkBadge');
  const dkQuote = document.getElementById('dkQuote');
  const dkExplanation = document.getElementById('dkExplanation');
  const dkKanji = document.getElementById('dkKanji');
  const dojoKunDetail = document.getElementById('dojoKunDetail');

  dojoKunItems.forEach(btn => {
    btn.addEventListener('click', () => {
      dojoKunItems.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const preceptNum = btn.dataset.precept;
      const data = dojoKunData[preceptNum];
      if (!data) return;

      if (dojoKunDetail) {
        dojoKunDetail.style.opacity = '0';
        dojoKunDetail.style.transform = 'translateY(8px)';
      }

      setTimeout(() => {
        if (dkBadge) dkBadge.textContent = data.badge;
        if (dkQuote) dkQuote.textContent = data.quote;
        if (dkExplanation) dkExplanation.textContent = data.explanation;
        if (dkKanji) dkKanji.textContent = data.kanji;

        if (dojoKunDetail) {
          dojoKunDetail.style.opacity = '1';
          dojoKunDetail.style.transform = 'translateY(0)';
        }
      }, 150);
    });
  });

});
