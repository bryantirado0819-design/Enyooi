<?php
// api/buy_menu.php - comprar un menu personalizado
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../app/includes/csrf.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }

$uid = (int) $_SESSION['logueando'];
$idmenu = (int) ($_POST['idmenu'] ?? 0);
if ($idmenu <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }

$stmt = $mysqli->prepare("SELECT idmenu,idcreadora,precio_zafiros FROM menus WHERE idmenu = ? LIMIT 1");
$stmt->bind_param('i',$idmenu); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$menu = $res->fetch_assoc();
$price = (int)$menu['precio_zafiros'];
$creadora = (int)$menu['idcreadora'];

$q = $mysqli->prepare("SELECT saldo_zafiros FROM usuarios WHERE idusuario = ? LIMIT 1");
$q->bind_param('i',$uid); $q->execute(); $rq = $q->get_result();
$urow = $rq->fetch_assoc();
if ((int)$urow['saldo_zafiros'] < $price) { http_response_code(402); echo json_encode(['ok'=>false,'error'=>'insufficient']); exit; }

$commission_percent = (int) (getenv('ENYOOI_COMMISSION') ?: 20);
$comision = (int) floor($price * $commission_percent / 100);
$creadora_recibe = $price - $comision;

$mysqli->begin_transaction();
try {
    $d = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - ? WHERE idusuario = ? AND saldo_zafiros >= ?");
    $d->bind_param('iii', $price, $uid, $price);
    if (!$d->execute() || $d->affected_rows === 0) throw new Exception('deduct_fail');
    $c = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + ? WHERE idusuario = ?");
    $c->bind_param('ii', $creadora_recibe, $creadora);
    if (!$c->execute()) throw new Exception('credit_fail');
    // record in compras table with idmenu set
    $ins = $mysqli->prepare("INSERT INTO compras (idusuario,idmenu,idcreadora,zafiros,comision,creadora_recibe) VALUES (?,?,?,?,?,?)");
    $ins->bind_param('iiiiii', $uid, $idmenu, $creadora, $price, $comision, $creadora_recibe);
    if (!$ins->execute()) throw new Exception('insert_fail');
    $mysqli->commit();
    echo json_encode(['ok'=>true]);
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'tx_error','msg'=>$e->getMessage()]);
}
?>