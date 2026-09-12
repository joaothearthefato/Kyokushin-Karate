<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

validar_csrf();
$email = trim($_POST['email'] ?? '');

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = mysqli_prepare($conn, 'SELECT id, nome FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $delete = mysqli_prepare($conn, 'DELETE FROM password_resets WHERE usuario_id = ? OR expires_at < NOW()');
        mysqli_stmt_bind_param($delete, 'i', $usuario['id']);
        mysqli_stmt_execute($delete);

        $insert = mysqli_prepare($conn, 'INSERT INTO password_resets (usuario_id, token_hash, expires_at) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($insert, 'iss', $usuario['id'], $tokenHash, $expiresAt);
        if (mysqli_stmt_execute($insert)) {
            $appUrl = rtrim((string) (getenv('APP_URL') ?: 'http://localhost/Kyokushin-Karate'), '/');
            $link = $appUrl . '/php/redefinir_senha.php?token=' . rawurlencode($token);
            $from = getenv('MAIL_FROM');
            if ($from) {
                $subject = 'Recuperação de senha - Oyama Hub';
                $message = "Olá, {$usuario['nome']}.\n\nUse este link para redefinir sua senha (válido por 1 hora):\n$link\n\nSe você não solicitou isso, ignore esta mensagem.";
                $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                mail($email, $subject, $message, $headers);
            }
        }
    }
}

header('Location: login.php?recovery=sent');
exit;
