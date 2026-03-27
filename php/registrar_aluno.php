<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nome  = $_POST["nome"];
  $email = $_POST["email"];
  $senha = $_POST["senha"];

  $sql    = "SELECT id FROM usuarios_oyama WHERE email = '$email'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    /* volta para o formulário com flag de erro */
    $origem = $_SERVER['HTTP_REFERER'] ?? '../index.html';
    header("Location: $origem?modal=email_existente");
    exit();
  } else {
    $sql = "INSERT INTO usuarios_oyama (nome, email,  senha, tipo) VALUES ('$nome', '$email', '$senha', 'aluno')";

    if (mysqli_query($conn, $sql)) {
      $usuario_id = mysqli_insert_id($conn);
      header("Location: perfil_aluno.php?id=$usuario_id");
      exit();
    } else {
      $origem = $_SERVER['HTTP_REFERER'] ?? '../index.html';
      header("Location: $origem?modal=erro_cadastro");
      exit();
    }
  }
} else {
  header("Location: ../php/login.php");
  exit();
}

mysqli_close($conn);
?>