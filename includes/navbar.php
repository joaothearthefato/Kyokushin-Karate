<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

/* ── Cálculo de paths relativos ── */
if ($currentDir === 'dashboard') {
    $link_dashboard = '../php/dashboard.php';
    $link_katas     = 'katas.php';
    $link_treinos   = 'treinos.php';
    $link_kihons    = 'kihons.php';
    $link_logout    = '../php/logout.php';
    $link_progresso = 'progresso.php';
    $link_anotacoes = 'anotacoes.php';
    $link_perfil    = 'perfil.php';
    $link_admin     = '../php/admin/index.php';
    $css_base       = '../css/';
    $js_a11y        = '../js/acessibilidade.js';
    $js_modal       = '../js/app-modal.js';
} else {
    $link_dashboard = 'dashboard.php';
    $link_katas     = '../dashboard/katas.php';
    $link_treinos   = '../dashboard/treinos.php';
    $link_kihons    = '../dashboard/kihons.php';
    $link_logout    = 'logout.php';
    $link_progresso = '../dashboard/progresso.php';
    $link_anotacoes = '../dashboard/anotacoes.php';
    $link_perfil    = '../dashboard/perfil.php';
    $link_admin     = 'admin/index.php';
    $css_base       = '../css/';
    $js_a11y        = '../js/acessibilidade.js';
    $js_modal       = 'js/app-modal.js';
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $css_base ?>tokens.css">
<link rel="stylesheet" href="<?= $css_base ?>navbar.css">
<link rel="stylesheet" href="<?= $css_base ?>app-modal.css">
<script src="<?= $js_modal ?>"></script>

<nav class="navbar-oh" role="navigation" aria-label="Navegação principal">

    <a href="<?= $link_dashboard ?>" class="navbar-oh-logo" aria-label="Ir para o Dashboard">
        OYAMA <span>HUB</span>
    </a>

    <button
        class="navbar-oh-hamburger"
        id="navHamburger"
        aria-label="Abrir menu de navegação"
        aria-expanded="false"
        aria-controls="navLinks"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="navbar-oh-links" id="navLinks" role="menubar">

        <a href="<?= $link_dashboard ?>"
           role="menuitem"
           class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'dashboard.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="<?= $link_treinos ?>"
           role="menuitem"
           class="<?= $currentPage === 'treinos.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'treinos.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Treinos</span>
        </a>

        <a href="<?= $link_katas ?>"
           role="menuitem"
           class="<?= $currentPage === 'katas.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'katas.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
            <span>Katas</span>
        </a>

        <a href="<?= $link_kihons ?>"
           role="menuitem"
           class="<?= $currentPage === 'kihons.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'kihons.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="8" r="6"></circle>
                <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"></path>
            </svg>
            <span>Kihons</span>
        </a>

        <a href="<?= $link_progresso ?>"
           role="menuitem"
           class="<?= $currentPage === 'progresso.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'progresso.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <span>Progresso</span>
        </a>

        <a href="<?= $link_anotacoes ?>"
           role="menuitem"
           class="<?= $currentPage === 'anotacoes.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'anotacoes.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                <path d="M6 6h10"></path>
                <path d="M6 10h10"></path>
            </svg>
            <span>Anotações</span>
        </a>

        <a href="<?= $link_perfil ?>"
           role="menuitem"
           class="<?= $currentPage === 'perfil.php' ? 'active' : '' ?>"
           aria-current="<?= $currentPage === 'perfil.php' ? 'page' : 'false' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Perfil</span>
        </a>

        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        <a href="<?= $link_admin ?>"
           role="menuitem"
           class="nav-admin <?= $currentPage === 'index.php' && $currentDir === 'admin' ? 'active' : '' ?>">
            <svg class="nav-link-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Admin</span>
        </a>
        <?php endif; ?>

        <!-- Ações móveis (visíveis apenas em telas pequenas) -->
        <div class="navbar-oh-mobile-actions">
            <button type="button" class="nav-theme-btn" id="mobileThemeToggle" aria-label="Alternar tema">
                <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
            <form action="<?= $link_logout ?>" method="POST" style="flex:1; margin:0;">
                <button type="submit" class="nav-logout-btn" style="width:100%;">Sair</button>
            </form>
        </div>

    </div>

    <div class="navbar-oh-right">
        <button type="button" class="nav-theme-btn" id="themeToggle" aria-label="Alternar tema">
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>

        <form action="<?= $link_logout ?>" method="POST" style="margin:0;">
            <button type="submit" class="nav-logout-btn">Sair</button>
        </form>
    </div>

</nav>

<script>
(function () {
    const THEME_KEY = 'oyama-theme';

    const toggleDesktop = document.getElementById('themeToggle');
    const toggleMobile  = document.getElementById('mobileThemeToggle');
    const hamburger     = document.getElementById('navHamburger');
    const navLinks      = document.getElementById('navLinks');

    /* ── Tema ── */
    function applyTheme(theme) {
        const isLight = (theme === 'light');
        document.documentElement.classList.toggle('light', isLight);
        document.body.classList.toggle('light-mode', isLight);
        localStorage.setItem(THEME_KEY, theme);
    }

    function toggleTheme() {
        const current = localStorage.getItem(THEME_KEY) || 'dark';
        applyTheme(current === 'light' ? 'dark' : 'light');
    }

    /* Aplicar tema salvo */
    const saved = localStorage.getItem(THEME_KEY);
    if (saved) applyTheme(saved);

    if (toggleDesktop) toggleDesktop.addEventListener('click', toggleTheme);
    if (toggleMobile)  toggleMobile.addEventListener('click', toggleTheme);

    /* ── Hamburger ── */
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('nav-open');
            hamburger.classList.toggle('is-open', isOpen);
            hamburger.setAttribute('aria-expanded', String(isOpen));
        });

        /* Fechar ao clicar em um link */
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        /* Fechar ao clicar fora */
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                closeMenu();
            }
        });

        /* Fechar com Escape */
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMenu();
        });

        function closeMenu() {
            navLinks.classList.remove('nav-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    }
})();
</script>

<script>
(function() {
    var s = document.createElement('script');
    s.src = '<?= $js_a11y ?>';
    s.defer = true;
    document.body.appendChild(s);
})();
</script>