<?php
session_start();

if (!isset($_SESSION['id'])) {
  header('Location: login.php');
  exit;
}

require('config.php');

$usuarioId = (int) $_SESSION['id'];

function colunaExiste(mysqli $conn, string $tabela, string $coluna): bool
{
  $tabelaSeguro = mysqli_real_escape_string($conn, $tabela);
  $colunaSeguro = mysqli_real_escape_string($conn, $coluna);
  $sql = "SHOW COLUMNS FROM `$tabelaSeguro` LIKE '$colunaSeguro'";
  $resultado = mysqli_query($conn, $sql);

  return $resultado && mysqli_num_rows($resultado) > 0;
}

$temIdade = colunaExiste($conn, 'usuarios_oyama', 'idade');
$temAltura = colunaExiste($conn, 'usuarios_oyama', 'altura');
$temPeso = colunaExiste($conn, 'usuarios_oyama', 'peso');
$temFaixa = colunaExiste($conn, 'usuarios_oyama', 'faixa_id');

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar_perfil') {
  $idade = isset($_POST['idade']) ? (int) $_POST['idade'] : null;
  $altura = isset($_POST['altura']) ? (float) $_POST['altura'] : null;
  $peso = isset($_POST['peso']) ? (float) $_POST['peso'] : null;
  $faixaId = isset($_POST['faixa_id']) ? (int) $_POST['faixa_id'] : null;

  if ($idade !== null && ($idade < 1 || $idade > 120)) {
    $erro = 'Idade inválida.';
  } elseif ($altura !== null && ($altura < 50 || $altura > 250)) {
    $erro = 'Altura inválida.';
  } elseif ($peso !== null && ($peso < 10 || $peso > 300)) {
    $erro = 'Peso inválido.';
  } else {
    $sets = [];

    if ($temIdade) {
      $sets[] = 'idade = ' . ($idade !== null ? $idade : 'NULL');
    }

    if ($temAltura) {
      $sets[] = 'altura = ' . ($altura !== null ? $altura : 'NULL');
    }

    if ($temPeso) {
      $sets[] = 'peso = ' . ($peso !== null ? $peso : 'NULL');
    }

    if ($temFaixa) {
      $sets[] = 'faixa_id = ' . ($faixaId > 0 ? $faixaId : 'NULL');
    }

    if (!empty($sets)) {
      $sqlUpdate = 'UPDATE usuarios_oyama SET ' . implode(', ', $sets) . " WHERE id = $usuarioId";
      if (mysqli_query($conn, $sqlUpdate)) {
        $mensagem = 'Perfil atualizado com sucesso!';
      } else {
        $erro = 'Não foi possível atualizar o perfil.';
      }
    }
  }
}

$selectCampos = [
  'u.id',
  'u.nome',
  'u.email',
  'u.tipo',
  'u.faixa_id',
  'f.nome AS faixa_nome',
  'f.ordem AS faixa_ordem'
];

if ($temIdade) {
  $selectCampos[] = 'u.idade';
}
if ($temAltura) {
  $selectCampos[] = 'u.altura';
}
if ($temPeso) {
  $selectCampos[] = 'u.peso';
}

$sqlUsuario = 'SELECT ' . implode(', ', $selectCampos) . ' FROM usuarios_oyama u '
  . 'LEFT JOIN faixas f ON f.id = u.faixa_id '
  . "WHERE u.id = $usuarioId LIMIT 1";

$resultUsuario = mysqli_query($conn, $sqlUsuario);

if (!$resultUsuario || mysqli_num_rows($resultUsuario) === 0) {
  session_unset();
  session_destroy();
  header('Location: login.php');
  exit;
}

$usuario = mysqli_fetch_assoc($resultUsuario);
$_SESSION['nome'] = $usuario['nome'];
$_SESSION['tipo'] = $usuario['tipo'];

$filtroNivel = $_GET['nivel'] ?? 'todos';
$niveisPermitidos = ['todos', 'iniciante', 'intermediario', 'avancado'];
if (!in_array($filtroNivel, $niveisPermitidos, true)) {
  $filtroNivel = 'todos';
}

$sqlKatas = 'SELECT id, nome, descricao, nivel_dificuldade, video FROM katas';
if ($filtroNivel !== 'todos') {
  $nivelSeguro = mysqli_real_escape_string($conn, $filtroNivel);
  $sqlKatas .= " WHERE nivel_dificuldade = '$nivelSeguro'";
}
$sqlKatas .= ' ORDER BY id DESC';
$resultKatas = mysqli_query($conn, $sqlKatas);

$totalKatas = mysqli_num_rows(mysqli_query($conn, 'SELECT id FROM katas'));
$totalUsuarios = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM usuarios_oyama WHERE tipo = 'aluno'"));

$progressoFaixa = 0;
if (!empty($usuario['faixa_ordem'])) {
  $maxFaixasResult = mysqli_query($conn, 'SELECT MAX(ordem) AS max_ordem FROM faixas');
  $maxFaixas = mysqli_fetch_assoc($maxFaixasResult);
  $maxOrdem = (int) ($maxFaixas['max_ordem'] ?? 0);

  if ($maxOrdem > 0) {
    $progressoFaixa = min(100, (int) round(((int) $usuario['faixa_ordem'] / $maxOrdem) * 100));
  }
}

