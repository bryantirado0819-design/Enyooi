<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
$uid = $_SESSION['logueando'] ?? null;
$idsession = (int) ($_POST['idsession'] ?? 0);
$seconds = (int) ($_POST['seconds'] ?? 0);
if ($idsession <= 0 || $seconds <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }
$mysqli->query("UPDATE stream_sessions SET total_watch_seconds = total_watch_seconds + " . intval($seconds) . " WHERE idsession = " . intval($idsession));
if ($uid) {
  $q = $mysqli->prepare("SELECT idviewer FROM stream_viewers WHERE idsession=? AND idusuario=? AND left_at IS NULL LIMIT 1");
  $q->bind_param('ii',$idsession,$uid); $q->execute(); $res = $q->get_result();
  if ($res && $res->num_rows) {
    $idv = $res->fetch_assoc()['idviewer'];
    $mysqli->query("UPDATE stream_viewers SET watch_seconds = watch_seconds + " . intval($seconds) . " WHERE idviewer = " . intval($idv));
  } else {
    $ins = $mysqli->prepare("INSERT INTO stream_viewers (idsession,idusuario,joined_at,watch_seconds) VALUES (?,?,NOW(),?)");
    $ins->bind_param('iii',$idsession,$uid,$seconds); $ins->execute();
  }
} else {
  $anon = $_POST['anon_hash'] ?? bin2hex(random_bytes(8));
  $q = $mysqli->prepare("SELECT idviewer FROM stream_viewers WHERE idsession=? AND anon_hash=? AND left_at IS NULL LIMIT 1");
  $q->bind_param('is',$idsession,$anon); $q->execute(); $res = $q->get_result();
  if ($res && $res->num_rows) {
    $idv = $res->fetch_assoc()['idviewer'];
    $mysqli->query("UPDATE stream_viewers SET watch_seconds = watch_seconds + " . intval($seconds) . " WHERE idviewer = " . intval($idv));
  } else {
    $ins = $mysqli->prepare("INSERT INTO stream_viewers (idsession,anon_hash,joined_at,watch_seconds) VALUES (?,?,NOW(),?)");
    $ins->bind_param('isi',$idsession,$anon,$seconds); $ins->execute();
  }
  echo json_encode(['ok'=>true,'anon_hash'=>$anon]); exit;
}
echo json_encode(['ok'=>true]);
