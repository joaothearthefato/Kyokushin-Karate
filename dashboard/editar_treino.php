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
$treino_id = intval($_GET['id'] ?? 0);

// Validar ID do treino
if ($treino_id <= 0) {
    header("Location: treinos.php?erro=treino_invalido");
    exit();
}

// Buscar dados do treino
$sql_treino = "SELECT id, data_treino, duracao_min, observacoes 
               FROM treinos 
               WHERE id = '$treino_id' AND usuario_id = '$usuario_id'";
$result_treino = mysqli_query($conn, $sql_treino);
$treino = mysqli_fetch_assoc($result_treino);

if (!$treino) {
    header("Location: treinos.php?erro=acesso_negado");
    exit();
}

// Buscar exercícios do treino
$sql_exercicios_treino = "SELECT descricao, series, repeticoes 
                          FROM treino_exercicios 
                          WHERE treino_id = '$treino_id'";
$result_exercicios_treino = mysqli_query($conn, $sql_exercicios_treino);
$exercicios_do_treino = [];
while ($ex = mysqli_fetch_assoc($result_exercicios_treino)) {
    $exercicios_do_treino[] = $ex;
}

// Buscar todos os exercícios disponíveis
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

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Treino | Kyokushin</title>
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
        <button id="theme-toggle" class="theme-btn" aria-label="Alternar tema">
            <span class="theme-icon">🌙</span>
            <span class="theme-label">Modo Escuro</span>
        </button>
        <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</section>

<!-- ── Main Content ── -->
<main class="treinos-container">
    <!-- Header -->
    <section class="treinos-header">
        <h1>EDITAR TREINO</h1>
        <p>Osu, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>! Atualize os dados do seu treino.</p>
    </section>

    <!-- Seção: Editar Treino -->
    <section class="registrar-treino-section">
        <h2>EDITAR TREINO</h2>
        <form method="POST" action="atualizar_treino.php" class="form-treino" id="formTreino">
            <input type="hidden" name="treino_id" value="<?php echo $treino['id']; ?>">

            <div class="form-group">
                <label for="data-treino">Data do Treino</label>
                <input type="date" id="data-treino" name="data_treino" value="<?php echo $treino['data_treino']; ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="nome-treino">Nome/Descrição do Treino</label>
                <input type="text" id="nome-treino" name="observacoes" value="<?php echo htmlspecialchars($treino['observacoes']); ?>" placeholder="Ex: Treino de Kumite" required>
            </div>
            
            <div class="form-group">
                <label for="duracao-treino">Duração (minutos)</label>
                <input type="number" id="duracao-treino" name="duracao_min" value="<?php echo $treino['duracao_min']; ?>" min="5" max="240" step="5" placeholder="Ex: 60" required>
            </div>

            <!-- Exercícios com Séries e Repetições -->
            <div class="exercicios-section">
                <h3>🏋️ EXERCÍCIOS</h3>
                <div id="exercicios-container">
                    <?php if (!empty($exercicios_do_treino)): ?>
                        <?php foreach ($exercicios_do_treino as $idx => $ex): ?>
                            <div class="exercicio-row" data-index="<?php echo $idx; ?>">
                                <select name="exercicios[<?php echo $idx; ?>][descricao]" class="exercicio-select" required>
                                    <option value="">Selecione um exercício...</option>
                                    <?php foreach ($exercicios_por_categoria as $categoria => $exercicios): ?>
                                        <optgroup label="<?php echo htmlspecialchars($categoria); ?>">
                                            <?php foreach ($exercicios as $e): ?>
                                                <option value="<?php echo htmlspecialchars($e['nome']); ?>" <?php echo ($e['nome'] === $ex['descricao']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($e['nome']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="exercicios[<?php echo $idx; ?>][series]" value="<?php echo $ex['series']; ?>" placeholder="Séries" min="0" max="10">
                                <input type="number" name="exercicios[<?php echo $idx; ?>][repeticoes]" value="<?php echo $ex['repeticoes']; ?>" placeholder="Repetições" min="0" max="50">
                                <button type="button" class="btn-remover-exercicio" onclick="removerExercicio(this)">✕</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="exercicio-row" data-index="0">
                            <select name="exercicios[0][descricao]" class="exercicio-select" required>
                                <option value="">Selecione um exercício...</option>
                                <?php foreach ($exercicios_por_categoria as $categoria => $exercicios): ?>
                                    <optgroup label="<?php echo htmlspecialchars($categoria); ?>">
                                        <?php foreach ($exercicios as $e): ?>
                                            <option value="<?php echo htmlspecialchars($e['nome']); ?>">
                                                <?php echo htmlspecialchars($e['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="exercicios[0][series]" placeholder="Séries" min="0" max="10">
                            <input type="number" name="exercicios[0][repeticoes]" placeholder="Repetições" min="0" max="50">
                            <button type="button" class="btn-remover-exercicio" onclick="removerExercicio(this)">✕</button>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn-adicionar-exercicio" onclick="adicionarExercicio()">+ Adicionar Exercício</button>
            </div>

            <div class="form-buttons">
                <button type="button" class="btn-cancelar" onclick="window.location.href='treinos.php'">Cancelar</button>
                <button type="submit" class="btn-registrar" onclick="return validarFormulario()">✓ Atualizar Treino</button>
            </div>
        </form>
    </section>
</main>

<script>
    let indiceExercicio = <?php echo max(count($exercicios_do_treino), 1); ?>;

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

    function removerExercicio(btn) {
        const rows = document.querySelectorAll('.exercicio-row');
        if (rows.length > 1) {
            btn.closest('.exercicio-row').remove();
        } else {
            alert('⚠️ Adicione pelo menos um exercício!');
        }
    }

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

    // ── Theme Toggle ──
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle.querySelector('.theme-icon');
    const themeLabel = themeToggle.querySelector('.theme-label');
    const html = document.documentElement;

    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        html.classList.add('light');
        themeIcon.textContent = '☀️';
        themeLabel.textContent = 'Modo Claro';
    }

    // Toggle theme
    themeToggle.addEventListener('click', () => {
        html.classList.toggle('light');
        const isLight = html.classList.contains('light');
        
        if (isLight) {
            themeIcon.textContent = '☀️';
            themeLabel.textContent = 'Modo Claro';
            localStorage.setItem('theme', 'light');
        } else {
            themeIcon.textContent = '🌙';
            themeLabel.textContent = 'Modo Escuro';
            localStorage.setItem('theme', 'dark');
        }
    });
</script>

</body>
</html>
