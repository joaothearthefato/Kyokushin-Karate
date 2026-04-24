<?php
session_start();
require '../php/config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

// RF07 – Processar edição de perfil
$msg_sucesso = '';
$msg_erro    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_perfil') {
    $nome      = trim($_POST['nome'] ?? '');
    $nascimento = $_POST['nascimento'] ?? '';
    $faixa_id  = intval($_POST['faixa_id'] ?? 0);
    $nova_senha = $_POST['nova_senha'] ?? '';
    $conf_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($nascimento)) {
        $msg_erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!empty($nova_senha) && $nova_senha !== $conf_senha) {
        $msg_erro = 'As senhas não coincidem.';
    } else {
        if (!empty($nova_senha)) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql  = "UPDATE usuarios SET nome='$nome', nascimento='$nascimento', faixa_id=$faixa_id, senha_hash='$hash' WHERE id=$usuario_id";
        } else {
            $sql = "UPDATE usuarios SET nome='$nome', nascimento='$nascimento', faixa_id=$faixa_id WHERE id=$usuario_id";
        }
        if (mysqli_query($conn, $sql)) {
            $_SESSION['nome'] = $nome;
            $msg_sucesso = 'Perfil atualizado com sucesso!';
        } else {
            $msg_erro = 'Erro ao atualizar perfil. Tente novamente.';
        }
    }
}

// RF06 – Buscar dados do usuário
$sql_user = "SELECT u.nome, u.email, u.nascimento, u.tipo, u.criado_em,
                    f.nome AS faixa_nome, f.ordem AS faixa_ordem, f.id AS faixa_id
             FROM usuarios u
             LEFT JOIN faixas f ON u.faixa_id = f.id
             WHERE u.id = $usuario_id";
$result = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($result);

// RF08 – Buscar progresso geral (resumo rápido)
$sql_treinos_count = "SELECT COUNT(*) as total, SUM(duracao_min) as total_min FROM treinos WHERE usuario_id = $usuario_id";
$r_treinos = mysqli_query($conn, $sql_treinos_count);
$dados_treino = mysqli_fetch_assoc($r_treinos);
$total_treinos = $dados_treino['total'] ?? 0;
$total_minutos = $dados_treino['total_min'] ?? 0;

$sql_katas_concluidos = "SELECT COUNT(*) as total FROM progresso WHERE usuario_id = $usuario_id AND tipo = 'kata' AND concluido = 1";
$r_katas = mysqli_query($conn, $sql_katas_concluidos);
$katas_concluidos = mysqli_fetch_assoc($r_katas)['total'] ?? 0;

$sql_kihons_concluidos = "SELECT COUNT(*) as total FROM progresso WHERE usuario_id = $usuario_id AND tipo = 'kihon' AND concluido = 1";
$r_kihons = mysqli_query($conn, $sql_kihons_concluidos);
$kihons_concluidos = mysqli_fetch_assoc($r_kihons)['total'] ?? 0;

// Buscar todas as faixas para o select
$sql_faixas = "SELECT id, nome FROM faixas ORDER BY ordem";
$r_faixas   = mysqli_query($conn, $sql_faixas);
$faixas     = [];
if ($r_faixas) {
    while ($f = mysqli_fetch_assoc($r_faixas)) $faixas[] = $f;
}

// Calcular idade
$nascimento_dt = new DateTime($usuario['nascimento'] ?? 'now');
$hoje = new DateTime();
$idade = $hoje->diff($nascimento_dt)->y;

