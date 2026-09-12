<?php
require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/csrf.php';
require_once __DIR__ . '/../includes/icons.php';

// RNF04 – Validação de Sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id   = intval($_SESSION['id']);
$usuario_nome = $_SESSION['nome'] ?? 'Praticante';

// Buscar dados do usuário (prepared statement)
$stmt_user = mysqli_prepare($conn, "SELECT u.nome, f.nome AS faixa_nome, f.ordem AS faixa_ordem 
                                     FROM usuarios u 
                                     LEFT JOIN faixas f ON u.faixa_id = f.id 
                                     WHERE u.id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $usuario_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$usuario     = $result_user ? mysqli_fetch_assoc($result_user) : null;
$faixa_ordem = $usuario['faixa_ordem'] ?? 1;

// RF03 – Listar Exercícios agrupados por categoria
$sql_exercicios = "SELECT id, nome, categoria FROM exercicios_kyokushin ORDER BY categoria, nome";
$result_exercicios = mysqli_query($conn, $sql_exercicios);
$exercicios_por_categoria = [];

if ($result_exercicios) {
    while ($ex = mysqli_fetch_assoc($result_exercicios)) {
        $cat = $ex['categoria'];
        if (!isset($exercicios_por_categoria[$cat])) {
            $exercicios_por_categoria[$cat] = [];
        }
        $exercicios_por_categoria[$cat][] = $ex;
    }
}

// RF07 – Mensagens de Feedback
$mensagem_sucesso = '';
$mensagem_erro = '';

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_registrado') {
    $mensagem_sucesso = 'Treino registrado com sucesso! Parabéns pela dedicação! Osu!';
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_deletado') {
    $mensagem_sucesso = 'Treino deletado com sucesso!';
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_atualizado') {
    $mensagem_sucesso = 'Treino atualizado com sucesso!';
} elseif (isset($_GET['erro'])) {
    $erro = $_GET['erro'];
    switch($erro) {
        case 'data_futura':
            $mensagem_erro = 'Erro: Não é permitido registrar treinos com data futura.';
            break;
        case 'campos_obrigatorios':
            $mensagem_erro = 'Erro: Preencha todos os campos obrigatórios.';
            break;
        case 'sem_exercicios':
            $mensagem_erro = 'Erro: Adicione pelo menos um exercício ao treino.';
            break;
        case 'banco_dados':
            $mensagem_erro = 'Erro ao processar o treino no banco de dados. Tente novamente.';
            break;
        default:
            $mensagem_erro = 'Erro inesperado. Tente novamente.';
    }
}

// Filtros e Paginação
$filtro_mes = intval($_GET['mes'] ?? 0);
$filtro_ano = intval($_GET['ano'] ?? 0);
$busca      = trim($_GET['busca'] ?? '');
$pagina     = max(1, intval($_GET['pagina'] ?? 1));
$limite     = 8; // Treinos por página
$offset     = ($pagina - 1) * $limite;

$where_parts = ["usuario_id = ?"];
$params_filtro = [$usuario_id];
$types_filtro = "i";

if ($filtro_mes > 0 && $filtro_mes <= 12) {
    $where_parts[] = "MONTH(data_treino) = ?";
    $params_filtro[] = $filtro_mes;
    $types_filtro .= "i";
}
if ($filtro_ano > 0) {
    $where_parts[] = "YEAR(data_treino) = ?";
    $params_filtro[] = $filtro_ano;
    $types_filtro .= "i";
}
if ($busca !== '') {
    $where_parts[] = "observacoes LIKE ?";
    $params_filtro[] = "%" . $busca . "%";
    $types_filtro .= "s";
}

$where_sql = implode(" AND ", $where_parts);

// Contar total de registros
$stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) c FROM treinos WHERE $where_sql");
mysqli_stmt_bind_param($stmt_count, $types_filtro, ...$params_filtro);
mysqli_stmt_execute($stmt_count);
$res_count = mysqli_stmt_get_result($stmt_count);
$total_treinos_count = ($res_count && $rowC = mysqli_fetch_assoc($res_count)) ? intval($rowC['c']) : 0;
$total_paginas = max(1, (int)ceil($total_treinos_count / $limite));

