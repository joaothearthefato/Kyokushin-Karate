<?php
include("config.php");

$status = $_GET['status'] ?? '';
$msgKey = $_GET['msg'] ?? '';
$messages = [
    'email_cadastrado' => 'Este e-mail já está cadastrado.',
    'db_error' => 'Erro ao salvar seus dados. Tente novamente mais tarde.',
    'campos_obrigatorios' => 'Por favor, preencha todos os campos obrigatórios.',
    'email_invalido' => 'Informe um endereço de e-mail válido.',
    'perfil_integrado' => 'O processo de perfil foi integrado ao cadastro. Use o formulário de registro.',
];
$feedback = '';
if ($status === 'erro' && isset($messages[$msgKey])) {
    $feedback = $messages[$msgKey];
} elseif ($status === 'sucesso_registro') {
    $feedback = 'Cadastro realizado com sucesso! Faça login para entrar no sistema.';
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
  <title>Registro - Kyokushin Dojo</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="icon" href="../img/kyokushinicon.png" type="image/x-icon">
  <link rel="stylesheet" href="../css/registerlogin.css">

</head>

<body>

  <div class="register-box">
    <button id="theme-toggle" class="theme-btn-icon" aria-label="Alternar tema">
      <span class="theme-icon">☀️</span>
    </button>
    
    <h2>NOVO <span>ALUNO</span></h2>
    <?php if ($feedback): ?>
        <div class="form-feedback"><?php echo htmlspecialchars($feedback); ?></div>
    <?php endif; ?>
    <form action="registrar_aluno.php" method="POST">
      <div class="input-group">
        <label for="nome">Nome Completo</label>
        <input type="text" id="nome" name="nome" required placeholder="Seu nome">
      </div>
      <div class="input-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required placeholder="seu@email.com">
      </div>
      <div class="input-group">
        <label for="senha">Crie uma Senha</label>
        <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
      </div>
      <div class="input-group">
        <label for="nascimento">Data de Nascimento</label>
        <input type="date" id="nascimento" name="nascimento" required>
      </div>
      <div class="input-group">
        <label for="faixa_id">Sua Faixa Atual</label>
        <select id="faixa_id" name="faixa_id">
          <option value="">Selecione sua faixa</option>
          <?php foreach ($faixas as $faixa): ?>
            <option value="<?php echo $faixa['id']; ?>"><?php echo htmlspecialchars($faixa['nome']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-submit">MATRICULAR-SE</button>
    </form>
    <a href="login.php" class="login-link">Já tem cadastro? Faça o login</a>
  </div>

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

      // Add click animation
      themeToggle.style.transform = 'scale(0.95)';
      setTimeout(() => {
        themeToggle.style.transform = '';
      }, 150);
    });
  </script>

</body>

</html>