<?php
require_once __DIR__ . '/../config/Database.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$db = Database::getConnection();

if($q === '') {
    // return recent users
    $stmt = $db->prepare("SELECT id, username, display_name, avatar FROM users ORDER BY created_at DESC LIMIT 30");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo json_encode(['ok'=>true,'users'=>$rows]);
    exit;
}

$like = '%' . $q . '%';
$stmt = $db->prepare("SELECT id, username, display_name, avatar FROM users WHERE username LIKE ? OR display_name LIKE ? LIMIT 50");
$stmt->execute([$like, $like]);
$rows = $stmt->fetchAll();
echo json_encode(['ok'=>true,'users'=>$rows]);
