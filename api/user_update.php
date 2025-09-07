<?php
// api/user_update.php (with CSRF check)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

// CSRF protection
$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int) $_SESSION['logueando'];

$nickname = trim($_POST['nickname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$metodo   = trim($_POST['metodo_pago'] ?? '');
$billing_name = trim($_POST['billing_name'] ?? '');
$billing_ruc  = trim($_POST['billing_ruc'] ?? '');

// validate email if provided
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid_email']); exit;
}
if ($email !== '') {
    $stm = $mysqli->prepare("SELECT idusuario FROM usuarios WHERE correo = ? AND idusuario <> ? LIMIT 1");
    $stm->bind_param('si', $email, $uid); $stm->execute(); $r = $stm->get_result();
    if ($r && $r->num_rows) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>'email_taken']); exit; }
}

// update
$stmt = $mysqli->prepare("UPDATE usuarios SET nickname = ?, correo = ?, metodo_pago = ?, billing_name = ?, billing_ruc = ? WHERE idusuario = ?");
$stmt->bind_param('sssssi', $nickname, $email, $metodo, $billing_name, $billing_ruc, $uid);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

if ($email !== '') $_SESSION['email'] = $email;
echo json_encode(['ok'=>true]);
?>