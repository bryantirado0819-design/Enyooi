<?php
require_once __DIR__ . '/../config/Database.php';
class Follow {
    public static function toggle($follower, $followed) {
        $db = Database::getConnection();
        $check = $db->prepare("SELECT id FROM follows WHERE follower_id=? AND followed_id=?");
        $check->execute([$follower, $followed]);
        if($check->rowCount()>0){
            $db->prepare("DELETE FROM follows WHERE follower_id=? AND followed_id=?")
               ->execute([$follower,$followed]);
            return false;
        } else {
            $db->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)")
               ->execute([$follower,$followed]);
            return true;
        }
    }

    public static function isFollowing($follower, $followed) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT 1 FROM follows WHERE follower_id=? AND followed_id=?");
        $stmt->execute([$follower,$followed]);
        return $stmt->fetch() ? true : false;
    }
}
