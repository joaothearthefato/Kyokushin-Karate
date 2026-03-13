<?php
include('connect.php')
try {
    // Estabelecendo a conexão
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Configura o PDO para mostrar erros caso algo dê errado
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha_digitada = $_POST['senha'];

        // Criptografando a senha antes de salvar no banco (Boa prática de segurança)
        $senha_criptografada = password_hash($senha_digitada, PASSWORD_DEFAULT);

        // Preparando o comando SQL para inserir o aluno
        $sql = "INSERT INTO alunos (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $pdo->prepare($sql);

        // Vinculando os dados digitados aos parâmetros do SQL
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_criptografada);

        // Executando a inserção
        if ($stmt->execute()) {
            echo "<script>
                    alert('Osu! Cadastro realizado com sucesso.');
                    window.location.href='login.html';
                  </script>";
        }
    }

} catch(PDOException $e) {
    // Verifica se o erro é de e-mail duplicado (código 23000 no MySQL)
    if ($e->getCode() == 23000) {
        echo "<script>
                alert('Este e-mail já está cadastrado no Dojo!');
                window.history.back();
              </script>";
    } else {
        echo "Erro no servidor: " . $e->getMessage();
    }
}
?>