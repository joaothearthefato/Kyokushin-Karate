<?php
include("config.php");
require_once("csrf.php");

$status = $_GET['status'] ?? '';
$msgKey = $_GET['msg'] ?? '';
$messages = [
    'data_nascimento_invalida' => 'A data de nascimento não pode ser futura.',
    'email_cadastrado'         => 'Este e-mail já está cadastrado.',
    'db_error'                 => 'Erro ao salvar seus dados. Tente novamente mais tarde.',
    'campos_obrigatorios'      => 'Por favor, preencha todos os campos obrigatórios.',
    'email_invalido'           => 'Informe um endereço de e-mail válido.',
    'perfil_integrado'         => 'O processo de perfil foi integrado ao cadastro. Use o formulário de registro.',
];
$feedback = '';
$isSuccess = false;
if ($status === 'erro' && isset($messages[$msgKey])) {
    $feedback = $messages[$msgKey];
} elseif ($status === 'sucesso_registro') {
    $feedback  = 'Cadastro realizado! Faça login para acessar sua conta.';
    $isSuccess = true;
}

$result_faixas = mysqli_query($conn, "SELECT id, nome FROM faixas ORDER BY ordem ASC");
$faixas = [];
if ($result_faixas) {
    while ($row = mysqli_fetch_assoc($result_faixas)) {
        $faixas[] = $row;
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro — Oyama Hub</title>
  <meta name="description" content="Crie sua conta no Oyama Hub e comece a registrar seus treinos de Kyokushin.">
  <link rel="icon" href="../img/kyokushinicon.png" type="image/x-icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/tokens.css">
  <link rel="stylesheet" href="../css/registerlogin.css">
  <link rel="preconnect" href="https://vlibras.gov.br">
  <script>
    (function() {
      var t = localStorage.getItem('oyama-theme');
      if (t === 'light') document.documentElement.classList.add('light');
    })();
  </script>
</head>
<body>

<div class="auth-page">

  <!-- Painel lateral esquerdo -->
  <aside class="auth-panel" aria-hidden="true">
    <div class="auth-panel-logo">OYAMA <span>HUB</span></div>
    <div class="auth-panel-quote">
      <blockquote>
        Só a prática constante<br><em>revela</em><br>o verdadeiro caminho.
      </blockquote>
      <cite>— Kyokushin Dojo</cite>
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
        <h1 class="auth-box-title">Criar<br><span>nova conta</span></h1>
      </div>

      <!-- Feedback -->
      <?php if ($feedback): ?>
      <div class="auth-alert <?= $isSuccess ? 'auth-alert-success' : 'auth-alert-error' ?>" role="alert">
        <?php if ($isSuccess): ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php endif; ?>
        <span><?= htmlspecialchars($feedback) ?></span>
      </div>
      <?php endif; ?>

      <!-- Formulário -->
      <form action="registrar_aluno.php" method="POST" class="auth-form" novalidate>
        <?= csrf_input() ?>

        <div class="form-field">
          <label for="nome" class="form-label">Nome completo</label>
          <input type="text" id="nome" name="nome" class="form-input"
                 placeholder="Seu nome completo" autocomplete="name" required>
        </div>

        <div class="form-field">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" id="email" name="email" class="form-input"
                 placeholder="seu@email.com" autocomplete="email" required>
        </div>

        <div class="form-field">
          <label for="senha" class="form-label">Senha</label>
          <input type="password" id="senha" name="senha" class="form-input"
                 placeholder="Mínimo 6 caracteres" autocomplete="new-password"
                 minlength="6" required>
        </div>

        <div class="form-field">
          <label for="nascimento" class="form-label">Data de nascimento</label>
          <input type="date" id="nascimento" name="nascimento" class="form-input"
                 max="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-field">
          <label for="faixa_id" class="form-label">Faixa atual</label>
          <select id="faixa_id" name="faixa_id" class="form-input form-select">
            <option value="">Selecione sua faixa</option>
            <?php foreach ($faixas as $faixa): ?>
              <option value="<?= $faixa['id'] ?>"><?= htmlspecialchars($faixa['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="auth-submit">
          Criar conta
        </button>
      </form>

      <div class="auth-footer">
        <div class="auth-divider"></div>
        <a href="login.php" class="auth-link">
          Já tem cadastro? <strong class="auth-link-accent">Faça login</strong>
        </a>
        <a href="../index.html" class="auth-link">← Voltar ao início</a>
      </div>

    </div>
  </main>

</div>

<!-- VLibras Acessibilidade -->
<div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>

<script>
  (function() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function() {
      var isLight = document.documentElement.classList.toggle('light');
      localStorage.setItem('oyama-theme', isLight ? 'light' : 'dark');
    });
  })();
</script>

<script src="../js/acessibilidade.js" defer></script>
</body>
</html>
