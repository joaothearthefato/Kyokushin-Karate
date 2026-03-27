<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = mysqli_real_escape_string($conn, $_POST["nome"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $senha = $_POST["senha"]; // Recomendo usar password_hash futuramente

    $sql    = "SELECT id FROM usuarios_oyama WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        header("Location: ../index.html?status=erro&msg=email_cadastrado");
        exit();
    } else {
        // Removi a idade daqui, pois ela será preenchida no perfil_aluno.php
        $sql = "INSERT INTO usuarios_oyama (nome, email, senha, tipo) VALUES ('$nome', '$email', '$senha', 'aluno')";

        if (mysqli_query($conn, $sql)) {
            $usuario_id = mysqli_insert_id($conn);
            // Redireciona com sucesso
            header("Location: perfil_aluno.php?id=$usuario_id&status=sucesso_registro");
            exit();
        } else {
            header("Location: ../index.html?status=erro&msg=db_error");
            exit();
        }
    }
}
mysqli_close($conn);
?>