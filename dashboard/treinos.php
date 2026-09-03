<?php
session_start();
require '../php/config.php';
require_once '../php/csrf.php';
require_once '../includes/icons.php';

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
    $mensagem_sucesso = '✅ Treino registrado com sucesso! Parabéns pela dedicação! 💪';
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_deletado') {
    $mensagem_sucesso = '✅ Treino deletado com sucesso!';
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_atualizado') {
    $mensagem_sucesso = '✅ Treino atualizado com sucesso!';
} elseif (isset($_GET['erro'])) {
    $erro = $_GET['erro'];
    switch($erro) {
        case 'data_futura':
            $mensagem_erro = '❌ Erro: Não é permitido registrar treinos com data futura.';
            break;
        case 'campos_obrigatorios':
            $mensagem_erro = '❌ Erro: Preencha todos os campos obrigatórios.';
            break;
        case 'sem_exercicios':
            $mensagem_erro = '❌ Erro: Adicione pelo menos um exercício ao treino.';
            break;
        case 'banco_dados':
            $mensagem_erro = '❌ Erro ao salvar treino. Tente novamente.';
            break;
        default:
            $mensagem_erro = '❌ Erro desconhecido. Tente novamente.';
    }
}

// Filtros e Paginação
$filtro_mes = intval($_GET['mes'] ?? 0);
$filtro_ano = intval($_GET['ano'] ?? 0);
$busca      = trim($_GET['busca'] ?? '');
$pagina     = max(1, intval($_GET['pagina'] ?? 1));
$limite     = 6; // Treinos por página
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

// Sugestões por faixa
$treino_sugestoes = [
    1 => ['nivel' => 'Iniciante', 'cor' => '#ffffff', 'treinos' => [
        ['nome' => 'Fundamentos Básicos', 'duracao' => 30, 'desc' => 'Oi Tsuki, Age Uke, Gedan Barai — 10x cada lado.'],
        ['nome' => 'Aquecimento + Geri', 'duracao' => 40, 'desc' => 'Mae Geri + Mawashi Geri — 3 séries de 20 repetições.']
    ]],
    2 => ['nivel' => 'Laranja', 'cor' => '#ff8c00', 'treinos' => [
        ['nome' => 'Combinações de Kihon', 'duracao' => 45, 'desc' => 'Tsuki + Geri em combinação.'],
        ['nome' => 'Kata Taikyoku', 'duracao' => 40, 'desc' => 'Taikyoku Sono Ichi, Ni e San — 5x cada.']
    ]],
    3 => ['nivel' => 'Azul', 'cor' => '#1a6fdf', 'treinos' => [
        ['nome' => 'Kata Pinan', 'duracao' => 50, 'desc' => 'Pinan Sono Ichi ao Sono Go — revisão completa.'],
        ['nome' => 'Kumite', 'duracao' => 60, 'desc' => '5 rounds de 2 min de Jiyu Kumite.']
    ]],
];
$sugestoes_treino = $treino_sugestoes[$faixa_ordem] ?? $treino_sugestoes[1];

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
<!-- ── Navbar ── -->
<?php include '../includes/navbar.php'; ?>

