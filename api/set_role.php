<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'method_not_allowed']);
    exit;
}
if (empty($_SESSION['logueando'])) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'unauthorized']);
    exit;
}

$uid = (int) $_SESSION['logueando'];
$role = trim($_POST['role'] ?? '');

if (!in_array($role, ['creadora','espectador'], true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid_role']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE usuarios SET rol=? WHERE idusuario=?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare']);
    exit;
}
$stmt->bind_param('si', $role, $uid);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_execute']);
    exit;
}

// actualizar sesión para evitar que vuelva a pedir rol al recargar
$_SESSION['rol'] = $role;

// indicar al front donde redirigir
$redirect = ($role === 'creadora') ? '/ENYOOI/app/view/pages/creadora_onboarding.php' : '/ENYOOI/home';

echo json_encode(['ok'=>true,'role'=>$role,'redirect'=>$redirect]);
exit;
