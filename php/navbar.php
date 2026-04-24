<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

if ($currentDir === 'dashboard') {
    $link_dashboard = '../php/dashboard.php';
    $link_katas     = 'katas.php';
    $link_treinos   = 'treinos.php';
    $link_kihons    = 'kihons.php';
    $link_logout    = '../php/logout.php';
    $link_progresso = 'progresso.php';
    $link_perfil    = 'perfil.php';
} else {
    $link_dashboard = 'dashboard.php';
    $link_katas     = '../dashboard/katas.php';
    $link_treinos   = '../dashboard/treinos.php';
    $link_kihons    = '../dashboard/kihons.php';
    $link_logout    = 'logout.php';
    $link_progresso = '../dashboard/progresso.php';
    $link_perfil    = '../dashboard/perfil.php';
}
?>
<nav class="navbar">
  <a href="<?= $link_dashboard ?>" class="navbar-brand">OYAMA<span>HUB</span></a>
  <div class="nav-links">
    <a href="<?= $link_dashboard ?>" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Início</a>
    <a href="<?= $link_katas ?>" class="<?= $currentPage == 'katas.php' ? 'active' : '' ?>">Katas</a>
    <a href="<?= $link_kihons ?>" class="<?= $currentPage == 'kihon.php' ? 'active' : '' ?>">Kihon</a>
    <a href="<?= $link_treinos ?>" class="<?= $currentPage == 'treinos.php' ? 'active' : '' ?>">Treinos</a>
    <a href="<?= $link_progresso ?>" class="<?= $currentPage == 'progresso.php' ? 'active' : '' ?>">Progresso</a>
    <a href="<?= $link_perfil ?>" class="<?= $currentPage == 'perfil.php' ? 'active' : '' ?>">Perfil</a>
  </div>
  <button id="theme-toggle" class="theme-btn" aria-label="Alternar tema">
    <span class="theme-icon">☀️</span>
    <span class="theme-label">Light</span>
  </button>
  <a href="<?= $link_logout ?>" class="logout-btn">Logout</a>
</nav>
<script>
const themeToggle = document.getElementById('theme-toggle');
if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('light');
    document.body.classList.toggle('light-mode');
    const isLight = document.documentElement.classList.contains('light');
    localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    updateThemeIcon();
  });
}

function updateThemeIcon() {
  const themeToggle = document.getElementById('theme-toggle');
  if (!themeToggle) return;
  const icon = themeToggle.querySelector('.theme-icon');
  const label = themeToggle.querySelector('.theme-label');
  const isLight = document.documentElement.classList.contains('light');
  if (icon) icon.textContent = isLight ? '🌙' : '☀️';
  if (label) label.textContent = isLight ? 'Dark' : 'Light';
}

// On load
const savedTheme = localStorage.getItem('oyama-theme') || localStorage.getItem('theme');
if (savedTheme === 'light') {
  document.documentElement.classList.add('light');
  document.body.classList.add('light-mode');
} else {
  document.documentElement.classList.remove('light');
  document.body.classList.remove('light-mode');
}
updateThemeIcon();
</script>