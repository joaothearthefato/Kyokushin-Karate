<?php
include("config.php");

if (!isset($_GET['id'])) {
  header("Location: ../index.html");
  exit();
}

$usuario_id = $_GET['id'];
$sql = "SELECT id, nome, email FROM usuarios_oyama WHERE id = '$usuario_id' AND tipo = 'aluno'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
  $modal_erro = "Usuário não encontrado!";
  $modal_redirect = "../index.html";
}

$usuario = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $idade = $_POST["idade"];
  $altura = $_POST["altura"];
  $peso = $_POST["peso"];
  $faixa_id = $_POST["faixa_id"];

  $sql = "UPDATE usuarios_oyama SET idade = '$idade', altura = '$altura', peso = '$peso', faixa_id = '$faixa_id' WHERE id = '$usuario_id'";

  if (mysqli_query($conn, $sql)) {
    $modal_sucesso = "Perfil completado com sucesso!";
    $modal_redirect = "../index.html";
  } else {
    $erro = "Erro ao atualizar perfil: " . mysqli_error($conn);
  }
}

$sql_faixas = "SELECT id, nome FROM faixas ORDER BY ordem ASC";
$result_faixas = mysqli_query($conn, $sql_faixas);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completar Perfil - Kyokushin Dojo</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="icon" href="../img/kyokushinicon.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/perfil_aluno.css">
  <style>
    /* ── Modal overlay ── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .55);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active { display: flex; }

    .modal-box {
      background: #fff;
      border-radius: 12px;
      padding: 2rem 2.5rem;
      max-width: 360px;
      width: 90%;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0,0,0,.2);
      animation: modalIn .25s ease;
    }
    @keyframes modalIn {
      from { transform: translateY(-20px); opacity: 0; }
      to   { transform: translateY(0);    opacity: 1; }
    }

    .modal-icon { font-size: 2.5rem; margin-bottom: .5rem; }
    .modal-title {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
      margin-bottom: 1.25rem;
      color: #222;
    }
    .modal-btn {
      background: #c0392b;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: .6rem 2rem;
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: .95rem;
      cursor: pointer;
      transition: background .2s;
    }
    .modal-btn:hover { background: #a93226; }
    .modal-btn.success { background: #27ae60; }
    .modal-btn.success:hover { background: #1e8449; }
  </style>
</head>

<body>

  <!-- Modal -->
  <div class="modal-overlay" id="modal">
    <div class="modal-box">
      <div class="modal-icon" id="modal-icon"></div>
      <div class="modal-title" id="modal-message"></div>
      <button class="modal-btn" id="modal-btn" onclick="modalConfirm()">OK</button>
    </div>
  </div>

  <div class="perfil-container">
    <div class="perfil-card">
      <div class="perfil-header">
        <h1>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
        <p>Complete seu perfil para começar sua jornada no Kyokushin</p>
      </div>

      <?php if (isset($erro)): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
      <?php endif; ?>

      <form method="POST" class="perfil-form">
        <div class="form-row">
          <div class="form-group">
            <label for="idade">Idade</label>
            <input type="number" id="idade" name="idade" min="1" max="120" required placeholder="Digite sua idade">
          </div>
          <div class="form-group">
            <label for="altura">Altura (cm)</label>
            <input type="number" id="altura" name="altura" min="50" max="250" step="0.01" required placeholder="Ex: 170">
          </div>
          <div class="form-group">
            <label for="peso">Peso (kg)</label>
            <input type="number" id="peso" name="peso" min="10" max="300" step="0.1" required placeholder="Ex: 70.5">
          </div>
        </div>

        <div class="form-group full-width">
          <label for="faixa_id">Sua Faixa Atual</label>
          <select id="faixa_id" name="faixa_id" required>
            <option value="">Selecione sua faixa</option>
            <?php while ($faixa = mysqli_fetch_assoc($result_faixas)): ?>
              <option value="<?php echo $faixa['id']; ?>">
                <?php echo htmlspecialchars($faixa['nome']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="info-box">
          <p><strong>Email cadastrado:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit">Completar Perfil</button>
          <a href="../index.html" class="btn-skip">Pular por enquanto</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    let modalRedirect = null;

    function showModal(message, type, redirect) {
      document.getElementById('modal-message').textContent = message;
      document.getElementById('modal-icon').textContent = type === 'success' ? '✅' : '❌';
      document.getElementById('modal-btn').className = 'modal-btn' + (type === 'success' ? ' success' : '');
      modalRedirect = redirect || null;
      document.getElementById('modal').classList.add('active');
    }

    function modalConfirm() {
      document.getElementById('modal').classList.remove('active');
      if (modalRedirect) window.location.href = modalRedirect;
    }

  
    <?php if (isset($modal_erro)): ?>
      showModal("<?php echo $modal_erro; ?>", "error", "<?php echo $modal_redirect; ?>");
    <?php elseif (isset($modal_sucesso)): ?>
      showModal("<?php echo $modal_sucesso; ?>", "success", "<?php echo $modal_redirect; ?>");
    <?php endif; ?>
  </script>
</body>
</html>