<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Message.php';
$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $from = (int)$payload['from'];
    $to = (int)$payload['to'];
    $message = trim($payload['message'] ?? '');
    if(!$message) {
        http_response_code(400);
        echo json_encode(['error'=>'message empty']);
        exit;
    }
    $id = Message::create($from, $to, $message);

    // Emit to socket server so receiver sees it in real-time
    $socketUrl = rtrim($config['socket_server'], '/').'/emit';
    $data = [
        'room' => "user_{$to}",
        'event' => 'new_message',
        'payload' => [
            'id' => $id,
            'from' => $from,
            'to' => $to,
            'message' => $message,
            'created_at' => date('c')
        ]
    ];
    // POST to socket server (fire-and-forget)
    $ch = curl_init($socketUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(['ok'=>true,'id'=>$id]);
    exit;
}

if($method === 'GET') {
    $u1 = (int)($_GET['u1'] ?? 0);
    $u2 = (int)($_GET['u2'] ?? 0);
    $conv = Message::getConversation($u1, $u2);
    echo json_encode(['ok'=>true,'conversation'=>$conv]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
