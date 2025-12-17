<?php
// api/db_connect.php
// Ajusta según tu config (o require '../config.php' si ya definiste los constants allí)
ini_set('display_errors', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'Administrador';
$db   = getenv('DB_NAME') ?: 'jade';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    // avoid leaking DB error to client in production
    die(json_encode(['ok'=>false,'error'=>'db_connect']));
}
$mysqli->set_charset('utf8mb4');
?>