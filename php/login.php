<?php
session_start();
require("config.php");

if (isset($_POST["email"])) {

  $email = $_POST["email"];
  $senha = $_POST["senha"];

  $sql = "SELECT * FROM usuarios_oyama WHERE email='$email' AND senha='$senha'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) == 1) {

    $usuario = mysqli_fetch_assoc($result);

    $_SESSION["id"] = $usuario["id"];
    $_SESSION["nome"] = $usuario["nome"];

    header("Location: dashboard.php");
    exit;

  } else {
    $erro = "Email ou senha incorretos";
  }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Login - Kyokushin</title>

  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="stylesheet" href="../css/registro.css">
</head>

<body>

  <div class="register-box">

    <h2>LOGIN <span>DOJO</span></h2>

        <?php if (isset($erro))
          echo "<p style='color:red;text-align:center;'>$erro</p>"; ?>

    <form method="POST">

      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="input-group">
        <label>Senha</label>
        <input type="password" name="senha" required>
      </div>

      <button class="btn-submit">ENTRAR</button>

    </form>

    <a href="registro.php" class="login-link">
      Não tem conta? Cadastre-se
    </a>

  </div>

</body>

</html>