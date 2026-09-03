<?php
session_start();
require '../php/config.php';
require_once '../php/auth_check.php';
require_once '../php/csrf.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit();
}

// Validar CSRF
validar_csrf();

$usuarioId = intval($_SESSION['id']);
$stmtAtual = mysqli_prepare($conn, 'SELECT foto_perfil FROM usuarios WHERE id = ?');
mysqli_stmt_bind_param($stmtAtual, 'i', $usuarioId);
mysqli_stmt_execute($stmtAtual);
$atual     = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAtual));
$fotoAtual = $atual['foto_perfil'] ?? 'default_avatar.png';
$raiz      = realpath(__DIR__ . '/..');

function foto_propria(string $foto): bool {
    return str_starts_with($foto, 'uploads/perfil/');
}

function remover_foto_antiga(string $raiz, string $foto): void {
    if (!foto_propria($foto)) return;
    $arquivo = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $foto);
    if (is_file($arquivo)) unlink($arquivo);
}

/**
 * Redimensiona e converte imagem para WebP usando GD.
 * Retorna o caminho relativo (uploads/perfil/...) ou false em caso de erro.
 */
function processar_imagem_webp(string $tmpFile, string $mime, int $usuarioId, string $raiz): string|false {
    $maxLargura  = 400;
    $maxAltura   = 400;
    $qualidade   = 85;

    // Carregar imagem
    $imagemOriginal = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($tmpFile),
        'image/png'  => imagecreatefrompng($tmpFile),
        'image/webp' => imagecreatefromwebp($tmpFile),
        default      => false,
    };

    if (!$imagemOriginal) return false;

    $largOrig  = imagesx($imagemOriginal);
    $altOrig   = imagesy($imagemOriginal);

    // Calcular proporção para não distorcer
    $ratio     = min($maxLargura / $largOrig, $maxAltura / $altOrig, 1.0);
    $novaLarg  = (int)round($largOrig * $ratio);
    $novaAlt   = (int)round($altOrig * $ratio);

    // Criar canvas com fundo branco (para imagens PNG com transparência)
    $imagemFinal = imagecreatetruecolor($novaLarg, $novaAlt);
    $branco = imagecolorallocate($imagemFinal, 255, 255, 255);
    imagefill($imagemFinal, 0, 0, $branco);

    // Redimensionar
    imagecopyresampled($imagemFinal, $imagemOriginal, 0, 0, 0, 0, $novaLarg, $novaAlt, $largOrig, $altOrig);

    // Definir destino sempre em WebP
    $diretorio = $raiz . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfil';
    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) {
        imagedestroy($imagemOriginal);
        imagedestroy($imagemFinal);
        return false;
    }

    $nomeRelativo = 'uploads/perfil/user_' . $usuarioId . '_' . bin2hex(random_bytes(8)) . '.webp';
    $destino      = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nomeRelativo);

    $sucesso = imagewebp($imagemFinal, $destino, $qualidade);

    imagedestroy($imagemOriginal);
    imagedestroy($imagemFinal);

    return $sucesso ? $nomeRelativo : false;
}

// ─── Rota: Remover foto ───────────────────────────────────────
if (isset($_POST['remover'])) {
    $padrao = 'default_avatar.png';
    $stmt   = mysqli_prepare($conn, 'UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'si', $padrao, $usuarioId);
    if (mysqli_stmt_execute($stmt)) {
        remover_foto_antiga($raiz, $fotoAtual);
        log_activity($conn, 'perfil_foto_removida', "Usuário ID $usuarioId removeu foto de perfil");
        header('Location: perfil.php?foto=removida');
        exit();
    }
}

// ─── Rota: Upload de nova foto ────────────────────────────────
$arquivo = $_FILES['foto_perfil'] ?? null;

// Validar upload (tamanho máximo 5MB)
if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > 5 * 1024 * 1024) {
    header('Location: perfil.php?foto=erro');
    exit();
}

// Validar tipo REAL do arquivo (não confiar na extensão)
$mime      = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
$mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mime, $mimesPermitidos, true) || !getimagesize($arquivo['tmp_name'])) {
    header('Location: perfil.php?foto=tipo_invalido');
    exit();
}

// Processar: redimensionar e converter para WebP
$novaFoto = processar_imagem_webp($arquivo['tmp_name'], $mime, $usuarioId, $raiz);

if (!$novaFoto) {
    header('Location: perfil.php?foto=erro');
    exit();
}

// Salvar no banco
$stmt = mysqli_prepare($conn, 'UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $novaFoto, $usuarioId);
if (mysqli_stmt_execute($stmt)) {
    remover_foto_antiga($raiz, $fotoAtual);
    log_activity($conn, 'perfil_foto_atualizada', "Usuário ID $usuarioId atualizou foto de perfil");
    header('Location: perfil.php?foto=salva');
    exit();
}

// Limpar arquivo se falhou o banco
$destinoFail = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $novaFoto);
if (is_file($destinoFail)) unlink($destinoFail);

header('Location: perfil.php?foto=erro');
exit();
