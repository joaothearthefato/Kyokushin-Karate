<?php
session_start();

if (!isset($_SESSION['id'])) {
  header('Location: login.php');
  exit;
}

require('config.php');

$usuarioId = (int) $_SESSION['id'];
$mensagem = '';
$erro = '';

function tabelaExiste(mysqli $conn, string $tabela): bool
{
  $tabelaSeguro = mysqli_real_escape_string($conn, $tabela);
  $sql = "SHOW TABLES LIKE '$tabelaSeguro'";
  $resultado = mysqli_query($conn, $sql);

  return $resultado && mysqli_num_rows($resultado) > 0;
}

function colunaExiste(mysqli $conn, string $tabela, string $coluna): bool
{
  $tabelaSeguro = mysqli_real_escape_string($conn, $tabela);
  $colunaSeguro = mysqli_real_escape_string($conn, $coluna);
  $sql = "SHOW COLUMNS FROM `$tabelaSeguro` LIKE '$colunaSeguro'";
  $resultado = mysqli_query($conn, $sql);

  return $resultado && mysqli_num_rows($resultado) > 0;
}

if (!tabelaExiste($conn, 'treinos')) {
  mysqli_query(
    $conn,
    "CREATE TABLE treinos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      usuario_id INT NOT NULL,
      data_treino DATE NOT NULL,
      descricao TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (usuario_id) REFERENCES usuarios_oyama(id) ON DELETE CASCADE
    )"
  );
}

if (!colunaExiste($conn, 'katas', 'foto')) {
  mysqli_query($conn, "ALTER TABLE katas ADD COLUMN foto VARCHAR(255) NULL AFTER video");
}

