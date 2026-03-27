<?php ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oyama Hub | Progresso</title>

    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #0f0f0f;
            color: #fff;
        }

        .navbar {
            background: #111;
            padding: 15px 0;
            border-bottom: 2px solid #e60000;
        }

        .nav-links {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .nav-links li a {
            text-decoration: none;
            color: #ccc;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .nav-links li a:hover,
        .nav-links li a.active {
            color: #e60000;
        }

        .logout-btn {
            color: #ff4d4d !important;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .header-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-text h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header-text p {
            color: #aaa;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .card {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }

        .main-card {
            grid-column: span 2;
            text-align: center;
        }

        .belt-sequence {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .belt {
            width: 40px;
            height: 12px;
            border-radius: 4px;
        }

        .white { background: #fff; }
        .white-orange { background: linear-gradient(90deg, #fff 50%, orange 50%); }
        .orange { background: orange; }
        .blue { background: #007bff; }
        .yellow { background: #ffd000; }

        .current {
            transform: scale(1.2);
            box-shadow: 0 0 10px orange;
        }

        .arrow {
            font-size: 20px;
            color: #888;
        }

        .progress-big-text {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .red-text {
            color: #e60000;
        }

        .sub-text {
            color: #aaa;
        }

        .chart-sim {
            position: relative;
            height: 150px;
            border-left: 2px solid #444;
            border-bottom: 2px solid #444;
        }

        .red-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: #e60000;
            top: 50%;
        }

        .dots .dot {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #e60000;
            border-radius: 50%;
        }

        .list-card h3 {
            font-family: 'Oswald', sans-serif;
            margin-bottom: 15px;
        }

        .list-card ul {
            list-style: none;
        }

        .list-card li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #333;
        }

        .done {
            color: #00ff88;
        }

        .pending {
            color: #ffaa00;
        }

        .icon {
            color: #00ff88;
        }

        .icon-p {
            color: #ffaa00;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .main-card {
                grid-column: span 1;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <ul class="nav-links">
        <li><a href="../index.php">INICIO</a></li>
        <li><a href="progresso.php" class="active">PROGRESSO</a></li>
        <li><a href="katas.php">KATAS</a></li>
        <li><a href="kihon.php">KIHON</a></li>
        <li><a href="treinos.php">TREINOS</a></li>
        <li><a href="logout.php" class="logout-btn">LOGOUT</a></li>
    </ul>
</header>

<main class="container">
    <section class="header-text">
        <h1>MEU PROGRESSO - KYOKUSHIN KARATE</h1>
        <p>Olá, <strong>PARDON</strong>! Acompanhe aqui os seus passos rumo à faixa preta.</p>
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