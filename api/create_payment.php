<?php
// api/create_payment.php
// Crea una transacción pendiente y devuelve la URL para redirigir al checkout de Datafast (Dataweb)
// Ajusta CLIENT_ID, CLIENT_SECRET y URL según tu integración con Datafast (sandbox/production).

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if (empty($_SESSION['logueando'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

$uid = (int) $_SESSION['logueando'];
$zafiros = (int) ($_POST['zafiros'] ?? 0);
$monto = (float) ($_POST['monto'] ?? 0.0);

if ($zafiros <= 0 || $monto <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid_params']); exit; }

$referencia = 'ZF-' . time() . '-' . $uid . '-' . bin2hex(random_bytes(4));

$stmt = $mysqli->prepare("INSERT INTO transacciones (idusuario, referencia, monto, zafiros, estado, metodo) VALUES (?,?,?,?, 'pendiente','datafast')");
$stmt->bind_param('isdi', $uid, $referencia, $monto, $zafiros);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

$idtrans = $stmt->insert_id;

// --------- Preparar petición a Datafast (DATAWEB) ---------
// Nota: cada integración de Datafast tiene campos y firma específicos.
// Aquí te doy un template; reemplaza con los campos exactos de Datafast que uses.

$DATAFAST = [
  'merchant_id' => getenv('DF_MERCHANT_ID') ?: 'TU_MERCHANT_ID',
  'account' => getenv('DF_ACCOUNT') ?: 'TU_ACCOUNT',
  'amount' => number_format($monto,2,'.',''),
  'currency' => 'USD',
  'reference' => $referencia,
  'customer_email' => $_SESSION['correo'] ?? '',
  // 'return_url' => 'https://tu-dominio.com/api/callback_payment.php'  // some providers use return instead of server callbacks
];

// For demo / sandbox we simulate a redirect link. In a real integration you'd create a signed request or token.
$checkoutUrl = '/api/simulate_datafast_checkout.php?ref=' . urlencode($referencia);

echo json_encode(['ok'=>true,'redirect'=>$checkoutUrl,'referencia'=>$referencia,'idtransaccion'=>$idtrans]);
exit;
?>