$temIdade = colunaExiste($conn, 'usuarios_oyama', 'idade');
$temAltura = colunaExiste($conn, 'usuarios_oyama', 'altura');
$temPeso = colunaExiste($conn, 'usuarios_oyama', 'peso');
$temFaixa = colunaExiste($conn, 'usuarios_oyama', 'faixa_id');

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
$ehAdministrador = $usuario['tipo'] === 'administrador';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $acao = $_POST['acao'] ?? '';

  if ($acao === 'atualizar_perfil') {
    $idade = isset($_POST['idade']) && $_POST['idade'] !== '' ? (int) $_POST['idade'] : null;
    $altura = isset($_POST['altura']) && $_POST['altura'] !== '' ? (float) $_POST['altura'] : null;
    $peso = isset($_POST['peso']) && $_POST['peso'] !== '' ? (float) $_POST['peso'] : null;
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

  if ($acao === 'criar_treino') {
    $dataTreino = trim($_POST['data_treino'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($dataTreino === '' || $descricao === '') {
      $erro = 'Informe a data e a descrição do treino.';
    } else {
      $dataSeguro = mysqli_real_escape_string($conn, $dataTreino);
      $descricaoSeguro = mysqli_real_escape_string($conn, $descricao);
      $sql = "INSERT INTO treinos (usuario_id, data_treino, descricao) VALUES ($usuarioId, '$dataSeguro', '$descricaoSeguro')";
      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Treino registrado com sucesso.';
      } else {
        $erro = 'Erro ao registrar treino.';
      }
    }
  }

  if ($acao === 'editar_treino') {
    $treinoId = (int) ($_POST['treino_id'] ?? 0);
    $dataTreino = trim($_POST['data_treino'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($treinoId < 1 || $dataTreino === '' || $descricao === '') {
      $erro = 'Dados inválidos para atualização do treino.';
    } else {
      $dataSeguro = mysqli_real_escape_string($conn, $dataTreino);
      $descricaoSeguro = mysqli_real_escape_string($conn, $descricao);
      $sql = "UPDATE treinos SET data_treino = '$dataSeguro', descricao = '$descricaoSeguro' WHERE id = $treinoId AND usuario_id = $usuarioId";
      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Treino atualizado.';
      } else {
        $erro = 'Não foi possível atualizar treino.';
      }
    }
  }

  if ($acao === 'deletar_treino') {
    $treinoId = (int) ($_POST['treino_id'] ?? 0);
    if ($treinoId < 1) {
      $erro = 'Treino inválido.';
    } else {
      $sql = "DELETE FROM treinos WHERE id = $treinoId AND usuario_id = $usuarioId";
      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Treino removido com sucesso.';
      } else {
        $erro = 'Não foi possível remover treino.';
      }
    }
  }

  if ($ehAdministrador && $acao === 'criar_kata') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $nivel = trim($_POST['nivel_dificuldade'] ?? '');
    $video = trim($_POST['video'] ?? '');
    $foto = trim($_POST['foto'] ?? '');

    if ($nome === '' || $descricao === '' || !in_array($nivel, ['iniciante', 'intermediario', 'avancado'], true)) {
      $erro = 'Preencha nome, descrição e nível do kata.';
    } else {
      $nomeSeguro = mysqli_real_escape_string($conn, $nome);
      $descricaoSeguro = mysqli_real_escape_string($conn, $descricao);
      $nivelSeguro = mysqli_real_escape_string($conn, $nivel);
      $videoSeguro = mysqli_real_escape_string($conn, $video);
      $fotoSeguro = mysqli_real_escape_string($conn, $foto);

      $sql = "INSERT INTO katas (nome, descricao, video, foto, nivel_dificuldade) VALUES ('$nomeSeguro', '$descricaoSeguro', '" . ($videoSeguro !== '' ? $videoSeguro : '') . "', '" . ($fotoSeguro !== '' ? $fotoSeguro : '') . "', '$nivelSeguro')";

      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Kata criado com sucesso.';
      } else {
        $erro = 'Erro ao criar kata.';
      }
    }
  }

  if ($ehAdministrador && $acao === 'editar_kata') {
    $kataId = (int) ($_POST['kata_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $nivel = trim($_POST['nivel_dificuldade'] ?? '');
    $video = trim($_POST['video'] ?? '');
    $foto = trim($_POST['foto'] ?? '');

    if ($kataId < 1 || $nome === '' || $descricao === '' || !in_array($nivel, ['iniciante', 'intermediario', 'avancado'], true)) {
      $erro = 'Dados inválidos para editar kata.';
    } else {
      $nomeSeguro = mysqli_real_escape_string($conn, $nome);
      $descricaoSeguro = mysqli_real_escape_string($conn, $descricao);
      $nivelSeguro = mysqli_real_escape_string($conn, $nivel);
      $videoSeguro = mysqli_real_escape_string($conn, $video);
      $fotoSeguro = mysqli_real_escape_string($conn, $foto);
      $sql = "UPDATE katas SET nome = '$nomeSeguro', descricao = '$descricaoSeguro', nivel_dificuldade = '$nivelSeguro', video = '$videoSeguro', foto = '$fotoSeguro' WHERE id = $kataId";

      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Kata atualizado com sucesso.';
      } else {
        $erro = 'Erro ao atualizar kata.';
      }
    }
  }

  if ($ehAdministrador && $acao === 'deletar_kata') {
    $kataId = (int) ($_POST['kata_id'] ?? 0);
    if ($kataId < 1) {
      $erro = 'Kata inválido.';
    } else {
      $sql = "DELETE FROM katas WHERE id = $kataId";
      if (mysqli_query($conn, $sql)) {
        $mensagem = 'Kata removido com sucesso.';
      } else {
        $erro = 'Não foi possível remover o kata.';
      }
    }
  }
}

$filtroNivel = $_GET['nivel'] ?? 'todos';
$niveisPermitidos = ['todos', 'iniciante', 'intermediario', 'avancado'];
if (!in_array($filtroNivel, $niveisPermitidos, true)) {
  $filtroNivel = 'todos';
}

