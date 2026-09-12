<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

$token = trim($_POST['token'] ?? $_GET['token'] ?? '');
$erro = null;
$sucesso = false;

function buscar_reset(mysqli $conn, string $token): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $hash = hash('sha256', $token);
    $stmt = mysqli_prepare($conn, 'SELECT pr.id, pr.usuario_id FROM password_resets pr JOIN usuarios u ON u.id = pr.usuario_id WHERE pr.token_hash = ? AND pr.usado_em IS NULL AND pr.expires_at > NOW() AND u.ativo = 1 LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $hash);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';
    $reset = buscar_reset($conn, $token);

    if (!$reset) {
        $erro = 'Este link é inválido ou expirou. Solicite uma nova recuperação.';
    } elseif (strlen($senha) < 8 || strlen($senha) > 255) {
        $erro = 'A senha deve ter entre 8 e 255 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não conferem.';
    } else {
        mysqli_begin_transaction($conn);
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $updateUser = mysqli_prepare($conn, 'UPDATE usuarios SET senha_hash = ? WHERE id = ? AND ativo = 1');
        mysqli_stmt_bind_param($updateUser, 'si', $hashSenha, $reset['usuario_id']);
        $markUsed = mysqli_prepare($conn, 'UPDATE password_resets SET usado_em = NOW() WHERE id = ? AND usado_em IS NULL');
        mysqli_stmt_bind_param($markUsed, 'i', $reset['id']);

        if (mysqli_stmt_execute($updateUser) && mysqli_stmt_execute($markUsed) && mysqli_affected_rows($markUsed) === 1) {
            mysqli_commit($conn);
            $sucesso = true;
        } else {
            mysqli_rollback($conn);
            $erro = 'Não foi possível redefinir a senha. Tente novamente.';
        }
    }
}

$tokenValido = $sucesso || buscar_reset($conn, $token);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinir senha | Oyama Hub</title>
  <link rel="stylesheet" href="../css/tokens.css">
  <link rel="stylesheet" href="../css/registerlogin.css">
</head>
<body>
<main class="auth-page">
  <section class="auth-form-col" aria-labelledby="reset-title">
    <div class="auth-box">
      <div class="auth-box-header">
        <span class="auth-box-eyebrow">Oyama Hub</span>
        <h1 class="auth-box-title" id="reset-title">Redefinir<br><span>sua senha</span></h1>
      </div>

      <?php if ($sucesso): ?>
        <div class="auth-alert auth-alert-success" role="status">Senha alterada. Você já pode entrar com a nova senha.</div>
        <a class="auth-submit" href="login.php">Voltar para o login</a>
      <?php elseif ($erro): ?>
        <div class="auth-alert auth-alert-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
        <a class="auth-link" href="login.php">Solicitar outro link</a>
      <?php elseif ($tokenValido): ?>
        <form method="POST" class="auth-form">
          <?= csrf_input() ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
          <div class="form-field">
            <label for="senha" class="form-label">Nova senha</label>
            <input class="form-input" id="senha" name="senha" type="password" minlength="8" maxlength="255" autocomplete="new-password" required>
          </div>
          <div class="form-field">
            <label for="confirmacao" class="form-label">Confirme a nova senha</label>
            <input class="form-input" id="confirmacao" name="confirmacao" type="password" minlength="8" maxlength="255" autocomplete="new-password" required>
          </div>
          <button class="auth-submit" type="submit">Salvar nova senha</button>
        </form>
      <?php else: ?>
        <div class="auth-alert auth-alert-error" role="alert">Este link é inválido ou expirou.</div>
        <a class="auth-link" href="login.php">Solicitar outro link</a>
      <?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>
