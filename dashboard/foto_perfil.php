<?php
require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/auth_check.php';
require_once __DIR__ . '/../php/csrf.php';

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
    if (is_file($arquivo)) @unlink($arquivo);
}

/**
 * Processa a foto do usuário com segurança máxima:
 * 1. Tenta otimizar/redimensionar via GD caso a extensão esteja ativa.
 * 2. Se a biblioteca GD não estiver instalada/habilitada no PHP, realiza fallback
 *    seguro salvando o arquivo original com nome único e sanitizado.
 */
function processar_foto_perfil(array $arquivo, string $mime, int $usuarioId, string $raiz): string|false {
    $diretorio = $raiz . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfil';
    if (!is_dir($diretorio) && !@mkdir($diretorio, 0755, true)) {
        return false;
    }

    $tmpFile = $arquivo['tmp_name'];

    // Tentativa 1: Otimizar e converter para WebP caso GD esteja ativo
    if (extension_loaded('gd') && function_exists('imagecreatetruecolor') && function_exists('imagewebp')) {
        $imagemOriginal = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($tmpFile) : false,
            'image/png'  => function_exists('imagecreatefrompng')  ? @imagecreatefrompng($tmpFile) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpFile) : false,
            default      => false,
        };

        if ($imagemOriginal) {
            $maxLargura = 400;
            $maxAltura  = 400;
            $qualidade  = 85;

            $largOrig = imagesx($imagemOriginal);
            $altOrig  = imagesy($imagemOriginal);

            $ratio    = min($maxLargura / $largOrig, $maxAltura / $altOrig, 1.0);
            $novaLarg = max(1, (int)round($largOrig * $ratio));
            $novaAlt  = max(1, (int)round($altOrig * $ratio));

            $imagemFinal = @imagecreatetruecolor($novaLarg, $novaAlt);
            if ($imagemFinal) {
                // Fundo transparente ou branco
                if ($mime === 'image/png' || $mime === 'image/webp') {
                    imagealphablending($imagemFinal, false);
                    imagesavealpha($imagemFinal, true);
                    $transp = imagecolorallocatealpha($imagemFinal, 255, 255, 255, 127);
                    imagefilledrectangle($imagemFinal, 0, 0, $novaLarg, $novaAlt, $transp);
                } else {
                    $branco = imagecolorallocate($imagemFinal, 255, 255, 255);
                    imagefill($imagemFinal, 0, 0, $branco);
                }

                imagecopyresampled($imagemFinal, $imagemOriginal, 0, 0, 0, 0, $novaLarg, $novaAlt, $largOrig, $altOrig);

                $nomeRelativo = 'uploads/perfil/user_' . $usuarioId . '_' . bin2hex(random_bytes(8)) . '.webp';
                $destino      = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nomeRelativo);

                $sucessoWebp = @imagewebp($imagemFinal, $destino, $qualidade);

                imagedestroy($imagemOriginal);
                imagedestroy($imagemFinal);

                if ($sucessoWebp && is_file($destino)) {
                    return $nomeRelativo;
                }
            } else {
                imagedestroy($imagemOriginal);
            }
        }
    }

    // Tentativa 2: Fallback direto e seguro sem dependência da extensão GD
    $extensao = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };

    $nomeRelativo = 'uploads/perfil/user_' . $usuarioId . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
    $destino      = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nomeRelativo);

    if (@move_uploaded_file($tmpFile, $destino) || @copy($tmpFile, $destino)) {
        return $nomeRelativo;
    }

    return false;
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
if (!$arquivo || !isset($arquivo['tmp_name']) || $arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > 5 * 1024 * 1024) {
    header('Location: perfil.php?foto=erro');
    exit();
}

// Validar tipo REAL do arquivo (não confiar apenas na extensão)
$mime = '';
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($arquivo['tmp_name']);
} elseif (function_exists('mime_content_type')) {
    $mime = mime_content_type($arquivo['tmp_name']);
}

$mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/pjpeg'];
if (!in_array($mime, $mimesPermitidos, true)) {
    // Validação secundária por getimagesize caso finfo seja restrito
    $imgInfo = @getimagesize($arquivo['tmp_name']);
    if ($imgInfo && isset($imgInfo['mime']) && in_array($imgInfo['mime'], $mimesPermitidos, true)) {
        $mime = $imgInfo['mime'];
    } else {
        header('Location: perfil.php?foto=tipo_invalido');
        exit();
    }
}

// Normalizar pjpeg para jpeg
if ($mime === 'image/pjpeg') {
    $mime = 'image/jpeg';
}

$dimensoes = @getimagesize($arquivo['tmp_name']);
if (!$dimensoes || $dimensoes[0] < 1 || $dimensoes[1] < 1 || $dimensoes[0] > 4000 || $dimensoes[1] > 4000) {
    header('Location: perfil.php?foto=dimensoes_invalidas');
    exit();
}

// Processar a foto (com ou sem GD)
$novaFoto = processar_foto_perfil($arquivo, $mime, $usuarioId, $raiz);

if (!$novaFoto) {
    header('Location: perfil.php?foto=erro');
    exit();
}

// Salvar no banco de dados com prepared statement
$stmt = mysqli_prepare($conn, 'UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $novaFoto, $usuarioId);

if (mysqli_stmt_execute($stmt)) {
    remover_foto_antiga($raiz, $fotoAtual);
    log_activity($conn, 'perfil_foto_atualizada', "Usuário ID $usuarioId atualizou foto de perfil");
    header('Location: perfil.php?foto=salva');
    exit();
}

// Limpar arquivo criado se o banco falhou
$destinoFail = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $novaFoto);
if (is_file($destinoFail)) @unlink($destinoFail);

header('Location: perfil.php?foto=erro');
exit();
