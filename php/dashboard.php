<?php
session_start();
require 'config.php'; 


?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oyama Hub | Dashboard</title>
<link rel="icon" href="../img/kyokushinicon.png">
<link rel="stylesheet" href="../css/dashboard.css">
<meta name="description" content="Treine Karate Kyokushin e desenvolva força, disciplina e superação.">
</head>
<body>

<section class="navbarArea">
<div class="header">
<a href="../index.html">Inicio</a>
<a href="../dashboard/progresso.php">Progresso</a>
<a href="../dashboard/katas.php">Katas</a>
<a href="../dashboard/kihon.php">Kihon</a>
<a href="../dashboard/treinos.php">Treinos</a> 
 <a href="../php/logout.php"> <button class="logout-btn">Logout</button></a>
</div>
</section>

<div class="welcome-container">
<h1>Bem-vindo ao Oyama Hub, <?php 
echo $_SESSION['nome']; ?>
!</h1>
</div>

<div class="dashboard-container">
<section class="dashboard-section">
  <a href="../dashboard/progresso.php" class="dashboard-link">
    <div class="dashboard-card">
      <h2>Progresso</h2>
      <p>Acompanhe seu progresso e conquistas no Karate Kyokushin.</p>
    </div>
  </a>

  <a href="../dashboard/katas.php" class="dashboard-link">
    <div class="dashboard-card">
      <h2>Katas</h2>
      <p>Explore os katas e aprimore suas técnicas de forma estruturada.</p>
    </div>
  </a>

  <a href="../dashboard/kihon.php" class="dashboard-link">
    <div class="dashboard-card">
      <h2>Kihon</h2>
      <p>Domine os fundamentos do Karate Kyokushin com os exercícios de Kihon.</p>
    </div>
  </a>

  <a href="../dashboard/treinos.php" class="dashboard-link">
    <div class="dashboard-card">
      <h2>Treinos</h2>
      <p>Encontre treinos personalizados para aprimorar suas habilidades no Karate Kyokushin.</p>
    </div>
  </a>
</section>
</div>


</body>
</html>