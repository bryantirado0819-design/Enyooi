<?php
// api/tip.php - enviar propina (zafiros) a una creadora
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../app/includes/csrf.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }

$uid = (int) $_SESSION['logueando'];
$idcreadora = (int) ($_POST['idcreadora'] ?? 0);
$zafiros = (int) ($_POST['zafiros'] ?? 0);
if ($idcreadora <= 0 || $zafiros <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }

// balance check
$q = $mysqli->prepare("SELECT saldo_zafiros FROM usuarios WHERE idusuario = ? LIMIT 1");
$q->bind_param('i',$uid); $q->execute(); $rq = $q->get_result();
if (!$rq || !$rq->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'user_not_found']); exit; }
$urow = $rq->fetch_assoc();
if ((int)$urow['saldo_zafiros'] < $zafiros) { http_response_code(402); echo json_encode(['ok'=>false,'error'=>'insufficient']); exit; }

$commission_percent = (int) (getenv('ENYOOI_COMMISSION') ?: 20);
$comision = (int) floor($zafiros * $commission_percent / 100);
$creadora_recibe = $zafiros - $comision;

$mysqli->begin_transaction();
try {
    $d = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - ? WHERE idusuario = ? AND saldo_zafiros >= ?");
    $d->bind_param('iii', $zafiros, $uid, $zafiros);
    if (!$d->execute() || $d->affected_rows === 0) throw new Exception('deduct_fail');
    $c = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + ? WHERE idusuario = ?");
    $c->bind_param('ii', $creadora_recibe, $idcreadora);
    if (!$c->execute()) throw new Exception('credit_fail');
    $ins = $mysqli->prepare("INSERT INTO propinas (idusuario,idcreadora,zafiros,comision,creadora_recibe) VALUES (?,?,?,?,?)");
    $ins->bind_param('iiiii', $uid, $idcreadora, $zafiros, $comision, $creadora_recibe);
    if (!$ins->execute()) throw new Exception('insert_fail');
    $mysqli->commit();
    echo json_encode(['ok'=>true]);
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'tx_error','msg'=>$e->getMessage()]);
}
?>