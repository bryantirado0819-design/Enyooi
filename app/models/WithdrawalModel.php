<?php
// app/models/WithdrawalModel.php
require_once __DIR__ . '/../../config/config.php';

class WithdrawalModel {
  public function request($user_id, $amount_usd, $bank){
    if($amount_usd < 10) return [false, 'Mínimo $10'];
    // Descuento del saldo NETO del creador si lo manejas en USD; aquí asumimos control por aprobaciones
    $st = db()->prepare("INSERT INTO withdrawals (user_id,amount_usd,bank_json) VALUES (?,?,?)");
    $ok = $st->execute([$user_id, $amount_usd, json_encode($bank)]);
    return [$ok, $ok ? db()->lastInsertId() : null];
  }
  public function listUser($uid){
    $st = db()->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY id DESC");
    $st->execute([$uid]); return $st->fetchAll();
  }
  public function listAdmin($status=null){
    if($status){
      $st = db()->prepare("SELECT w.*, u.username FROM withdrawals w JOIN users u ON u.id=w.user_id WHERE w.status=? ORDER BY w.id DESC");
      $st->execute([$status]); return $st->fetchAll();
    } else {
      $st = db()->query("SELECT w.*, u.username FROM withdrawals w JOIN users u ON u.id=w.user_id ORDER BY w.id DESC");
      return $st->fetchAll();
    }
  }
  public function approve($id, $txn, $receiptPath){
    $st = db()->prepare("UPDATE withdrawals SET status='approved', txn_number=?, receipt_path=? WHERE id=?");
    return $st->execute([$txn, $receiptPath, $id]);
  }
  public function reject($id, $reason=''){
    $st = db()->prepare("UPDATE withdrawals SET status='rejected' WHERE id=?");
    return $st->execute([$id]);
  }
  public function find($id){
    $st = db()->prepare("SELECT * FROM withdrawals WHERE id=?"); $st->execute([$id]); return $st->fetch();
  }
}