<!-- ── Main Content ── -->
<main class="treinos-container">
    <!-- Header -->
    <section class="treinos-header">
        <h1>MEUS TREINOS</h1>
        <p>Osu, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>! Registre seus treinos e acompanhe o histórico.</p>
    </section>

    <!-- Filtro de data -->
    <form method="GET" class="treinos-filtro">
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
        <button type="submit" class="filtro-btn">Filtrar</button>
        <?php if ($filtro_mes || $filtro_ano): ?>
            <a href="treinos.php" class="filtro-limpar">Limpar</a>
        <?php endif; ?>
    </form>

    <!-- Mensagens de Confirmação/Erro -->
    <?php if (!empty($mensagem_erro)): ?>
        <div class="mensagem-erro">
            <?php echo $mensagem_erro; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert-modal show" id="alertModal">
            <div class="alert-box success-box">
                <div class="alert-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3>Sucesso!</h3>
                <p><?php echo $mensagem_sucesso; ?></p>
                <button type="button" class="alert-close" onclick="fecharModal()">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="delete-confirm-modal" id="deleteConfirmModal">
        <div class="delete-confirm-box">
            <div class="alert-icon warning-icon">
                <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3>Excluir Treino?</h3>
            <p>Tem certeza que deseja excluir este treino? Esta ação não pode ser desfeita.</p>
            <div class="delete-confirm-buttons">
                <button type="button" class="btn-cancelar" onclick="cancelarDelecao()">Cancelar</button>
                <button type="button" class="btn-confirmar-delete" onclick="executarDelecao()">Excluir</button>
            </div>
            <input type="hidden" id="treinoParaDeletar" value="">
        </div>
    </div>

    <div class="treinos-layout">
        <!-- Coluna Esquerda: Sugestões e Histórico -->
        <div class="treinos-coluna-esquerda">
            <!-- Sugestões -->
            <section class="treinos-sugestoes">
                <h2>SUGESTÕES: FAIXA <?php echo mb_strtoupper($sugestoes_treino['nivel'], 'UTF-8'); ?></h2>
                <div class="sugestoes-list">
                    <?php foreach ($sugestoes_treino['treinos'] as $sug): ?>
                        <div class="sugestao-card" style="border-left-color: <?= $sugestoes_treino['cor'] ?>">
                            <div class="sugestao-header">
                                <h3><?= $sug['nome'] ?></h3>
                                <span class="duracao" style="color: <?= $sugestoes_treino['cor'] ?>; border: 1px solid <?= $sugestoes_treino['cor'] ?>; background: transparent;">⏱ <?= $sug['duracao'] ?> min</span>
                            </div>
                            <p><?= $sug['desc'] ?></p>
                            <button type="button" class="btn-iniciar" onclick="registrarTreino('<?= addslashes($sug['nome']) ?>', <?= $sug['duracao'] ?>)">Usar Sugestão</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Histórico -->
            <section class="treinos-historico">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                    <h2 style="margin: 0;">HISTÓRICO (<?= $total_treinos_count ?> total)</h2>
                    
                    <!-- Busca rápida por texto -->
                    <form method="GET" action="treinos.php" style="display: flex; gap: 6px; align-items: center;">
                        <?php if ($filtro_mes): ?><input type="hidden" name="mes" value="<?= $filtro_mes ?>"><?php endif; ?>
                        <?php if ($filtro_ano): ?><input type="hidden" name="ano" value="<?= $filtro_ano ?>"><?php endif; ?>
                        <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar treino..." 
                               style="padding: 6px 12px; background: var(--surface, #181818); border: 1px solid var(--border, rgba(255,255,255,0.1)); color: var(--white, #fff); font-family: inherit; font-size: 13px; border-radius: 3px;">
                        <button type="submit" style="padding: 6px 12px; background: var(--red, #c8000a); border: none; color: #fff; cursor: pointer; font-family: inherit; font-size: 13px; border-radius: 3px;">Buscar</button>
                        <?php if ($busca !== '' || $filtro_mes > 0 || $filtro_ano > 0): ?>
                            <a href="treinos.php" style="color: var(--muted, #888); font-size: 12px; text-decoration: none; padding: 4px;">Limpar</a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (count($treinos_list) > 0): ?>
                    <div class="historico-list">
                        <?php foreach ($treinos_list as $t): ?>
                            <article class="historico-card">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($t['observacoes'] ?: 'Treino Registrado'); ?></h3>
                                    <time><?php echo date('d/m/Y', strtotime($t['data_treino'])); ?></time>
                                </div>
                                <div class="card-duracao">
                                    ⏱ <?php echo intval($t['duracao_min']); ?> minutos
                                </div>
                                <div class="card-buttons">
                                    <button type="button" class="btn-editar-treino" onclick="window.location.href='editar_treino.php?id=<?php echo $t['id']; ?>'">
                                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        Editar
                                    </button>
                                    <button type="button" class="btn-deletar-treino" onclick="confirmarDelecao(<?php echo $t['id']; ?>)">
                                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        Excluir
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                        <nav aria-label="Navegação de treinos" style="display: flex; justify-content: center; gap: 8px; margin-top: 24px; align-items: center; flex-wrap: wrap;">
                            <?php
                            $queryParams = $_GET;
                            $linkPage = function($p) use ($queryParams) {
                                $queryParams['pagina'] = $p;
                                return '?' . http_build_query($queryParams);
                            };
                            ?>

                            <?php if ($pagina > 1): ?>
                                <a href="<?= $linkPage($pagina - 1) ?>" style="padding: 6px 14px; background: var(--surface, #181818); border: 1px solid var(--border, rgba(255,255,255,0.1)); color: var(--white, #fff); text-decoration: none; border-radius: 3px; font-size: 13px;">← Anterior</a>
                            <?php endif; ?>

                            <span style="font-size: 13px; color: var(--muted, #888); padding: 0 8px;">
                                Página <?= $pagina ?> de <?= $total_paginas ?>
                            </span>

                            <?php if ($pagina < $total_paginas): ?>
                                <a href="<?= $linkPage($pagina + 1) ?>" style="padding: 6px 14px; background: var(--surface, #181818); border: 1px solid var(--border, rgba(255,255,255,0.1)); color: var(--white, #fff); text-decoration: none; border-radius: 3px; font-size: 13px;">Próxima →</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="vazio">
                        <p>Nenhum treino registrado ainda.</p>
                        <small>Comece a treinar e apareça aqui!</small>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Coluna Direita: Formulário -->
        <div class="treinos-coluna-direita">
            <section class="registrar-treino-section">
                <h2>REGISTRAR UM TREINO</h2>
                <form method="POST" action="registrar_treino.php" class="form-treino" id="formTreino">
                    <?= csrf_input() ?>
                    <div class="form-group">
                        <label for="data-treino">Data do Treino</label>
                        <input type="date" id="data-treino" name="data_treino" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nome-treino">Nome/Descrição do Treino</label>
                        <input type="text" id="nome-treino" name="observacoes" placeholder="Ex: Treino de Kumite" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duracao-treino">Duração (minutos)</label>
                        <input type="number" id="duracao-treino" name="duracao_min" min="5" max="240" step="5" placeholder="Ex: 60" required>
                    </div>

                    <!-- RF02 e RF03 – Exercícios com Séries e Repetições -->
                    <div class="exercicios-section">
                        <h3>🏋️ EXERCÍCIOS</h3>
                        <div id="exercicios-container">
                            <div class="exercicio-row" data-index="0">
                                <select name="exercicios[0][descricao]" class="exercicio-select" required>
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
                                </select>
                                <input type="number" name="exercicios[0][series]" placeholder="Séries" min="0" max="10">
                                <input type="number" name="exercicios[0][repeticoes]" placeholder="Repetições" min="0" max="100">
                                <button type="button" class="btn-remover-exercicio" onclick="removerExercicio(this)">✕</button>
                            </div>
                        </div>

                        <button type="button" class="btn-adicionar-exercicio" onclick="adicionarExercicio()">+ Adicionar Exercício</button>
                    </div>

                    <button type="submit" class="btn-registrar" onclick="return validarFormulario()">
                        ✓ Registrar Treino
                    </button>
                </form>
            </section>
        </div>
    </div>