// Buscar registros paginados
$sql_treinos = "SELECT id, duracao_min, observacoes, data_treino, criado_em 
                FROM treinos 
                WHERE $where_sql 
                ORDER BY data_treino DESC, criado_em DESC 
                LIMIT ? OFFSET ?";

$params_treinos = $params_filtro;
$params_treinos[] = $limite;
$params_treinos[] = $offset;
$types_treinos = $types_filtro . "ii";

$stmt_treinos = mysqli_prepare($conn, $sql_treinos);
mysqli_stmt_bind_param($stmt_treinos, $types_treinos, ...$params_treinos);
mysqli_stmt_execute($stmt_treinos);
$result_treinos = mysqli_stmt_get_result($stmt_treinos);

$treinos_list = [];
if ($result_treinos) {
    while ($row = mysqli_fetch_assoc($result_treinos)) {
        $treinos_list[] = $row;
    }
}

// Buscar exercícios de todos os treinos da página atual (Batch query para performance)
$exercicios_por_treino = [];
if (!empty($treinos_list)) {
    $treino_ids = array_column($treinos_list, 'id');
    $in_ids = implode(',', array_map('intval', $treino_ids));
    $res_ex = mysqli_query($conn, "SELECT treino_id, descricao, series, repeticoes FROM treino_exercicios WHERE treino_id IN ($in_ids) ORDER BY id ASC");
    if ($res_ex) {
        while ($row_ex = mysqli_fetch_assoc($res_ex)) {
            $exercicios_por_treino[$row_ex['treino_id']][] = $row_ex;
        }
    }
}

