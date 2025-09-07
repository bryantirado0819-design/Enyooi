<?php
// app/models/UserModel.php
require_once __DIR__ . '/../../config/config.php';

class UserModel {
  public function updateAccount($id, $data){
    $fields=[]; $params=[];
    if(isset($data['email'])) { $fields[]='email=?'; $params[]=$data['email']; }
    if(isset($data['username'])) { $fields[]='username=?'; $params[]=$data['username']; }
    if(isset($data['verify_badge'])) { $fields[]='verify_badge=?'; $params[]=$data['verify_badge']; }
    if(isset($data['password'])) { $fields[]='password_hash=?'; $params[]=password_hash($data['password'], PASSWORD_BCRYPT); }
    if(!$fields) return false;
    $params[]=$id; $sql='UPDATE users SET '.implode(',', $fields).' WHERE id=?';
    $st = db()->prepare($sql); return $st->execute($params);
  }
  public function setZafiros($id, $balance){
    $st = db()->prepare("INSERT INTO zafiros_wallet (user_id,balance) VALUES (?,?) ON DUPLICATE KEY UPDATE balance=VALUES(balance)");
    return $st->execute([$id, (int)$balance]);
  }
  public function find($id){
    $st = db()->prepare('SELECT * FROM users WHERE id=?'); $st->execute([$id]); return $st->fetch();
  }
}
