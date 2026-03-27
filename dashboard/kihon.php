<?php
session_start();
include_once __DIR__ . '/../php/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kihon | Oyama Hub</title>
  <link rel="icon" href="./img/kyokushinicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Noto+Sans+JP:wght@300;400;700&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    :root {
      --black:      #0a0a0a;
      --deep:       #111111;
      --panel:      #161616;
      --border:     rgba(255,255,255,0.07);
      --red:        #c0392b;
      --red-glow:   rgba(192,57,43,0.35);
      --red-soft:   rgba(192,57,43,0.12);
      --gold:       #d4af37;
      --gold-soft:  rgba(212,175,55,0.15);
      --white:      #f0ece4;
      --muted:      #888;
      --radius:     14px;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    html { scroll-behavior: smooth; }

    body {
      background: var(--black);
      color: var(--white);
      font-family: 'Montserrat', sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ─── NOISE TEXTURE OVERLAY ─── */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: .4;
    }

    /* ─── NAV ─── */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 3rem;
      background: rgba(10,10,10,0.85);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border);
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: .75rem;
      text-decoration: none;
    }
    .nav-logo img { width: 36px; height: 36px; }
    .nav-logo span {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.4rem;
      letter-spacing: .08em;
      color: var(--white);
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      align-items: center;
    }
    .nav-links a {
      color: var(--muted);
      text-decoration: none;
      font-size: .8rem;
      font-weight: 600;
      letter-spacing: .1em;
      text-transform: uppercase;
      transition: color .2s;
    }
    .nav-links a:hover { color: var(--gold); }
    .nav-links a.active { color: var(--white); }

    .navbarArea {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: linear-gradient(to bottom, rgba(10,10,10,0.98) 0%, rgba(10,10,10,0.85) 100%);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    .header {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 40px;
      height: 72px;
      display: flex;
      align-items: center;
      gap: 36px;
      justify-content: flex-end;
      border-bottom: 1px solid var(--border);
    }

    .header a.active {
      color: var(--white);
    }

    .btn-back {
      background: var(--red-soft);
      border: 1px solid var(--red);
      color: var(--white);
      padding: .45rem 1.2rem;
      border-radius: 50px;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .08em;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s, box-shadow .2s;
    }
    .btn-back:hover {
      background: var(--red);
      box-shadow: 0 0 16px var(--red-glow);
    }

    /* ─── HERO BANNER ─── */
    .hero {
      position: relative;
      padding: 10rem 3rem 5rem;
      text-align: center;
      overflow: hidden;
    }

    .hero::before {
      content: 'KIHON';
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(8rem, 25vw, 20rem);
      color: rgba(255,255,255,0.025);
      white-space: nowrap;
      pointer-events: none;
      letter-spacing: .1em;
      line-height: 1;
    }

    .hero-tag {
      display: inline-block;
      background: var(--red-soft);
      border: 1px solid var(--red);
      color: var(--red);
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .2em;
      padding: .35rem 1rem;
      border-radius: 50px;
      margin-bottom: 1.5rem;
      text-transform: uppercase;
    }

    .hero h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(3rem, 8vw, 6.5rem);
      letter-spacing: .06em;
      line-height: .95;
      margin-bottom: 1.25rem;
    }

    .hero h1 em {
      color: var(--gold);
      font-style: normal;
    }

    .hero p {
      color: var(--muted);
      font-size: .95rem;
      max-width: 520px;
      margin: 0 auto 2.5rem;
      line-height: 1.7;
      font-weight: 400;
    }

    /* ─── STICKY CATEGORY NAV ─── */
    .cat-nav {
      position: sticky;
      top: 65px;
      z-index: 50;
      background: rgba(10,10,10,0.9);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: center;
      gap: 0;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .cat-nav::-webkit-scrollbar { display: none; }

    .cat-btn {
      background: none;
      border: none;
      color: var(--muted);
      font-family: 'Montserrat', sans-serif;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      padding: 1rem 1.8rem;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      white-space: nowrap;
      transition: color .2s, border-color .2s;
    }
    .cat-btn:hover { color: var(--white); background: rgba(255,255,255,0.1); border-bottom-color: var(--gold); }
    .cat-btn.active {
      color: var(--gold);
      border-bottom-color: var(--gold);
    }

    /* ─── MAIN CONTENT ─── */
    main {
      position: relative;
      z-index: 1;
      padding: 0 2rem 6rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* ─── SECTION ─── */
    .kihon-section {
      padding: 5rem 0 2rem;
      opacity: 0;
      transform: translateY(30px);
      transition: opacity .6s ease, transform .6s ease;
    }
    .kihon-section.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .section-header {
      display: flex;
      align-items: flex-end;
      gap: 1.5rem;
      margin-bottom: 2.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--border);
    }

    .section-number {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 5rem;
      line-height: 1;
      color: var(--red);
      opacity: .3;
      min-width: 4rem;
      text-align: right;
    }

    .section-info {}

    .section-kanji {
      font-family: 'Noto Sans JP', sans-serif;
      font-size: 1rem;
      color: var(--gold);
      letter-spacing: .1em;
      margin-bottom: .2rem;
    }

    .section-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 5vw, 3.5rem);
      letter-spacing: .06em;
      line-height: 1;
    }

    .section-sub {
      color: var(--muted);
      font-size: .8rem;
      margin-top: .3rem;
      font-weight: 400;
    }

    /* ─── CARDS GRID ─── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.25rem;
    }

    /* ─── TECHNIQUE CARD ─── */
    .tech-card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      position: relative;
      cursor: pointer;
      transition: transform .25s, border-color .25s, box-shadow .25s;
      display: flex;
      flex-direction: column;
    }

    .tech-card:hover {
      transform: translateY(-5px);
      border-color: rgba(192,57,43,0.4);
      box-shadow: 0 16px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(192,57,43,0.2);
    }

    /* colored left accent */
    .tech-card::before {
      content: '';
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 3px;
      background: var(--card-accent, var(--red));
      border-radius: 4px 0 0 4px;
    }

    .card-icon-zone {
      background: var(--deep);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 140px;
      font-size: 4rem;
      position: relative;
      overflow: hidden;
    }

    .card-icon-zone::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at center, var(--card-accent, var(--red)) 0%, transparent 70%);
      opacity: .08;
    }

    .card-kana {
      position: absolute;
      bottom: .5rem;
      right: .75rem;
      font-family: 'Noto Sans JP', sans-serif;
      font-size: .75rem;
      color: var(--muted);
      letter-spacing: .05em;
    }

    .card-body {
      padding: 1.25rem 1.25rem 1.25rem 1.6rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: .5rem;
    }

    .card-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.4rem;
      letter-spacing: .06em;
      line-height: 1;
    }

    .card-romaji {
      color: var(--gold);
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .15em;
      text-transform: uppercase;
    }

    .card-desc {
      color: #aaa;
      font-size: .82rem;
      line-height: 1.6;
      font-weight: 400;
      margin-top: .25rem;
      flex: 1;
    }

    .card-tags {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
      margin-top: .5rem;
    }

    .tag {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      color: var(--muted);
      font-size: .65rem;
      font-weight: 600;
      letter-spacing: .08em;
      padding: .2rem .6rem;
      border-radius: 50px;
      text-transform: uppercase;
    }

    /* ─── DIVIDER ─── */
    .divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--border), transparent);
      margin: 1rem 0;
    }

    /* ─── FOOTER ─── */
    footer {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 2rem;
      border-top: 1px solid var(--border);
      color: var(--muted);
      font-size: .75rem;
      letter-spacing: .05em;
    }

    /* ─── SCROLL TO TOP ─── */
    #scroll-top {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--red);
      border: none;
      color: #fff;
      font-size: 1.2rem;
      cursor: pointer;
      opacity: 0;
      pointer-events: none;
      transition: opacity .3s, transform .3s;
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 20px var(--red-glow);
    }
    #scroll-top.show { opacity: 1; pointer-events: all; }
    #scroll-top:hover { transform: translateY(-3px); }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 768px) {
      nav { padding: 1rem 1.2rem; }
      .hero { padding: 8rem 1.5rem 3rem; }
      main { padding: 0 1rem 4rem; }
      .section-number { font-size: 3rem; }
      .cards-grid { grid-template-columns: 1fr; }
    }

    /* ─── STAGGER ANIMATION ─── */
    .tech-card {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity .4s ease, transform .4s ease,
                  border-color .25s, box-shadow .25s, transform .25s;
    }
    .tech-card.visible { opacity: 1; transform: translateY(0); }
    .tech-card.visible:hover { transform: translateY(-5px); }
  </style>