// Sugestões por faixa (ordem 1–8 do sistema Kyokushin do Hub)
$treino_sugestoes = [
    1 => ['nivel' => 'Branca', 'cor' => '#d4d4d8', 'treinos' => [
        ['nome' => 'Fundamentos de Kihon', 'duracao' => 30, 'desc' => 'Seiken Tsuki, Age Uke e Gedan Barai — 3 séries de 10x cada lado, com kiai.'],
        ['nome' => 'Base e Mae Geri', 'duracao' => 35, 'desc' => 'Zenkutsu Dachi estático + Mae Geri no ar — 3x20. Foco em equilíbrio e chusoku.'],
        ['nome' => 'Kata Taikyoku Sono Ichi', 'duracao' => 25, 'desc' => '5 execuções lentas e 3 em ritmo de exame. Ibuki no último movimento.']
    ]],
    2 => ['nivel' => 'Laranja', 'cor' => '#f97316', 'treinos' => [
        ['nome' => 'Combinações Tsuki + Geri', 'duracao' => 40, 'desc' => 'Oi Tsuki + Mae Geri e Gyaku Zuki + Mawashi Geri — 4 séries de 12 cada lado.'],
        ['nome' => 'Taikyoku Completo', 'duracao' => 40, 'desc' => 'Taikyoku Sono Ichi, Ni e San — 4x cada. Corrija linhas e giro de quadril.'],
        ['nome' => 'Uke em deslocamento', 'duracao' => 35, 'desc' => 'Soto Uke, Uchi Uke e Gedan Barai avançando  — 3 linhas de 8 movimentos.']
    ]],
    3 => ['nivel' => 'Azul', 'cor' => '#2563eb', 'treinos' => [
        ['nome' => 'Pinan Sono Ichi e Ni', 'duracao' => 45, 'desc' => 'Revisão técnica lenta + 3 execuções em ritmo de exame de cada kata.'],
        ['nome' => 'Yoko Geri e Sabaki', 'duracao' => 40, 'desc' => 'Yoko Keage e Yoko Kekomi no saco — 3x15. Depois 4 min de tai sabaki.'],
        ['nome' => 'Kumite controlado', 'duracao' => 50, 'desc' => '4 rounds de 2 min de Jiyu Kumite leve. Trabalhe guarda e distância.']
    ]],
    4 => ['nivel' => 'Amarela', 'cor' => '#eab308', 'treinos' => [
        ['nome' => 'Pinan Sono San', 'duracao' => 45, 'desc' => 'Bunkai dos movimentos-chave + 5 execuções completas com kime.'],
        ['nome' => 'Gyaku Zuki de potência', 'duracao' => 40, 'desc' => '3x20 no saco + combinações Gyaku + Mawashi Jodan (sem contato na cabeça).'],
        ['nome' => 'Condicionamento de Kyu', 'duracao' => 35, 'desc' => '40 flexões, 50 abdominais, 30 agachamentos e 2 min de kihon no ar.']
    ]],
    5 => ['nivel' => 'Verde', 'cor' => '#16a34a', 'treinos' => [
        ['nome' => 'Pinan Sono Yon e Go', 'duracao' => 50, 'desc' => 'Sequência completa Pinan I–V: 1x lento e 1x exame. Anote correções.'],
        ['nome' => 'Ushiro Geri e clinch', 'duracao' => 45, 'desc' => 'Ushiro Geri 3x12 cada perna + Hiza Geri no saco 3x15.'],
        ['nome' => 'Kumite de pressão', 'duracao' => 55, 'desc' => '5 rounds de 2 min. Alterne ataque contínuo e contra-ataque.']
    ]],
    6 => ['nivel' => 'Marrom', 'cor' => '#b45309', 'treinos' => [
        ['nome' => 'Tsuki no Kata e Gekisai', 'duracao' => 50, 'desc' => '3 execuções de cada com Ibuki. Foque respiração tanden e estabilidade.'],
        ['nome' => 'Kihon de exame', 'duracao' => 55, 'desc' => 'Linha de combinações oficiais: tsuki, geri e uke em ida e volta no tatame.'],
        ['nome' => 'Kumite de resistência', 'duracao' => 60, 'desc' => '6 rounds de 2 min. Último round só com gyaku zuki e low kick.']
    ]],
    7 => ['nivel' => 'Marrom / Preta', 'cor' => '#78350f', 'treinos' => [
        ['nome' => 'Yantsu e polimento de kata', 'duracao' => 55, 'desc' => 'Yantsu 4x + revisão dos Pinan com cronômetro de exame.'],
        ['nome' => 'Preparação física de ponta', 'duracao' => 50, 'desc' => '60 flexões nos nós, 80 abdominais, 40 burpees e 3 min de sombra.'],
        ['nome' => 'Simulado de graduação', 'duracao' => 70, 'desc' => 'Kihon + 2 katas + 4 rounds de kumite. Trate como exame real.']
    ]],
    8 => ['nivel' => 'Preta', 'cor' => '#eab308', 'treinos' => [
        ['nome' => 'Sanchin e Tensho', 'duracao' => 45, 'desc' => 'Sanchin 3x com Ibuki profundo. Tensho 3x fluído. Controle de centro.'],
        ['nome' => 'Kanku e bunkai', 'duracao' => 60, 'desc' => 'Kanku Dai 2x lento / 2x exame. Extraia 4 aplicações de bunkai.'],
        ['nome' => 'Espírito de Yudansha', 'duracao' => 75, 'desc' => 'Aquecimento, kihon de ensino, 6 rounds de 2 min e volta à calma com Dojo Kun.']
    ]],
];
$sugestoes_treino = $treino_sugestoes[intval($faixa_ordem)] ?? $treino_sugestoes[1];
$sugestoes_treino['nivel'] = $usuario['faixa_nome'] ?? $sugestoes_treino['nivel'];

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treinos | Oyama Hub</title>
    <link rel="icon" href="../img/kyokushinicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/treinos.css">
</head>
<body>
<!-- ── Navbar ── -->
<?php include '../includes/navbar.php'; ?>

