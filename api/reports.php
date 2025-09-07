<?php
require_once __DIR__ . '/../config/Database.php';
header('Content-Type: application/json');

$db = Database::getConnection();

// filtros simples
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

$sql = "SELECT u.id, u.username, COUNT(t.id) as transactions, SUM(t.amount) as total_amount
        FROM users u
        LEFT JOIN transactions t ON t.user_id = u.id
        WHERE 1=1 ";
$params = [];

if($start){
  $sql .= " AND t.created_at >= ? ";
  $params[] = $start;
}
if($end){
  $sql .= " AND t.created_at <= ? ";
  $params[] = $end;
}
$sql .= " GROUP BY u.id ORDER BY total_amount DESC LIMIT 200";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo json_encode(['ok'=>true,'data'=>$rows]);
