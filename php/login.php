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
  <title>Login — Oyama Hub</title>
  <meta name="description" content="Acesse sua conta no Oyama Hub e gerencie seus treinos de Kyokushin.">
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/tokens.css">
  <link rel="stylesheet" href="../css/registerlogin.css">
  <script>
    (function() {
      var t = localStorage.getItem('oyama-theme');
      if (t === 'light') document.documentElement.classList.add('light');
    })();
  </script>
</head>
<body>

<div class="auth-page">

  <!-- Painel lateral esquerdo (visível em telas grandes) -->
  <aside class="auth-panel" aria-hidden="true">
    <div class="auth-panel-logo">OYAMA <span>HUB</span></div>
    <div class="auth-panel-quote">
      <blockquote>
        O Kyokushin é uma escola de<br><em>vida baseada</em><br>na luta.
      </blockquote>
      <cite>— Mas Oyama</cite>
    </div>
    <p class="auth-panel-bottom">Kyokushin Karate · Dojo Management</p>
  </aside>

  <!-- Coluna do formulário -->
  <main class="auth-form-col">

    <!-- Botão de tema -->
    <button id="theme-toggle" class="auth-theme-btn" aria-label="Alternar tema">
      <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    </button>

    <div class="auth-box">

      <!-- Cabeçalho -->
      <div class="auth-box-header">
        <span class="auth-box-eyebrow">Oyama Hub</span>
        <h1 class="auth-box-title">Entrar na<br><span>sua conta</span></h1>
      </div>

      <!-- Alert de erro (PHP) -->
      <?php if ($erro): ?>
      <div class="auth-alert auth-alert-error" role="alert" id="auth-error-alert">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?= htmlspecialchars($erro) ?></span>
      </div>
      <?php endif; ?>

      <!-- Formulário -->
      <form method="POST" class="auth-form" novalidate>
        <?= csrf_input() ?>

        <div class="form-field">
          <label for="email" class="form-label">E-mail</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            placeholder="seu@email.com"
            autocomplete="email"
            required
            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
          >
        </div>

        <div class="form-field">
          <label for="senha" class="form-label">Senha</label>
          <input
            type="password"
            id="senha"
            name="senha"
            class="form-input"
            placeholder="Sua senha"
            autocomplete="current-password"
            required
          >
        </div>

        <button type="submit" class="auth-submit" id="login-submit-btn">
          Entrar
        </button>
      </form>

      <!-- Links auxiliares -->
      <div class="auth-footer">
        <button class="auth-forgot-btn" type="button" onclick="openForgotPasswordModal()">
          Esqueci minha senha
        </button>
        <div class="auth-divider"></div>
        <a href="registro.php" class="auth-link">
          Não tem conta? <strong class="auth-link-accent">Cadastre-se</strong>
        </a>
        <a href="../index.html" class="auth-link">← Voltar ao início</a>
      </div>

    </div>
  </main>

</div>

<!-- ── Modal: Recuperação de Senha ── -->
<div class="modal-overlay" id="modal-forgot-password" role="dialog" aria-modal="true" aria-labelledby="modal-forgot-title">
  <div class="modal-box">
    <div class="modal-icon-wrap info">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h2 class="modal-title" id="modal-forgot-title">Recuperar senha</h2>
    <p class="modal-msg">Digite seu e-mail cadastrado para receber as instruções de recuperação.</p>

    <form id="forgot-password-form" onsubmit="handleForgotPassword(event)" novalidate>
      <input
        type="email"
        id="forgot-email"
        class="modal-input"
        placeholder="seu@email.com"
        required
        autocomplete="email"
      >
      <div class="modal-actions">
        <button type="submit" class="modal-btn-primary" id="forgot-submit-btn">
          <span id="forgot-btn-text">Enviar instruções</span>
        </button>
        <button type="button" class="modal-btn-secondary" onclick="closeForgotPasswordModal()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Modal: Sucesso do envio ── -->
<div class="modal-overlay" id="modal-success" role="dialog" aria-modal="true" aria-labelledby="modal-success-title">
  <div class="modal-box">
    <div class="modal-icon-wrap success">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h2 class="modal-title" id="modal-success-title">E-mail enviado</h2>
    <p class="modal-msg">Verifique sua caixa de entrada e siga as instruções para recuperar sua senha.</p>
    <div class="modal-actions">
      <button type="button" class="modal-btn-primary" onclick="closeSuccessModal()">Entendi</button>
    </div>
  </div>
</div>

<script>
  // ── Tema ──
  (function() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function() {
      var isLight = document.documentElement.classList.toggle('light');
      localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    });
  })();

  // ── Modais ──
  function openForgotPasswordModal() {
    document.getElementById('modal-forgot-password').classList.add('open');
    document.getElementById('forgot-email').focus();
  }

  function closeForgotPasswordModal() {
    document.getElementById('modal-forgot-password').classList.remove('open');
    document.getElementById('forgot-password-form').reset();
    var btn = document.getElementById('forgot-submit-btn');
    if (btn) btn.disabled = false;
    document.getElementById('forgot-btn-text').textContent = 'Enviar instruções';
  }

  function closeSuccessModal() {
    document.getElementById('modal-success').classList.remove('open');
    closeForgotPasswordModal();
  }

  function handleForgotPassword(event) {
    event.preventDefault();
    var submitBtn = document.getElementById('forgot-submit-btn');
    var btnText   = document.getElementById('forgot-btn-text');
    submitBtn.disabled = true;
    btnText.textContent = 'Enviando...';
    setTimeout(function() {
      document.getElementById('modal-forgot-password').classList.remove('open');
      document.getElementById('modal-success').classList.add('open');
      document.getElementById('forgot-password-form').reset();
      submitBtn.disabled = false;
      btnText.textContent = 'Enviar instruções';
    }, 1500);
  }

  // Fechar clicando fora
  ['modal-forgot-password', 'modal-success'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('open');
      }
    });
  });

  // Fechar com Escape
  document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    closeForgotPasswordModal();
    closeSuccessModal();
  });
</script>

<script src="../js/acessibilidade.js" defer></script>
</body>
</html>