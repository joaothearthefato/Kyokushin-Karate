<?php
require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/csrf.php';

// Validação de Sessão
if (!isset($_SESSION['id'])) {
    header('Location: ../php/login.php');
    exit();
}

$usuario_id = intval($_SESSION['id']);
$usuario_nome = $_SESSION['nome'] ?? 'Praticante';

// Mapa de Categorias e Cores
$categorias_config = [
    'Tática'     => ['cor' => 'red',    'label' => 'Tática',     'icon' => '⚔️'],
    'Dica'       => ['cor' => 'gold',   'label' => 'Dica',       'icon' => '💡'],
    'Meta'       => ['cor' => 'green',  'label' => 'Meta',       'icon' => '🎯'],
    'Observação' => ['cor' => 'blue',   'label' => 'Observação', 'icon' => '📋'],
    'Geral'      => ['cor' => 'purple', 'label' => 'Geral',      'icon' => '📝'],
];

// Processamento do CRUD via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();

    $action    = $_POST['action'] ?? '';
    $id        = intval($_POST['id'] ?? 0);
    $titulo    = trim($_POST['titulo'] ?? '');
    $conteudo  = trim($_POST['conteudo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Geral');

    if (!array_key_exists($categoria, $categorias_config)) {
        $categoria = 'Geral';
    }
    $cor = $categorias_config[$categoria]['cor'];

    if ($action === 'criar') {
        if ($titulo !== '' && $conteudo !== '') {
            $stmt = mysqli_prepare($conn, "INSERT INTO anotacoes (usuario_id, titulo, conteudo, categoria, cor) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issss", $usuario_id, $titulo, $conteudo, $categoria, $cor);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: anotacoes.php?sucesso=criado');
                exit();
            } else {
                header('Location: anotacoes.php?erro=falha_salvar');
                exit();
            }
        } else {
            header('Location: anotacoes.php?erro=campos_obrigatorios');
            exit();
        }
    } elseif ($action === 'editar' && $id > 0) {
        if ($titulo !== '' && $conteudo !== '') {
            $stmt = mysqli_prepare($conn, "UPDATE anotacoes SET titulo = ?, conteudo = ?, categoria = ?, cor = ? WHERE id = ? AND usuario_id = ?");
            mysqli_stmt_bind_param($stmt, "ssssii", $titulo, $conteudo, $categoria, $cor, $id, $usuario_id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: anotacoes.php?sucesso=editado');
                exit();
            } else {
                header('Location: anotacoes.php?erro=falha_atualizar');
                exit();
            }
        } else {
            header('Location: anotacoes.php?erro=campos_obrigatorios');
            exit();
        }
    } elseif ($action === 'excluir' && $id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM anotacoes WHERE id = ? AND usuario_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: anotacoes.php?sucesso=excluido');
            exit();
        } else {
            header('Location: anotacoes.php?erro=falha_excluir');
            exit();
        }
    }
}

// Parâmetros de Filtro/Busca (Server-side inicial)
$busca_query = trim($_GET['q'] ?? '');
$filtro_cat  = trim($_GET['cat'] ?? '');

// Buscar Anotações do Usuário
$sql = "SELECT id, titulo, conteudo, categoria, cor, criado_em, atualizado 
        FROM anotacoes 
        WHERE usuario_id = ?";
$params = [$usuario_id];
$types  = "i";

if ($filtro_cat !== '' && array_key_exists($filtro_cat, $categorias_config)) {
    $sql .= " AND categoria = ?";
    $params[] = $filtro_cat;
    $types   .= "s";
}

if ($busca_query !== '') {
    $sql .= " AND (titulo LIKE ? OR conteudo LIKE ?)";
    $termo = '%' . $busca_query . '%';
    $params[] = $termo;
    $params[] = $termo;
    $types   .= "ss";
}

$sql .= " ORDER BY atualizado DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $anotacoes = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $anotacoes[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    $anotacoes = [];
}

