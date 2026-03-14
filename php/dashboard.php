<?php
session_start();

if (!isset($_SESSION["id"])) {
  header("Location: login.php");
  exit;
}

require("config.php");
?>

<?php
include 'connect.php';
//dado do banco po dashboard
$total_treinos = $conn->query("SELECT COUNT(*) as total FROM treinos")->fetch_assoc()['total'];
$ultimo_treino = $conn->query("SELECT tipo_treino FROM treinos ORDER BY id DESC LIMIT 1")->fetch_assoc()['tipo_treino'] ?? "Nenhum";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Oyama Hub | Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #d32f2f; /* Vermelho Kyokushin */
            --dark: #121212;
            --gray: #f4f4f4;
        }

        body { background: var(--gray); font-family: 'Segoe UI', sans-serif; margin: 0; }
        
        /* Layout Principal */
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr; /* Sidebar e Conteúdo */
            min-height: 100vh;
        }

        /* Sidebar Estilo Dojo */
        .sidebar {
            background: var(--dark);
            color: white;
            padding: 20px;
        }

        .sidebar h2 { color: var(--primary); font-size: 1.2rem; margin-bottom: 30px; }
        .sidebar-menu a {
            display: block;
            color: #ccc;
            padding: 12px 0;
            text-decoration: none;
            border-bottom: 1px solid #333;
        }

        /* Cards de Resumo */
        .main-content { padding: 30px; }
        
        .header-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-top: 4px solid var(--primary);
        }

        .stat-card h3 { margin: 0; font-size: 0.9rem; color: #666; }
        .stat-card p { margin: 10px 0 0; font-size: 1.8rem; font-weight: bold; }

        /* Area da Planilha */
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .table-treinos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-treinos th { text-align: left; padding: 12px; border-bottom: 2px solid var(--gray); }
        .table-treinos td { padding: 12px; border-bottom: 1px solid var(--gray); }

        .btn-add {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <h2>OYAMA HUB</h2>
        <nav class="sidebar-menu">
            <a href="#">🏠 Início</a>
            <a href="#planilha">📊 Minha Planilha</a>
            <a href="index.html#techniques">🥋 Técnicas</a>
            <a href="index.html#about">📖 História</a>
            <a href="index.html" style="color: var(--primary);">⬅ Sair</a>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <h1>Osu, Praticante!</h1>
            <p>Acompanhe sua evolução no Kyokushin Karate.</p>
        </header>

        <div class="header-stats">
            <div class="stat-card">
                <h3>Total de Treinos</h3>
                <p><?php echo $total_treinos; ?></p>
            </div>
            <div class="stat-card">
                <h3>Último Foco</h3>
                <p><?php echo $ultimo_treino; ?></p>
            </div>
            <div class="stat-card" style="border-top-color: #ff9800;">
                <h3>Status Kyu</h3>
                <p>Faixa Branca</p>
            </div>
        </div>

        <section class="content-card" id="planilha">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Histórico de Treinos</h2>
                <a href="novo_treino.php" class="btn-add">+ Registrar Treino</a>
            </div>

            <table class="table-treinos">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Modalidade</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM treinos ORDER BY data_treino DESC LIMIT 5");
                    while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['data_treino'])) ?></td>
                        <td><strong><?= $row['tipo_treino'] ?></strong></td>
                        <td><?= $row['descricao'] ?></td>
                        <td>
                            <a href="editar.php?id=<?= $row['id'] ?>">✏️</a>
                            <a href="deletar.php?id=<?= $row['id'] ?>" style="margin-left:10px">❌</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>
</html>