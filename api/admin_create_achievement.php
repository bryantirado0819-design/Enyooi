<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando']) || empty($_SESSION['is_admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'not_allowed']); exit; }
$code = trim($_POST['code'] ?? ''); $title = trim($_POST['title'] ?? ''); $desc = trim($_POST['description'] ?? ''); $icon = trim($_POST['icon'] ?? ''); $z = (int) ($_POST['zafiros_reward'] ?? 0);
if (!$code || !$title) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'missing']); exit; }
$stmt = $mysqli->prepare("INSERT INTO achievements (code,title,description,icon,zafiros_reward) VALUES (?,?,?,?,?)");
$stmt->bind_param('ssssi',$code,$title,$desc,$icon,$z);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }
echo json_encode(['ok'=>true,'id'=>$stmt->insert_id]);
