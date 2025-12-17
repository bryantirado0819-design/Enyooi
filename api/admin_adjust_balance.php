<?php
// api/admin_adjust_balance.php - ajusta saldo zafiros (solo admin)
// WARNING: adapt admin check to your system (this uses $_SESSION['is_admin']=1 as example)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_allowed']); exit; }
$uid = (int) ($_POST['idusuario'] ?? 0);
$amount = (int) ($_POST['amount'] ?? 0);
if ($uid <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }

$stmt = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = ? WHERE idusuario = ?");
$stmt->bind_param('ii', $amount, $uid);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }
echo json_encode(['ok'=>true]);
?>