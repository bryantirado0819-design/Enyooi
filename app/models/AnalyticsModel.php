<?php
// app/models/AnalyticsModel.php
require_once __DIR__ . '/../../config/config.php';

class AnalyticsModel {
  public function revenueSplitCreator(){
    $st = db()->prepare("SELECT value FROM system_settings WHERE `key`='revenue_split_creator'");
    $st->execute(); $v = $st->fetchColumn();
    return $v ? (float)$v : 60.0;
  }
  public function setRevenueSplitCreator($pct){
    $st = db()->prepare("INSERT INTO system_settings(`key`,`value`) VALUES('revenue_split_creator',?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    return $st->execute([ (string)$pct ]);
  }
  public function dailyRegistrations($days=7){
    $st = db()->prepare("SELECT DATE(registered_at) d, COUNT(*) c FROM registrations WHERE registered_at >= CURDATE() - INTERVAL ? DAY GROUP BY DATE(registered_at) ORDER BY d ASC");
    $st->execute([$days]); return $st->fetchAll();
  }
  public function todaysLoginsByCountry(){
    $st = db()->query("SELECT COALESCE(country,'Desconocido') country, COUNT(*) c FROM user_logins WHERE DATE(logged_at)=CURDATE() GROUP BY country ORDER BY c DESC");
    return $st->fetchAll();
  }
  public function globalZafirosTotal(){
    $st = db()->query("SELECT SUM(balance) FROM zafiros_wallet"); return (int)($st->fetchColumn() ?: 0);
  }
  public function creatorBalanceZafiros($user_id){
    $st = db()->prepare("SELECT balance FROM zafiros_wallet WHERE user_id=?"); $st->execute([$user_id]);
    return (int)($st->fetchColumn() ?: 0);
  }
  public function creatorStreams($creator_id){
    $st = db()->prepare("SELECT * FROM stream_sessions WHERE creator_id=? ORDER BY id DESC"); $st->execute([$creator_id]); return $st->fetchAll();
  }
  public function viewersByGeo($creator_id){
    $st = db()->prepare("SELECT COALESCE(country,'Desconocido') country, COUNT(*) viewers FROM stream_views sv JOIN stream_sessions ss ON ss.id=sv.stream_id WHERE ss.creator_id=? GROUP BY country ORDER BY viewers DESC");
    $st->execute([$creator_id]); return $st->fetchAll();
  }
  public function viewersByCity($creator_id){
    $st = db()->prepare("SELECT COALESCE(city,'Desconocida') city, COUNT(*) viewers FROM stream_views sv JOIN stream_sessions ss ON ss.id=sv.stream_id WHERE ss.creator_id=? GROUP BY city ORDER BY viewers DESC LIMIT 10");
    $st->execute([$creator_id]); return $st->fetchAll();
  }
}
