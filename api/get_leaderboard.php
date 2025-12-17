<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
$res = $mysqli->query("SELECT idusuario, usuario, nickname, saldo_zafiros FROM usuarios ORDER BY saldo_zafiros DESC LIMIT 100");
$rows = []; while($r=$res->fetch_assoc()) $rows[]=$r;
echo json_encode(['ok'=>true,'rows'=>$rows]);