</head>
<body>

  <!-- NAV -->
  <section class="navbarArea">
    <div class="header">
      <a href="../index.php">Inicio</a>
      <a href="../dashboard/progresso.php">Progresso</a>
      <a href="../dashboard/katas.php">Katas</a>
      <a href="../dashboard/kihon.php">Kihon</a>
      <a href="../dashboard/treinos.php">Treinos</a>
      <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
    </div>
  </section>

  <!-- HERO -->
  <header class="hero">
    <div class="hero-tag">基本 · Fundamentos</div>
    <h1>KIHON DO<br><em>KYOKUSHIN</em></h1>
    <p>Os fundamentos que formam a base de todo karateka. Domine cada técnica antes de avançar — <em>kihon</em> é o alicerce da excelência.</p>
  </header>

  <!-- STICKY CATEGORY NAV -->
  <div class="cat-nav">
    <button class="cat-btn active" onclick="scrollTo('tsuki')">突き · Socos</button>
    <button class="cat-btn" onclick="scrollTo('geri')">蹴り · Chutes</button>
    <button class="cat-btn" onclick="scrollTo('uke')">受け · Bloqueios</button>
    <button class="cat-btn" onclick="scrollTo('dachi')">立ち · Posições</button>
    <button class="cat-btn" onclick="scrollTo('uchi')">打ち · Golpes</button>
  </div>

  <!-- MAIN -->
  <main>

    <!-- ══════════════════════════════
         TSUKI — SOCOS
    ══════════════════════════════ -->
    <section class="kihon-section" id="tsuki">
      <div class="section-header">
        <div class="section-number">01</div>
        <div class="section-info">
          <div class="section-kanji">突き · Tsuki</div>
          <h2 class="section-title">SOCOS</h2>
          <p class="section-sub">Técnicas de ataque com os punhos — velocidade, alinhamento e quadril.</p>
        </div>
      </div>
      <div class="cards-grid">

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">👊<span class="card-kana">正拳</span></div>
          <div class="card-body">
            <div class="card-romaji">Seiken Tsuki</div>
            <div class="card-name">Soco Direto</div>
            <div class="card-desc">Soco básico com os dois primeiros nós dos dedos. O punho gira no final do movimento para potencializar o impacto. Base de todos os socos do Kyokushin.</div>
            <div class="card-tags"><span class="tag">Básico</span><span class="tag">Punho</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">🥊<span class="card-kana">逆突き</span></div>
          <div class="card-body">
            <div class="card-romaji">Gyaku Tsuki</div>
            <div class="card-name">Soco Reverso</div>
            <div class="card-desc">Soco com a mão oposta à perna da frente. Usa a rotação completa do quadril — o golpe de maior potência no karate. Muito usado em kumite.</div>
            <div class="card-tags"><span class="tag">Potência</span><span class="tag">Quadril</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">⚡<span class="card-kana">追い突き</span></div>
          <div class="card-body">
            <div class="card-romaji">Oi Tsuki</div>
            <div class="card-name">Soco com Avanço</div>
            <div class="card-desc">Soco executado enquanto se avança um passo. A perna da frente lidera o movimento e o soco é desferido com a mão do mesmo lado. Gera distância e poder simultâneos.</div>
            <div class="card-tags"><span class="tag">Avanço</span><span class="tag">Deslocamento</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">✊<span class="card-kana">上段突き</span></div>
          <div class="card-body">
            <div class="card-romaji">Jodan Tsuki</div>
            <div class="card-name">Soco Alto</div>
            <div class="card-desc">Soco direcionado à cabeça/queixo do adversário. No Kyokushin full-contact, socos à cabeça são proibidos no kumite — mas o kihon os pratica para desenvolver controle de nível.</div>
            <div class="card-tags"><span class="tag">Jodan</span><span class="tag">Cabeça</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">💥<span class="card-kana">中段突き</span></div>
          <div class="card-body">
            <div class="card-romaji">Chudan Tsuki</div>
            <div class="card-name">Soco Médio</div>
            <div class="card-desc">Soco ao nível do solar plexus ou costelas. É o alvo principal no kumite do Kyokushin, onde socos ao corpo são permitidos e muito efetivos.</div>
            <div class="card-tags"><span class="tag">Chudan</span><span class="tag">Corpo</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#c0392b">
          <div class="card-icon-zone">🤛<span class="card-kana">連突き</span></div>
          <div class="card-body">
            <div class="card-romaji">Ren Tsuki</div>
            <div class="card-name">Socos em Sequência</div>
            <div class="card-desc">Combinação rápida de socos alternados — geralmente dois ou três. Treina a velocidade de recuperação do punho e a manutenção da posição do corpo durante combinações.</div>
            <div class="card-tags"><span class="tag">Combinação</span><span class="tag">Velocidade</span></div>
          </div>
        </div>

      </div>
    </section>

    <div class="divider"></div>

    <!-- ══════════════════════════════
         GERI — CHUTES
    ══════════════════════════════ -->
    <section class="kihon-section" id="geri">
      <div class="section-header">
        <div class="section-number">02</div>
        <div class="section-info">
          <div class="section-kanji">蹴り · Geri</div>
          <h2 class="section-title">CHUTES</h2>
          <p class="section-sub">A especialidade do Kyokushin — chutes poderosos ao corpo e pernas.</p>
        </div>
      </div>
      <div class="cards-grid">

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">🦵<span class="card-kana">前蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Mae Geri</div>
            <div class="card-name">Chute Frontal</div>
            <div class="card-desc">Chute em linha reta para frente com a base da planta do pé (koshi). Usado para manter distância e atingir o abdômen. A joelho sobe antes de estender a perna.</div>
            <div class="card-tags"><span class="tag">Frontal</span><span class="tag">Distância</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">🌀<span class="card-kana">回し蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Mawashi Geri</div>
            <div class="card-name">Chute Circular</div>
            <div class="card-desc">Chute em arco horizontal com o peito do pé ou canela. Usado para atingir as costelas lateralmente. No Kyokushin a versão jodan (à cabeça) é um dos golpes de maior pontuação.</div>
            <div class="card-tags"><span class="tag">Circular</span><span class="tag">Costelas</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">💢<span class="card-kana">下段回し</span></div>
          <div class="card-body">
            <div class="card-romaji">Gedan Mawashi Geri</div>
            <div class="card-name">Low Kick</div>
            <div class="card-desc">Chute circular baixo direcionado à coxa ou panturrilha do adversário com a canela. Muito utilizado no Kyokushin para desgastar e desequilibrar. Golpe de alta frequência em competição.</div>
            <div class="card-tags"><span class="tag">Perna</span><span class="tag">Desgaste</span><span class="tag">Gedan</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">🔙<span class="card-kana">後ろ蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Ushiro Geri</div>
            <div class="card-name">Chute de Costas</div>
            <div class="card-desc">Chute para trás com o calcanhar, executado após girar o quadril. Extremamente poderoso pela linha reta e peso corporal envolvido. Requer boa consciência espacial.</div>
            <div class="card-tags"><span class="tag">Calcanhar</span><span class="tag">Potência</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">⬆️<span class="card-kana">膝蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Hiza Geri</div>
            <div class="card-name">Joelhada</div>
            <div class="card-desc">Ataque com o joelho ao corpo do adversário em distância curta. Muito efetivo no clinch. A mão puxa o adversário para baixo enquanto o joelho sobe para o abdômen.</div>
            <div class="card-tags"><span class="tag">Joelho</span><span class="tag">Curta distância</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">🌪️<span class="card-kana">飛び蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Tobi Geri</div>
            <div class="card-name">Chute Voador</div>
            <div class="card-desc">Chute executado no ar após um salto. Combina potência e alcance inesperado. Treinado para desenvolver explosão muscular e coordenação. Exige grande habilidade técnica.</div>
            <div class="card-tags"><span class="tag">Salto</span><span class="tag">Avançado</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#d4af37">
          <div class="card-icon-zone">🦶<span class="card-kana">横蹴り</span></div>
          <div class="card-body">
            <div class="card-romaji">Yoko Geri</div>
            <div class="card-name">Chute Lateral</div>
            <div class="card-desc">Chute em linha reta para o lado com o lado do pé (sokuto). O quadril abre completamente e o corpo inclina. Eficiente para criar ângulo e quebrar a guarda lateral.</div>
            <div class="card-tags"><span class="tag">Lateral</span><span class="tag">Sokuto</span></div>
          </div>
        </div>

      </div>
    </section>

    <div class="divider"></div>

    <!-- ══════════════════════════════
         UKE — BLOQUEIOS
    ══════════════════════════════ -->
    <section class="kihon-section" id="uke">
      <div class="section-header">
        <div class="section-number">03</div>
        <div class="section-info">
          <div class="section-kanji">受け · Uke</div>
          <h2 class="section-title">BLOQUEIOS</h2>
          <p class="section-sub">Defesas que redirecionam e neutralizam ataques — não apenas bloqueiam.</p>
        </div>
      </div>
      <div class="cards-grid">

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🛡️<span class="card-kana">上段受け</span></div>
          <div class="card-body">
            <div class="card-romaji">Jodan Uke</div>
            <div class="card-name">Bloqueio Alto</div>
            <div class="card-desc">Bloqueio ascendente do antebraço para proteger a cabeça. O braço vai de baixo para cima, desviando golpes altos para cima e para fora. Mão defensora termina acima da cabeça.</div>
            <div class="card-tags"><span class="tag">Alto</span><span class="tag">Antebraço</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🤚<span class="card-kana">中段受け</span></div>
          <div class="card-body">
            <div class="card-romaji">Chudan Uke</div>
            <div class="card-name">Bloqueio Médio</div>
            <div class="card-desc">Bloqueio externo do antebraço ao nível do corpo. Desvia socos e chutes dirigidos ao abdômen para o lado. O antebraço roda no impacto para redirecionar a força.</div>
            <div class="card-tags"><span class="tag">Médio</span><span class="tag">Externo</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🖐️<span class="card-kana">下段払い</span></div>
          <div class="card-body">
            <div class="card-romaji">Gedan Barai</div>
            <div class="card-name">Bloqueio Baixo</div>
            <div class="card-desc">Varredura descendente do antebraço para bloquear chutes baixos e socos ao abdômen inferior. Movimento de cima para baixo e para fora. Um dos bloqueios mais praticados no kihon.</div>
            <div class="card-tags"><span class="tag">Baixo</span><span class="tag">Varredura</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🤜<span class="card-kana">内受け</span></div>
          <div class="card-body">
            <div class="card-romaji">Uchi Uke</div>
            <div class="card-name">Bloqueio Interno</div>
            <div class="card-desc">Bloqueio de dentro para fora com o antebraço. Ideal contra socos retos ao corpo — redireciona a força para o lado externo do atacante, abrindo uma contra-atacar imediata.</div>
            <div class="card-tags"><span class="tag">Interno</span><span class="tag">Contra-ataque</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🌊<span class="card-kana">手刀受け</span></div>
          <div class="card-body">
            <div class="card-romaji">Shuto Uke</div>
            <div class="card-name">Bloqueio Mão-Faca</div>
            <div class="card-desc">Bloqueio com a lateral da mão aberta (shuto). Pode interceptar socos e também ser usado como ataque. A mão não-defensora fica na cintura em posição de câmara (hikite).</div>
            <div class="card-tags"><span class="tag">Mão aberta</span><span class="tag">Shuto</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#2980b9">
          <div class="card-icon-zone">🔄<span class="card-kana">回し受け</span></div>
          <div class="card-body">
            <div class="card-romaji">Mawashi Uke</div>
            <div class="card-name">Bloqueio Circular</div>
            <div class="card-desc">Bloqueio em movimento circular que redireciona o ataque. Ambas as mãos participam do movimento — uma guia e outra bloqueia. Eficiente para neutralizar chutes circulares.</div>
            <div class="card-tags"><span class="tag">Circular</span><span class="tag">Duas mãos</span></div>
          </div>
        </div>

      </div>
    </section>

    <div class="divider"></div>

    <!-- ══════════════════════════════
         DACHI — POSIÇÕES
    ══════════════════════════════ -->
    <section class="kihon-section" id="dachi">
      <div class="section-header">
        <div class="section-number">04</div>
        <div class="section-info">
          <div class="section-kanji">立ち · Dachi</div>
          <h2 class="section-title">POSIÇÕES</h2>
          <p class="section-sub">A base de tudo — equilíbrio, estabilidade e prontidão para o movimento.</p>
        </div>
      </div>
      <div class="cards-grid">

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">🧍<span class="card-kana">平行立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Heiko Dachi</div>
            <div class="card-name">Posição Paralela</div>
            <div class="card-desc">Pés paralelos na largura dos ombros. Posição natural de repouso e ponto de partida para muitos movimentos. Usada no Yoi (prontidão) antes de executar sequências de kihon.</div>
            <div class="card-tags"><span class="tag">Básico</span><span class="tag">Neutro</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">🤺<span class="card-kana">前屈立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Zenkutsu Dachi</div>
            <div class="card-name">Posição de Combate Frontal</div>
            <div class="card-desc">Perna da frente dobrada a 90°, perna de trás estendida. Peso ~70% na frente. Excelente para socos com avanço. Proporciona grande força de impulso para frente.</div>
            <div class="card-tags"><span class="tag">Ataque</span><span class="tag">Avanço</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">🧘<span class="card-kana">騎馬立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Kiba Dachi</div>
            <div class="card-name">Posição do Cavaleiro</div>
            <div class="card-desc">Pés afastados, joelhos dobrados e para fora, como se montasse um cavalo. Base muito estável e baixa. Excelente para treinar força de pernas e golpes laterais (yoko geri).</div>
            <div class="card-tags"><span class="tag">Estabilidade</span><span class="tag">Lateral</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">⚖️<span class="card-kana">後屈立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Kokutsu Dachi</div>
            <div class="card-name">Posição Recuada</div>
            <div class="card-desc">Peso ~70% na perna de trás, joelho traseiro dobrado. Perna da frente quase estendida no chão. Posição defensiva que mantém distância e facilita chutes rápidos com a perna da frente.</div>
            <div class="card-tags"><span class="tag">Defesa</span><span class="tag">Distância</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">🥋<span class="card-kana">不動立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Fudo Dachi</div>
            <div class="card-name">Posição Imóvel</div>
            <div class="card-desc">Posição natural de combate do Kyokushin — similar ao zenkutsu mas mais natural, com os pés um à frente do outro. Base do kumite, equilibra mobilidade e estabilidade.</div>
            <div class="card-tags"><span class="tag">Kumite</span><span class="tag">Combate</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#27ae60">
          <div class="card-icon-zone">🕴️<span class="card-kana">三戦立ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Sanchin Dachi</div>
            <div class="card-name">Posição dos Três Conflitos</div>
            <div class="card-desc">Posição fechada e tensa onde os pés se cruzam levemente. Base do kata Sanchin — treina contração muscular total, respiração e tensão corporal. Fundamental no Kyokushin.</div>
            <div class="card-tags"><span class="tag">Kata</span><span class="tag">Tensão</span><span class="tag">Respiração</span></div>
          </div>
        </div>

      </div>
    </section>

    <div class="divider"></div>

    <!-- ══════════════════════════════
         UCHI — GOLPES ESPECIAIS
    ══════════════════════════════ -->
    <section class="kihon-section" id="uchi">
      <div class="section-header">
        <div class="section-number">05</div>
        <div class="section-info">
          <div class="section-kanji">打ち · Uchi</div>
          <h2 class="section-title">GOLPES ESPECIAIS</h2>
          <p class="section-sub">Ataques com partes específicas da mão — precisão e penetração.</p>
        </div>
      </div>
      <div class="cards-grid">

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🗡️<span class="card-kana">手刀打ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Shuto Uchi</div>
            <div class="card-name">Golpe Mão-Faca</div>
            <div class="card-desc">Ataque com a lateral da mão aberta em movimento circular. Pode ser executado de dentro para fora ou de fora para dentro. Alvo clássico: pescoço ou têmpora do adversário.</div>
            <div class="card-tags"><span class="tag">Mão aberta</span><span class="tag">Pescoço</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🪨<span class="card-kana">鉄槌打ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Tetsui Uchi</div>
            <div class="card-name">Golpe Martelo</div>
            <div class="card-desc">Golpe com a parte inferior do punho fechado (lado do mindinho), como um martelo. Movimento descendente ou circular. Útil contra alvos duros como o topo da cabeça ou costelas.</div>
            <div class="card-tags"><span class="tag">Punho</span><span class="tag">Martelo</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🏹<span class="card-kana">貫手</span></div>
          <div class="card-body">
            <div class="card-romaji">Nukite</div>
            <div class="card-name">Golpe Lança-Dedos</div>
            <div class="card-desc">Ataque com as pontas dos dedos estendidos, como uma lança. Alvo: garganta, abdômen ou pontos vitais. Exige dedos muito fortalecidos — treinados com makiwara e areia.</div>
            <div class="card-tags"><span class="tag">Dedos</span><span class="tag">Pontos vitais</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🌑<span class="card-kana">肘打ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Empi Uchi</div>
            <div class="card-name">Cotovelada</div>
            <div class="card-desc">Golpe com a ponta do cotovelo em curta distância. Devastador quando executado corretamente — o cotovelo é um dos ossos mais duros do corpo. Pode ser horizontal, ascendente ou descendente.</div>
            <div class="card-tags"><span class="tag">Cotovelo</span><span class="tag">Curta distância</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🎯<span class="card-kana">一本拳</span></div>
          <div class="card-body">
            <div class="card-romaji">Ippon Ken</div>
            <div class="card-name">Soco Um Nó</div>
            <div class="card-desc">Soco com o nó do dedo indicador projetado à frente. Penetra em alvos pequenos e pontos de pressão como têmpora, philtrum ou costelas. Exige condicionamento dos dedos.</div>
            <div class="card-tags"><span class="tag">Precisão</span><span class="tag">Pontos</span></div>
          </div>
        </div>

        <div class="tech-card" style="--card-accent:#8e44ad">
          <div class="card-icon-zone">🔱<span class="card-kana">裏拳打ち</span></div>
          <div class="card-body">
            <div class="card-romaji">Uraken Uchi</div>
            <div class="card-name">Golpe Dorso do Punho</div>
            <div class="card-desc">Golpe com o dorso (costas) do punho fechado. Movimento rápido de chicote lateral ou circular. Eficiente para atingir a têmpora ou a lateral da cabeça com velocidade surpreendente.</div>
            <div class="card-tags"><span class="tag">Dorso</span><span class="tag">Chicote</span></div>
          </div>
        </div>

      </div>
    </section>

  </main>

  <footer>
    <p>© 2026 Oyama Hub · Kyokushin Karate · <em>OSU!</em></p>
  </footer>

  <button id="scroll-top" title="Voltar ao topo" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>

  <script>
    /* ── Scroll-to-top button ── */
    window.addEventListener('scroll', () => {
      document.getElementById('scroll-top').classList.toggle('show', window.scrollY > 400);
    });

    /* ── Cat-nav active state + smooth scroll ── */
    function scrollTo(id) {
      document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    const sections = document.querySelectorAll('.kihon-section');
    const catBtns  = document.querySelectorAll('.cat-btn');

    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          catBtns.forEach(b => b.classList.remove('active'));
          const active = [...catBtns].find(b => b.getAttribute('onclick')?.includes(id));
          if (active) active.classList.add('active');
        }
      });
    }, { threshold: 0.25 });

    /* ── Reveal sections on scroll ── */
    sections.forEach(s => sectionObserver.observe(s));

    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.1 });

    sections.forEach(s => revealObserver.observe(s));

    /* ── Stagger cards ── */
    const cardObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const cards = entry.target.querySelectorAll('.tech-card');
          cards.forEach((c, i) => {
            setTimeout(() => c.classList.add('visible'), i * 80);
          });
          cardObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.05 });

    document.querySelectorAll('.cards-grid').forEach(g => cardObserver.observe(g));

    /* ── Dashboard-style top nav active item ── */
    const navLinks = document.querySelectorAll('.header a');
    const currentPage = window.location.pathname.split('/').pop();
    navLinks.forEach(link => {
      const hrefPage = link.getAttribute('href').split('/').pop();
      if (hrefPage === currentPage) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  </script>
</body>
</html>