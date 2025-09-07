<?php
// api/simulate_datafast_callback.php - Simula el callback del proveedor
require_once __DIR__ . '/db_connect.php';

$ref = $_POST['reference'] ?? '';
$result = $_POST['result'] ?? 'rejected';
$method = $_POST['method'] ?? 'card';

if (!$ref) { echo 'no ref'; exit; }

// find pending transaction
$stmt = $mysqli->prepare("SELECT idtransaccion,idusuario,zafiros FROM transacciones WHERE referencia = ? AND estado='pendiente' LIMIT 1");
$stmt->bind_param('s', $ref); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { echo 'transaccion no encontrada'; exit; }
$row = $res->fetch_assoc();

if ($result === 'approved') {
  $mysqli->query("UPDATE transacciones SET estado='aprobado', metodo='datafast' WHERE idtransaccion=".intval($row['idtransaccion']));
  $mysqli->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + " . intval($row['zafiros']) . " WHERE idusuario=".intval($row['idusuario']));
  echo 'Pago aprobado y saldo acreditado. Regresa al sitio.';
} else {
  $mysqli->query("UPDATE transacciones SET estado='rechazado' WHERE idtransaccion=".intval($row['idtransaccion']));
  echo 'Pago rechazado'; 
}
?>