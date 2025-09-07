<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
$stream_key = $_GET['stream_key'] ?? '';
if (!$stream_key) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'no_key']); exit; }
$stmt = $mysqli->prepare("SELECT idstream,estado FROM streams WHERE stream_key=? LIMIT 1");
$stmt->bind_param('s',$stream_key); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$row = $res->fetch_assoc(); $idstream = (int)$row['idstream'];
$s = $mysqli->prepare("SELECT idsession, started_at FROM stream_sessions WHERE idstream = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$s->bind_param('i',$idstream); $s->execute(); $rs = $s->get_result();
if ($rs && $rs->num_rows) { $r = $rs->fetch_assoc(); echo json_encode(['ok'=>true,'idsession'=> (int)$r['idsession'], 'started_at'=>$r['started_at']]); exit; }
echo json_encode(['ok'=>false,'error'=>'no_session']);
