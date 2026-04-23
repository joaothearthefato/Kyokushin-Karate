<?php
session_start();
include_once __DIR__ . '/../php/config.php';

// ── Redireciona se não autenticado ──────────────────────────────
if (empty($_SESSION['id'])) {
    header('Location: ../index.html');
    exit;
}

/**
 * Extrai o Video ID de qualquer formato de URL do YouTube.
 * Suporta: watch?v=, youtu.be/, /embed/
 */
function yt_id(string $url): string {
    if (preg_match('/(?:v=|youtu\.be\/|\/embed\/)([A-Za-z0-9_\-]{11})/', $url, $m)) {
        return $m[1];
    }
    return '';
}

// ── Busca categorias + kihons do banco ─────────────────────────
$categorias = [];

$sql = "
    SELECT
        c.id        AS cat_id,
        c.slug,
        c.nome      AS cat_nome,
        c.kanji,
        c.cor,
        c.numero,
        k.id        AS kihon_id,
        k.nome,
        k.romaji,
        k.kana,
        k.descricao,
        k.video_url,
        k.nivel
    FROM kihon_categorias c
    LEFT JOIN kihons k ON k.categoria_id = c.id
    ORDER BY c.numero, k.ordem
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cid = $row['cat_id'];
        if (!isset($categorias[$cid])) {
            $categorias[$cid] = [
                'slug'   => $row['slug'],
                'nome'   => $row['cat_nome'],
                'kanji'  => $row['kanji'],
                'cor'    => $row['cor'],
                'numero' => str_pad($row['numero'], 2, '0', STR_PAD_LEFT),
                'kihons' => [],
            ]; 
        }
        if ($row['kihon_id']) {
            // Extrai o vídeo ID do URL
            $video_id = yt_id($row['video_url'] ?? '');
            $categorias[$cid]['kihons'][] = [
                'id'        => $row['kihon_id'],
                'nome'      => htmlspecialchars($row['nome']),
                'romaji'    => htmlspecialchars($row['romaji']),
                'kana'      => htmlspecialchars($row['kana']),
                'descricao' => htmlspecialchars($row['descricao']),
                'video_url' => $row['video_url'],
                'video_id'  => $video_id,
                'nivel'     => $row['nivel'],
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kihon | Oyama Hub</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Noto+Sans+JP:wght@300;400;700&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="../css/kihon.css">
</head>
<body>

<!-- ── Navbar ── -->
<section class="navbarArea">
  <div class="header">
    <a href="../php/dashboard.php">Início</a>
    <a href="progresso.php">Progresso</a>
    <a href="katas.php">Katas</a>
    <a href="kihon.php" class="active">Kihon</a>
    <a href="treinos.php">Treinos</a>
    <button id="theme-toggle" class="theme-btn" aria-label="Alternar tema">
        <span class="theme-icon">☀️</span>
        <span class="theme-label">Light</span>
    </button>
    <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
  </div>
</section>

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

<!-- ── Hero ── -->
<header class="hero">
  <div class="hero-tag">基本 · Fundamentos</div>
  <h1>KIHON DO<br><em>KYOKUSHIN</em></h1>
  <p>Os fundamentos que formam a base de todo karateka. Domine cada técnica antes de avançar — <em>kihon</em> é o alicerce da excelência.</p>
</header>

<!-- ── Search Bar ── -->
<div class="search-container">
  <div class="search-wrapper">
    <input type="text" id="search-input" placeholder="Buscar técnica por nome..." autocomplete="off">
    <button id="clear-search" class="clear-btn" title="Limpar busca">✕</button>
    <div class="search-icon">🔍</div>
  </div>
  <div id="search-results" class="search-results"></div>
</div>

<!-- ── Category nav ── -->
<div class="cat-nav">
  <?php foreach ($categorias as $cat): ?>
    <button class="cat-btn" onclick="scrollToSection('<?= $cat['slug'] ?>')">
      <?= $cat['kanji'] . ' · ' . $cat['nome'] ?>
    </button>
  <?php endforeach; ?>
</div>

<!-- ── Main ── -->
<main>
  <?php $i = 0; foreach ($categorias as $cat): $i++; ?>

    <?php if ($i > 1): ?><div class="divider"></div><?php endif; ?>

    <section class="kihon-section" id="<?= $cat['slug'] ?>">
      <div class="section-header">
        <div class="section-number"><?= $cat['numero'] ?></div>
        <div class="section-info">
          <div class="section-kanji"><?= $cat['kanji'] ?></div>
          <h2 class="section-title"><?= strtoupper($cat['nome']) ?></h2>
        </div>
      </div>

      <div class="cards-grid">
        <?php foreach ($cat['kihons'] as $k):
          $vid_id  = $k['video_id']; // Já extraído no PHP
          $thumb   = $vid_id
            ? "https://img.youtube.com/vi/{$vid_id}/mqdefault.jpg"
            : '../img/kihon-placeholder.jpg';
          $has_vid = (bool)$vid_id;
        ?>
        <div class="tech-card"
             style="--card-accent:<?= $cat['cor'] ?>"
             data-video="<?= $vid_id ?>"
             data-nome="<?= $k['nome'] ?>"
             data-romaji="<?= $k['romaji'] ?>"
             data-desc="<?= $k['descricao'] ?>"
             onclick="openModal(this)">

          <div class="card-video-zone">
            <img class="yt-thumb"
                 src="<?= $thumb ?>"
                 alt="<?= $k['romaji'] ?>"
                 loading="lazy">

            <div class="play-btn">
              <div class="play-icon">
                <?php if ($has_vid): ?>
                  <!-- play triangle -->
                  <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                <?php else: ?>
                  <!-- clock (em breve) -->
                  <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
                  </svg>
                <?php endif; ?>
              </div>
              <span class="play-label"><?= $has_vid ? 'Ver vídeo' : 'Em breve' ?></span>
            </div>

            <span class="card-kana"><?= $k['kana'] ?></span>
            <span class="level-badge <?= $k['nivel'] ?>">
              <?= ucfirst($k['nivel']) ?>
            </span>
          </div>

          <div class="card-body">
            <div class="card-romaji"><?= $k['romaji'] ?></div>
            <div class="card-name"><?= $k['nome'] ?></div>
            <div class="card-desc"><?= $k['descricao'] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

  <?php endforeach; ?>
</main>

<footer>
  <p>© 2026 Oyama Hub · Kyokushin Karate · <em>OSU!</em></p>
</footer>

<button id="scroll-top" title="Voltar ao topo" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>

<!-- ── Modal ── -->
<div class="modal-overlay" id="modal" onclick="closeOnBackdrop(event)">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title-group">
        <div class="modal-romaji" id="modal-romaji"></div>
        <div class="modal-name"   id="modal-name"></div>
      </div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-video">
      <iframe id="modal-iframe"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
              referrerpolicy="strict-origin-when-cross-origin">
      </iframe>
    </div>
    <p class="modal-desc" id="modal-desc"></p>
  </div>
</div>

<script>
/* ── Modal ──────────────────────────────────────────── */
const modal  = document.getElementById('modal');
const iframe = document.getElementById('modal-iframe');

function openModal(card) {
  const vid = card.dataset.video;
  if (!vid) return; // sem vídeo ainda

  document.getElementById('modal-romaji').textContent = card.dataset.romaji;
  document.getElementById('modal-name').textContent   = card.dataset.nome;
  document.getElementById('modal-desc').textContent   = card.dataset.desc;

  iframe.src = `https://www.youtube.com/embed/${vid}?autoplay=1&rel=0`;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  modal.classList.remove('open');
  iframe.src = '';                    // para o vídeo ao fechar
  document.body.style.overflow = '';
}

function closeOnBackdrop(e) {
  if (e.target === modal) closeModal();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

/* ── Scroll-to-top ──────────────────────────────────── */
const topBtn = document.getElementById('scroll-top');
window.addEventListener('scroll', () => topBtn.classList.toggle('show', scrollY > 400));

/* ── Smooth scroll para seção ───────────────────────── */
function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const offset = el.getBoundingClientRect().top + scrollY - 130;
  window.scrollTo({ top: offset, behavior: 'smooth' });
}

/* ── Cat-nav highlight ───────────────────────────────── */
const sections = document.querySelectorAll('.kihon-section');
const catBtns  = document.querySelectorAll('.cat-btn');

const navObserver = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const id = e.target.id;
      catBtns.forEach(b => {
        const match = b.getAttribute('onclick')?.includes(`'${id}'`);
        b.classList.toggle('active', match);
      });
    }
  });
}, { threshold: 0.25 });

