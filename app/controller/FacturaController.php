<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Dompdf\Dompdf;

class FacturaController {

    public function generarFactura($compra, $stream = true) {
        $subtotal = $compra['cantidad'] * $compra['precio'];
        $iva = $subtotal * 0.12;
        $total = $subtotal + $iva;

        $html = '<html><head>
        <link href="public/assets/css/factura.css" rel="stylesheet">
        </head><body>';
        $html .= '<div class="header"><img src="public/assets/img/logo.png" /><h2>Factura ENYOOI</h2></div>';
        $html .= '<div class="cliente"><strong>Cliente:</strong> '.$compra['usuario'].'<br>
                  <strong>Email:</strong> '.$compra['email'].'</div>';
        $html .= '<table><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr></thead><tbody>';
        $html .= '<tr><td>Zafiros</td><td>'.$compra['cantidad'].'</td><td>$'.number_format($compra['precio'],2).'</td><td>$'.number_format($subtotal,2).'</td></tr>';
        $html .= '</tbody></table>';
        $html .= '<div class="totales"><strong>Subtotal:</strong> $'.number_format($subtotal,2).'<br>';
        $html .= '<strong>IVA 12%:</strong> $'.number_format($iva,2).'<br>';
        $html .= '<strong>Total:</strong> $'.number_format($total,2).'</div>';
        $html .= '<div class="footer">Factura generada automáticamente desde ENYOOI</div>';
        $html .= '</body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        if ($stream) {
            $dompdf->stream("factura_zafiros.pdf", ["Attachment" => true]);
        } else {
            header('Content-Type: application/pdf');
            echo $dompdf->output();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if(isset($data['compra'])){
        $fc = new FacturaController();
        $fc->generarFactura($data['compra'], false);
    }
}
?>