$sqlFaixas = 'SELECT id, nome FROM faixas ORDER BY ordem ASC';
$resultFaixas = mysqli_query($conn, $sqlFaixas);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Oyama Hub</title>
  <link rel="icon" href="../img/kyokushinicon.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
  <header class="topbar">
    <div class="brand">
      <img src="../img/kyokushinicon.png" alt="Logo Kyokushin">
      <div>
        <h1>OYAMA HUB</h1>
        <p>Dashboard do Dojo</p>
      </div>
    </div>

    <nav class="nav-links">
      <a href="#resumo">Resumo</a>
      <a href="#perfil">Perfil</a>
      <a href="#katas">Katas</a>
      <a href="logout.php" class="logout-link">Sair</a>
    </nav>
  </header>

  <main class="container">
    <section class="hero" id="resumo">
      <div class="hero-content">
        <h2>OSU, <?= htmlspecialchars($usuario['nome']) ?>!</h2>
        <p>
          <?= $usuario['tipo'] === 'administrador' ? 'Você está logado como administrador.' : 'Continue firme na sua evolução no Kyokushin.' ?>
        </p>
      </div>
    </section>

    <section class="stats-grid">
      <article class="card stat-card">
        <h3>Total de Alunos</h3>
        <strong><?= (int) $totalUsuarios ?></strong>
      </article>
      <article class="card stat-card">
        <h3>Total de Katas</h3>
        <strong><?= (int) $totalKatas ?></strong>
      </article>
      <article class="card stat-card">
        <h3>Sua Faixa</h3>
        <strong><?= htmlspecialchars($usuario['faixa_nome'] ?? 'Não definida') ?></strong>
      </article>
      <article class="card stat-card">
        <h3>Progresso</h3>
        <strong><?= $progressoFaixa ?>%</strong>
      </article>
    </section>

    <?php if ($mensagem): ?>
      <div class="alert success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <?php if ($erro): ?>
      <div class="alert error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <section class="content-grid">
      <article class="card" id="perfil">
        <h3>Meu Perfil</h3>
        <form method="POST" class="perfil-form">
          <input type="hidden" name="acao" value="atualizar_perfil">

          <label>Nome</label>
          <input type="text" value="<?= htmlspecialchars($usuario['nome']) ?>" disabled>

          <label>Email</label>
          <input type="email" value="<?= htmlspecialchars($usuario['email']) ?>" disabled>

          <?php if ($temIdade): ?>
            <label>Idade</label>
            <input type="number" name="idade" min="1" max="120" value="<?= htmlspecialchars((string) ($usuario['idade'] ?? '')) ?>">
          <?php endif; ?>

          <?php if ($temAltura): ?>
            <label>Altura (cm)</label>
            <input type="number" name="altura" min="50" max="250" step="0.01" value="<?= htmlspecialchars((string) ($usuario['altura'] ?? '')) ?>">
          <?php endif; ?>

          <?php if ($temPeso): ?>
            <label>Peso (kg)</label>
            <input type="number" name="peso" min="10" max="300" step="0.1" value="<?= htmlspecialchars((string) ($usuario['peso'] ?? '')) ?>">
          <?php endif; ?>

          <?php if ($temFaixa): ?>
            <label>Faixa</label>
            <select name="faixa_id">
              <option value="">Selecione</option>
              <?php if ($resultFaixas): ?>
                <?php while ($faixa = mysqli_fetch_assoc($resultFaixas)): ?>
                  <option value="<?= (int) $faixa['id'] ?>" <?= ((int) ($usuario['faixa_id'] ?? 0) === (int) $faixa['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($faixa['nome']) ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          <?php endif; ?>

          <button type="submit">Salvar alterações</button>
        </form>
      </article>

      <article class="card" id="katas">
        <div class="card-header">
          <h3>Katas cadastrados</h3>
          <form method="GET" class="filtro-form">
            <label for="nivel">Nível</label>
            <select id="nivel" name="nivel" onchange="this.form.submit()">
              <option value="todos" <?= $filtroNivel === 'todos' ? 'selected' : '' ?>>Todos</option>
              <option value="iniciante" <?= $filtroNivel === 'iniciante' ? 'selected' : '' ?>>Iniciante</option>
              <option value="intermediario" <?= $filtroNivel === 'intermediario' ? 'selected' : '' ?>>Intermediário</option>
              <option value="avancado" <?= $filtroNivel === 'avancado' ? 'selected' : '' ?>>Avançado</option>
            </select>
          </form>
        </div>

        <div class="kata-lista">
          <?php if ($resultKatas && mysqli_num_rows($resultKatas) > 0): ?>
            <?php while ($kata = mysqli_fetch_assoc($resultKatas)): ?>
              <div class="kata-item">
                <h4><?= htmlspecialchars($kata['nome']) ?></h4>
                <p><?= htmlspecialchars($kata['descricao']) ?></p>
                <div class="meta">
                  <span class="badge"><?= htmlspecialchars($kata['nivel_dificuldade']) ?></span>
                  <?php if (!empty($kata['video'])): ?>
                    <a href="<?= htmlspecialchars($kata['video']) ?>" target="_blank" rel="noopener noreferrer">Ver vídeo</a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="empty">Nenhum kata encontrado para o filtro selecionado.</p>
          <?php endif; ?>
        </div>
      </article>
    </section>
  </main>
</body>

</html>
