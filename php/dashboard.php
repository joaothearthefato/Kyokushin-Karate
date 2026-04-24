<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oyama Hub | Dashboard</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <meta name="description" content="Dashboard do aluno com progresso, katas, kihon e plano de treinos.">
</head>
<body>
  <header>
    <nav class="navbar">
      <a href="../index.html" aria-label="Voltar para a página inicial">
        <img src="../img/kyokushinicon.png" class="logo" alt="Kyokushin Logo">
      </a>

      <div class="nav-links">
        <a href="#inicio">Início</a>
        <a href="#progresso">Progresso</a>
        <a href="#katas">Katas</a>
        <a href="#kihon">Kihon</a>
        <a href="#treinos">Treinos</a>
      </div>

      <a href="../php/logout.php">
        <button id="login">Logout</button>
      </a>
    </nav>
  </header>

  <main class="dashboard-main container" id="inicio">
    <section class="dashboard-hero">
      <p class="dashboard-tag">OSS • DISCIPLINA • EVOLUÇÃO</p>
      <h1>Bem-vindo ao seu Dojo Digital</h1>
      <p>Acompanhe sua evolução técnica e mantenha constância nos treinos da semana.</p>
    </section>

    <section class="dashboard-grid" id="progresso">
      <article class="dashboard-card emphasis">
        <h2>Progresso Geral</h2>
        <p>Você concluiu <strong>72%</strong> das metas mensais.</p>
        <div class="progress-track" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100">
          <span style="width: 72%;"></span>
        </div>
      </article>

      <article class="dashboard-card" id="katas">
        <h2>Katas em foco</h2>
        <ul>
          <li>Taikyoku Sono Ichi — revisão técnica</li>
          <li>Pinan Sono Ni — precisão de base</li>
          <li>Sanchin No Kata — respiração e postura</li>
        </ul>
      </article>

      <article class="dashboard-card" id="kihon">
        <h2>Kihon da Semana</h2>
        <ul>
          <li>Gyaku Zuki (4x20)</li>
          <li>Mae Geri Chudan (4x15)</li>
          <li>Gedan Barai + Oi Zuki (4x10)</li>
        </ul>
      </article>

      <article class="dashboard-card" id="treinos">
        <h2>Plano de Treino</h2>
        <p>Próximo treino: <strong>Sábado • 08:00</strong></p>
        <p>Foco: condicionamento, low kicks e rounds de kumite.</p>
        <a class="dashboard-action" href="../php/perfil_aluno.php">Ver perfil completo</a>
      </article>
    </section>
  </main>
</body>
</html>
