<?php
// /api/save_espectador.php
session_start();
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ENYOOI/home');
    exit;
}
if (empty($_SESSION['logueando'])) {
    header('Location: /ENYOOI/home/entrar');
    exit;
}

$uid = (int) $_SESSION['logueando'];

// verificar rol en BD (opcionalmente permitir si ya es espectador)
$stmt = $mysqli->prepare("SELECT rol FROM usuarios WHERE idusuario = ?");
if (!$stmt) { header('Location: /ENYOOI/home'); exit; }
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || !$res->num_rows) { header('Location: /ENYOOI/home'); exit; }
$row = $res->fetch_assoc();
if ($row['rol'] !== 'espectador') {
    // Si por algún motivo aún no está marcado, marcar ahora
    $u = $mysqli->prepare("UPDATE usuarios SET rol='espectador' WHERE idusuario = ?");
    if ($u) { $u->bind_param('i', $uid); $u->execute(); }
}

$nickname = trim($_POST['nickname'] ?? '');
if ($nickname === '') {
    $nickname = 'user' . substr(bin2hex(random_bytes(3)), 0, 6);
}
$nickname = mb_substr($nickname, 0, 60);

$defaultAvatar = '/ENYOOI/public/img/defaults/default_avatar.png';

// comprobar perfil
$check = $mysqli->prepare("SELECT idusuario FROM perfil WHERE idusuario = ?");
$check->bind_param('i', $uid);
$check->execute();
$r = $check->get_result();

if (!$r || $r->num_rows === 0) {
    $ins = $mysqli->prepare("INSERT INTO perfil (idusuario, nickname_artistico, foto_perfil, bio) VALUES (?, ?, ?, '')");
    if ($ins) { $ins->bind_param('iss', $uid, $nickname, $defaultAvatar); $ins->execute(); }
} else {
    $upd = $mysqli->prepare("UPDATE perfil SET nickname_artistico = ? WHERE idusuario = ?");
    if ($upd) { $upd->bind_param('si', $nickname, $uid); $upd->execute(); }
}

$_SESSION['nickname'] = $nickname;
$_SESSION['foto_perfil'] = $defaultAvatar;

header('Location: /ENYOOI/home');
exit;
