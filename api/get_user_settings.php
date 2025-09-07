<?php
// api/get_user_settings.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }
$uid = (int) $_SESSION['logueando'];

$stmt = $mysqli->prepare("SELECT idusuario, usuario, correo AS email, nickname, foto_perfil, metodo_pago, prefer_dark, twofa_enabled, notify_email, COALESCE(blocked_countries,'[]') AS blocked_countries, billing_name, billing_ruc FROM usuarios WHERE idusuario = ? LIMIT 1");
$stmt->bind_param('i',$uid);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$user = $res->fetch_assoc();
echo json_encode(['ok'=>true,'user'=>$user]);
?>