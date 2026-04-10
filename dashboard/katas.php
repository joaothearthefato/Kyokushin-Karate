<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: ../php/login.php");
    exit;
}

// 1. CONEXÃO
require_once('../php/config.php'); 
// Se no seu config.php a variável for $con, mude abaixo para $con
$conexao = $conn; 

// 2. BUSCAR KATAS
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
  <link rel="stylesheet" href="../css/katas.css">
</head>
<body>

<nav class="navbar">
  <a href="../php/dashboard.php" class="navbar-brand">OYAMA<span>HUB</span></a>
  <div class="nav-links">
    <a href="../php/dashboard.php">Início</a>
    <a href="progresso.php">Progresso</a>
    <a href="katas.php" class="active">Katas</a>
    <a href="kihon.php">Kihon</a>
    <a href="treinos.php">Treinos</a>
  </div>
  <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
</nav>

<div class="page-hero">
  <div class="hero-tag">Kyokushin Karate</div>
  <h1>KATAS</h1>
  <p>Formas codificadas de combate. Cada kata é um diálogo com os fundadores do estilo.</p>
</div>

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
        <p class="kata-desc">${k.descricao.substring(0, 80)}...</p>
        <button class="kata-btn" onclick="openModal(${k.id})">Assistir Vídeo ›</button>
      </div>`;
    grid.appendChild(card);
  });
}

// Função para extrair o ID do vídeo do YouTube e gerar link de Embed
function getYouTubeEmbed(url) {
    if (!url) return "";
    let videoId = "";
    
    // Formato: youtube.com/watch?v=XXXX
    if (url.includes("v=")) {
        videoId = url.split("v=")[1].split("&")[0];
    } 
    // Formato: youtu.be/XXXX
    else if (url.includes("youtu.be/")) {
        videoId = url.split("youtu.be/")[1].split("?")[0];
    }
    // Formato: youtube.com/embed/XXXX
    else if (url.includes("embed/")) {
        videoId = url.split("embed/")[1].split("?")[0];
    }

    return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1` : "";
}

function openModal(id) {
  const k = katas.find(x => x.id == id);
  if (!k) return;

  document.getElementById('m-name').textContent = k.nome;
  document.getElementById('m-level-badge').innerHTML = `<span class="kata-level level-${k.nivel}">${k.nivel}</span>`;
  document.getElementById('m-desc').textContent = k.descricao;
  
  // Transforma o link do banco em link de embed funcional
  const embedLink = getYouTubeEmbed(k.video_url); 
  // IMPORTANTE: k.video_url deve ser o nome da sua coluna no banco!
  
  document.getElementById('m-video').src = embedLink;
  document.getElementById('modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  document.getElementById('m-video').src = ""; // Para o som ao fechar
  document.body.style.overflow = '';
}

// Eventos
document.getElementById('modalClose').addEventListener('click', closeModal);
document.getElementById('searchInput').addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase();
    const filtrados = katas.filter(k => {
        const matchFilter = activeFilter === 'todos' || k.nivel === activeFilter;
        return matchFilter && (k.nome.toLowerCase().includes(q) || k.descricao.toLowerCase().includes(q));
    });
    renderGrid(filtrados);
});

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter;
    // Dispara a busca novamente
    document.getElementById('searchInput').dispatchEvent(new Event('input'));
  });
});

window.onclick = (e) => { if (e.target.id === 'modal') closeModal(); }

// Inicializa
renderGrid(katas);
</script>
</body>
</html>