// Membro há...
$criado = new DateTime($usuario['criado_em'] ?? 'now');
$membro_dias = $hoje->diff($criado)->days;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Oyama Hub</title>
    <meta name="description" content="Visualize e edite seus dados pessoais no Oyama Hub.">
    <link rel="icon" href="../img/kyokushinicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&family=Barlow+Condensed:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <!-- Tema aplicado antes de renderizar para evitar flash -->
    <script>
        (function () {
            const t = localStorage.getItem('oyama-theme');
            if (t === 'light') {
                document.documentElement.classList.add('light');
                document.body && document.body.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="perfil-container">

    <!-- ── Header ── -->
    <section class="perfil-hero">
        <div class="perfil-avatar">
            <span><?= strtoupper(substr($usuario['nome'], 0, 1)) ?></span>
        </div>
        <div class="perfil-hero-info">
            <h1><?= htmlspecialchars($usuario['nome']) ?></h1>
            <p class="perfil-tipo"><?= ucfirst($usuario['tipo'] ?? 'Aluno') ?> · <?= htmlspecialchars($usuario['faixa_nome'] ?? 'Sem faixa') ?></p>
            <p class="perfil-meta">Membro há <strong><?= $membro_dias ?></strong> dias · <?= $idade ?> anos</p>
        </div>
    </section>

    <!-- ── Mensagens ── -->
    <?php if ($msg_erro): ?>
        <div class="perfil-alert perfil-alert-erro"><?= $msg_erro ?></div>
    <?php endif; ?>
    <?php if ($msg_sucesso): ?>
        <div class="perfil-alert perfil-alert-sucesso"><?= $msg_sucesso ?></div>
    <?php endif; ?>

    <!-- ── Grid Principal ── -->
    <div class="perfil-grid">

        <!-- Coluna 1: Dados + Edição -->
        <div class="perfil-col-left">

            <!-- RF06 – Visualizar dados pessoais -->
            <section class="perfil-card">
                <div class="perfil-card-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <h2>DADOS PESSOAIS</h2>
                </div>
                <ul class="dados-list">
                    <li>
                        <span class="dado-label">Nome</span>
                        <span class="dado-valor"><?= htmlspecialchars($usuario['nome']) ?></span>
                    </li>
                    <li>
                        <span class="dado-label">E-mail</span>
                        <span class="dado-valor"><?= htmlspecialchars($usuario['email']) ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Nascimento</span>
                        <span class="dado-valor"><?= date('d/m/Y', strtotime($usuario['nascimento'])) ?> (<?= $idade ?> anos)</span>
                    </li>
                    <li>
                        <span class="dado-label">Faixa Atual</span>
                        <span class="dado-valor faixa-badge"><?= htmlspecialchars($usuario['faixa_nome'] ?? '—') ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Tipo de Conta</span>
                        <span class="dado-valor"><?= ucfirst($usuario['tipo'] ?? 'aluno') ?></span>
                    </li>
                    <li>
                        <span class="dado-label">Membro desde</span>
                        <span class="dado-valor"><?= date('d/m/Y', strtotime($usuario['criado_em'])) ?></span>
                    </li>
                </ul>
            </section>

            <!-- RF07 – Editar perfil -->
            <section class="perfil-card">
                <div class="perfil-card-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <h2>EDITAR PERFIL</h2>
                </div>
                <form method="POST" class="perfil-form" id="formPerfil">
                    <input type="hidden" name="action" value="editar_perfil">

                    <div class="form-group">
                        <label for="p-nome">Nome Completo</label>
                        <input type="text" id="p-nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="p-nascimento">Data de Nascimento</label>
                        <input type="date" id="p-nascimento" name="nascimento" value="<?= $usuario['nascimento'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="p-faixa">Faixa Atual</label>
                        <select id="p-faixa" name="faixa_id">
                            <?php foreach ($faixas as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($f['id'] == ($usuario['faixa_id'] ?? 0)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-separator">
                        <span>ALTERAR SENHA (opcional)</span>
                    </div>

                    <div class="form-group">
                        <label for="p-nova-senha">Nova Senha</label>
                        <input type="password" id="p-nova-senha" name="nova_senha" placeholder="Deixe vazio para não alterar" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="p-confirmar-senha">Confirmar Nova Senha</label>
                        <input type="password" id="p-confirmar-senha" name="confirmar_senha" placeholder="Repita a nova senha" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn-salvar-perfil">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Salvar Alterações
                    </button>
                </form>
            </section>

        </div>

        <!-- Coluna 2: Progresso Geral (RF08) -->
        <div class="perfil-col-right">

            <section class="perfil-card">
                <div class="perfil-card-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <h2>PROGRESSO GERAL</h2>
                </div>

                <div class="progresso-stats">
                    <div class="stat-item">
                        <div class="stat-icon red-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div class="stat-text">
                            <span class="stat-number"><?= $total_treinos ?></span>
                            <span class="stat-label">Treinos Realizados</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon gold-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="stat-text">
                            <span class="stat-number"><?= round($total_minutos / 60, 1) ?>h</span>
                            <span class="stat-label">Horas Treinadas</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon green-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="stat-text">
                            <span class="stat-number"><?= $katas_concluidos ?></span>
                            <span class="stat-label">Katas Concluídos</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon blue-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                        </div>
                        <div class="stat-text">
                            <span class="stat-number"><?= $kihons_concluidos ?></span>
                            <span class="stat-label">Kihons Dominados</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Jornada da Faixa -->
            <section class="perfil-card">
                <div class="perfil-card-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                    <h2>JORNADA DE FAIXAS</h2>
                </div>
                <?php
                $faixas_cores = ['#ffffff','#ff8c00','#1a6fdf','#f5c518','#27ae60','#6b3a2a','#5a2d0c','#1a1a1a'];
                $faixa_atual_ordem = $usuario['faixa_ordem'] ?? 1;
                ?>
                <div class="faixas-journey">
                    <?php foreach ($faixas as $i => $f): ?>
                        <?php
                        $cores_map = ['#f2f2f2','#ff8c00','#1a6fdf','#f5c518','#27ae60','#6b3a2a','#5a2d0c','#1a1a1a'];
                        $cor = $cores_map[$i] ?? '#888';
                        $concluida = ($i + 1) < $faixa_atual_ordem;
                        $atual = ($i + 1) == $faixa_atual_ordem;
                        ?>
                        <div class="faixa-step <?= $concluida ? 'concluida' : ($atual ? 'atual' : 'pendente') ?>">
                            <div class="faixa-belt" style="background: <?= $cor ?>; <?= $cor === '#f2f2f2' ? 'border: 1px solid #ccc;' : '' ?>">
                                <?php if ($concluida): ?>
                                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="#19c36d" stroke-width="3" fill="none"><polyline points="20 6 9 17 4 12"/></svg>
                                <?php elseif ($atual): ?>
                                    <div class="belt-pulse"></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($i < count($faixas) - 1): ?>
                                <div class="faixa-connector <?= $concluida ? 'done' : '' ?>"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="faixas-legend">
                    Faixa atual: <strong style="color:var(--red)"><?= htmlspecialchars($usuario['faixa_nome'] ?? '—') ?></strong>
                </p>
            </section>

            <!-- Link para progresso completo -->
            <a href="progresso.php" class="btn-ver-progresso">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Ver Relatório de Progresso Completo
            </a>

        </div>
    </div>

</main>
</body>
</html>