</main>

<script>
    let indiceExercicio = 1;

    function registrarTreino(nome, duracao) {
        document.getElementById('nome-treino').value = nome;
        document.getElementById('duracao-treino').value = duracao;
        document.querySelector('.form-treino').scrollIntoView({ behavior: 'smooth' });
    }

    function fecharModal() {
        const modal = document.getElementById('alertModal');
        if (modal) {
            modal.classList.remove('show');
            // Remove ?sucesso da URL para não mostrar de novo se recarregar
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    function confirmarDelecao(treinoId) {
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.classList.add('show');
            document.getElementById('treinoParaDeletar').value = treinoId;
        }
    }

    function cancelarDelecao() {
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }

    function executarDelecao() {
        const treinoId = document.getElementById('treinoParaDeletar').value;
        window.location.href = 'deletar_treino.php?id=' + treinoId;
    }

    // RF02 – Adicionar Exercício Dinamicamente
    function adicionarExercicio() {
        const container = document.getElementById('exercicios-container');
        const select = document.querySelector('.exercicio-select');
        
        const novaRow = document.createElement('div');
        novaRow.className = 'exercicio-row';
        novaRow.dataset.index = indiceExercicio;
        novaRow.innerHTML = `
            <select name="exercicios[${indiceExercicio}][descricao]" class="exercicio-select" required>
                <option value="">Selecione um exercício...</option>
                ${select.innerHTML}
            </select>
            <input type="number" name="exercicios[${indiceExercicio}][series]" placeholder="Séries" min="0" max="10">
            <input type="number" name="exercicios[${indiceExercicio}][repeticoes]" placeholder="Repetições" min="0" max="100">
            <button type="button" class="btn-remover-exercicio" onclick="removerExercicio(this)">✕</button>
        `;
        
        container.appendChild(novaRow);
        indiceExercicio++;
    }

    // RF04 – Remover Exercício
    function removerExercicio(btn) {
        const rows = document.querySelectorAll('.exercicio-row');
        if (rows.length > 1) {
            btn.closest('.exercicio-row').remove();
        } else {
            alert('⚠️ Adicione pelo menos um exercício!');
        }
    }

    // RF05 – Validar Formulário
    function validarFormulario() {
        const data = document.getElementById('data-treino').value;
        const duracao = parseInt(document.getElementById('duracao-treino').value);
        const exercicios = document.querySelectorAll('.exercicio-row select');
        
        let temExercicio = false;
        exercicios.forEach(ex => {
            if (ex.value) temExercicio = true;
        });

        if (!data || duracao < 5) {
            alert('❌ Preencha todos os campos corretamente.');
            return false;
        }

        if (new Date(data) > new Date()) {
            alert('❌ Não é permitido registrar treinos com data futura.');
            return false;
        }

        if (!temExercicio) {
            alert('⚠️ Adicione pelo menos um exercício!');
            return false;
        }

        return true;
    }

    // O controle do tema já é feito pelo includes/navbar.php,
    // não precisamos duplicar a lógica aqui e causar erros.
</script>

<?php include '../includes/toast.php'; ?>
</body>
</html>