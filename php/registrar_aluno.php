<?php

require_once __DIR__ . '/config.php';

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth_check.php';

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

if (strlen($senha) < 8 || strlen($senha) > 255) {
    header("Location: registro.php?status=erro&msg=senha_invalida");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: registro.php?status=erro&msg=email_invalido");
    exit();
}

$dataNascimento = DateTime::createFromFormat('!Y-m-d', $nascimento);

if (
    !$dataNascimento ||
    $dataNascimento->format('Y-m-d') !== $nascimento ||
    $dataNascimento > new DateTime('today')
) {
    header("Location: registro.php?status=erro&msg=data_nascimento_invalida");
    exit();
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

/*
 * Verifica se o e-mail já existe
 */
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM usuarios WHERE email = ?"
);

if (!$stmt) {
    error_log("Erro ao preparar SELECT: " . mysqli_error($conn));
    header("Location: registro.php?status=erro&msg=db_error");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Erro ao executar SELECT: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    header("Location: registro.php?status=erro&msg=db_error");
    exit();
}

$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: registro.php?status=erro&msg=email_cadastrado");
    exit();
}

mysqli_stmt_close($stmt);

/*
 * Prepara INSERT
 */
if ($faixa_id === "") {

    $stmt_insert = mysqli_prepare(
        $conn,
        "INSERT INTO usuarios
        (nome, email, senha_hash, nascimento, tipo, faixa_id)
        VALUES (?, ?, ?, ?, 'aluno', NULL)"
    );

    if (!$stmt_insert) {
        error_log("Erro ao preparar INSERT: " . mysqli_error($conn));
        mysqli_close($conn);

        header("Location: registro.php?status=erro&msg=db_error");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt_insert,
        "ssss",
        $nome,
        $email,
        $senha_hash,
        $nascimento
    );

} else {

    $faixa_id_sql = (int) $faixa_id;

    if ($faixa_id_sql <= 0) {
        header("Location: registro.php?status=erro&msg=faixa_invalida");
        exit();
    }

    $stmt_faixa = mysqli_prepare($conn, "SELECT id FROM faixas WHERE id = ?");
    mysqli_stmt_bind_param($stmt_faixa, "i", $faixa_id_sql);
    mysqli_stmt_execute($stmt_faixa);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_faixa))) {
        header("Location: registro.php?status=erro&msg=faixa_invalida");
        exit();
    }

    $stmt_insert = mysqli_prepare(
        $conn,
        "INSERT INTO usuarios
        (nome, email, senha_hash, nascimento, tipo, faixa_id)
        VALUES (?, ?, ?, ?, 'aluno', ?)"
    );

    if (!$stmt_insert) {
        error_log("Erro ao preparar INSERT: " . mysqli_error($conn));
        mysqli_close($conn);

        header("Location: registro.php?status=erro&msg=db_error");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt_insert,
        "ssssi",
        $nome,
        $email,
        $senha_hash,
        $nascimento,
        $faixa_id_sql
    );
}

/*
 * Executa INSERT
 */
if (mysqli_stmt_execute($stmt_insert)) {

    $novo_id = mysqli_insert_id($conn);

    log_activity(
        $conn,
        'registro_aluno',
        "Novo aluno registrado: '{$nome}' (ID: {$novo_id}, email: {$email})"
    );

    mysqli_stmt_close($stmt_insert);
    mysqli_close($conn);

    header("Location: login.php?status=sucesso_registro");
    exit();
}

/*
 * Erro no INSERT
 */
$erro = mysqli_stmt_error($stmt_insert);

error_log("Erro ao inserir usuário: " . $erro);

mysqli_stmt_close($stmt_insert);
mysqli_close($conn);

header("Location: registro.php?status=erro&msg=db_error");
exit();

?>