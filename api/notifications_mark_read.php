<?php
require_once __DIR__ . '/../config/Database.php';
header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);
$notifId = (int)($payload['notification_id'] ?? 0);
if(!$notifId){ http_response_code(400); echo json_encode(['error'=>'notification_id required']); exit; }

$db = Database::getConnection();
$stmt = $db->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
$stmt->execute([$notifId]);
echo json_encode(['ok'=>true]);
