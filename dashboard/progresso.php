<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oyama Hub | Progresso</title>

    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/progresso.css">
</head>

<body>

<section class="navbarArea">
  <div class="header">
    <a href="../index.php">INICIO</a>
    <a href="progresso.php" class="active">PROGRESSO</a>
    <a href="katas.php">KATAS</a>
    <a href="kihon.php">KIHON</a>
    <a href="treinos.php">TREINOS</a>
    <a href="../php/logout.php" class="logout-btn">LOGOUT</a>
  </div>
</section>

<main class="container">
    <section class="header-text">
        <h1>MEU PROGRESSO - KYOKUSHIN KARATE</h1>
        <p>Olá, <strong><?php echo isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : 'PARDON'; ?></strong>! Acompanhe aqui os seus passos rumo à faixa preta.</p>
    </section>

    <div class="dashboard-grid">
        <div class="card main-card">
            <div class="belt-sequence">
                <div class="belt white"></div>
                <div class="belt white-orange"></div>
                <div class="belt orange current"></div>
                <div class="arrow">»</div>
                <div class="belt blue"></div>
                <div class="belt yellow"></div>
            </div>
            <div class="progress-big-text">
                <span class="red-text">40%</span> CONCLUÍDO
            </div>
            <p class="sub-text">Orange Belt - 8º Kyu</p>
        </div>

        <div class="card graph-card">
            <div class="chart-sim">
                <div class="line red-line"></div>
                <div class="dots">
                    <span class="dot" style="bottom: 10%; left: 10%;"></span>
                    <span class="dot" style="bottom: 40%; left: 50%;"></span>
                    <span class="dot" style="bottom: 80%; left: 90%;"></span>
                </div>
            </div>
        </div>

        <div class="card list-card">
            <h3>KATAS CONCLUÍDOS</h3>
            <ul>
                <li class="done">Taikyoku Sono Ichi <span class="icon">✔</span></li>
                <li class="done">Taikyoku Sono Ni <span class="icon">✔</span></li>
                <li class="pending">Taikyoku Sono San <span class="icon-p">⏳</span></li>
            </ul>
        </div>

        <div class="card list-card">
            <h3>KIHONS DOMINADOS</h3>
            <ul>
                <li class="done">Chudan-zuki <span class="icon">✔</span></li>
                <li class="done">Jodan-uke <span class="icon">✔</span></li>
                <li class="pending">Mae-geri <span class="icon-p">⏳</span></li>
            </ul>
        </div>
    </div>
</main>

</body>
</html>