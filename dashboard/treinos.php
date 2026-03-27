<?php
session_start();
require '../php/config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit;
}

$usuario_id = $_SESSION['id'];

// Busca faixa do usuário
$sql_user = "SELECT u.nome, f.nome AS faixa_nome, f.ordem AS faixa_ordem 
             FROM usuarios_oyama u 
             LEFT JOIN faixas f ON u.faixa_id = f.id 
             WHERE u.id = '$usuario_id'";
$result_user = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($result_user);
$faixa_ordem = $usuario['faixa_ordem'] ?? 1;

// Salvar treino
$msg_sucesso = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_treino'])) {
    $exercicios = mysqli_real_escape_string($conn, implode(',', $_POST['exercicios'] ?? []));
    $duracao    = (int) $_POST['duracao'];
    $obs        = mysqli_real_escape_string($conn, $_POST['observacoes'] ?? '');
    $data_treino = date('Y-m-d');

    // Tabela precisa existir — script SQL no final do arquivo como comentário
    $sql_insert = "INSERT INTO treinos_registrados (usuario_id, exercicios, duracao_min, observacoes, data_treino)
                   VALUES ('$usuario_id', '$exercicios', '$duracao', '$obs', '$data_treino')";
    if (mysqli_query($conn, $sql_insert)) {
        $msg_sucesso = true;
    }
}

// Últimos treinos
$sql_hist = "SELECT * FROM treinos_registrados WHERE usuario_id = '$usuario_id' ORDER BY data_treino DESC, id DESC LIMIT 5";
$result_hist = mysqli_query($conn, $sql_hist);
$historico = [];
if ($result_hist) {
    while ($row = mysqli_fetch_assoc($result_hist)) $historico[] = $row;
}

// Sugestões por faixa
$sugestoes = [
    // Iniciante (faixas 1-2)
    1 => [
        'label' => 'Iniciante',
        'cor'   => '#ffffff',
        'treinos' => [
            ['nome' => 'Treino Básico de Kihon', 'exercicios' => ['Kihon'], 'duracao' => 30,
             'desc' => 'Oi Tsuki, Age Uke, Gedan Barai — 10x cada lado. Foco em postura (Zenkutsu-dachi).'],
            ['nome' => 'Fundamentos de Geri', 'exercicios' => ['Kihon'], 'duracao' => 40,
             'desc' => 'Mae Geri + Mawashi Geri — 3 séries de 20 repetições. Aquecimento com 10 min de corrida.'],
        ]
    ],
    2 => [
        'label' => 'Laranja',
        'cor'   => '#ff8c00',
        'treinos' => [
            ['nome' => 'Combinações de Kihon', 'exercicios' => ['Kihon', 'Kumite'], 'duracao' => 45,
             'desc' => 'Tsuki + Geri em combinação. Introdução ao Ippon Kumite básico.'],
            ['nome' => 'Kata Taikyoku', 'exercicios' => ['Kata'], 'duracao' => 40,
             'desc' => 'Taikyoku Sono Ichi, Ni e San — 5x cada. Foco na respiração (Kiai).'],
        ]
    ],
    // Intermediário (faixas 3-5)
    3 => [
        'label' => 'Azul',
        'cor'   => '#1a6fdf',
        'treinos' => [
            ['nome' => 'Kata Pinan', 'exercicios' => ['Kata', 'Kihon'], 'duracao' => 50,
             'desc' => 'Pinan Sono Ichi ao Sono Go — revisão completa + correção de bunkai.'],
            ['nome' => 'Kondicionamento + Kumite', 'exercicios' => ['Kumite'], 'duracao' => 60,
             'desc' => '5 rounds de 2 min de Jiyu Kumite com foco em Sabaki e Gedan Mawashi Geri.'],
        ]
    ],
    4 => [
        'label' => 'Amarela',
        'cor'   => '#f5c518',
        'treinos' => [
            ['nome' => 'Potência de Chutes', 'exercicios' => ['Kihon', 'Kumite'], 'duracao' => 55,
             'desc' => 'Low kicks no saco — 5 séries de 30. Uchi Mawashi Geri na mitts.'],
            ['nome' => 'Kata Sokugi', 'exercicios' => ['Kata'], 'duracao' => 45,
             'desc' => 'Sokugi Taikyoku Sono Ichi ao San — ritmo forte, kiai preciso.'],
        ]
    ],
    5 => [
        'label' => 'Verde',
        'cor'   => '#1db954',
        'treinos' => [
            ['nome' => 'Treino de Resistência', 'exercicios' => ['Kihon', 'Kumite'], 'duracao' => 75,
             'desc' => 'Circuito: 100 tsuki + 50 gedan mawashi geri por lado + 10 min sparring leve.'],
            ['nome' => 'Kata Avançado', 'exercicios' => ['Kata'], 'duracao' => 60,
             'desc' => 'Gekisai e Tensho — 5x cada. Estudo de bunkai com parceiro.'],
        ]
    ],
    // Avançado (faixas 6-8)
    6 => [
        'label' => 'Marrom',
        'cor'   => '#8b4513',
        'treinos' => [
            ['nome' => 'Sparring Intenso', 'exercicios' => ['Kumite'], 'duracao' => 90,
             'desc' => '10 rounds de 3 min. Foco em estratégia e controle de distância.'],
            ['nome' => 'Kanku + Garyu', 'exercicios' => ['Kata'], 'duracao' => 60,
             'desc' => 'Kata de faixa marrom — 5x cada com autocrítica de execução.'],
        ]
    ],
    7 => [
        'label' => 'Marrom/Ponta Preta',
        'cor'   => '#5a2d0c',
        'treinos' => [
            ['nome' => 'Preparação para Shodan', 'exercicios' => ['Kihon', 'Kata', 'Kumite'], 'duracao' => 120,
             'desc' => 'Simulação completa de exame de faixa preta. Todos os katas + kumite livre.'],
        ]
    ],
    8 => [
        'label' => 'Preta',
        'cor'   => '#111',
        'treinos' => [
            ['nome' => 'Treinamento Completo', 'exercicios' => ['Kihon', 'Kata', 'Kumite'], 'duracao' => 120,
             'desc' => 'Programa livre avançado. Ênfase em ensinamento e refinamento técnico.'],
        ]
    ],
];

