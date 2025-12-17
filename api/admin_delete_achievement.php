<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_allowed']); exit; }
$id = (int) ($_POST['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'missing']); exit; }
$stmt = $mysqli->prepare("DELETE FROM achievements WHERE idachievement=?");
$stmt->bind_param('i',$id); $stmt->execute();
echo json_encode(['ok'=>true]);
