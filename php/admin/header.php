<?php
// admin/header.php - Administrative Panel Reusable Shell Header
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';

require_admin();

$admin_nome = $_SESSION['nome'] ?? 'Administrador';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Painel Administrativo' ?> | Oyama Hub</title>
    <link rel="icon" href="../../img/kyokushinicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Admin Styling -->
    <link rel="stylesheet" href="../../css/admin.css">
    
    <script>
        (function() {
            const theme = localStorage.getItem('oyama-theme');
            if (theme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <img src="../../img/kyokushinicon.png" alt="Oyama Hub" class="admin-brand-icon">
            <div class="admin-brand-title">OYAMA <span>HUB</span></div>
        </div>

        <nav class="admin-sidebar-nav">
            <div class="admin-nav-group">Geral</div>
            
            <a href="index.php" class="admin-nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>

            <a href="users.php" class="admin-nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Usuários
            </a>

            <div class="admin-nav-group">Conteúdos & Técnicas</div>

            <a href="katas.php" class="admin-nav-link <?= $current_page === 'katas.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                Katas
            </a>

            <a href="kihons.php" class="admin-nav-link <?= $current_page === 'kihons.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                Kihons
            </a>

            <a href="treinos.php" class="admin-nav-link <?= $current_page === 'treinos.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v8H2z"/><path d="M6 8v8"/><path d="M14 8v8"/></svg>
                Treinos
            </a>

            <a href="exercicios.php" class="admin-nav-link <?= $current_page === 'exercicios.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6.5 6.5h11M6.5 17.5h11M4 12h16"/></svg>
                Exercícios
            </a>

            <a href="faixas.php" class="admin-nav-link <?= $current_page === 'faixas.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 0 0 0 14 7 7 0 0 0 0-14z"/></svg>
                Faixas
            </a>

            <div class="admin-nav-group">Acompanhamento</div>

            <a href="progresso.php" class="admin-nav-link <?= $current_page === 'progresso.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Progresso
            </a>

            <a href="configuracoes.php" class="admin-nav-link <?= $current_page === 'configuracoes.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Configurações
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <img src="../../img/kyokushinicon.png" alt="Admin" class="admin-avatar">
                <div class="admin-user-details">
                    <span class="admin-user-name"><?= htmlspecialchars($admin_nome) ?></span>
                    <span class="admin-user-role">ADMINISTRADOR</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="admin-main">
        <!-- TOPBAR -->
        <header class="admin-topbar">
            <div class="admin-topbar-title">
                <h2><?= $page_title ?? 'Dashboard' ?></h2>
            </div>
            <div class="admin-topbar-actions">
                <a href="../dashboard.php" class="btn-secondary" style="font-size:0.85rem; text-decoration:none;">Ver Área do Aluno</a>
                <button class="btn-icon-toggle" id="themeToggleBtn" title="Alternar Tema">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <a href="../logout.php" class="btn-action delete" title="Sair do Sistema">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </header>

        <div class="admin-content">