$sugestao_atual = $sugestoes[$faixa_ordem] ?? $sugestoes[1];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treinos | Oyama Hub</title>
    <link rel="icon" href="../img/kyokushinicon.png">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/treinos.css">
</head>
<body>

<!-- NAVBAR (igual ao dashboard) -->
<section class="navbarArea">
    <div class="header">
        <a href="../index.php">Inicio</a>
        <a href="progresso.php">Progresso</a>
        <a href="katas.php">Katas</a>
        <a href="kihon.php">Kihon</a>
        <a href="treinos.php" class="nav-active">Treinos</a>
        <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</section>

<main class="treinos-main">

    <!-- HERO -->
    <div class="treinos-hero">
        <div class="treinos-hero-inner">
            <p class="treinos-kyu">Faixa <?php echo htmlspecialchars($usuario['faixa_nome'] ?? 'Branca'); ?></p>
            <h1 class="treinos-title">TREINOS</h1>
            <p class="treinos-sub">Sugestões para o seu nível + registro do seu treino de hoje.</p>
        </div>
        <div class="treinos-hero-kanji" aria-hidden="true">稽古</div>
    </div>

    <div class="treinos-content">

        <!-- ══════════════════════════════════════
             BLOCO 1 — SUGESTÕES
        ══════════════════════════════════════ -->
        <section class="treinos-section">
            <div class="section-label">
                <span class="label-line"></span>
                <span class="label-text">SUGESTÕES PARA VOCÊ</span>
                <span class="label-line"></span>
            </div>

            <div class="sugestoes-grid">
                <?php foreach ($sugestao_atual['treinos'] as $i => $t): ?>
                <div class="sugestao-card" style="--delay: <?php echo $i * 0.1; ?>s">
                    <div class="sugestao-top">
                        <span class="sugestao-tags">
                            <?php foreach ($t['exercicios'] as $ex): ?>
                            <span class="tag"><?php echo $ex; ?></span>
                            <?php endforeach; ?>
                        </span>
                        <span class="sugestao-dur"><?php echo $t['duracao']; ?> min</span>
                    </div>
                    <h3 class="sugestao-nome"><?php echo htmlspecialchars($t['nome']); ?></h3>
                    <p class="sugestao-desc"><?php echo htmlspecialchars($t['desc']); ?></p>
                    <button class="btn-usar-sugestao"
                            data-exercicios='<?php echo json_encode($t['exercicios']); ?>'
                            data-duracao="<?php echo $t['duracao']; ?>">
                        Usar como base →
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ══════════════════════════════════════
             BLOCO 2 — REGISTRAR TREINO
        ══════════════════════════════════════ -->
        <section class="treinos-section">
            <div class="section-label">
                <span class="label-line"></span>
                <span class="label-text">REGISTRAR TREINO DE HOJE</span>
                <span class="label-line"></span>
            </div>

            <?php if ($msg_sucesso): ?>
            <div class="alert-sucesso">✓ Treino registrado com sucesso!</div>
            <?php endif; ?>

            <form method="POST" class="registro-form" id="formTreino">
                <input type="hidden" name="salvar_treino" value="1">

                <!-- Exercícios -->
                <div class="form-bloco">
                    <label class="form-titulo">O que você treinou?</label>
                    <div class="exercicios-grid">
                        <?php
                        $tipos = [
                            'Kihon'  => 'Técnicas fundamentais',
                            'Kata'   => 'Formas / sequências',
                            'Kumite' => 'Combate / sparring',
                            'Físico' => 'Condicionamento físico',
                            'Livre'  => 'Treino livre',
                        ];
                        foreach ($tipos as $tipo => $desc): ?>
                        <label class="exercicio-check" id="ex-<?php echo strtolower($tipo); ?>">
                            <input type="checkbox" name="exercicios[]" value="<?php echo $tipo; ?>">
                            <div class="check-card">
                                <span class="check-nome"><?php echo $tipo; ?></span>
                                <span class="check-desc"><?php echo $desc; ?></span>
                                <span class="check-mark">✓</span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Duração -->
                <div class="form-bloco">
                    <label class="form-titulo" for="duracao">Duração do treino</label>
                    <div class="duracao-wrapper">
                        <input type="range" id="duracao" name="duracao"
                               min="10" max="180" step="5" value="60"
                               oninput="document.getElementById('duracao-val').textContent = this.value">
                        <div class="duracao-display">
                            <span id="duracao-val">60</span>
                            <span class="duracao-unit">min</span>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="form-bloco">
                    <label class="form-titulo" for="observacoes">Observações livres</label>
                    <textarea id="observacoes" name="observacoes"
                              placeholder="Como foi o treino? O que focou? Dificuldades..."
                              rows="4"></textarea>
                </div>

                <button type="submit" class="btn-registrar">REGISTRAR TREINO</button>
            </form>
        </section>

        <!-- ══════════════════════════════════════
             BLOCO 3 — HISTÓRICO
        ══════════════════════════════════════ -->
        <?php if (!empty($historico)): ?>
        <section class="treinos-section">
            <div class="section-label">
                <span class="label-line"></span>
                <span class="label-text">ÚLTIMOS TREINOS</span>
                <span class="label-line"></span>
            </div>
            <div class="historico-list">
                <?php foreach ($historico as $h): ?>
                <div class="historico-item">
                    <div class="hist-data"><?php echo date('d/m', strtotime($h['data_treino'])); ?></div>
                    <div class="hist-info">
                        <div class="hist-tags">
                            <?php foreach (explode(',', $h['exercicios']) as $ex): ?>
                            <span class="tag"><?php echo trim($ex); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($h['observacoes']): ?>
                        <p class="hist-obs"><?php echo htmlspecialchars($h['observacoes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="hist-dur"><?php echo $h['duracao_min']; ?> min</div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div><!-- /treinos-content -->
</main>

<footer style="text-align:center; padding: 40px; color: #333; font-family: 'Barlow Condensed', sans-serif; letter-spacing: 2px; font-size: 12px;">
    © 2026 OYAMA HUB
</footer>

<script>
// Preenche o form com dados da sugestão clicada
document.querySelectorAll('.btn-usar-sugestao').forEach(btn => {
    btn.addEventListener('click', () => {
        const exercicios = JSON.parse(btn.dataset.exercicios);
        const duracao    = btn.dataset.duracao;

        // Desmarca todos
        document.querySelectorAll('input[name="exercicios[]"]').forEach(cb => cb.checked = false);

        // Marca os da sugestão
        exercicios.forEach(ex => {
            const cb = document.querySelector(`input[name="exercicios[]"][value="${ex}"]`);
            if (cb) cb.checked = true;
        });

        // Atualiza slider de duração
        const slider = document.getElementById('duracao');
        slider.value = duracao;
        document.getElementById('duracao-val').textContent = duracao;

        // Scroll suave até o form
        document.getElementById('formTreino').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>

</body>
</html>