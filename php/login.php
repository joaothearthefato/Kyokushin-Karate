<?php
session_start();

if (isset($_SESSION["id"])) {
    header("Location: ../php/dashboard.php");
    exit;
}

require("config.php");
require_once("csrf.php");
require_once("auth_check.php");

$erro = null;

if (isset($_POST["email"])) {
    validar_csrf();
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $stmt = mysqli_prepare($conn, "SELECT * FROM usuarios WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $usuario = mysqli_fetch_assoc($result);

        if (password_verify($senha, $usuario["senha_hash"])) {
            session_regenerate_id(true);
            $_SESSION["id"]   = $usuario["id"];
            $_SESSION["nome"] = $usuario["nome"];
            $_SESSION["tipo"] = $usuario["tipo"];
            log_activity($conn, 'login', "Usuário '{$usuario['nome']}' fez login");

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Oyama Hub</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="preconnect" href="https://vlibras.gov.br">
  <link rel="stylesheet" href="../css/registerlogin.css">
  <script>
    if (localStorage.getItem('oyama-theme') === 'light' || localStorage.getItem('theme') === 'light') {
      document.documentElement.classList.add('light');
    }
  </script>
</head>
<body>

  <div class="register-box">
    <button id="theme-toggle" class="theme-btn-icon" aria-label="Alternar tema">
      <span class="theme-icon">☀️</span>
    </button>
    
    <h2>LOGIN <span>Oyama-HUB</span></h2>

    <form method="POST">
      <?= csrf_input() ?>
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
    <button class="forgot-password-btn" onclick="openForgotPasswordModal()">Esqueci minha senha</button>
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

  <!-- Modal de recuperação de senha -->
  <div class="modal-overlay" id="modal-forgot-password">
    <div class="modal-box">
      <div class="modal-icon">🔐</div>
      <div class="modal-title">Recuperar Senha</div>
      <p class="modal-msg">Digite seu email para receber instruções de recuperação de senha.</p>
      
      <form id="forgot-password-form" onsubmit="handleForgotPassword(event)">
        <div class="input-group" style="margin-bottom: 1.5rem;">
          <input type="email" id="forgot-email" placeholder="seu@email.com" required 
                 style="width: 100%; padding: 12px 14px; background: var(--dark); border: 1px solid var(--border); color: var(--white); font-family: 'Barlow Condensed', sans-serif; outline: none; transition: border-color 0.2s;">
        </div>
        
        <button class="modal-btn" type="submit" id="forgot-submit-btn">
          <span id="forgot-btn-text">Enviar</span>
        </button>
      </form>
      
      <button class="modal-btn" onclick="closeForgotPasswordModal()" style="background: transparent; border: 1px solid var(--border); color: var(--muted); margin-top: 10px;">
        Cancelar
      </button>
    </div>
  </div>

  <!-- Modal de sucesso -->
  <div class="modal-overlay" id="modal-success">
    <div class="modal-box">
      <div class="modal-icon">✅</div>
      <div class="modal-title">Email Enviado!</div>
      <p class="modal-msg">Verifique sua caixa de entrada e siga as instruções para recuperar sua senha.</p>
      <button class="modal-btn" onclick="closeSuccessModal()">Entendi</button>
    </div>
  </div>

  <?php if ($erro): ?>
  <script>
    document.getElementById('modal-erro').classList.add('open');
  </script>
  <?php endif; ?>

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
  
  <script src="../js/theme.js" defer></script>
  <script>
    function fecharModal() {
      document.getElementById('modal-erro').classList.remove('open');
    }

    // fecha clicando fora
    document.getElementById('modal-erro').addEventListener('click', function(e) {
      if (e.target === this) fecharModal();
    });

    // Funções para modal de recuperação de senha
    function openForgotPasswordModal() {
      document.getElementById('modal-forgot-password').classList.add('open');
      document.getElementById('forgot-email').focus();
    }

    function closeForgotPasswordModal() {
      document.getElementById('modal-forgot-password').classList.remove('open');
      document.getElementById('forgot-password-form').reset();
      document.getElementById('forgot-submit-btn').disabled = false;
      document.getElementById('forgot-btn-text').textContent = 'Enviar';
    }

    function closeSuccessModal() {
      document.getElementById('modal-success').classList.remove('open');
      closeForgotPasswordModal();
    }

    function handleForgotPassword(event) {
      event.preventDefault();
      
      const submitBtn = document.getElementById('forgot-submit-btn');
      const btnText = document.getElementById('forgot-btn-text');
      
      submitBtn.disabled = true;
      btnText.textContent = 'Enviando...';
      
      setTimeout(() => {
        document.getElementById('modal-forgot-password').classList.remove('open');
        document.getElementById('modal-success').classList.add('open');
        document.getElementById('forgot-password-form').reset();
        submitBtn.disabled = false;
        btnText.textContent = 'Enviar';
      }, 1500);
    }

    document.getElementById('modal-forgot-password').addEventListener('click', function(e) {
      if (e.target === this) closeForgotPasswordModal();
    });

    document.getElementById('modal-success').addEventListener('click', function(e) {
      if (e.target === this) closeSuccessModal();
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        const modalErro = document.getElementById('modal-erro');
        if (modalErro && modalErro.classList.contains('open')) fecharModal();
        closeForgotPasswordModal();
        closeSuccessModal();
      }
    });
  </script>

</body>
</html>