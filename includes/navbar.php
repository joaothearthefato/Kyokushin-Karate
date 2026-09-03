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
   HAMBURGER (hidden on desktop)
========================= */
.navbar-oh-hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    width: 38px;
    height: 38px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 4px;
    cursor: pointer;
    padding: 6px;
    margin-left: auto;
    flex-shrink: 0;
    transition: border-color 0.2s;
}

.navbar-oh-hamburger:hover { border-color: var(--red2); }

.navbar-oh-hamburger span {
    display: block;
    width: 20px;
    height: 2px;
    background: var(--text);
    border-radius: 2px;
    transition: transform 0.25s, opacity 0.25s;
    transform-origin: center;
}

.navbar-oh-hamburger.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar-oh-hamburger.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.navbar-oh-hamburger.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

@media (max-width: 768px) {

    .navbar-oh {
        flex-wrap: nowrap;
        height: 60px;
        padding: 0 16px;
    }

    .navbar-oh-links {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        background: rgba(10,10,10,0.97);
        backdrop-filter: blur(16px);
        flex-direction: column;
        align-items: stretch;
        gap: 0;
        padding: 8px 0;
        border-bottom: 2px solid var(--red);
        transform: translateY(-110%);
        transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
        z-index: 9998;
        box-shadow: 0 16px 40px rgba(0,0,0,0.6);
    }

    .navbar-oh-links.nav-open {
        transform: translateY(0);
    }

    .navbar-oh-links a {
        height: 50px;
        padding: 0 24px;
        border-bottom: 1px solid var(--border);
        font-size: 0.82rem;
        letter-spacing: 2.5px;
    }

    .navbar-oh-links a:last-child { border-bottom: none; }

    .navbar-oh-hamburger {
        display: flex;
    }

    .navbar-oh-right {
        display: none;
    }

    .navbar-oh-mobile-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px 18px;
        border-top: 1px solid var(--border);
    }

    .navbar-oh-mobile-actions .nav-btn-oh {
        flex: 1;
        text-align: center;
        padding: 10px 14px;
        font-size: 0.75rem;
    }

}

.navbar-oh-mobile-actions {
    display: none;
}

body.light-mode .navbar-oh-links {
    background: rgba(255,255,255,0.97);
}


</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;600&display=swap" rel="stylesheet">

<nav class="navbar-oh">

    <a href="<?= $link_dashboard ?>" class="navbar-oh-logo">
        OYAMA <span>HUB</span>
    </a>

    <button class="navbar-oh-hamburger" id="navHamburger" aria-label="Abrir menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="navbar-oh-links" id="navLinks">

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

        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
            <a href="<?= $currentDir === 'dashboard' ? '../php/admin/index.php' : 'admin/index.php' ?>"
               style="color: var(--gold, #d4af37); font-weight: 600;">
               Painel Admin
            </a>
        <?php endif; ?>

        <div class="navbar-oh-mobile-actions">
            <button type="button" class="nav-btn-oh" id="mobileThemeToggle">
                Light Mode
            </button>
            <form action="<?= $link_logout ?>" method="POST" style="margin:0; flex:1;">
                <button type="submit" class="nav-btn-oh" style="width:100%; border-color: var(--red2); color: var(--red2);">
                    Sair
                </button>
            </form>
        </div>

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
const mobileThemeToggle = document.getElementById('mobileThemeToggle');

function applyTheme(theme) {

    const isLight = (theme === 'light');
    const label = isLight ? 'Dark Mode' : 'Light Mode';

    if(isLight) {
        document.documentElement.classList.add('light');
        document.body.classList.add('light-mode');
    } else {
        document.documentElement.classList.remove('light');
        document.body.classList.remove('light-mode');
    }

    if (themeToggle) themeToggle.textContent = label;
    if (mobileThemeToggle) mobileThemeToggle.textContent = label;
}

const savedTheme = localStorage.getItem('oyama-theme');

if(savedTheme) {
    applyTheme(savedTheme);
}

function toggleTheme() {
    const isLight = document.body.classList.contains('light-mode');
    const newTheme = isLight ? 'dark' : 'light';
    localStorage.setItem('oyama-theme', newTheme);
    applyTheme(newTheme);
}

if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
if (mobileThemeToggle) mobileThemeToggle.addEventListener('click', toggleTheme);

// ── Hamburger menu ──
const hamburger = document.getElementById('navHamburger');
const navLinks  = document.getElementById('navLinks');

if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('nav-open');
        hamburger.classList.toggle('is-open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen);
    });

    // Close when a link is clicked
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('nav-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('nav-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });
}

</script>