sections.forEach(s => navObserver.observe(s));

/* ── Reveal sections ─────────────────────────────────── */
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.08 });

sections.forEach(s => revealObs.observe(s));

/* ── Stagger cards ───────────────────────────────────── */
const cardObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    e.target.querySelectorAll('.tech-card')
      .forEach((c, i) => setTimeout(() => c.classList.add('visible'), i * 80));
    cardObs.unobserve(e.target);
  });
}, { threshold: 0.05 });

document.querySelectorAll('.cards-grid').forEach(g => cardObs.observe(g));

/* ── Active nav link ─────────────────────────────────── */
const page = location.pathname.split('/').pop();
document.querySelectorAll('.header a').forEach(a => {
  a.classList.toggle('active', a.getAttribute('href').split('/').pop() === page);
});

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

/* ── Search Functionality ───────────────────────────────────── */
const searchInput = document.getElementById('search-input');
const clearBtn = document.getElementById('clear-search');
const searchResults = document.getElementById('search-results');
const techCards = document.querySelectorAll('.tech-card');
const kihonSections = document.querySelectorAll('.kihon-section');

// Create searchable data array
let searchableData = [];
techCards.forEach(card => {
  const nome = card.dataset.nome || '';
  const romaji = card.dataset.romaji || '';
  const desc = card.dataset.desc || '';
  const categoryEl = card.closest('.kihon-section').querySelector('.section-title');
  const category = categoryEl ? categoryEl.textContent : '';

  searchableData.push({
    element: card,
    nome: nome.toLowerCase(),
    romaji: romaji.toLowerCase(),
    desc: desc.toLowerCase(),
    category: category.toLowerCase(),
    fullText: `${nome} ${romaji} ${desc} ${category}`.toLowerCase()
  });
});

