<?php
include("config.php");

if (!isset($_GET['id'])) {
    header("Location: ./dashboard.php");
    exit();
}

$usuario_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT id, nome, email FROM usuarios_oyama WHERE id = '$usuario_id' AND tipo = 'aluno'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: ../index.html?status=erro&msg=usuario_nao_encontrado");
    exit();
}

$usuario = mysqli_fetch_assoc($result);

// Lógica de Update
$mensagem_sucesso = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idade = $_POST["idade"];
    $altura = $_POST["altura"];
    $peso = $_POST["peso"];
    $faixa_id = $_POST["faixa_id"];

    $sql = "UPDATE usuarios_oyama SET idade = '$idade', altura = '$altura', peso = '$peso', faixa_id = '$faixa_id' WHERE id = '$usuario_id'";

    if (mysqli_query($conn, $sql)) {
        $mensagem_sucesso = true;
    }
}

$sql_faixas = "SELECT id, nome FROM faixas ORDER BY ordem ASC";
$result_faixas = mysqli_query($conn, $sql_faixas);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - Kyokushin Dojo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/perfil_aluno.css">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="perfil-container">
    <div class="perfil-card">
        <div class="perfil-header">
            <h1>Osu, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
            <p>Complete sua graduação para continuar.</p>
        </div>

        <form method="POST" class="perfil-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Idade</label>
                    <input type="number" name="idade" required>
                </div>
                <div class="form-group">
                    <label>Altura (cm)</label>
                    <input type="number" name="altura" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Peso (kg)</label>
                    <input type="number" name="peso" step="0.1" required>
                </div>
            </div>

            <div class="form-group full-width">
                <label>Sua Faixa Atual</label>
                <select name="faixa_id" required>
                    <option value="">Selecione sua faixa</option>
                    <?php while ($faixa = mysqli_fetch_assoc($result_faixas)): ?>
                        <option value="<?php echo $faixa['id']; ?>"><?php echo $faixa['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Finalizar Cadastro</button>
            </div>
        </form>
    </div>
</div>

<script>
// 1. Verifica se acabou de vir do registro
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'sucesso_registro') {
    Swal.fire({
        title: 'Bem-vindo ao Dojo!',
        text: 'Sua conta foi criada. Agora, complete seu perfil técnico.',
        icon: 'success',
        confirmButtonColor: '#c0392b'
    });
}

// 2. Verifica se o formulário de perfil foi salvo com sucesso
<?php if ($mensagem_sucesso): ?>
    Swal.fire({
        title: 'Perfil Atualizado!',
        text: 'Suas informações foram salvas com sucesso.',
        icon: 'success',
        confirmButtonColor: '#27ae60'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../index.html';
        }
    });
<?php endif; ?>
</script>

</body>
</html>