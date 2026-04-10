<?php
session_start();
require '../php/config.php';

// RNF04 – Validação de Sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$usuario_nome = $_SESSION['nome'] ?? 'Praticante';

// Buscar dados do usuário
$sql_user = "SELECT u.nome, f.nome AS faixa_nome, f.ordem AS faixa_ordem 
             FROM usuarios u 
             LEFT JOIN faixas f ON u.faixa_id = f.id 
             WHERE u.id = '$usuario_id'";
$result_user = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($result_user);
$faixa_ordem = $usuario['faixa_ordem'] ?? 1;

// RF03 – Listar Exercícios agrupados por categoria
$sql_exercicios = "SELECT id, nome, categoria FROM exercicios_kyokushin ORDER BY categoria, nome";
$result_exercicios = mysqli_query($conn, $sql_exercicios);
$exercicios_por_categoria = [];

while ($ex = mysqli_fetch_assoc($result_exercicios)) {
    $cat = $ex['categoria'];
    if (!isset($exercicios_por_categoria[$cat])) {
        $exercicios_por_categoria[$cat] = [];
    }
    $exercicios_por_categoria[$cat][] = $ex;
}

// RF07 – Mensagens de Feedback
$mensagem_sucesso = '';
$mensagem_erro = '';

if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'treino_registrado') {
    $mensagem_sucesso = '✅ Treino registrado com sucesso! Parabéns pela dedicação! 💪';
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

// Buscar histórico de treinos
$sql_treinos = "SELECT id, duracao_min, observacoes, data_treino, criado_em 
                FROM treinos 
                WHERE usuario_id = '$usuario_id' 
                ORDER BY data_treino DESC, criado_em DESC 
                LIMIT 10";
$result_treinos = mysqli_query($conn, $sql_treinos);
$treinos_list = [];
while ($row = mysqli_fetch_assoc($result_treinos)) {
    $treinos_list[] = $row;
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

<!-- ── Navbar ── -->
<section class="navbarArea">
    <div class="header">
        <a href="../php/dashboard.php">Início</a>
        <a href="progresso.php">Progresso</a>
        <a href="katas.php">Katas</a>
        <a href="kihon.php">Kihon</a>
        <a href="treinos.php" class="active">Treinos</a>
        <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</section>

<!-- ── Main Content ── -->
<main class="treinos-container">
    <!-- Header -->
    <section class="treinos-header">
        <h1>MEUS TREINOS</h1>
        <p>Osu, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>! Registre seus treinos e acompanhe o histórico.</p>
    </section>

    <!-- Mensagens de Confirmação/Erro -->
    <?php if (!empty($mensagem_erro)): ?>
        <div class="mensagem-erro">
            <?php echo $mensagem_erro; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert-modal show" id="alertModal">
            <div class="alert-box">
                <p><?php echo $mensagem_sucesso; ?></p>
                <button type="button" class="alert-close" onclick="fecharModal()">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção: Registrar Treino -->
    <section class="registrar-treino-section">
        <h2>REGISTRAR UM TREINO</h2>
        <form method="POST" action="registrar_treino.php" class="form-treino" id="formTreino">
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
                        <input type="number" name="exercicios[0][repeticoes]" placeholder="Repetições" min="0" max="50">
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

    <!-- Histórico -->
    <section class="treinos-historico">
        <h2>HISTÓRICO (Últimos 10)</h2>
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
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="vazio">
                <p>Nenhum treino registrado ainda.</p>
                <small>Comece a treinar e apareça aqui!</small>
            </div>
        <?php endif; ?>
    </section>
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
        }
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
            <input type="number" name="exercicios[${indiceExercicio}][repeticoes]" placeholder="Repetições" min="0" max="50">
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
</script>

</body>
</html>