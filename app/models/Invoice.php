<?php
require_once __DIR__ . '/../config/Database.php';
class Invoice {
    public static function createFromTransaction($transaction_id, $invoice_number=null) {
        $db = Database::getConnection();
        $txn = $db->prepare("SELECT * FROM transactions WHERE id=?");
        $txn->execute([$transaction_id]);
        $t = $txn->fetch();
        if(!$t) return false;
        if(!$invoice_number) $invoice_number = 'INV-' . time() . '-' . rand(100,999);
        $stmt = $db->prepare("INSERT INTO invoices (invoice_number, transaction_id, user_id, amount, status) VALUES (?, ?, ?, ?, 'issued')");
        $stmt->execute([$invoice_number, $transaction_id, $t['user_id'], $t['amount']]);
        return $db->lastInsertId();
    }
}
