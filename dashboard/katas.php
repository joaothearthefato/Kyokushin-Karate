<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../php/login.php');
    exit;
}

require_once('../php/config.php');
$conexao = $conn;
$usuario_id = $_SESSION['id'];

function youtubeEmbed(?string $url): string {
    if (!$url) return '';
    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~',
        '~youtube\.com/watch\?v=([A-Za-z0-9_-]{11})~',
        '~youtube\.com/embed/([A-Za-z0-9_-]{11})~',
        '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $url, $m)) return 'https://www.youtube.com/embed/' . $m[1];
    }
    return '';
}

function get_yt_id(?string $url): string {
    if (!$url) return '';
    if (preg_match('/(?:v=|youtu\.be\/|\/embed\/|\/shorts\/)([A-Za-z0-9_\-]{11})/', $url, $m)) {
        return $m[1];
    }
    return '';
}

$sql = 'SELECT * FROM katas ORDER BY ordem ASC';
$result = $conexao->query($sql);

$katas_db = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $katas_db[] = $row;
}

// Buscar progresso do usuário para katas
$concluidos = [];
$sql_prog = "SELECT referencia_id FROM progresso WHERE usuario_id = $usuario_id AND tipo = 'kata' AND concluido = 1";
$r_prog = $conexao->query($sql_prog);
if ($r_prog) {
    while ($p = $r_prog->fetch_assoc()) $concluidos[] = $p['referencia_id'];
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
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@300;400;500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/katas.css">
</head>
<body>

<!-- Navbar -->
 <?php include '../includes/navbar.php'; ?>

<!-- HERO -->
<section class="page-hero">
  <span class="hero-tag">Kyokushin Karate</span>
  <h1>KATAS</h1>
  <p>Formas codificadas de combate. Cada kata é um diálogo com os fundadores do estilo.</p>
</section>

<!-- CONTROLS -->
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
      $vid_id = get_yt_id($kata['video_url'] ?? '');
      $thumb = $vid_id ? "https://img.youtube.com/vi/{$vid_id}/mqdefault.jpg" : '../img/oyama2.jpg';
      $has_vid = (bool)$vid_id;
  ?>
    <article class="kata-card"
             data-nivel="<?= htmlspecialchars($kata['nivel']) ?>"
             data-nome="<?= htmlspecialchars(strtolower($kata['nome'])) ?>"
             data-video="<?= htmlspecialchars($embed) ?>"
             data-titulo="<?= htmlspecialchars($kata['nome']) ?>"
             data-descricao="<?= htmlspecialchars($kata['descricao']) ?>"
             data-id="<?= $kata['id'] ?>">

      <!-- Zona da Capa de Vídeo do Kata (estilo Kihon) -->
      <div class="card-video-zone">
        <img class="yt-thumb"
             src="<?= $thumb ?>"
             alt="<?= htmlspecialchars($kata['nome']) ?>"
             loading="lazy">

        <div class="play-btn">
          <div class="play-icon">
            <?php if ($has_vid): ?>
              <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
              </svg>
            <?php endif; ?>
          </div>
          <span class="play-label"><?= $has_vid ? 'Ver execução' : 'Em breve' ?></span>
        </div>

        <span class="level-badge level-<?= htmlspecialchars($kata['nivel']) ?>">
          <?= ucfirst(htmlspecialchars($kata['nivel'])) ?>
        </span>

        <?php if (in_array($kata['id'], $concluidos)): ?>
          <span class="kata-concluido-badge">✓ Concluído</span>
        <?php endif; ?>
      </div>

      <div class="kata-card-inner">
        <span class="kata-number"><?= str_pad($kata['ordem'], 2, '0', STR_PAD_LEFT) ?></span>
        <h3 class="kata-name"><?= htmlspecialchars($kata['nome']) ?></h3>
        <p class="kata-desc"><?= htmlspecialchars($kata['descricao']) ?></p>
        <div class="kata-actions">
          <button class="kata-btn" onclick="event.stopPropagation(); openKataModal(this.closest('.kata-card'))">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            Assistir Kata
          </button>
          <button class="btn-concluir <?= in_array($kata['id'], $concluidos) ? 'concluido' : '' ?>"
                  data-id="<?= $kata['id'] ?>"
                  data-tipo="kata"
                  onclick="event.stopPropagation(); toggleConcluido(this)">
            <?= in_array($kata['id'], $concluidos) ? '✓ Concluído' : 'Marcar como Concluído' ?>
          </button>
        </div>
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

  // Expor globalmente para uso no onclick
  window.openKataModal = function(card) {
    const video = card.dataset.video;
    titleEl.textContent = card.dataset.titulo || '';
    descEl.textContent  = card.dataset.descricao || '';

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
  };

  function closeModal() {
    modal.classList.remove('open');
    iframe.src = '';
    document.body.style.overflow = '';
  }

  grid.addEventListener('click', (e) => {
    // Não abrir modal ao clicar nos botões de ação
    if (e.target.closest('.btn-concluir') || e.target.closest('.kata-btn')) return;
    const card = e.target.closest('.kata-card');
    if (!card) return;
    openKataModal(card);
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

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

// ── Toggle conclusão via AJAX ──
function toggleConcluido(btn) {
  const id   = btn.dataset.id;
  const tipo = btn.dataset.tipo;

  const fd = new FormData();
  fd.append('referencia_id', id);
  fd.append('tipo', tipo);

  fetch('toggle_progresso.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) return;
      const card = btn.closest('.kata-card');
      const videoZone = card.querySelector('.card-video-zone');
      let badge = card.querySelector('.kata-concluido-badge');
      if (data.concluido) {
        btn.textContent = '✓ Concluído';
        btn.classList.add('concluido');
        if (!badge && videoZone) {
          badge = document.createElement('span');
          badge.className = 'kata-concluido-badge';
          badge.textContent = '✓ Concluído';
          videoZone.appendChild(badge);
        }
        if (window.AppModal) {
          AppModal.toast({ message: 'Kata marcado como concluído! OSU!', type: 'success' });
        }
      } else {
        btn.textContent = 'Marcar como Concluído';
        btn.classList.remove('concluido');
        if (badge) badge.remove();
        if (window.AppModal) {
          AppModal.toast({ message: 'Progresso do Kata desmarcado.', type: 'info' });
        }
      }
    })
    .catch(() => {
      if (window.AppModal) {
        AppModal.alert({ title: 'Aviso', message: 'Erro ao atualizar progresso do kata.', type: 'error' });
      } else {
        alert('Erro ao atualizar progresso.');
      }
    });
}
</script>

</body>
</html>