// Contagem total e por categoria (para as badges dos filtros)
$totais_cat = ['Todas' => 0, 'Tática' => 0, 'Dica' => 0, 'Meta' => 0, 'Observação' => 0, 'Geral' => 0];
$stmt_count = mysqli_prepare($conn, "SELECT categoria, COUNT(*) as qtd FROM anotacoes WHERE usuario_id = ? GROUP BY categoria");
if ($stmt_count) {
    mysqli_stmt_bind_param($stmt_count, "i", $usuario_id);
    mysqli_stmt_execute($stmt_count);
    $res_count = mysqli_stmt_get_result($stmt_count);
    while ($c = mysqli_fetch_assoc($res_count)) {
        $cat_nome = $c['categoria'];
        if (isset($totais_cat[$cat_nome])) {
            $totais_cat[$cat_nome] = intval($c['qtd']);
        }
        $totais_cat['Todas'] += intval($c['qtd']);
    }
    mysqli_stmt_close($stmt_count);
}

// Mensagens de Feedback
$mensagem_sucesso = '';
$mensagem_erro = '';
if (isset($_GET['sucesso'])) {
    if ($_GET['sucesso'] === 'criado') $mensagem_sucesso = 'Anotação registrada com sucesso no diário!';
    elseif ($_GET['sucesso'] === 'editado') $mensagem_sucesso = 'Anotação atualizada com sucesso!';
    elseif ($_GET['sucesso'] === 'excluido') $mensagem_sucesso = 'Anotação removida com sucesso!';
} elseif (isset($_GET['erro'])) {
    if ($_GET['erro'] === 'campos_obrigatorios') $mensagem_erro = 'Por favor, preencha o título e o conteúdo da anotação.';
    else $mensagem_erro = 'Ocorreu um erro ao processar a operação. Tente novamente.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diário de Treino & Anotações | Oyama Hub</title>
    <link rel="icon" href="../img/kyokushinicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/tokens.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/anotacoes.css">
    <script>
        (function() {
            const t = localStorage.getItem('oyama-theme');
            if (t === 'light') {
                document.documentElement.classList.add('light');
                document.body?.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<main class="anot-container">

    <!-- HERO / CABEÇALHO -->
    <header class="anot-hero">
        <div class="anot-hero-text">
            <div class="anot-hero-badge">
                <span class="anot-badge-dot"></span>
                <span>DOJO JOURNAL • KYOKUSHIN</span>
            </div>
            <h1 class="anot-hero-title">DIÁRIO DE TREINO <span>& ANOTAÇÕES</span></h1>
            <p class="anot-hero-subtitle">
                Registre percepções dos treinos, correções de Kata, táticas de Kumite e metas para a sua evolução marcial.
            </p>
        </div>

        <button type="button" class="btn-anot-primary" id="btnAbrirModalNova" aria-haspopup="dialog">
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nova Anotação</span>
        </button>
    </header>

    <!-- FEEDBACK TOAST / ALERTS -->
    <?php if ($mensagem_sucesso): ?>
        <div class="anot-alert anot-alert-success" role="alert">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            <span><?= htmlspecialchars($mensagem_sucesso) ?></span>
            <button type="button" class="anot-alert-close" onclick="this.parentElement.remove()" aria-label="Fechar mensagem">✕</button>
        </div>
    <?php endif; ?>

    <?php if ($mensagem_erro): ?>
        <div class="anot-alert anot-alert-error" role="alert">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <span><?= htmlspecialchars($mensagem_erro) ?></span>
            <button type="button" class="anot-alert-close" onclick="this.parentElement.remove()" aria-label="Fechar mensagem">✕</button>
        </div>
    <?php endif; ?>

    <!-- BARRA DE CONTROLE: BUSCA E FILTROS -->
    <section class="anot-controls" aria-label="Filtros e Busca de Anotações">
        <div class="anot-search-box">
            <svg class="anot-search-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                id="anotSearchInput" 
                placeholder="Buscar por palavras-chave no título ou conteúdo..." 
                value="<?= htmlspecialchars($busca_query) ?>"
                autocomplete="off"
            >
            <button type="button" id="anotClearSearch" class="anot-search-clear <?= $busca_query !== '' ? 'visible' : '' ?>" title="Limpar busca" aria-label="Limpar busca">
                ✕
            </button>
        </div>

        <div class="anot-filters-scroll" role="tablist" aria-label="Filtrar por Categoria">
            <button 
                type="button" 
                class="anot-filter-pill <?= ($filtro_cat === '' || $filtro_cat === 'Todas') ? 'active' : '' ?>" 
                data-category="Todas"
                role="tab"
                aria-selected="<?= ($filtro_cat === '' || $filtro_cat === 'Todas') ? 'true' : 'false' ?>"
            >
                <span class="pill-dot pill-dot-all"></span>
                <span class="pill-label">Todas</span>
                <span class="pill-count" id="count-Todas"><?= $totais_cat['Todas'] ?></span>
            </button>

            <?php foreach ($categorias_config as $cat_nome => $cat_data): ?>
                <button 
                    type="button" 
                    class="anot-filter-pill cat-<?= $cat_data['cor'] ?> <?= ($filtro_cat === $cat_nome) ? 'active' : '' ?>" 
                    data-category="<?= htmlspecialchars($cat_nome) ?>"
                    role="tab"
                    aria-selected="<?= ($filtro_cat === $cat_nome) ? 'true' : 'false' ?>"
                >
                    <span class="pill-dot"></span>
                    <span class="pill-label"><?= $cat_data['label'] ?></span>
                    <span class="pill-count" id="count-<?= $cat_nome ?>"><?= $totais_cat[$cat_nome] ?? 0 ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- GRID DE CARDS / LISTA -->
    <section class="anot-content-area" aria-live="polite">
        <div class="anot-grid" id="anotGrid">
            <?php foreach ($anotacoes as $a): 
                $cat = $a['categoria'] ?? 'Geral';
                $cor = $a['cor'] ?? ($categorias_config[$cat]['cor'] ?? 'purple');
                $cfg = $categorias_config[$cat] ?? ['cor' => 'purple', 'label' => $cat, 'icon' => '📝'];
                $data_formatada = date('d/m/Y \à\s H:i', strtotime($a['atualizado']));
            ?>
                <article 
                    class="anot-card cor-<?= htmlspecialchars($cor) ?>" 
                    id="anot-card-<?= $a['id'] ?>"
                    data-id="<?= $a['id'] ?>"
                    data-titulo="<?= htmlspecialchars($a['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                    data-categoria="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"
                    data-conteudo="<?= htmlspecialchars($a['conteudo'], ENT_QUOTES, 'UTF-8') ?>"
                >
                    <!-- Top Bar do Card com Categoria e Ações -->
                    <div class="anot-card-header">
                        <div class="anot-tag-badge cat-<?= htmlspecialchars($cor) ?>">
                            <span class="anot-tag-dot"></span>
                            <span class="anot-tag-text"><?= htmlspecialchars($cat) ?></span>
                        </div>

                        <div class="anot-card-actions">
                            <button 
                                type="button" 
                                class="anot-action-btn btn-edit" 
                                title="Editar anotação"
                                aria-label="Editar anotação <?= htmlspecialchars($a['titulo']) ?>"
                                onclick="abrirModalEdicao(<?= $a['id'] ?>)"
                            >
                                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>

                            <button 
                                type="button" 
                                class="anot-action-btn btn-del" 
                                title="Excluir anotação"
                                aria-label="Excluir anotação <?= htmlspecialchars($a['titulo']) ?>"
                                onclick="excluirAnotacao(<?= $a['id'] ?>, <?= json_encode($a['titulo']) ?>)"
                            >
                                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Título -->
                    <h2 class="anot-card-title"><?= htmlspecialchars($a['titulo']) ?></h2>

                    <!-- Conteúdo com formatação -->
                    <div class="anot-card-body">
                        <?= nl2br(htmlspecialchars($a['conteudo'])) ?>
                    </div>

                    <!-- Rodapé com Data -->
                    <footer class="anot-card-footer">
                        <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Atualizado em <?= $data_formatada ?></span>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- ESTADO VAZIO (EMPTY STATE) -->
        <div class="anot-empty-state <?= empty($anotacoes) ? 'visible' : '' ?>" id="anotEmptyState">
            <div class="anot-empty-icon">
                <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                    <path d="M6 6h10"></path>
                    <path d="M6 10h10"></path>
                    <path d="M6 14h6"></path>
                </svg>
            </div>
            <h3 class="anot-empty-title" id="anotEmptyTitle">Nenhuma anotação criada ainda</h3>
            <p class="anot-empty-desc" id="anotEmptyDesc">
                Clique em "+ Nova Anotação" para registrar suas dicas, metas marciais e orientações dos mestres.
            </p>
            <button type="button" class="btn-anot-primary" id="btnEmptyAction" onclick="abrirModalNova()">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Criar Primeira Anotação</span>
            </button>
        </div>
    </section>

</main>

<!-- ================================================================
     MODAL DE CRIAÇÃO / EDIÇÃO DE ANOTAÇÃO
     ================================================================ -->
<div class="anot-modal-overlay" id="anotFormModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalHeading">
    <div class="anot-modal-box">
        
        <header class="anot-modal-header">
            <div class="anot-modal-title-group">
                <div class="anot-modal-icon-badge" id="modalIconBadge">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="anot-modal-heading" id="modalHeading">Nova Anotação</h3>
                    <p class="anot-modal-subheading" id="modalSubheading">Registre técnicas, metas ou reflexões para seu progresso no Kyokushin.</p>
                </div>
            </div>
            <button type="button" class="anot-modal-close" id="btnFecharModal" aria-label="Fechar janela">✕</button>
        </header>

        <form method="POST" action="anotacoes.php" id="formAnotacao" class="anot-modal-form" novalidate>
            <?= csrf_input() ?>
            <input type="hidden" name="action" id="inputAction" value="criar">
            <input type="hidden" name="id" id="inputId" value="">

            <!-- CATEGORIA / TAG SELECTOR -->
            <div class="anot-form-group">
                <label class="anot-form-label">
                    <span>Categoria / Tag</span>
                    <span class="label-hint">Selecione o tipo da anotação</span>
                </label>
                <div class="anot-category-picker" role="radiogroup" aria-label="Selecione uma categoria">
                    <?php 
                    $first = true;
                    foreach ($categorias_config as $cat_key => $cat_info): 
                    ?>
                        <label class="cat-radio-label cat-<?= $cat_info['cor'] ?>">
                            <input 
                                type="radio" 
                                name="categoria" 
                                value="<?= htmlspecialchars($cat_key) ?>" 
                                <?= $first ? 'checked' : '' ?>
                            >
                            <span class="cat-radio-pill">
                                <span class="cat-radio-dot"></span>
                                <span class="cat-radio-name"><?= $cat_info['label'] ?></span>
                            </span>
                        </label>
                    <?php 
                    $first = false;
                    endforeach; 
                    ?>
                </div>
            </div>

            <!-- TÍTULO -->
            <div class="anot-form-group">
                <div class="anot-label-row">
                    <label for="inputTitulo" class="anot-form-label">
                        Título da Anotação <span class="required">*</span>
                    </label>
                    <span class="anot-char-count" id="charCountTitulo">0 / 150</span>
                </div>
                <input 
                    type="text" 
                    id="inputTitulo" 
                    name="titulo" 
                    class="anot-input" 
                    placeholder="Ex: Dica do Sensei para Kumite / Ajuste no Pinan Sono Ni" 
                    required 
                    maxlength="150"
                    autocomplete="off"
                >
                <span class="anot-field-error" id="errorTitulo">Por favor, informe um título para a anotação.</span>
            </div>

            <!-- CONTEÚDO -->
            <div class="anot-form-group">
                <div class="anot-label-row">
                    <label for="inputConteudo" class="anot-form-label">
                        Conteúdo Detalhado <span class="required">*</span>
                    </label>
                    <span class="anot-char-count" id="charCountConteudo">0 / 2000</span>
                </div>
                <textarea 
                    id="inputConteudo" 
                    name="conteudo" 
                    class="anot-textarea" 
                    rows="6" 
                    placeholder="Descreva as instruções, correções recebidas, sensações de base, rotação de quadril, postura ou meta a atingir..." 
                    required 
                    maxlength="2000"
                ></textarea>
                <span class="anot-field-error" id="errorConteudo">Por favor, escreva o conteúdo da anotação.</span>
            </div>

            <!-- BOTÕES DE AÇÃO -->
            <footer class="anot-modal-footer">
                <button type="button" class="btn-anot-secondary" id="btnCancelarModal">
                    Cancelar
                </button>
                <button type="submit" class="btn-anot-primary" id="btnSalvarModal">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span id="btnSalvarText">Salvar Anotação</span>
                </button>
            </footer>
        </form>

    </div>
</div>

<!-- FORMULÁRIO OCULTO PARA EXCLUSÃO SEGURA -->
<form method="POST" action="anotacoes.php" id="formExcluirAnotacao" style="display:none;">
    <?= csrf_input() ?>
    <input type="hidden" name="action" value="excluir">
    <input type="hidden" name="id" id="inputDeleteId" value="">
</form>

<!-- SCRIPT DE GERENCIAMENTO DE ESTADO E INTERATIVIDADE -->
<script>
(function() {
    'use strict';

    // Elementos do Modal
    const modalOverlay     = document.getElementById('anotFormModal');
    const modalHeading     = document.getElementById('modalHeading');
    const modalSubheading  = document.getElementById('modalSubheading');
    const formAnotacao     = document.getElementById('formAnotacao');
    const inputAction      = document.getElementById('inputAction');
    const inputId          = document.getElementById('inputId');
    const inputTitulo      = document.getElementById('inputTitulo');
    const inputConteudo    = document.getElementById('inputConteudo');
    const btnSalvarText    = document.getElementById('btnSalvarText');
    const btnAbrirModal    = document.getElementById('btnAbrirModalNova');
    const btnFecharModal   = document.getElementById('btnFecharModal');
    const btnCancelarModal = document.getElementById('btnCancelarModal');
    const charCountTitulo  = document.getElementById('charCountTitulo');
    const charCountConteudo= document.getElementById('charCountConteudo');
    const errorTitulo      = document.getElementById('errorTitulo');
    const errorConteudo    = document.getElementById('errorConteudo');

    // Elementos de Filtro e Busca
    const searchInput      = document.getElementById('anotSearchInput');
    const clearSearchBtn   = document.getElementById('anotClearSearch');
    const filterPills      = document.querySelectorAll('.anot-filter-pill');
    const anotGrid         = document.getElementById('anotGrid');
    const emptyState       = document.getElementById('anotEmptyState');
    const emptyTitle       = document.getElementById('anotEmptyTitle');
    const emptyDesc        = document.getElementById('anotEmptyDesc');
    const btnEmptyAction   = document.getElementById('btnEmptyAction');

    // Formulário de Exclusão
    const formDelete       = document.getElementById('formExcluirAnotacao');
    const inputDeleteId    = document.getElementById('inputDeleteId');

    // Estado local da UI
    let categoriaAtiva = 'Todas';
    let termoBusca     = searchInput ? searchInput.value.trim().toLowerCase() : '';

    // Inicializar contadores de caracteres
    function atualizarContadores() {
        if (charCountTitulo && inputTitulo) {
            charCountTitulo.textContent = `${inputTitulo.value.length} / 150`;
        }
        if (charCountConteudo && inputConteudo) {
            charCountConteudo.textContent = `${inputConteudo.value.length} / 2000`;
        }
    }

    if (inputTitulo) inputTitulo.addEventListener('input', atualizarContadores);
    if (inputConteudo) inputConteudo.addEventListener('input', atualizarContadores);

    // Abrir Modal para Criação
    window.abrirModalNova = function() {
        if (!modalOverlay) return;
        modalHeading.textContent = 'Nova Anotação';
        modalSubheading.textContent = 'Registre técnicas, metas ou reflexões para seu progresso no Kyokushin.';
        btnSalvarText.textContent = 'Salvar Anotação';
        inputAction.value = 'criar';
        inputId.value = '';
        inputTitulo.value = '';
        inputConteudo.value = '';

        // Reset erros
        if (errorTitulo) errorTitulo.style.display = 'none';
        if (errorConteudo) errorConteudo.style.display = 'none';
        inputTitulo.classList.remove('has-error');
        inputConteudo.classList.remove('has-error');

        // Categoria padrão: Geral ou a selecionada atualmente no filtro se não for "Todas"
        const catDefault = (categoriaAtiva !== 'Todas') ? categoriaAtiva : 'Geral';
        const radio = formAnotacao.querySelector(`input[name="categoria"][value="${catDefault}"]`);
        if (radio) radio.checked = true;

        atualizarContadores();
        modalOverlay.classList.add('open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        setTimeout(() => inputTitulo.focus(), 150);
    };

    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', window.abrirModalNova);
    }

    // Abrir Modal para Edição
    window.abrirModalEdicao = function(id) {
        const card = document.getElementById(`anot-card-${id}`);
        if (!card) return;

        const titulo    = card.getAttribute('data-titulo') || '';
        const categoria = card.getAttribute('data-categoria') || 'Geral';
        const conteudo  = card.getAttribute('data-conteudo') || '';

        modalHeading.textContent = 'Editar Anotação';
        modalSubheading.textContent = 'Atualize as informações registradas nesta anotação.';
        btnSalvarText.textContent = 'Salvar Alterações';
        inputAction.value = 'editar';
        inputId.value = id;
        inputTitulo.value = titulo;
        inputConteudo.value = conteudo;

        // Reset erros
        if (errorTitulo) errorTitulo.style.display = 'none';
        if (errorConteudo) errorConteudo.style.display = 'none';
        inputTitulo.classList.remove('has-error');
        inputConteudo.classList.remove('has-error');

        const radio = formAnotacao.querySelector(`input[name="categoria"][value="${categoria}"]`);
        if (radio) radio.checked = true;

        atualizarContadores();
        modalOverlay.classList.add('open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        setTimeout(() => inputTitulo.focus(), 150);
    };

    // Fechar Modal
    function fecharModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('open');
        modalOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (btnFecharModal) btnFecharModal.addEventListener('click', fecharModal);
    if (btnCancelarModal) btnCancelarModal.addEventListener('click', fecharModal);

    modalOverlay?.addEventListener('click', (e) => {
        if (e.target === modalOverlay) fecharModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('open')) {
            fecharModal();
        }
    });

    // Validação antes do Envio
    formAnotacao?.addEventListener('submit', function(e) {
        let isValid = true;
        const tit = inputTitulo.value.trim();
        const cont = inputConteudo.value.trim();

        if (!tit) {
            isValid = false;
            errorTitulo.style.display = 'block';
            inputTitulo.classList.add('has-error');
            inputTitulo.focus();
        } else {
            errorTitulo.style.display = 'none';
            inputTitulo.classList.remove('has-error');
        }

        if (!cont) {
            isValid = false;
            errorConteudo.style.display = 'block';
            inputConteudo.classList.add('has-error');
            if (isValid) inputConteudo.focus();
        } else {
            errorConteudo.style.display = 'none';
            inputConteudo.classList.remove('has-error');
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Exclusão com Confirmação Interativa
    window.excluirAnotacao = function(id, titulo) {
        const mensagem = `Deseja realmente excluir a anotação "${titulo}"? Esta ação não pode ser desfeita.`;

        if (window.AppModal && typeof window.AppModal.confirm === 'function') {
            window.AppModal.confirm({
                title: 'Excluir Anotação?',
                message: mensagem,
                confirmText: 'Sim, Excluir',
                cancelText: 'Cancelar',
                type: 'error'
            }).then((confirmado) => {
                if (confirmado) {
                    inputDeleteId.value = id;
                    formDelete.submit();
                }
            });
        } else {
            if (confirm(`Excluir anotação "${titulo}"?`)) {
                inputDeleteId.value = id;
                formDelete.submit();
            }
        }
    };

    // Filtros e Busca em Tempo Real (Client-Side Instantâneo)
    function aplicarFiltros() {
        const cards = anotGrid ? anotGrid.querySelectorAll('.anot-card') : [];
        let visiveisCount = 0;

        cards.forEach(card => {
            const cat = card.getAttribute('data-categoria') || '';
            const tit = (card.getAttribute('data-titulo') || '').toLowerCase();
            const cont = (card.getAttribute('data-conteudo') || '').toLowerCase();

            const matchCat = (categoriaAtiva === 'Todas' || cat === categoriaAtiva);
            const matchBusca = (termoBusca === '' || tit.includes(termoBusca) || cont.includes(termoBusca));

            if (matchCat && matchBusca) {
                card.style.display = '';
                visiveisCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Gerenciar Estado Vazio
        if (visiveisCount === 0) {
            emptyState?.classList.add('visible');
            if (termoBusca !== '' || categoriaAtiva !== 'Todas') {
                emptyTitle.textContent = 'Nenhuma anotação encontrada';
                emptyDesc.textContent = `Nenhum registro corresponde aos filtros selecionados (${categoriaAtiva !== 'Todas' ? 'Categoria: ' + categoriaAtiva : ''}${termoBusca ? ' Busca: "' + termoBusca + '"' : ''}).`;
                btnEmptyAction.innerHTML = `<span>Limpar Filtros</span>`;
                btnEmptyAction.onclick = function() {
                    searchInput.value = '';
                    termoBusca = '';
                    if (clearSearchBtn) clearSearchBtn.classList.remove('visible');
                    selecionarCategoria('Todas');
                };
            } else {
                emptyTitle.textContent = 'Nenhuma anotação criada ainda';
                emptyDesc.textContent = 'Clique em "+ Nova Anotação" para registrar suas dicas, metas marciais e orientações dos mestres.';
                btnEmptyAction.innerHTML = `
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Criar Primeira Anotação</span>
                `;
                btnEmptyAction.onclick = window.abrirModalNova;
            }
        } else {
            emptyState?.classList.remove('visible');
        }
    }

    function selecionarCategoria(cat) {
        categoriaAtiva = cat;
        filterPills.forEach(pill => {
            const pillCat = pill.getAttribute('data-category');
            const isActive = (pillCat === cat);
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', String(isActive));
        });
        aplicarFiltros();
    }

    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            const cat = pill.getAttribute('data-category');
            selecionarCategoria(cat);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            termoBusca = e.target.value.trim().toLowerCase();
            if (clearSearchBtn) {
                clearSearchBtn.classList.toggle('visible', termoBusca !== '');
            }
            aplicarFiltros();
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            termoBusca = '';
            clearSearchBtn.classList.remove('visible');
            searchInput.focus();
            aplicarFiltros();
        });
    }

    // Inicialização ao carregar
    aplicarFiltros();

})();
</script>

</body>
</html>
