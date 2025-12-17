<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }
$uid = (int) $_SESSION['logueando'];
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + 60*60*2);
$stmt = $mysqli->prepare("INSERT INTO ws_tokens (token,idusuario,expires_at) VALUES (?,?,?)");
$stmt->bind_param('sis',$token,$uid,$expires);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }
echo json_encode(['ok'=>true,'token'=>$token,'expires'=>$expires,'ws_url'=> (getenv('WS_URL')?:'ws://localhost:8082') ]);
