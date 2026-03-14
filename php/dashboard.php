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

  <?php
include 'connect.php'; // Remova o "$conn->close();" do seu connect.php original para funcionar aqui

// Lógica para DELETAR (Delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM treinos WHERE id=$id");
    header("Location: dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="../css/style.css">
    <title>Meu Diário de Treino - Oyama Hub</title>
</head>
<body>
    <section class="container">
        <h2>🥋 Meu Diário de Treino Kyokushin</h2>
        
        <form action="inserir_treino.php" method="POST" class="training-card">
            <input type="date" name="data_treino" required>
            <select name="tipo_treino">
                <option value="Kihon">Kihon (Básico)</option>
                <option value="Kata">Kata (Forma)</option>
                <option value="Kumite">Kumite (Luta)</option>
                <option value="Condicionamento">Condicionamento</option>
            </select>
            <input type="number" name="duracao" placeholder="Minutos (ex: 60)">
            <textarea name="descricao" placeholder="O que você treinou hoje?"></textarea>
            <button type="submit" class="btn-primary">Registrar Treino</button>
        </form>

        <hr>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Duração</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM treinos ORDER BY data_treino DESC");
                while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['data_treino']; ?></td>
                    <td><?php echo $row['tipo_treino']; ?></td>
                    <td><?php echo $row['duracao']; ?> min</td>
                    <td><?php echo $row['descricao']; ?></td>
                    <td>
                        <a href="editar_treino.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                        <a href="dashboard.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Excluir registro?')">Apagar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</body>
</html>

  <a href="logout.php">Sair</a>

</body>

</html>