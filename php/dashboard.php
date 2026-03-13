<?php
session_start();

if (!isset($_SESSION["id"])) {
  header("Location: login.php");
  exit;
}

require("config.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>

<body>

  <h1>Bem vindo <?php echo $_SESSION["nome"]; ?></h1>

  <a href="logout.php">Sair</a>

</body>

</html>