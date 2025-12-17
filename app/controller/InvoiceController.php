<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Invoice.php';

// Example of generating PDF using Dompdf (requires composer install dompdf/dompdf)
class InvoiceController {
    public static function generatePdf($invoiceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT i.*, t.* FROM invoices i LEFT JOIN transactions t ON t.id = i.transaction_id WHERE i.id = ?");
        $stmt->execute([$invoiceId]);
        $inv = $stmt->fetch();
        if(!$inv) return false;

        // HTML invoice simple template (styled)
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            body{font-family: Arial, sans-serif; color:#222}
            .header{background:#ff4fa3;color:white;padding:10px;text-align:center}
            .content{padding:20px}
            .row{display:flex;justify-content:space-between;margin-bottom:8px}
            .footer{font-size:12px;color:#666;margin-top:20px}
            </style></head><body>';
        $html .= '<div class="header"><h2>Factura ' . htmlspecialchars($inv['invoice_number']) . '</h2></div>';
        $html .= '<div class="content">';
        $html .= '<div class="row"><div>Usuario ID:</div><div>' . htmlspecialchars($inv['user_id']) . '</div></div>';
        $html .= '<div class="row"><div>Transacción ID:</div><div>' . htmlspecialchars($inv['transaction_id']) . '</div></div>';
        $html .= '<div class="row"><div>Monto:</div><div>$' . number_format($inv['amount'],2) . '</div></div>';
        $html .= '<div class="row"><div>Fecha:</div><div>' . $inv['created_at'] . '</div></div>';
        $html .= '<div class="footer">Enyooi - Facturación Electrónica</div>';
        $html .= '</div></body></html>';

        // render PDF (dompdf)
        require __DIR__ . '/../vendor/autoload.php';
        if(!class_exists('Dompdf\Dompdf')) {
            // Dompdf not installed
            header('Content-Type: application/json');
            echo json_encode(['error'=>'dompdf not installed. run composer install.']);
            return false;
        }
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        // send to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $inv['invoice_number'] . '.pdf"');
        echo $dompdf->output();
        return true;
    }

    // Example: create XML and sign for SRI (illustrative - adapt with your certificate)
    public static function createSignedXmlForSRI($invoiceId, $privateKeyPem, $publicCertPem) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT i.*, t.* FROM invoices i LEFT JOIN transactions t ON t.id = i.transaction_id WHERE i.id = ?");
        $stmt->execute([$invoiceId]);
        $inv = $stmt->fetch();
        if(!$inv) return false;

        $xml = new DOMDocument('1.0','UTF-8');
        $root = $xml->createElement('factura');
        $root->appendChild($xml->createElement('numero', $inv['invoice_number']));
        $root->appendChild($xml->createElement('monto', $inv['amount']));
        $root->appendChild($xml->createElement('fecha', $inv['created_at']));
        $xml->appendChild($root);
        $xmlStr = $xml->saveXML();

        // Sign the XML (PKCS1) - this is illustrative; SRI requires specific XML canonicalization and schema
        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if(!$privateKey) return false;
        openssl_sign($xmlStr, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);
        $sigB64 = base64_encode($signature);

        // Attach signature element
        $sigEl = $xml->createElement('signature', $sigB64);
        $root->appendChild($sigEl);
        return $xml->saveXML();
    }
}
