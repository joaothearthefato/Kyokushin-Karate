<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acessibilidade | Oyama Hub</title>
  <link rel="icon" href="../img/kyokushinicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  
  <style>
    .a11y-container {
        max-width: 900px;
        margin: 40px auto 100px;
        padding: 0 24px;
        color: var(--text, #d8d3cc);
        font-family: 'Inter', sans-serif;
    }
    
    .a11y-header {
        text-align: center;
        margin-bottom: 50px;
        border-bottom: 1px solid var(--border, rgba(255,255,255,0.1));
        padding-bottom: 30px;
    }
    
    .a11y-header h1 {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 4rem;
        letter-spacing: 4px;
        color: #fff;
        margin-bottom: 10px;
    }
    
    .a11y-section {
        background: var(--surface, #181818);
        border: 1px solid var(--border, rgba(255,255,255,0.1));
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 8px;
    }
    
    .a11y-section h2 {
        font-family: 'Oswald', sans-serif;
        font-size: 1.8rem;
        color: var(--red, #c8000a);
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .a11y-section p, .a11y-section li {
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 15px;
        color: var(--muted, #888);
    }
    
    .a11y-section ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }
    
    .a11y-shortcuts {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .a11y-shortcuts th, .a11y-shortcuts td {
        border: 1px solid var(--border, rgba(255,255,255,0.1));
        padding: 12px 15px;
        text-align: left;
    }
    
    .a11y-shortcuts th {
        background: rgba(255,255,255,0.05);
        color: #fff;
        font-family: 'Oswald', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    kbd {
        background: var(--dark, #111);
        border: 1px solid var(--muted, #555);
        border-radius: 4px;
        padding: 4px 8px;
        font-family: monospace;
        color: #fff;
        font-size: 0.9rem;
    }
    
    .btn-voltar {
        display: inline-block;
        margin-top: 20px;
        color: var(--red, #c8000a);
        text-decoration: none;
        font-family: 'Oswald', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 600;
        transition: color 0.2s;
    }
    
    .btn-voltar:hover {
        color: var(--text, #d8d3cc);
    }
  </style>
  <script src="../js/theme.js" defer></script>
</head>
<body>

  <header>
    <nav class="navbar" aria-label="Menu principal">
      <div class="nav-left">
        <a href="../index.html"><img src="../img/kyokushinicon.png" class="logo" alt="Ir para a Home"></a>
      </div>
      <div class="nav-links">
        <a href="../index.html">Voltar ao Início</a>
      </div>
    </nav>
  </header>

  <main class="a11y-container">
    
    <header class="a11y-header">
        <h1>Acessibilidade</h1>
        <p>Recursos destinados a facilitar o acesso às informações e ao Karatê Kyokushin para todas as pessoas.</p>
    </header>

    <section class="a11y-section">
        <h2>Recursos de Visualização e Leitura</h2>
        <p>O Oyama Hub disponibiliza um <strong>Painel Flutuante de Acessibilidade</strong> (ícone ♿ no canto inferior direito da tela). Através dele, você pode personalizar a sua experiência no site:</p>
        <ul>
            <li><strong>Tamanho do Texto:</strong> Aumente a fonte em até 125% para facilitar a leitura sem quebrar o layout.</li>
            <li><strong>Alto Contraste:</strong> Alterne para um tema de cores puras e alto contraste, auxiliando usuários com baixa visão ou daltonismo.</li>
            <li><strong>Reduzir Animações:</strong> Desative movimentos contínuos e transições CSS para prevenir episódios de náusea ou fadiga (Motion Sickness).</li>
            <li><strong>Leitura por Voz (TTS):</strong> Ative a narração por voz nativa do navegador para ler o conteúdo principal da tela.</li>
        </ul>
        <p>Todas as configurações são salvas automaticamente e persistem através das páginas que você visitar.</p>
    </section>

    <section class="a11y-section">
        <h2>Navegação por Teclado</h2>
        <p>Todo o site é 100% navegável sem a necessidade de um mouse. Você notará um forte contorno vermelho ao navegar interativamente pelos botões e formulários.</p>
        
        <table class="a11y-shortcuts">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>Atalho do Teclado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Avançar para o próximo elemento interativo</td>
                    <td><kbd>TAB</kbd></td>
                </tr>
                <tr>
                    <td>Voltar para o elemento interativo anterior</td>
                    <td><kbd>SHIFT</kbd> + <kbd>TAB</kbd></td>
                </tr>
                <tr>
                    <td>Ativar um botão ou link focado</td>
                    <td><kbd>ENTER</kbd></td>
                </tr>
                <tr>
                    <td>Fechar menus ou modais abertos</td>
                    <td><kbd>ESC</kbd></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="a11y-section">
        <h2>♿ Karatê e Acessibilidade</h2>
        <p>O Kyokushin é o caminho da verdade e da superação. Acreditamos que as artes marciais devem ser inclusivas e adaptáveis a diferentes necessidades corporais e cognitivas.</p>
        
        <ul>
            <li><strong>Treinamentos Adaptados:</strong> Praticantes cadeirantes ou com mobilidade reduzida adaptam técnicas focando nos membros superiores e força de tronco.</li>
            <li><strong>Deficiência Visual:</strong> Katas (formas) podem ser ensinados através da biomecânica do toque e som, explorando o equilíbrio (kamae).</li>
            <li><strong>Benefícios Cognitivos:</strong> A rotina rígida, o respeito e a repetição auxiliam diretamente no controle de ansiedade, TDAH e desenvolvimento neuropsicomotor infantil.</li>
        </ul>
        <p>Se você tem dúvidas sobre como adaptar o seu treino, entre em contato com o Sensei da sua academia. O espírito Kyokushin de "nunca desistir" mora dentro de todos nós.</p>
    </section>

    <a href="../index.html" class="btn-voltar">← Voltar ao Início</a>

  </main>

  <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
      <div class="vw-plugin-top-wrapper"></div>
    </div>
  </div>
  <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
  <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
  </script>

  <!-- Script A11Y Global -->
  <script src="../js/acessibilidade.js" defer></script>
</body>
</html>
