<?php
// api/admin_refund.php - refunds purchases (admin only)
// revert zafiros: buyer gets refunded, creator deduction of creadora_recibe
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_allowed']); exit; }
$idcompra = (int) ($_POST['idcompra'] ?? 0);
if ($idcompra <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }

$stmt = $mysqli->prepare("SELECT idcompra,idusuario,idcreadora,zafiros,creadora_recibe,estado FROM compras WHERE idcompra = ? LIMIT 1");
$stmt->bind_param('i',$idcompra); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$row = $res->fetch_assoc();
if ($row['estado'] !== 'ok') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'not_refundable']); exit; }

$buyer = (int)$row['idusuario'];
$creator = (int)$row['idcreadora'];
$zafiros = (int)$row['zafiros'];
$creadora_recibe = (int)$row['creadora_recibe'];

$mysqli->begin_transaction();
try {
    // mark as refunded
    $u1 = $mysqli->prepare("UPDATE compras SET estado='reembolsado' WHERE idcompra = ?");
    $u1->bind_param('i',$idcompra); if(!$u1->execute()) throw new Exception('mark_fail');
    // return zafiros to buyer
    $u2 = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + ? WHERE idusuario = ?");
    $u2->bind_param('ii',$zafiros,$buyer); if(!$u2->execute()) throw new Exception('refund_fail');
    // deduct from creator (if possible) - avoid negative balances
    $u3 = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = GREATEST(0, saldo_zafiros - ?) WHERE idusuario = ?");
    $u3->bind_param('ii',$creadora_recibe,$creator); if(!$u3->execute()) throw new Exception('deduct_creator');
    $mysqli->commit();
    echo json_encode(['ok'=>true]);
} catch(Exception $e) {
    $mysqli->rollback();
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'tx_error','msg'=>$e->getMessage()]);
}
?>