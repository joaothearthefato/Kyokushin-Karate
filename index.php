<?php
session_start();
require './php/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oyama Hub | Karate Kyokushin</title>
<link rel="icon" href="./img/kyokushinicon.png">
<link rel="stylesheet" href="css/style.css">
<meta name="description" content="Treine Karate Kyokushin e desenvolva força, disciplina e superação.">

</head>
<body>
<header>
<nav class="navbar">

<img src="./img/kyokushinicon.png" class="logo" alt="Kyokushin Logo">

<div class="nav-links">
<a href="#home">Home</a>
<a href="#about">Sobre</a>
<a href="#techniques">Técnicas</a>
<a href="#training">Equipamento</a>
<a href="#testimonials">Depoimentos</a>
<a href="#faixas">Faixas</a>
</div>
<?php 
if(isset($_SESSION['nome'])) {
    echo '<a href="./php/dashboard.php"><button id="login">' . htmlspecialchars($_SESSION['nome']) . '</button></a>';
} else {
    echo '<a href="./php/login.php"><button id="login">Login</button></a>';
}
?>
</nav>
</header>


<main>

<section id="home" class="hero">
<div class="hero-content">

<h1>DOMINE O <br>KYOKUSHIN</h1>

<p>O caminho da verdade. Disciplina, força e superação.</p>

<div class="button-group">
<a href="./php/registro.php" class="btn-primary">Registrar</a>
<a href="https://www.youtube.com/watch?v=viLy-JlkTCw&pp=ygUdZG9jdW1lbnRhcmlvIGthcmF0ZSBreW9rdXNoaW4%3D" class="btn-secondary">Saiba mais</a>
</div>

</div>
</section>


<section id="about" class="about container">

<h2 class="section-title">Sobre o Kyokushin</h2>

<div class="about-content">

<p>
Arte marcial full-contact criada por <strong>Masutatsu Oyama</strong>.
O Kyokushin desenvolve disciplina mental, resistência física e
combate real.
</p>

<div class="placeholder-image">
<img src="./img/oyama.jpg" alt="Treino de Kyokushin" style="width: 100%; max-width: 400px; height: 100%;">
</div>

</div>

</section>


<section id="techniques" class="techniques container">

<h2 class="section-title">Técnicas</h2>

<div class="technique-grid">

<article class="technique-card">
<h3>Mae Geri</h3>
<p>Chute frontal rápido usado para manter distância e atingir o abdômen.</p>
</article>

<article class="technique-card">
<h3>Gedan Mawashi Geri</h3>
<p>Low kick na perna para enfraquecer a base e mobilidade do adversário.</p>
</article>

<article class="technique-card">
<h3>Gyaku Zuki</h3>
<p>Soco reverso forte, principal golpe de potência do karate.</p>
</article>

<article class="technique-card">
<h3>Mawashi Geri</h3>
<p>Chute circular usado para atingir costelas ou cabeça.</p>
</article>

<article class="technique-card">
<h3>Hiza Geri</h3>
<p>Joelhada de curta distância, muito forte no combate próximo.</p>
</article>

<article class="technique-card">
<h3>Tsuki</h3>
<p>Sequência de socos no corpo para pressionar e cansar o adversário.</p>
</article>

<article class="technique-card">
<h3>Sabaki</h3>
<p>Movimento de esquiva e ângulo para sair da linha do ataque e contra-atacar.</p>
</article>

</div>

</section>


<section id="training" class="training container">

<h2 class="section-title">Equipamento</h2>

<div class="training-grid">

<div class="training-card">
<h3>Karategi</h3>
<p>Um roupão longo em formato de "T", com mangas largas e retas, que envolve o corpo</p>
<img src="img/kimono-Photoroom.png" alt="Foto de um kimono" style="width: 250px; height: 250px;">
</div>

<div class="training-card">
<h3 id="faixa-title">Faixa</h3>
<p>
As faixas no Karate Kyokushin representam a evolução técnica, física e espiritual do praticante
<img src="img/faixakarate.png" alt="Foto de um kimono" style="width: 250px; height: 250px; align-items: center; display: flex; justify-content: center;">
</p>
</div>

</section>


<section id="testimonials" class="testimonials container">

<h2 class="section-title">Depoimentos</h2>

<div class="testimonial-grid">

<blockquote class="testimonial-card">
<p>"Mudou minha vida."</p>
<cite>João</cite>
</blockquote>

<blockquote class="testimonial-card">
<p>"Treino duro e recompensador."</p>
<cite>Maria</cite>
</blockquote>

<blockquote class="testimonial-card">
<p>"Comunidade incrível."</p>
<cite>Pedro</cite>
</blockquote>

</div>

</section>
<section class="faixas" id="faixas">
<h2 class="section-title">Faixa</h2>  
<div class="faixa-grid"> 
  <div class="faixacss">

<blockquote class="faixa-card" id="faixabranca">
<p>"Faixa Branca"</p><br>
<cite>11° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixalaranja">
<p>"Faixa Laranja"</p><br>
<cite>10° e 9° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixaazul">
<p>"Faixa Azul</p><br>
<cite>8° e 7° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixaamarela">
<p>"Faixa Amarela"</p><br>
<cite>6° e 5° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixaverde">
<p>"Faixa Verde"</p><br>
<cite>4° e 3° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixamarrom">
<p>"Faixa Marrom"</p><br>
<cite>2° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixamarrompontapreta">
<p>"Faixa Marrom com Ponto Preto"</p><br>
<cite>1° Kyu</cite>
</blockquote>

<blockquote class="faixa-card" id="faixapreta">
<p>"Faixa Preta"</p><br>
<cite>Senpai</cite>
</blockquote>
</div>
</div>
</section>

</main>
<footer>

<p>© 2026 Oyama Hub</p>

<div class="footer-links">
<a href="#">Privacy</a>
<a href="#">Contato</a>
</div>

</footer>

</body>
</html>
