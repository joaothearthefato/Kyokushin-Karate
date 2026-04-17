<?php
session_start();

if (isset($_SESSION["id"])) {
    header("Location: ../php/dashboard.php");
    exit;
}

require("config.php");

$erro = null;

if (isset($_POST["email"])) {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql    = "SELECT * FROM usuarios WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $usuario = mysqli_fetch_assoc($result);

        if (password_verify($senha, $usuario["senha_hash"])) {
            $_SESSION["id"]   = $usuario["id"];
            $_SESSION["nome"] = $usuario["nome"];
            $_SESSION["tipo"] = $usuario["tipo"];

            header("Location: ../php/dashboard.php");
            exit;
        }
    }

    $erro = "Email ou senha incorretos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Oyama Hub</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="stylesheet" href="../css/registerlogin.css">

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
  
  <style>
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.7);
      backdrop-filter: blur(3px);
      z-index: 999;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      opacity: 0;
      pointer-events: none;
      transition: opacity .25s;
    }
    .modal-overlay.open {
      opacity: 1;
      pointer-events: all;
    }
    .modal-box {
      background: var(--surface);
      border-left: 4px solid var(--red);
      padding: 2rem 2rem 1.75rem;
      width: 100%;
      max-width: 340px;
      box-shadow: 0 20px 60px rgba(0,0,0,.7);
      text-align: center;
      transform: translateY(20px);
      transition: transform .3s ease;
    }
    .modal-overlay.open .modal-box {
      transform: translateY(0);
    }
    .modal-icon {
      font-size: 2rem;
      margin-bottom: .75rem;
    }
    .modal-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.6rem;
      letter-spacing: 3px;
      color: var(--red);
      margin-bottom: .5rem;
    }
    .modal-msg {
      font-family: 'Oswald', sans-serif;
      font-size: .85rem;
      letter-spacing: 1px;
      color: var(--muted);
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }
    .modal-btn {
      background: var(--red);
      border: none;
      color: #fff;
      font-family: 'Oswald', sans-serif;
      font-size: .8rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      padding: .75rem 2rem;
      cursor: pointer;
      width: 100%;
      transition: background .2s, box-shadow .2s, transform .2s;
    }
    .modal-btn:hover {
      background: var(--red2);
      box-shadow: 0 0 20px var(--red-glow);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <div class="register-box">
    <button id="theme-toggle" class="theme-btn-icon" aria-label="Alternar tema">
      <span class="theme-icon">☀️</span>
    </button>
    
    <h2>LOGIN <span>Oyama-HUB</span></h2>

    <form method="POST">
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
      </div>
      <div class="input-group">
        <label>Senha</label>
        <input type="password" name="senha" required>
      </div>
      <button class="btn-submit" type="submit">ENTRAR</button>
    </form>

    <a href="registro.php" class="login-link">Não tem conta? Cadastre-se</a>
    <a href="../index.html" class="login-link">Voltar ao início</a>
  </div>

  <!-- Modal de erro -->
  <div class="modal-overlay" id="modal-erro">
    <div class="modal-box">
      <div class="modal-icon">⚠️</div>
      <div class="modal-title">Erro ao tentar entrar</div>
      <p class="modal-msg"><?= $erro ?? '' ?></p>
      <button class="modal-btn" onclick="fecharModal()">Tentar Novamente</button>
    </div>
  </div>

  <?php if ($erro): ?>
  <script>
    document.getElementById('modal-erro').classList.add('open');
  </script>
  <?php endif; ?>

  <script>
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle.querySelector('.theme-icon');
    const html = document.documentElement;

    // Check for saved theme preference or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') {
      html.classList.add('light');
      themeIcon.textContent = '🌙';
    }

    themeToggle.addEventListener('click', () => {
      html.classList.toggle('light');
      const isLight = html.classList.contains('light');

      // Update button appearance with animation
      if (isLight) {
        themeIcon.textContent = '🌙';
        localStorage.setItem('theme', 'light');
      } else {
        themeIcon.textContent = '☀️';
        localStorage.setItem('theme', 'dark');
      }
    });

    function fecharModal() {
      document.getElementById('modal-erro').classList.remove('open');
    }

    // fecha clicando fora
    document.getElementById('modal-erro').addEventListener('click', function(e) {
      if (e.target === this) fecharModal();
    });

    // fecha com Escape
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') fecharModal();
    });
  </script>

</body>
</html>