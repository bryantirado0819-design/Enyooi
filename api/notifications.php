<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Notification.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $list = Notification::listByUser($user_id);
    echo json_encode(['ok'=>true,'notifications'=>$list]);
    exit;
}

if($method === 'POST') {
    // mark read
    $data = json_decode(file_get_contents('php://input'), true);
    if(isset($data['mark_read'])) {
        Notification::markRead((int)$data['mark_read']);
        echo json_encode(['ok'=>true]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['error'=>'bad request']);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