<!-- ── Main Content ── -->
<main class="treinos-container">
    <!-- Header com Ação Principal -->
    <section class="treinos-header">
        <div class="treinos-header-flex">
            <div class="treinos-header-info">
                <span class="treinos-tag-topo">OYAMA HUB • DIÁRIO DO GUERREIRO</span>
                <h1>MEUS TREINOS</h1>
                <p>Osu, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>! Registre suas sessões e acompanhe sua evolução marcial.</p>
            </div>
            <button type="button" class="btn-novo-treino" onclick="abrirModalNovoTreino()">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>+ Novo Treino</span>
            </button>
        </div>
    </section>

    <!-- Barra de Filtros e Busca -->
    <section class="treinos-filtro-bar">
        <form method="GET" class="treinos-filtro-form">
            <div class="filtro-inputs-wrap">
                <div class="filtro-group">
                    <label>Mês</label>
                    <select name="mes">
                        <option value="0">Todos os meses</option>
                        <?php
                        $meses_nomes = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                        for ($m = 1; $m <= 12; $m++):
                        ?>
                        <option value="<?= $m ?>" <?= ($filtro_mes === $m ? 'selected' : '') ?>><?= $meses_nomes[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filtro-group">
                    <label>Ano</label>
                    <select name="ano">
                        <option value="0">Todos os anos</option>
                        <?php for ($y = intval(date('Y')); $y >= 2023; $y--): ?>
                        <option value="<?= $y ?>" <?= ($filtro_ano === $y ? 'selected' : '') ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filtro-group busca-group">
                    <label>Buscar</label>
                    <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar por descrição ou técnica...">
                </div>
            </div>

            <div class="filtro-actions">
                <button type="submit" class="filtro-btn">Filtrar</button>
                <?php if ($filtro_mes || $filtro_ano || $busca !== ''): ?>
                    <a href="treinos.php" class="filtro-limpar">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- Mensagens de Sucesso/Erro -->
    <?php if (!empty($mensagem_erro)): ?>
        <div class="mensagem-erro">
            <span class="msg-icon">✕</span>
            <span><?php echo $mensagem_erro; ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert-modal show" id="alertModal">
            <div class="alert-box success-box">
                <div class="alert-icon">
                    <svg viewBox="0 0 24 24" width="44" height="44" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3>Sucesso!</h3>
                <p><?php echo $mensagem_sucesso; ?></p>
                <button type="button" class="alert-close" onclick="fecharModalAlerta()">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção de Sugestões de Treino -->
    <section class="treinos-sugestoes-section">
        <div class="section-title-wrap">
            <h2>SUGESTÕES PARA A SUA FAIXA</h2>
            <span class="sub-label">Programa técnico da faixa <?= htmlspecialchars($sugestoes_treino['nivel']) ?> — baseado na sua graduação atual</span>
        </div>
        <div class="sugestao-faixa-atual" style="--faixa-cor: <?= htmlspecialchars($sugestoes_treino['cor']) ?>">
            <span class="sugestao-faixa-dot"></span>
            <span>Seu plano atual</span>
            <strong><?= htmlspecialchars($sugestoes_treino['nivel']) ?></strong>
            <span class="sugestao-faixa-note">3 treinos recomendados para o seu próximo passo</span>
        </div>
        <div class="sugestoes-grid">
            <?php foreach ($sugestoes_treino['treinos'] as $sug): ?>
                <div class="sugestao-card" style="border-left-color: <?= $sugestoes_treino['cor'] ?>">
                    <div class="sugestao-header">
                        <h3><?= htmlspecialchars($sug['nome']) ?></h3>
                        <span class="badge-duracao-sugestao" style="border-color: <?= $sugestoes_treino['cor'] ?>; color: <?= $sugestoes_treino['cor'] ?>">
                            ⏱ <?= $sug['duracao'] ?> min
                        </span>
                    </div>
                    <p><?= htmlspecialchars($sug['desc']) ?></p>
                    <button type="button" class="btn-iniciar-sugestao" onclick="abrirModalComSugestao('<?= addslashes($sug['nome']) ?>', <?= $sug['duracao'] ?>)">
                        Usar Sugestão
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Seção de Histórico de Treinos -->
    <section class="treinos-historico-section">
        <div class="section-title-wrap">
            <h2>HISTÓRICO DE TREINOS <span class="badge-total">(<?= $total_treinos_count ?>)</span></h2>
            <span class="sub-label">Consulte suas sessões passadas ou faça ajustes</span>
        </div>

        <?php if (count($treinos_list) > 0): ?>
            <div class="historico-grid">
                <?php foreach ($treinos_list as $t): 
                    $exs = $exercicios_por_treino[$t['id']] ?? [];
                ?>
                    <article class="historico-card">
                        <div class="card-topo">
                            <div class="card-info-principal">
                                <time class="card-data"><?php echo date('d/m/Y', strtotime($t['data_treino'])); ?></time>
                                <h3 class="card-titulo"><?php echo htmlspecialchars($t['observacoes'] ?: 'Treino de Karate'); ?></h3>
                            </div>
                            <span class="tag-duracao">
                                ⏱ <?php echo intval($t['duracao_min']); ?> min
                            </span>
                        </div>

                        <!-- Resumo dos Exercícios -->
                        <div class="card-exercicios-wrap">
                            <span class="exercicios-titulo-pequeno">EXERCÍCIOS REGISTRADOS:</span>
                            <?php if (!empty($exs)): ?>
                                <div class="chips-exercicios-list">
                                    <?php foreach ($exs as $ex): ?>
                                        <span class="chip-exercicio">
                                            <span class="chip-metric"><?= intval($ex['series']) ?>x<?= intval($ex['repeticoes']) ?></span>
                                            <span class="chip-nome"><?= htmlspecialchars($ex['descricao']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="sem-detalhes-ex">Nenhum exercício especificado.</span>
                            <?php endif; ?>
                        </div>

                        <!-- Botões de Ação do Card -->
                        <div class="card-footer-actions">
                            <button type="button" class="btn-card-editar" 
                                    onclick='abrirModalEditar(<?= json_encode([
                                        "id" => $t["id"],
                                        "observacoes" => $t["observacoes"],
                                        "duracao_min" => $t["duracao_min"],
                                        "data_treino" => $t["data_treino"],
                                        "exercicios" => $exs
                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Editar</span>
                            </button>

                            <button type="button" class="btn-card-deletar" onclick="confirmarDelecao(<?php echo $t['id']; ?>)">
                                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span>Excluir</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegação de treinos" class="treinos-paginacao">
                    <?php
                    $queryParams = $_GET;
                    $linkPage = function($p) use ($queryParams) {
                        $queryParams['pagina'] = $p;
                        return '?' . http_build_query($queryParams);
                    };
                    ?>
                    <?php if ($pagina > 1): ?>
                        <a href="<?= $linkPage($pagina - 1) ?>" class="page-link">← Anterior</a>
                    <?php endif; ?>

                    <span class="page-current">
                        Página <?= $pagina ?> de <?= $total_paginas ?>
                    </span>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="<?= $linkPage($pagina + 1) ?>" class="page-link">Próxima →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="historico-vazio">
                <div class="vazio-icon">🥋</div>
                <h3>Nenhum treino registrado ainda</h3>
                <p>Pratique seus fundamentos e registre sua primeira sessão para acompanhar o gráfico de progresso.</p>
                <button type="button" class="btn-novo-treino" onclick="abrirModalNovoTreino()">
                    Registrar Primeiro Treino
                </button>
            </div>
        <?php endif; ?>
    </section>
</main>

<!-- ── MODAL ELEGANTE: REGISTRAR / EDITAR TREINO ── -->
<div class="treino-modal-backdrop" id="modalTreinoBackdrop" onclick="fecharModalTreinoPorBackdrop(event)">
    <div class="treino-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTreinoTitulo">
        <div class="modal-dialog-header">
            <div class="modal-dialog-title-group">
                <span class="modal-bullet" id="modalBullet"></span>
                <h2 id="modalTreinoTitulo">REGISTRAR NOVO TREINO</h2>
            </div>
            <button type="button" class="btn-modal-close" onclick="fecharModalTreino()" aria-label="Fechar Modal">✕</button>
        </div>

        <form method="POST" action="registrar_treino.php" class="modal-form-treino" id="formTreinoModal" onsubmit="return validarFormularioModal()">
            <input type="hidden" name="treino_id" id="modalTreinoId" value="">
            <?= csrf_input() ?>

            <div class="modal-dialog-body">
                <!-- Informações Principais -->
                <div class="form-row-grid">
                    <div class="form-group">
                        <label for="modal-data-treino">Data do Treino *</label>
                        <input type="date" id="modal-data-treino" name="data_treino" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="modal-duracao-treino">Duração (minutos) *</label>
                        <input type="number" id="modal-duracao-treino" name="duracao_min" min="5" max="300" step="5" placeholder="Ex: 60" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="modal-nome-treino">Nome / Descrição do Treino *</label>
                    <input type="text" id="modal-nome-treino" name="observacoes" placeholder="Ex: Treino de Kumite e Chutes" required>
                </div>

                <!-- Lista de Exercícios Dinâmicos -->
                <div class="modal-exercicios-box">
                    <div class="modal-ex-header">
                        <div>
                            <h3 class="modal-ex-titulo">🏋️ TÉCNICAS E EXERCÍCIOS</h3>
                            <span class="modal-ex-sub">Adicione as séries e repetições de cada técnica</span>
                        </div>
                        <button type="button" class="btn-adicionar-ex-modal" onclick="adicionarExercicioModal()">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Adicionar Exercício</span>
                        </button>
                    </div>

                    <!-- Template oculto de select de exercícios para replicação em JS -->
                    <template id="templateExerciciosSelect">
                        <option value="">Selecione um exercício...</option>
                        <?php foreach ($exercicios_por_categoria as $categoria => $exercicios): ?>
                            <optgroup label="<?php echo htmlspecialchars($categoria); ?>">
                                <?php foreach ($exercicios as $ex): ?>
                                    <option value="<?php echo htmlspecialchars($ex['nome']); ?>">
                                        <?php echo htmlspecialchars($ex['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </template>

                    <div id="modalExerciciosContainer" class="modal-ex-lista">
                        <!-- Linhas de exercício injetadas via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="modal-dialog-footer">
                <button type="button" class="btn-modal-cancelar" onclick="fecharModalTreino()">Cancelar</button>
                <button type="submit" class="btn-modal-salvar" id="btnSalvarModal">✓ Concluir Registro</button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL DE CONFIRMAÇÃO DE DELEÇÃO ── -->
<div class="delete-confirm-modal" id="deleteConfirmModal">
    <div class="delete-confirm-box">
        <div class="alert-icon warning-icon">
            <svg viewBox="0 0 24 24" width="44" height="44" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3>Excluir Treino?</h3>
        <p>Tem certeza que deseja excluir este treino? Esta ação removerá todas as repetições e não poderá ser desfeita.</p>
        <div class="delete-confirm-buttons">
            <button type="button" class="btn-cancelar" onclick="cancelarDelecao()">Cancelar</button>
            <form id="deleteTreinoForm" method="POST" action="deletar_treino.php">
                <?= csrf_input() ?>
                <input type="hidden" name="id" id="treinoParaDeletar" value="">
                <button type="submit" class="btn-confirmar-delete">Sim, Excluir</button>
            </form>
        </div>
    </div>
</div>

<!-- ── JAVASCRIPT DO CRUD COMPLETO ── -->
<script>
    let contadorIndiceExercicio = 0;

    // Helper para extrair o HTML de options do template
    function getExerciciosOptionsHtml() {
        const template = document.getElementById('templateExerciciosSelect');
        return template ? template.innerHTML : '';
    }

    // ABRIR MODAL: NOVO TREINO
    function abrirModalNovoTreino() {
        document.getElementById('modalTreinoTitulo').innerText = 'REGISTRAR NOVO TREINO';
        document.getElementById('modalBullet').className = 'modal-bullet';
        document.getElementById('btnSalvarModal').innerText = '✓ Registrar Treino';
        
        const form = document.getElementById('formTreinoModal');
        form.action = 'registrar_treino.php';
        document.getElementById('modalTreinoId').value = '';

        // Resetar campos
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('modal-data-treino').value = hoje;
        document.getElementById('modal-nome-treino').value = '';
        document.getElementById('modal-duracao-treino').value = '45';

        // Resetar lista de exercícios com 1 linha vazia inicial
        const container = document.getElementById('modalExerciciosContainer');
        container.innerHTML = '';
        contadorIndiceExercicio = 0;
        adicionarExercicioModal('', 3, 15);

        // Exibir modal
        const modal = document.getElementById('modalTreinoBackdrop');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    // ABRIR MODAL: COM SUGESTÃO DE FAIXA PREENCHIDA
    function abrirModalComSugestao(nome, duracao) {
        abrirModalNovoTreino();
        document.getElementById('modal-nome-treino').value = nome;
        document.getElementById('modal-duracao-treino').value = duracao;
    }

    // ABRIR MODAL: EDITAR TREINO EXISTENTE (CORREÇÃO DE DEEP CLONE & ID)
    function abrirModalEditar(treino) {
        if (!treino || !treino.id) return;

        document.getElementById('modalTreinoTitulo').innerText = 'EDITAR TREINO';
        document.getElementById('modalBullet').className = 'modal-bullet edit';
        document.getElementById('btnSalvarModal').innerText = '✓ Salvar Alterações';

        const form = document.getElementById('formTreinoModal');
        form.action = 'atualizar_treino.php';
        document.getElementById('modalTreinoId').value = treino.id;

        // Preencher dados principais
        document.getElementById('modal-data-treino').value = treino.data_treino || '';
        document.getElementById('modal-nome-treino').value = treino.observacoes || '';
        document.getElementById('modal-duracao-treino').value = treino.duracao_min || '45';

        // Preencher exercícios com clonagem profunda dos valores existentes
        const container = document.getElementById('modalExerciciosContainer');
        container.innerHTML = '';
        contadorIndiceExercicio = 0;

        if (treino.exercicios && Array.isArray(treino.exercicios) && treino.exercicios.length > 0) {
            treino.exercicios.forEach(ex => {
                adicionarExercicioModal(ex.descricao || '', ex.series || 3, ex.repeticoes || 15);
            });
        } else {
            adicionarExercicioModal('', 3, 15);
        }

        // Exibir modal
        const modal = document.getElementById('modalTreinoBackdrop');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    // FECHAR MODAL DE TREINO
    function fecharModalTreino() {
        const modal = document.getElementById('modalTreinoBackdrop');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    function fecharModalTreinoPorBackdrop(event) {
        if (event.target === document.getElementById('modalTreinoBackdrop')) {
            fecharModalTreino();
        }
    }

    // ADICIONAR LINHA DINÂMICA DE EXERCÍCIO
    function adicionarExercicioModal(descricaoSelecionada = '', seriesPadrao = 3, repsPadrao = 15) {
        const container = document.getElementById('modalExerciciosContainer');
        const idx = contadorIndiceExercicio++;

        const row = document.createElement('div');
        row.className = 'modal-ex-card-row';
        row.dataset.index = idx;

        row.innerHTML = `
            <div class="modal-ex-select-col">
                <select name="exercicios[${idx}][descricao]" class="modal-ex-select" required>
                    ${getExerciciosOptionsHtml()}
                </select>
            </div>
            <div class="modal-ex-steppers-col">
                <div class="stepper-item">
                    <span class="stepper-label">Séries</span>
                    <div class="stepper-box">
                        <button type="button" class="btn-step" onclick="alterarValorStep(this, -1, 1)">−</button>
                        <input type="number" name="exercicios[${idx}][series]" value="${seriesPadrao}" min="1" max="30" required>
                        <button type="button" class="btn-step" onclick="alterarValorStep(this, 1, 1)">+</button>
                    </div>
                </div>
                <div class="stepper-item">
                    <span class="stepper-label">Reps</span>
                    <div class="stepper-box">
                        <button type="button" class="btn-step" onclick="alterarValorStep(this, -5, 1)">−</button>
                        <input type="number" name="exercicios[${idx}][repeticoes]" value="${repsPadrao}" min="1" max="500" required>
                        <button type="button" class="btn-step" onclick="alterarValorStep(this, 5, 1)">+</button>
                    </div>
                </div>
                <button type="button" class="btn-ex-remover" onclick="removerExercicioModal(this)" title="Remover exercício">
                    ✕
                </button>
            </div>
        `;

        container.appendChild(row);

        // Se uma técnica já estava selecionada, selecionar no dropdown
        if (descricaoSelecionada) {
            const selectEl = row.querySelector('.modal-ex-select');
            if (selectEl) {
                selectEl.value = descricaoSelecionada;
            }
        }
    }

    // REMOVER LINHA DE EXERCÍCIO
    function removerExercicioModal(btn) {
        const rows = document.querySelectorAll('.modal-ex-card-row');
        if (rows.length > 1) {
            btn.closest('.modal-ex-card-row').remove();
        } else {
            if (window.AppModal) {
                AppModal.alert({ title: 'Atenção', message: 'O treino precisa de pelo menos uma técnica/exercício cadastrado!', type: 'warning' });
            } else {
                alert('⚠️ O treino precisa de pelo menos uma técnica/exercício cadastrado!');
            }
        }
    }

    // CONTROLADORES STEPPERS (+ / -)
    function alterarValorStep(btn, delta, minimo = 1) {
        const input = btn.parentElement.querySelector('input[type="number"]');
        if (!input) return;
        let valorAtual = parseInt(input.value) || 0;
        let novoValor = Math.max(minimo, valorAtual + delta);
        input.value = novoValor;
    }

    // VALIDAÇÃO DO FORMULÁRIO MODAL
    function validarFormularioModal() {
        const data = document.getElementById('modal-data-treino').value;
        const duracao = parseInt(document.getElementById('modal-duracao-treino').value);
        const nome = document.getElementById('modal-nome-treino').value.trim();
        const selects = document.querySelectorAll('.modal-ex-select');

        if (!data || !duracao || !nome) {
            if (window.AppModal) {
                AppModal.alert({ title: 'Campos Obrigatórios', message: 'Por favor, preencha todos os campos obrigatórios.', type: 'warning' });
            } else {
                alert('❌ Por favor, preencha todos os campos obrigatórios.');
            }
            return false;
        }

        if (duracao < 5) {
            if (window.AppModal) {
                AppModal.alert({ title: 'Duração Mínima', message: 'A duração mínima de um treino é de 5 minutos.', type: 'warning' });
            } else {
                alert('❌ A duração mínima de um treino é de 5 minutos.');
            }
            return false;
        }

        if (new Date(data) > new Date()) {
            if (window.AppModal) {
                AppModal.alert({ title: 'Data Inválida', message: 'Não é permitido registrar treinos com data futura.', type: 'error' });
            } else {
                alert('❌ Não é permitido registrar treinos com data futura.');
            }
            return false;
        }

        let temExercicioValido = false;
        selects.forEach(select => {
            if (select.value.trim() !== '') {
                temExercicioValido = true;
            }
        });

        if (!temExercicioValido) {
            if (window.AppModal) {
                AppModal.alert({ title: 'Técnica Necessária', message: 'Selecione pelo menos um exercício/técnica para o seu treino!', type: 'warning' });
            } else {
                alert('⚠️ Selecione pelo menos um exercício/técnica para o seu treino!');
            }
            return false;
        }

        return true;
    }

    // CONTROLE DE EXCLUSÃO (DELETE)
    function confirmarDelecao(treinoId) {
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            document.getElementById('treinoParaDeletar').value = treinoId;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function cancelarDelecao() {
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // MODAL DE ALERTA DE SUCESSO
    function fecharModalAlerta() {
        const modal = document.getElementById('alertModal');
        if (modal) {
            modal.classList.remove('show');
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    // FECHAR MODAIS COM TECLA ESCAPE
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharModalTreino();
            cancelarDelecao();
            fecharModalAlerta();
        }
    });
</script>

<?php include '../includes/toast.php'; ?>
</body>
</html>