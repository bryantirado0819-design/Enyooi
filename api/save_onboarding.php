<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'method_not_allowed']);
    exit;
}
if (empty($_SESSION['logueando'])) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'unauthorized']);
    exit;
}

$uid = (int) $_SESSION['logueando'];

/* --- verificar usuario --- */
$stmt = $mysqli->prepare("SELECT rol, onboarding_creadora FROM usuarios WHERE idusuario=?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare']);
    exit;
}
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || !$res->num_rows) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'user_not_found']);
    exit;
}
$row = $res->fetch_assoc();

if ($row['rol'] !== 'creadora') {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'role_not_creator']);
    exit;
}
if ((int)$row['onboarding_creadora'] === 1) {
    header('Location: /ENYOOI/home');
    exit;
}

/* --- inputs --- */
$nickname = trim($_POST['nickname'] ?? '');
$bio      = trim($_POST['bio'] ?? '');
$metodo   = trim($_POST['pago'] ?? 'transferencia');

if ($nickname === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'nickname_required']);
    exit;
}
$nickname = mb_substr($nickname, 0, 60);

$allowedMethods = ['transferencia','stripe','paypal'];
if (!in_array($metodo, $allowedMethods, true)) {
    $metodo = 'transferencia';
}

/* --- carpeta de subidas --- */
$uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/ENYOOI/public/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* --- función segura para guardar archivos --- */
function save_upload($field, $prefix, $uploadDir) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES[$field]['tmp_name']);
    $ext   = '';
    if ($mime === 'image/jpeg') $ext = 'jpg';
    elseif ($mime === 'image/png') $ext = 'png';
    elseif ($mime === 'application/pdf') $ext = 'pdf';
    else return '';

    $name = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $uploadDir . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) return '';

    return '/ENYOOI/public/uploads/' . $name;
}

/* --- guardar archivos --- */
$foto   = save_upload('foto', 'foto_'.$uid, $uploadDir);
$banner = save_upload('banner', 'banner_'.$uid, $uploadDir);
$doc    = save_upload('documento', 'doc_'.$uid, $uploadDir);

/* --- si no sube foto, usar avatar por defecto --- */
if ($foto === '') {
    $foto = '/ENYOOI/public/img/defaults/default_avatar.png';
}

/* --- guardar/actualizar perfil --- */
$q = "INSERT INTO perfil (idusuario, nickname_artistico, foto_perfil, banner_portada, bio)
      VALUES (?,?,?,?,?)
      ON DUPLICATE KEY UPDATE
      nickname_artistico=VALUES(nickname_artistico),
      foto_perfil=COALESCE(VALUES(foto_perfil),foto_perfil),
      banner_portada=COALESCE(VALUES(banner_portada),banner_portada),
      bio=VALUES(bio)";
$upd = $mysqli->prepare($q);
if (!$upd) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare_insert']);
    exit;
}
$upd->bind_param('issss', $uid, $nickname, $foto, $banner, $bio);
if (!$upd->execute()) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_insert']);
    exit;
}

/* --- actualizar usuario --- */
$u2 = $mysqli->prepare("UPDATE usuarios 
                        SET onboarding_creadora=1, metodo_pago=?, documento_identidad=COALESCE(?, documento_identidad) 
                        WHERE idusuario=?");
if (!$u2) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare_update']);
    exit;
}
$u2->bind_param('ssi', $metodo, $doc, $uid);
$u2->execute();

/* --- actualizar sesión --- */
$_SESSION['onboarding_creadora'] = 1;
$_SESSION['nickname'] = $nickname;
$_SESSION['foto_perfil'] = $foto;

/* --- redirigir a home --- */
header('Location: /ENYOOI/home');
exit;
