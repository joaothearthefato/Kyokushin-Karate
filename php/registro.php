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
    <h2>NOVO <span>ALUNO</span></h2>
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
      <button type="submit" class="btn-submit">MATRICULAR-SE</button>
    </form>
    <a href="login.php" class="login-link">Já tem cadastro? Faça o login</a>
  </div>

</body>

</html>