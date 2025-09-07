<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../controllers/InvoiceController.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $transaction_id = (int)($payload['transaction_id'] ?? 0);
    if(!$transaction_id) { http_response_code(400); echo json_encode(['error'=>'transaction_id required']); exit; }
    $invoiceId = Invoice::createFromTransaction($transaction_id);
    if(!$invoiceId) { http_response_code(500); echo json_encode(['error'=>'could not create invoice']); exit; }

    // Create a notification for the user (transaction owner)
    // fetch transaction to get user
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT user_id FROM transactions WHERE id = ?");
    $stmt->execute([$transaction_id]);
    $txn = $stmt->fetch();
    if($txn && !empty($txn['user_id'])) {
        Notification::create($txn['user_id'], 'invoice_created', 'Se ha generado una factura para tu transacción', ['invoice_id'=>$invoiceId, 'transaction_id'=>$transaction_id]);
    }

    // If caller expects PDF directly, check 'download' flag or Accept header
    $download = isset($payload['download']) ? (bool)$payload['download'] : false;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if($download || strpos($accept, 'application/pdf') !== false) {
        // Generate PDF and output directly (InvoiceController will send PDF headers)
        InvoiceController::generatePdf($invoiceId);
        exit;
    }

    echo json_encode(['ok'=>true,'invoice_id'=>$invoiceId]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