// Search function
function performSearch(query) {
  const q = query.toLowerCase().trim();

  if (q === '') {
    // Show all cards and sections
    techCards.forEach(card => card.style.display = '');
    kihonSections.forEach(section => section.style.display = '');
    searchResults.style.display = 'none';
    clearBtn.style.display = 'none';
    return;
  }

  clearBtn.style.display = 'block';
  searchResults.innerHTML = '';

  let visibleCards = 0;
  let resultsHTML = '';

  searchableData.forEach(item => {
    const score = getSearchScore(q, item);
    if (score > 0) {
      item.element.style.display = '';
      visibleCards++;

      // Add to dropdown results
      const categoryEl = item.element.closest('.kihon-section').querySelector('.section-kanji');
      const categoryKanji = categoryEl ? categoryEl.textContent : '';

      resultsHTML += `
        <div class="search-result-item" onclick="scrollToCard('${item.element.dataset.nome}')">
          <div class="search-result-name">${item.element.dataset.nome}</div>
          <div class="search-result-romaji">${item.element.dataset.romaji}</div>
          <div class="search-result-category">${categoryKanji} · ${item.element.closest('.kihon-section').querySelector('.section-title').textContent}</div>
        </div>
      `;
    } else {
      item.element.style.display = 'none';
    }
  });

  // Hide sections with no visible cards
  kihonSections.forEach(section => {
    const visibleCardsInSection = section.querySelectorAll('.tech-card[style*="display: block"], .tech-card:not([style*="display"])').length;
    section.style.display = visibleCardsInSection > 0 ? '' : 'none';
  });

  if (resultsHTML) {
    searchResults.innerHTML = resultsHTML;
    searchResults.style.display = 'block';
  } else {
    searchResults.innerHTML = '<div class="search-result-item">Nenhuma técnica encontrada</div>';
    searchResults.style.display = 'block';
  }
}

// Calculate search relevance score
function getSearchScore(query, item) {
  let score = 0;
  const words = query.split(' ');

  words.forEach(word => {
    if (item.nome.includes(word)) score += 10;
    if (item.romaji.includes(word)) score += 8;
    if (item.category.includes(word)) score += 5;
    if (item.desc.includes(word)) score += 3;
  });

  return score;
}

// Scroll to specific card
function scrollToCard(cardName) {
  const card = Array.from(techCards).find(c => c.dataset.nome === cardName);
  if (card) {
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    // Highlight the card temporarily
    card.style.boxShadow = '0 0 30px var(--red-glow)';
    setTimeout(() => {
      card.style.boxShadow = '';
    }, 2000);
  }
  searchInput.value = '';
  searchResults.style.display = 'none';
  clearBtn.style.display = 'none';
}

// Event listeners
searchInput.addEventListener('input', (e) => {
  performSearch(e.target.value);
});

searchInput.addEventListener('focus', () => {
  if (searchInput.value.trim()) {
    searchResults.style.display = 'block';
  }
});

searchInput.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    searchInput.blur();
    searchResults.style.display = 'none';
  } else if (e.key === 'Enter') {
    const firstResult = searchResults.querySelector('.search-result-item');
    if (firstResult) {
      firstResult.click();
    }
  }
});

clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  performSearch('');
  searchInput.focus();
});

// Close search results when clicking outside
document.addEventListener('click', (e) => {
  if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
    searchResults.style.display = 'none';
  }
});
</script>
</body>
</html>