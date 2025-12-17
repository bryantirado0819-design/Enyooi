<?php
// api/buy_content.php (signed URL version)
// Compra contenido y devuelve URL firmada temporal para descargar/visualizar.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../app/includes/csrf.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }
$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }
$uid = (int) $_SESSION['logueando'];
$idcontenido = (int) ($_POST['idcontenido'] ?? 0);
if ($idcontenido <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }

// fetch content
$stmt = $mysqli->prepare("SELECT idcontenido,idcreadora,precio_zafiros,privado,ruta_archivo FROM contenido WHERE idcontenido = ? LIMIT 1");
$stmt->bind_param('i',$idcontenido); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$content = $res->fetch_assoc();
$price = (int)$content['precio_zafiros'];
$creadora = (int)$content['idcreadora'];
$ruta = $content['ruta_archivo'];

// check balance
$q = $mysqli->prepare("SELECT saldo_zafiros FROM usuarios WHERE idusuario = ? LIMIT 1");
$q->bind_param('i',$uid); $q->execute(); $rq = $q->get_result();
if (!$rq || !$rq->num_rows) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'user_not_found']); exit; }
$urow = $rq->fetch_assoc();
$saldo = (int)$urow['saldo_zafiros'];
if ($saldo < $price) { http_response_code(402); echo json_encode(['ok'=>false,'error'=>'insufficient']); exit; }

$commission_percent = (int) (getenv('ENYOOI_COMMISSION') ?: 20);
$comision = (int) floor($price * $commission_percent / 100);
$creadora_recibe = $price - $comision;

$mysqli->begin_transaction();
try {
    // deduct from buyer
    $u1 = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - ? WHERE idusuario = ? AND saldo_zafiros >= ?");
    $u1->bind_param('iii', $price, $uid, $price);
    if (!$u1->execute() || $u1->affected_rows === 0) throw new Exception('deduct_fail');
    // credit creator
    $u2 = $mysqli->prepare("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + ? WHERE idusuario = ?");
    $u2->bind_param('ii', $creadora_recibe, $creadora);
    if (!$u2->execute()) throw new Exception('credit_fail');
    // insert purchase record
    $ins = $mysqli->prepare("INSERT INTO compras (idusuario,idcontenido,idcreadora,zafiros,comision,creadora_recibe) VALUES (?,?,?,?,?,?)");
    $ins->bind_param('iiiiii', $uid, $idcontenido, $creadora, $price, $comision, $creadora_recibe);
    if (!$ins->execute()) throw new Exception('insert_fail');
    $idcompra = $ins->insert_id;
    $mysqli->commit();
    // generate signed URL (expires in 10 minutes)
    $secret = getenv('ENYOOI_SIGN_SECRET') ?: 'enyooi_dev_secret';
    $expires = time() + 600;
    $payload = $idcompra . '|' . $idcontenido . '|' . $expires;
    $sig = hash_hmac('sha256', $payload, $secret);
    $signed = '/api/serve_content.php?c=' . $idcontenido . '&p=' . $idcompra . '&e=' . $expires . '&s=' . $sig;
    echo json_encode(['ok'=>true,'message'=>'compra_ok','url'=>$signed]);
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'tx_error','msg'=>$e->getMessage()]);
}
?>