$sqlKatas = 'SELECT id, nome, descricao, nivel_dificuldade, video, foto FROM katas';
if ($filtroNivel !== 'todos') {
  $nivelSeguro = mysqli_real_escape_string($conn, $filtroNivel);
  $sqlKatas .= " WHERE nivel_dificuldade = '$nivelSeguro'";
}
$sqlKatas .= ' ORDER BY id DESC';
$resultKatas = mysqli_query($conn, $sqlKatas);

$sqlTreinos = "SELECT id, data_treino, descricao, created_at FROM treinos WHERE usuario_id = $usuarioId ORDER BY data_treino DESC, id DESC";
$resultTreinos = mysqli_query($conn, $sqlTreinos);

$totalKatas = mysqli_num_rows(mysqli_query($conn, 'SELECT id FROM katas'));
$totalUsuarios = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM usuarios_oyama WHERE tipo = 'aluno'"));
$totalTreinosUsuario = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM treinos WHERE usuario_id = $usuarioId"));

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
        <p><?= $ehAdministrador ? 'Área do Sensei Geral' : 'Área do Aluno' ?></p>
      </div>
    </div>

    <nav class="nav-links">
      <a href="#resumo">Resumo</a>
      <a href="#perfil">Perfil</a>
      <a href="#aluno">Área do Aluno</a>
      <a href="#katas">Biblioteca de Katas</a>
      <?php if ($ehAdministrador): ?>
        <a href="#sensei">Área do Sensei</a>
      <?php endif; ?>
      <a href="logout.php" class="logout-link">Sair</a>
    </nav>
  </header>

  <main class="container">
    <section class="hero" id="resumo">
      <div class="hero-content">
        <h2>OSU, <?= htmlspecialchars($usuario['nome']) ?>!</h2>
        <p>
          <?= $ehAdministrador ? 'Você está no painel do Sensei Geral e pode gerenciar katas do dojo inteiro.' : 'Registre seus treinos e acompanhe sua evolução no Kyokushin.' ?>
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
        <h3>Seus Treinos</h3>
        <strong><?= (int) $totalTreinosUsuario ?></strong>
      </article>
      <article class="card stat-card">
        <h3>Progresso de Faixa</h3>
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

      <article class="card" id="aluno">
        <h3>Área do Aluno · Registro de Treinos</h3>
        <form method="POST" class="perfil-form treino-form">
          <input type="hidden" name="acao" value="criar_treino">
          <label>Data do treino</label>
          <input type="date" name="data_treino" required>

          <label>Descrição do treino</label>
          <textarea name="descricao" rows="4" required placeholder="Ex.: kihon + kata + condicionamento"></textarea>

          <button type="submit">Registrar treino</button>
        </form>

        <div class="kata-lista treinos-lista">
          <?php if ($resultTreinos && mysqli_num_rows($resultTreinos) > 0): ?>
            <?php while ($treino = mysqli_fetch_assoc($resultTreinos)): ?>
              <div class="kata-item treino-item">
                <div class="meta">
                  <span class="badge"><?= htmlspecialchars(date('d/m/Y', strtotime($treino['data_treino']))) ?></span>
                </div>
                <p><?= nl2br(htmlspecialchars($treino['descricao'])) ?></p>

                <details>
                  <summary>Editar treino</summary>
                  <form method="POST" class="inline-form">
                    <input type="hidden" name="acao" value="editar_treino">
                    <input type="hidden" name="treino_id" value="<?= (int) $treino['id'] ?>">
                    <input type="date" name="data_treino" value="<?= htmlspecialchars($treino['data_treino']) ?>" required>
                    <textarea name="descricao" rows="3" required><?= htmlspecialchars($treino['descricao']) ?></textarea>
                    <button type="submit">Salvar</button>
                  </form>
                  <form method="POST" class="inline-form danger">
                    <input type="hidden" name="acao" value="deletar_treino">
                    <input type="hidden" name="treino_id" value="<?= (int) $treino['id'] ?>">
                    <button type="submit">Excluir treino</button>
                  </form>
                </details>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="empty">Nenhum treino registrado ainda.</p>
          <?php endif; ?>
        </div>
      </article>
    </section>

    <section class="card" id="katas">
      <div class="card-header">
        <h3>Biblioteca Geral de Katas</h3>
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
              <p><?= nl2br(htmlspecialchars($kata['descricao'])) ?></p>
              <?php if (!empty($kata['foto'])): ?>
                <img class="kata-foto" src="<?= htmlspecialchars($kata['foto']) ?>" alt="Foto do kata <?= htmlspecialchars($kata['nome']) ?>">
              <?php endif; ?>
              <div class="meta">
                <span class="badge"><?= htmlspecialchars($kata['nivel_dificuldade']) ?></span>
                <div class="media-links">
                  <?php if (!empty($kata['video'])): ?>
                    <a href="<?= htmlspecialchars($kata['video']) ?>" target="_blank" rel="noopener noreferrer">Vídeo</a>
                  <?php endif; ?>
                  <?php if (!empty($kata['foto'])): ?>
                    <a href="<?= htmlspecialchars($kata['foto']) ?>" target="_blank" rel="noopener noreferrer">Foto</a>
                  <?php endif; ?>
                </div>
              </div>

              <?php if ($ehAdministrador): ?>
                <details>
                  <summary>Editar/Excluir kata</summary>
                  <form method="POST" class="inline-form">
                    <input type="hidden" name="acao" value="editar_kata">
                    <input type="hidden" name="kata_id" value="<?= (int) $kata['id'] ?>">
                    <input type="text" name="nome" value="<?= htmlspecialchars($kata['nome']) ?>" required>
                    <textarea name="descricao" rows="3" required><?= htmlspecialchars($kata['descricao']) ?></textarea>
                    <select name="nivel_dificuldade" required>
                      <option value="iniciante" <?= $kata['nivel_dificuldade'] === 'iniciante' ? 'selected' : '' ?>>Iniciante</option>
                      <option value="intermediario" <?= $kata['nivel_dificuldade'] === 'intermediario' ? 'selected' : '' ?>>Intermediário</option>
                      <option value="avancado" <?= $kata['nivel_dificuldade'] === 'avancado' ? 'selected' : '' ?>>Avançado</option>
                    </select>
                    <input type="url" name="video" value="<?= htmlspecialchars((string) $kata['video']) ?>" placeholder="URL do vídeo">
                    <input type="url" name="foto" value="<?= htmlspecialchars((string) $kata['foto']) ?>" placeholder="URL da foto">
                    <button type="submit">Salvar kata</button>
                  </form>
                  <form method="POST" class="inline-form danger">
                    <input type="hidden" name="acao" value="deletar_kata">
                    <input type="hidden" name="kata_id" value="<?= (int) $kata['id'] ?>">
                    <button type="submit">Excluir kata</button>
                  </form>
                </details>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="empty">Nenhum kata encontrado para o filtro selecionado.</p>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($ehAdministrador): ?>
      <section class="card" id="sensei">
        <h3>Área do Sensei · Novo Kata</h3>
        <form method="POST" class="perfil-form cadastro-kata">
          <input type="hidden" name="acao" value="criar_kata">
          <label>Nome do kata</label>
          <input type="text" name="nome" required>

          <label>Descrição completa</label>
          <textarea name="descricao" rows="4" required placeholder="Objetivo, sequência e dicas de execução"></textarea>

          <label>Nível</label>
          <select name="nivel_dificuldade" required>
            <option value="iniciante">Iniciante</option>
            <option value="intermediario">Intermediário</option>
            <option value="avancado">Avançado</option>
          </select>

          <label>URL do vídeo</label>
          <input type="url" name="video" placeholder="https://...">

          <label>URL da foto</label>
          <input type="url" name="foto" placeholder="https://...">

          <button type="submit">Cadastrar kata</button>
        </form>
      </section>
    <?php endif; ?>
  </main>
</body>

</html>
