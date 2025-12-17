<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;

class ReportController {
    public function exportPdf() {
        $data = json_decode(file_get_contents('php://input'), true);
        $filters = $data['filters'] ?? '';
        $chart = $data['chart'] ?? '';

        $model = new ReportModel();
        $rows = $model->getFilteredReports();

        $html = '<html><head>
        <style>
            body { font-family: Poppins, sans-serif; font-size: 12px; color: #333; }
            .header { display:flex; align-items:center; margin-bottom:20px; }
            .header img { height:40px; margin-right:15px; }
            .filters { margin-bottom:10px; font-size:12px; color:#555; }
            table { width:100%; border-collapse: collapse; margin-bottom:20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align:left; }
            th { background:#f0f0f0; }
            tr:nth-child(even) { background:#fafafa; }
            .footer { font-size: 10px; color:#888; margin-top:30px; text-align:center; }
        </style></head><body>';

        $html .= '<div class="header"><img src="public/assets/img/logo.png" /><h2>Reporte ENYOOI</h2></div>';
        $html .= '<div class="filters"><strong>Filtros aplicados:</strong> ' . htmlspecialchars($filters) . '</div>';

        $html .= '<table><thead><tr><th>ID</th><th>Usuario</th><th>Monto</th><th>Fecha</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . $r['id'] . '</td><td>' . $r['usuario'] . '</td><td>' . $r['monto'] . '</td><td>' . $r['fecha'] . '</td></tr>';
        }
        $html .= '</tbody></table>';

        if ($chart) {
            $html .= '<div><img src="' . $chart . '" style="max-width:600px;" /></div>';
        }

        $html .= '<div class="footer">Reporte generado automáticamente desde ENYOOI - ' . date("Y-m-d H:i") . '</div>';
        $html .= '</body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("reporte.pdf", ["Attachment" => true]);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'exportPdf') {
    $controller = new ReportController();
    $controller->exportPdf();
}
?>
