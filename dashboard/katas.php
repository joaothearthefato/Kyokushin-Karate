<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: ../php/login.php");
    exit;
}

require_once('../php/config.php');
$conexao = $conn;

/**
 * Converte qualquer URL do YouTube para o formato embed.
 * Aceita: watch?v=ID, youtu.be/ID, shorts/ID, embed/ID
 * Retorna string vazia se não conseguir extrair o ID.
 */
function youtubeEmbed(?string $url): string {
    if (!$url) return '';
    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~',
        '~youtube\.com/watch\?v=([A-Za-z0-9_-]{11})~',
        '~youtube\.com/embed/([A-Za-z0-9_-]{11})~',
        '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
    }
    return '';
}

$sql = "SELECT * FROM katas ORDER BY ordem ASC";
$result = $conexao->query($sql);

$katas_db = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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

<!-- NAVBAR -->
<nav class="navbar">
  <a href="home.php" class="navbar-brand">OYAMA<span>HUB</span></a>
  <div class="nav-links">
    <a href="home.php">Início</a>
    <a href="progresso.php">Progresso</a>
    <a href="katas.php" class="active">Katas</a>
    <a href="kihon.php">Kihon</a>
    <a href="treinos.php">Treinos</a>
  </div>
  <a href="../php/logout.php" class="logout-btn">Logout</a>
</nav>

<!-- HERO -->
<section class="page-hero">
  <span class="hero-tag">Kyokushin Karate</span>
  <h1>KATAS</h1>
  <p>Formas codificadas de combate. Cada kata é um diálogo com os fundadores do estilo.</p>
</section>

<<<<<<< Updated upstream
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

=======
<!-- CONTROLS -->
>>>>>>> Stashed changes
<div class="controls">
  <div class="search-wrap">
    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
    <input type="text" id="search" class="search-input" placeholder="Buscar kata...">
  </div>
  <button class="filter-btn active" data-filter="todos">Todos</button>
  <button class="filter-btn" data-filter="iniciante">Iniciante</button>
  <button class="filter-btn" data-filter="intermediario">Intermediário</button>
  <button class="filter-btn" data-filter="avancado">Avançado</button>
</div>

<!-- GRID -->
<div class="kata-grid" id="kata-grid">
  <?php foreach ($katas_db as $i => $kata):
      $embed = youtubeEmbed($kata['video_url'] ?? '');
  ?>
    <article class="kata-card"
             data-nivel="<?= htmlspecialchars($kata['nivel']) ?>"
             data-nome="<?= htmlspecialchars(strtolower($kata['nome'])) ?>"
             data-video="<?= htmlspecialchars($embed) ?>"
             data-titulo="<?= htmlspecialchars($kata['nome']) ?>"
             data-descricao="<?= htmlspecialchars($kata['descricao']) ?>">
      <div class="kata-card-inner">
        <span class="kata-number"><?= str_pad($kata['ordem'], 2, '0', STR_PAD_LEFT) ?></span>
        <span class="kata-level level-<?= htmlspecialchars($kata['nivel']) ?>">
          <?= htmlspecialchars($kata['nivel']) ?>
        </span>
        <h3 class="kata-name"><?= htmlspecialchars($kata['nome']) ?></h3>
        <p class="kata-desc"><?= htmlspecialchars($kata['descricao']) ?></p>
        <button class="kata-btn">Ver Detalhes</button>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <button class="modal-close" id="modal-close" aria-label="Fechar">✕</button>

    <div id="video-container">
      <iframe id="m-video"
              src=""
              title="Vídeo do kata"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen></iframe>
    </div>

    <div class="modal-header">
      <h2 class="modal-kata-name" id="m-title">—</h2>
    </div>
    <div class="modal-body">
      <h4 class="modal-section-title">Sobre o Kata</h4>
      <p class="modal-text" id="m-desc">—</p>
    </div>
  </div>
</div>

<script>
(function () {
  const grid       = document.getElementById('kata-grid');
  const modal      = document.getElementById('modal');
  const closeBtn   = document.getElementById('modal-close');
  const iframe     = document.getElementById('m-video');
  const videoBox   = document.getElementById('video-container');
  const titleEl    = document.getElementById('m-title');
  const descEl     = document.getElementById('m-desc');
  const searchEl   = document.getElementById('search');
  const filterBtns = document.querySelectorAll('.filter-btn');

  function openModal(card) {
    const video = card.dataset.video;
    titleEl.textContent = card.dataset.titulo || '';
    descEl.textContent  = card.dataset.descricao || '';

    // Limpa qualquer placeholder anterior
    const old = videoBox.querySelector('.no-video');
    if (old) old.remove();

    if (video) {
      iframe.style.display = 'block';
      iframe.src = video + '?autoplay=1&rel=0';
    } else {
      iframe.style.display = 'none';
      iframe.src = '';
      const ph = document.createElement('div');
      ph.className = 'no-video';
      ph.textContent = 'Vídeo indisponível';
      videoBox.appendChild(ph);
    }
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('open');
    iframe.src = ''; // PARA o vídeo
    document.body.style.overflow = '';
  }

  // Abrir modal ao clicar no card ou no botão
  grid.addEventListener('click', (e) => {
    const card = e.target.closest('.kata-card');
    if (!card) return;
    openModal(card);
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // Filtros
  let currentFilter = 'todos';
  let currentSearch = '';

  function applyFilters() {
    document.querySelectorAll('.kata-card').forEach(card => {
      const nivel = card.dataset.nivel;
      const nome  = card.dataset.nome;
      const okNivel = currentFilter === 'todos' || nivel === currentFilter;
      const okBusca = !currentSearch || nome.includes(currentSearch);
      card.style.display = (okNivel && okBusca) ? '' : 'none';
    });
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.filter;
      applyFilters();
    });
  });

  searchEl.addEventListener('input', (e) => {
    currentSearch = e.target.value.trim().toLowerCase();
    applyFilters();
  });
})();
</script>

</body>
</html>
