<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: ../php/login.php");
    exit;
}

// 1. CONEXÃO
require_once('../php/config.php'); 
$conexao = $conn; 

// 2. BUSCAR KATAS (Note que adicionei categoria_id 6 no seu INSERT anterior, 
// se quiser filtrar apenas katas, pode usar WHERE categoria_id = 6 se a tabela for a mesma,
// mas como você criou uma tabela separada 'katas', o SQL abaixo está correto)
$sql = "SELECT * FROM katas ORDER BY ordem ASC";
$result = $conexao->query($sql);

$katas_db = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $katas_db[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oyama Hub | Katas</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@300;400;500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="../css/katas.css">
</head>
<body>

<!-- ── Navbar ── -->
<section class="navbarArea">
    <div class="header">
        <a href="../php/dashboard.php">Início</a>
        <a href="progresso.php">Progresso</a>
        <a href="katas.php" class="active">Katas</a>
        <a href="kihon.php">Kihon</a>
        <a href="treinos.php">Treinos</a>
        <button id="theme-toggle" class="theme-btn" aria-label="Alternar tema">
            <span class="theme-icon">☀️</span>
            <span class="theme-label">Light</span>
        </button>
        <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</section>

<div class="page-hero">
  <div class="hero-tag">Kyokushin Karate</div>
  <h1>KATAS</h1>
  <p>Formas codificadas de combate. Cada kata é um diálogo com os fundadores do estilo.</p>
</div>

 <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
      <div class="vw-plugin-top-wrapper"></div>
    </div>
  </div>
  <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
  <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
  </script>

<div class="controls">
  <div class="search-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" class="search-input" placeholder="Buscar kata..." id="searchInput">
  </div>
  <button class="filter-btn active" data-filter="todos">Todos</button>
  <button class="filter-btn" data-filter="iniciante">Iniciante</button>
  <button class="filter-btn" data-filter="intermediario">Intermediário</button>
  <button class="filter-btn" data-filter="avancado">Avançado</button>
</div>

<div class="kata-grid" id="kataGrid"></div>

<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <button class="modal-close" id="modalClose">✕</button>
    <div id="video-container">
        <iframe id="m-video" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <div class="modal-header">
      <div class="modal-kata-name" id="m-name"></div>
      <div id="m-level-badge"></div>
    </div>
    <div class="modal-body">
      <div class="modal-section-title">Sobre o Kata</div>
      <p class="modal-text" id="m-desc"></p>
    </div>
  </div>
</div>

<script>
// Passa os dados do PHP para o JS
const katas = <?php echo json_encode($katas_db); ?>;
let activeFilter = 'todos';

function renderGrid(lista) {
  const grid = document.getElementById('kataGrid');
  grid.innerHTML = '';
  
  if (lista.length === 0) {
    grid.innerHTML = '<div class="no-results">Nenhum kata encontrado</div>';
    return;
  }

  lista.forEach((k, i) => {
    const card = document.createElement('div');
    card.className = 'kata-card';
    card.style.animationDelay = (i * 0.05) + 's';
    card.innerHTML = `
      <div class="kata-card-inner">
        <div class="kata-number">${String(k.ordem).padStart(2,'0')}</div>
        <span class="kata-level level-${k.nivel}">${k.nivel}</span>
        <div class="kata-name">${k.nome}</div>
        <p class="kata-desc">${k.descricao.substring(0, 100)}...</p>
        <button class="kata-btn" onclick="openModal(${k.id})">Assistir Vídeo ›</button>
      </div>`;
    grid.appendChild(card);
  });
}

/**
 * Extrai o ID do vídeo de qualquer URL do YouTube e retorna o link de EMBED.
 */
function getYouTubeEmbed(url) {
    if (!url) return "";
    let videoId = "";
    
    try {
        if (url.includes("youtube.com/watch")) {
            const urlParams = new URLSearchParams(new URL(url).search);
            videoId = urlParams.get('v');
        } else if (url.includes("youtu.be/")) {
            videoId = url.split("youtu.be/")[1].split(/[?#]/)[0];
        } else if (url.includes("youtube.com/embed/")) {
            videoId = url.split("embed/")[1].split(/[?#]/)[0];
        }
        
        // Limpeza adicional caso o ID venha com parâmetros (ex: &pp=...)
        if (videoId && videoId.includes("&")) {
            videoId = videoId.split("&")[0];
        }
    } catch (e) {
        console.error("Erro ao processar URL do vídeo", e);
    }

    return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0` : "";
}

function openModal(id) {
  // Encontra o objeto no array carregado via PHP
  const k = katas.find(x => x.id == id);
  if (!k) return;

  document.getElementById('m-name').textContent = k.nome;
  document.getElementById('m-level-badge').innerHTML = `<span class="kata-level level-${k.nivel}">${k.nivel}</span>`;
  document.getElementById('m-desc').textContent = k.descricao;
  
  // Converte a URL do banco para Embed
  const embedLink = getYouTubeEmbed(k.video_url); 
  
  const iframe = document.getElementById('m-video');
  if (embedLink) {
      iframe.src = embedLink;
      iframe.style.display = "block";
  } else {
      iframe.style.display = "none";
      console.warn("URL de vídeo inválida para o kata:", k.nome);
  }

  document.getElementById('modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  document.getElementById('m-video').src = ""; // Reseta o iframe para parar o vídeo
  document.body.style.overflow = '';
}

// Eventos de Busca e Filtro
document.getElementById('searchInput').addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase();
    const filtrados = katas.filter(k => {
        const matchFilter = activeFilter === 'todos' || k.nivel === activeFilter;
        const matchSearch = k.nome.toLowerCase().includes(q) || k.descricao.toLowerCase().includes(q);
        return matchFilter && matchSearch;
    });
    renderGrid(filtrados);
});

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter;
    // Dispara o evento de input para reaplicar a lógica de filtro/busca
    document.getElementById('searchInput').dispatchEvent(new Event('input'));
  });
});

document.getElementById('modalClose').addEventListener('click', closeModal);

window.onclick = (e) => { 
    if (e.target.id === 'modal') closeModal(); 
}

// Inicializa a grade
renderGrid(katas);

const themeToggle = document.getElementById('theme-toggle');
const themeIcon = themeToggle.querySelector('.theme-icon');
const themeLabel = themeToggle.querySelector('.theme-label');
const html = document.documentElement;

// Check for saved theme preference or default to dark mode
const currentTheme = localStorage.getItem('theme') || 'dark';
if (currentTheme === 'light') {
  html.classList.add('light');
  themeIcon.textContent = '🌙';
  themeLabel.textContent = 'Dark';
}

themeToggle.addEventListener('click', () => {
  html.classList.toggle('light');
  const isLight = html.classList.contains('light');

  // Update button appearance with animation
  if (isLight) {
    themeIcon.textContent = '🌙';
    themeLabel.textContent = 'Dark';
    localStorage.setItem('theme', 'light');
  } else {
    themeIcon.textContent = '☀️';
    themeLabel.textContent = 'Light';
    localStorage.setItem('theme', 'dark');
  }

  // Add click animation
  themeToggle.style.transform = 'scale(0.95)';
  setTimeout(() => {
    themeToggle.style.transform = '';
  }, 150);
});
</script>
</body>
</html>