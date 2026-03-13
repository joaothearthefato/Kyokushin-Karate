<?php
session_start();
require("config.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <h1>Bem vindo <?php echo $_SESSION["nome"]; ?></h1>
  <a href="logout.php">Sair</a>
</body>

</html>