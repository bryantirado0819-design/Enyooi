<?php
require_once __DIR__ . '/../config/Database.php';
header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);
$messageId = (int)($payload['message_id'] ?? 0);
if(!$messageId){ http_response_code(400); echo json_encode(['error'=>'message_id required']); exit; }

$db = Database::getConnection();
$stmt = $db->prepare("UPDATE messages SET is_read=1 WHERE id=?");
$stmt->execute([$messageId]);
echo json_encode(['ok'=>true]);
