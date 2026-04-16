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
    <a href="../php/dashboard.php">Início</a>
    <a href="progresso.php" class="active">Progresso</a>
    <a href="katas.php">Katas</a>
    <a href="kihon.php">Kihon</a>
    <a href="treinos.php">Treinos</a>
    <button id="theme-toggle" class="theme-btn" aria-label="Alternar tema">
        <span class="theme-icon">☀️</span>
        <span class="theme-label">Light</span>
    </button>
    <a href="../php/logout.php"><button class="logout-btn">Logout</button></a>
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

<script>
  const themeToggle = document.getElementById('theme-toggle');
  const themeIcon = themeToggle.querySelector('.theme-icon');
  const themeLabel = themeToggle.querySelector('.theme-label');
  const html = document.documentElement;

  // Check for saved theme preference or default to dark mode
  const currentTheme = localStorage.getItem('theme') || 'dark';
  if (currentTheme === 'light') {
    html.classList.add('light');
    themeIcon.textContent = '🌙';
    themeLabel.textContent = 'Dark';
  }

  themeToggle.addEventListener('click', () => {
    html.classList.toggle('light');
    const isLight = html.classList.contains('light');

    // Update button appearance with animation
    if (isLight) {
      themeIcon.textContent = '🌙';
      themeLabel.textContent = 'Dark';
      localStorage.setItem('theme', 'light');
    } else {
      themeIcon.textContent = '☀️';
      themeLabel.textContent = 'Light';
      localStorage.setItem('theme', 'dark');
    }

    // Add click animation
    themeToggle.style.transform = 'scale(0.95)';
    setTimeout(() => {
      themeToggle.style.transform = '';
    }, 150);
  });
</script>

</body>
</html>