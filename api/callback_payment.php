<?php
// api/callback_payment.php
// Endpoint que Datafast (Dataweb) llamará con el resultado del pago.
// Debes ajustar la lectura de parámetros conforme al formato de Datafast (POST JSON/FORM y firmas).
require_once __DIR__ . '/db_connect.php';

// ejemplo: Datafast puede enviar 'merchantTransactionId' o 'reference' y un 'result' object.
// Aquí verificamos algunos nombres comunes.
$ref = $_POST['merchantTransactionId'] ?? $_POST['reference'] ?? '';
$result_code = $_POST['result']['code'] ?? $_POST['resultCode'] ?? '';

// Si proviene en JSON body
if (empty($ref)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if ($json) {
        $ref = $json['merchantTransactionId'] ?? $json['reference'] ?? '';
        $result_code = $json['result']['code'] ?? $json['resultCode'] ?? '';
    }
}

if (!$ref) { http_response_code(400); echo 'no_ref'; exit; }

// Buscar transacción pendiente
$stmt = $mysqli->prepare("SELECT idtransaccion,idusuario,zafiros FROM transacciones WHERE referencia = ? AND estado='pendiente' LIMIT 1");
$stmt->bind_param('s', $ref); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo 'not_found'; exit; }
$row = $res->fetch_assoc();

// Aquí decides qué códigos significan aprobación según Datafast.
// Este ejemplo trata como aprobado cuando result_code contiene '000.000' o '000.100.1' (ajusta según proveedor).
$approved = false;
if (is_string($result_code) && (strpos($result_code, '000.000') === 0 || strpos($result_code, '000.100.1') === 0)) $approved = true;

// Fallback: si no hay código, intenta ver un campo 'status' === 'APPROVED'
if (!$approved && isset($_POST['status']) && strtoupper($_POST['status']) === 'APPROVED') $approved = true;

if ($approved) {
    $mysqli->query("UPDATE transacciones SET estado='aprobado' WHERE idtransaccion=".intval($row['idtransaccion']));
    $mysqli->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + " . intval($row['zafiros']) . " WHERE idusuario=".intval($row['idusuario']));
    http_response_code(200);
    echo 'OK';
    exit;
} else {
    $mysqli->query("UPDATE transacciones SET estado='rechazado' WHERE idtransaccion=".intval($row['idtransaccion']));
    http_response_code(200);
    echo 'RECHAZADO';
    exit;
}
?>