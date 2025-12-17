<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Follow.php';
require_once __DIR__ . '/../models/Notification.php';
$config = require __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $follower = (int)$data['follower'];
    $followed = (int)$data['followed'];

    $isNowFollowing = Follow::toggle($follower,$followed);
    // Create notification to followed user
    Notification::create($followed, 'new_follower', ($isNowFollowing ? 'Tienes un nuevo seguidor' : 'Alguien dejó de seguirte'), ['follower'=>$follower]);

    echo json_encode(['ok'=>true,'following'=>$isNowFollowing]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
