<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_allowed']); exit; }
$res = $mysqli->query("SELECT idachievement,code,title,description,icon,zafiros_reward FROM achievements ORDER BY idachievement DESC");
$rows = []; while($r=$res->fetch_assoc()) $rows[]=$r;
echo json_encode(['ok'=>true,'rows'=>$rows]);
