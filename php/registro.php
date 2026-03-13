<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registro - Kyokushin Dojo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/kyokushinicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
            background-color: #1a1a1a;
            padding: 40px;
            border-radius: 8px;
            border-top: 5px solid #e60000; /* Vermelho Kyokushin */
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }
        .register-box h2 { margin-bottom: 20px; font-weight: 900; }
        .register-box h2 span { color: #e60000; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: #ccc; }
        .input-group input {
            width: 100%; padding: 12px; border: 1px solid #333;
            background-color: #222; color: #fff; border-radius: 4px;
        }
        .btn-submit {
            width: 100%; padding: 15px; background-color: #e60000;
            color: #fff; border: none; border-radius: 4px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-submit:hover { background-color: #cc0000; }
        .login-link {
            display: block; margin-top: 15px; font-size: 0.9rem; color: #888; text-decoration: none;
        }
        .login-link:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="register-box">
        <h2>NOVO <span>ALUNO</span></h2>
        <form action="registrar_aluno.php" method="POST">
            <div class="input-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required placeholder="Seu nome">
            </div>
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>
            <div class="input-group">
                <label for="senha">Crie uma Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres" minlength="6">
            </div>
            <button type="submit" class="btn-submit">MATRICULAR-SE</button>
        </form>
        <a href="login.html" class="login-link">Já tem cadastro? Faça o login</a>
    </div>

</body>
</html>