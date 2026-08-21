<?php
session_start();
require '../php/config.php';

if (!isset($_SESSION['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit();
}

$usuarioId = intval($_SESSION['id']);
$stmtAtual = mysqli_prepare($conn, 'SELECT foto_perfil FROM usuarios WHERE id = ?');
mysqli_stmt_bind_param($stmtAtual, 'i', $usuarioId);
mysqli_stmt_execute($stmtAtual);
$atual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAtual));
$fotoAtual = $atual['foto_perfil'] ?? 'default_avatar.png';
$raiz = realpath(__DIR__ . '/..');

function foto_propria($foto) {
    return is_string($foto) && str_starts_with($foto, 'uploads/perfil/');
}

function remover_foto_antiga($raiz, $foto) {
    if (!foto_propria($foto)) return;
    $arquivo = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $foto);
    if (is_file($arquivo)) unlink($arquivo);
}

if (isset($_POST['remover'])) {
    $padrao = 'default_avatar.png';
    $stmt = mysqli_prepare($conn, 'UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'si', $padrao, $usuarioId);
    if (mysqli_stmt_execute($stmt)) {
        remover_foto_antiga($raiz, $fotoAtual);
        header('Location: perfil.php?foto=removida');
        exit();
    }
}

$arquivo = $_FILES['foto_perfil'] ?? null;
if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > 5 * 1024 * 1024) {
    header('Location: perfil.php?foto=erro');
    exit();
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
$extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($extensoes[$mime]) || !getimagesize($arquivo['tmp_name'])) {
    header('Location: perfil.php?foto=tipo_invalido');
    exit();
}

$diretorio = $raiz . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfil';
if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) {
    header('Location: perfil.php?foto=erro');
    exit();
}

$novaFoto = 'uploads/perfil/user_' . $usuarioId . '_' . bin2hex(random_bytes(12)) . '.' . $extensoes[$mime];
$destino = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $novaFoto);
if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
    header('Location: perfil.php?foto=erro');
    exit();
}

$stmt = mysqli_prepare($conn, 'UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'si', $novaFoto, $usuarioId);
if (mysqli_stmt_execute($stmt)) {
    remover_foto_antiga($raiz, $fotoAtual);
    header('Location: perfil.php?foto=salva');
    exit();
}

unlink($destino);
header('Location: perfil.php?foto=erro');
exit();
