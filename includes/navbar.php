<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

if ($currentDir === 'dashboard') {
    $link_dashboard = '../php/dashboard.php';
    $link_katas     = 'katas.php';
    $link_treinos   = 'treinos.php';
    $link_kihons    = 'kihons.php';
    $link_logout    = '../php/logout.php';
    $link_progresso = 'progresso.php';
    $link_perfil    = 'perfil.php';
    $link_anotacoes = 'anotacoes.php';
} else {
    $link_dashboard = 'dashboard.php';
    $link_katas     = '../dashboard/katas.php';
    $link_treinos   = '../dashboard/treinos.php';
    $link_kihons    = '../dashboard/kihons.php';
    $link_logout    = 'logout.php';
    $link_progresso = '../dashboard/progresso.php';
    $link_perfil    = '../dashboard/perfil.php';
    $link_anotacoes = '../dashboard/anotacoes.php';
}
?>

<style>

/* =========================
   THEME VARIABLES
========================= */

:root {

    --bg: #0a0a0a;
    --surface: #111111;
    --border: rgba(255,255,255,0.07);

    --text: #f0f0f0;
    --muted: #888;

    --red: #c0392b;
    --red2: #e74c3c;

}

body.light-mode {

    --bg: #f4f4f4;
    --surface: #ffffff;
    --border: rgba(0,0,0,0.08);

    --text: #111111;
    --muted: #555;

    background: var(--bg);
    color: var(--text);
}

/* =========================
   NAVBAR
========================= */

.navbar-oh {

    position: sticky;
    top: 0;
    z-index: 9999;

    height: 70px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 32px;

    background: rgba(10,10,10,0.92);

    backdrop-filter: blur(12px);

    border-bottom: 1px solid var(--border);
}

body.light-mode .navbar-oh {
    background: rgba(255,255,255,0.9);
}

.navbar-oh * {
    box-sizing: border-box;
}

.navbar-oh-logo {

    text-decoration: none;

    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.8rem;

    letter-spacing: 4px;

    color: var(--text);
}

.navbar-oh-logo span {
    color: var(--red2);
}

/* =========================
   LINKS
========================= */

.navbar-oh-links {

    display: flex;
    align-items: center;

    gap: 5px;
}

.navbar-oh-links a {

    height: 70px;

    display: flex;
    align-items: center;

    padding: 0 18px;

    text-decoration: none;

    font-family: 'Oswald', sans-serif;
    font-size: 0.78rem;

    letter-spacing: 2px;
    text-transform: uppercase;

    color: var(--muted);

    border-bottom: 2px solid transparent;

    transition: 0.25s ease;
}

.navbar-oh-links a:hover {

    color: var(--text);

    border-bottom-color: rgba(231,76,60,0.4);
}

.navbar-oh-links a.active {

    color: var(--red2);

    border-bottom-color: var(--red2);
}

/* =========================
   RIGHT SIDE
========================= */

.navbar-oh-right {

    display: flex;
    align-items: center;

    gap: 10px;
}

/* =========================
   BUTTONS
========================= */

.nav-btn-oh {

    background: transparent;

    border: 1px solid #444;

    color: var(--muted);

    padding: 8px 16px;

    cursor: pointer;

    font-family: 'Oswald', sans-serif;
    font-size: 0.72rem;

    letter-spacing: 2px;
    text-transform: uppercase;

    transition: 0.25s ease;
}

.nav-btn-oh:hover {

    border-color: var(--red2);

    color: var(--red2);
}

/* =========================
   MOBILE
========================= */

@media (max-width: 768px) {

    .navbar-oh {

        height: auto;

        flex-direction: column;

        gap: 12px;

        padding: 16px;
    }

    .navbar-oh-links {

        flex-wrap: wrap;

        justify-content: center;
    }

    .navbar-oh-links a {

        height: auto;

        padding: 8px 10px;
    }

}

</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;600&display=swap" rel="stylesheet">

<nav class="navbar-oh">

    <a href="<?= $link_dashboard ?>" class="navbar-oh-logo">
        OYAMA <span>HUB</span>
    </a>

    <div class="navbar-oh-links">

        <a href="<?= $link_dashboard ?>"
           class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
           Dashboard
        </a>

        <a href="<?= $link_katas ?>"
           class="<?= $currentPage == 'katas.php' ? 'active' : '' ?>">
           Katas
        </a>

        <a href="<?= $link_treinos ?>"
           class="<?= $currentPage == 'treinos.php' ? 'active' : '' ?>">
           Treinos
        </a>

        <a href="<?= $link_kihons ?>"
           class="<?= $currentPage == 'kihons.php' ? 'active' : '' ?>">
           Kihons
        </a>

        <a href="<?= $link_progresso ?>"
           class="<?= $currentPage == 'progresso.php' ? 'active' : '' ?>">
           Progresso
        </a>

        <a href="<?= $link_perfil ?>"
           class="<?= $currentPage == 'perfil.php' ? 'active' : '' ?>">
           Perfil
        </a>

        <a href="<?= $link_anotacoes ?>"
           class="<?= $currentPage == 'anotacoes.php' ? 'active' : '' ?>">
           Anotações
        </a>

    </div>

    <div class="navbar-oh-right">

        <button class="nav-btn-oh" id="themeToggle">
            Light Mode
        </button>

        <form action="<?= $link_logout ?>" method="POST">

            <button type="submit" class="nav-btn-oh">
                Sair
            </button>

        </form>

    </div>

</nav>

<script>

const themeToggle = document.getElementById('themeToggle');

function applyTheme(theme) {

    if(theme === 'light') {

        document.documentElement.classList.add('light');
        document.body.classList.add('light-mode');
        themeToggle.textContent = 'Dark Mode';

    } else {

        document.documentElement.classList.remove('light');
        document.body.classList.remove('light-mode');
        themeToggle.textContent = 'Light Mode';

    }

}

const savedTheme = localStorage.getItem('oyama-theme');

if(savedTheme) {
    applyTheme(savedTheme);
}

themeToggle.addEventListener('click', () => {

    const isLight = document.body.classList.contains('light-mode');

    if(isLight) {

        localStorage.setItem('oyama-theme', 'dark');
        applyTheme('dark');

    } else {

        localStorage.setItem('oyama-theme', 'light');
        applyTheme('light');

    }

});

</script>