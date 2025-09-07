<?php
require_once __DIR__ . '/../config/Database.php';
class Notification {
    public static function create($user_id, $type, $message, $payload = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, payload) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $message, $payload ? json_encode($payload) : null]);
        $id = $db->lastInsertId();

        // Try to emit to socket server so user receives notification in real-time
        try {
            $cfg = require __DIR__ . '/../config/config.php';
            if(!empty($cfg['socket_server'])) {
                $socketUrl = rtrim($cfg['socket_server'], '/') . '/emit';
                $data = [
                    'room' => 'user_' . $user_id,
                    'event' => 'notification',
                    'payload' => [
                        'id' => $id,
                        'user_id' => $user_id,
                        'type' => $type,
                        'message' => $message,
                        'payload' => $payload,
                        'created_at' => date('c')
                    ]
                ];
                $ch = curl_init($socketUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_exec($ch);
                curl_close($ch);
            }
        } catch(Exception $e) {
            // silently ignore emission errors; notification still stored
        }

        return $id;
    }

    public static function listByUser($user_id, $limit = 50) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$user_id, (int)$limit]);
        return $stmt->fetchAll();
    }

    public static function markRead($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
        $stmt->execute([$id]);
    }
}
