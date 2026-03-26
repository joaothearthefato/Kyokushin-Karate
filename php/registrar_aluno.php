<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nome = $_POST["nome"];
  $email = $_POST["email"];
  $senha = $_POST["senha"];

  // Verificar se email já existe
  $sql = "SELECT id FROM usuarios_oyama WHERE email = '$email'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('Este email já está cadastrado!'); history.back();</script>";
  } else {
    // Inserir novo usuário
    $sql = "INSERT INTO usuarios_oyama (nome, email, senha, tipo) VALUES ('$nome', '$email', '$senha', 'aluno')";

    if (mysqli_query($conn, $sql)) {
      $usuario_id = mysqli_insert_id($conn);
      header("Location: perfil_aluno.php?id=$usuario_id");
      exit();
    } else {
      echo "<script>alert('Erro ao cadastrar usuário. Tente novamente.'); history.back();</script>";
    }
  }
} else {
  header("Location: ../php/login.php");
  exit();
}

mysqli_close($conn);
?>