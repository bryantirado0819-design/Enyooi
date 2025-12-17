<?php
// api/update_preferences.php (with CSRF)
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
$prefer_dark = isset($_POST['prefer_dark']) ? (int) ($_POST['prefer_dark'] ? 1 : 0) : 0;
$notify_email = isset($_POST['notify_email']) ? (int) ($_POST['notify_email'] ? 1 : 0) : 0;
$blocked = $_POST['blocked_countries'] ?? '[]';
if (!is_string($blocked) || json_decode($blocked) === null) $blocked = '[]';

$stmt = $mysqli->prepare("UPDATE usuarios SET prefer_dark = ?, notify_email = ?, blocked_countries = ? WHERE idusuario = ?");
$stmt->bind_param('iisi', $prefer_dark, $notify_email, $blocked, $uid);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

echo json_encode(['ok'=>true]);
?>