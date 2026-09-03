<?php
include("config.php");
require_once("csrf.php");
require_once("auth_check.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php");
    exit();
}

validar_csrf();

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

$dataNascimento = DateTime::createFromFormat('!Y-m-d', $nascimento);
if (!$dataNascimento || $dataNascimento->format('Y-m-d') !== $nascimento || $dataNascimento > new DateTime('today')) {
    header("Location: registro.php?status=erro&msg=data_nascimento_invalida");
    exit();
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$faixa_id_sql = ($faixa_id === "") ? null : (int) $faixa_id;

$stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    header("Location: registro.php?status=erro&msg=email_cadastrado");
    exit();
}

$stmt_insert = mysqli_prepare($conn, "INSERT INTO usuarios (nome, email, senha_hash, nascimento, tipo, faixa_id) VALUES (?, ?, ?, ?, 'aluno', ?)");
mysqli_stmt_bind_param($stmt_insert, "sssss", $nome, $email, $senha_hash, $nascimento, $faixa_id_sql);

if (mysqli_stmt_execute($stmt_insert)) {
    mysqli_close($conn);
    // Registrar novo cadastro no log de atividades
    $novo_id = mysqli_insert_id($conn);
    log_activity($conn, 'registro_aluno', "Novo aluno registrado: '{$nome}' (email: {$email})");
    header("Location: login.php?status=sucesso_registro");
    exit();
}

mysqli_close($conn);
header("Location: registro.php?status=erro&msg=db_error");
exit();
?>
