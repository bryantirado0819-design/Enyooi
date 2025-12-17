<?php
// api/get_wallet.php - devuelve saldo zafiros del usuario
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }
$uid = (int) $_SESSION['logueando'];
$stmt = $mysqli->prepare("SELECT saldo_zafiros FROM usuarios WHERE idusuario = ? LIMIT 1");
$stmt->bind_param('i',$uid); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$row = $res->fetch_assoc();
echo json_encode(['ok'=>true,'saldo'=> (int)$row['saldo_zafiros']]);
?>