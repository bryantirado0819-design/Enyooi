<?php
// api/change_password.php (with CSRF)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int) $_SESSION['logueando'];
$current = $_POST['current'] ?? '';
$new     = $_POST['new'] ?? '';
if (strlen($new) < 6) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'minlen']); exit; }

$stmt = $mysqli->prepare("SELECT contrasena FROM usuarios WHERE idusuario = ? LIMIT 1");
$stmt->bind_param('i',$uid); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$row = $res->fetch_assoc();
$stored = $row['contrasena'] ?? '';

$ok = false;
if ($stored !== '' && password_verify($current, $stored)) $ok = true;
elseif ($current === $stored) $ok = true;

if (!$ok) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'wrong_current']); exit; }

$hash = password_hash($new, PASSWORD_BCRYPT);
$u = $mysqli->prepare("UPDATE usuarios SET contrasena = ? WHERE idusuario = ?");
$u->bind_param('si', $hash, $uid);
if (!$u->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

echo json_encode(['ok'=>true]);
?>