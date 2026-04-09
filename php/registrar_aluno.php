<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php");
    exit();
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";
$nascimento = $_POST["nascimento"] ?? "";
$faixa_id = $_POST["faixa_id"] ?? "";

if ($nome === "" || $email === "" || $senha === "" || $nascimento === "") {
    header("Location: registro.php?status=erro&msg=campos_obrigatorios");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: registro.php?status=erro&msg=email_invalido");
    exit();
}

$nome_esc = mysqli_real_escape_string($conn, $nome);
$email_esc = mysqli_real_escape_string($conn, $email);
$nascimento_esc = mysqli_real_escape_string($conn, $nascimento);
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$faixa_id_sql = ($faixa_id === "" ? "NULL" : (int) $faixa_id);

$sql = "SELECT id FROM usuarios WHERE email = '$email_esc'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    header("Location: registro.php?status=erro&msg=email_cadastrado");
    exit();
}

$sql = "INSERT INTO usuarios (nome, email, senha_hash, nascimento, tipo, faixa_id) VALUES ('$nome_esc', '$email_esc', '$senha_hash', '$nascimento_esc', 'aluno', $faixa_id_sql)";

if (mysqli_query($conn, $sql)) {
    mysqli_close($conn);
    header("Location: login.php?status=sucesso_registro");
    exit();
}

mysqli_close($conn);
header("Location: registro.php?status=erro&msg=db_error");
exit();
?>