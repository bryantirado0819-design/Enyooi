<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class HistorialController extends Controller
{
    private $historialModel;
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando']) || ($_SESSION['rol'] ?? '') !== 'creadora') {
            redirection('/home');
        }

        $this->historialModel = $this->model('HistorialModel');
        // ✅ CORRECCIÓN: Usamos el nombre de tu modelo 'usuario'
        $this->usuarioModel = $this->model('usuario');
    }

    public function index()
    {
        $idCreadora = $_SESSION['logueando'];
        
        $ingresos = $this->historialModel->getIngresos($idCreadora);
        $retiros = $this->historialModel->getRetiros($idCreadora);
        // ✅ CORRECCIÓN: Llamamos al método correcto que existe en tu modelo 'usuario.php'
        $usuario = $this->usuarioModel->getUsuarioById($idCreadora);
        
        $datos = [
            'ingresos' => $ingresos,
            'retiros' => $retiros,
            'usuario' => $usuario
        ];

        $this->view('pages/historial', $datos);
    }

    public function exportarPDF($periodo = 'mes')
    {
        $idCreadora = $_SESSION['logueando'];
        // ✅ CORRECCIÓN: Llamamos al método correcto
        $usuario = $this->usuarioModel->getUsuarioById($idCreadora);
        
        date_default_timezone_set('America/Guayaquil');

        if ($periodo == 'semana') {
            $fechaFin = date('Y-m-d H:i:s');
            $fechaInicio = date('Y-m-d H:i:s', strtotime('-7 days'));
            $tituloReporte = 'Resumen Semanal de Ingresos';
        } else { // Por defecto es 'mes'
            $fechaFin = date('Y-m-d H:i:s');
            $fechaInicio = date('Y-m-d H:i:s', strtotime('-30 days'));
            $tituloReporte = 'Resumen Mensual de Ingresos';
        }

        $transacciones = $this->historialModel->getDatosParaResumen($idCreadora, $fechaInicio, $fechaFin);

        $html = $this->generarHtmlParaPdf($transacciones, $usuario, $tituloReporte, $fechaInicio, $fechaFin);
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'Resumen_Enyooi_' . str_replace(' ', '_', $usuario->usuario) . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
    }

    private function generarHtmlParaPdf($transacciones, $usuario, $titulo, $fechaInicio, $fechaFin)
    {
        $totalZafiros = 0;
        $totalUsd = 0;
        $filasTabla = '';

        foreach ($transacciones as $t) {
            $totalZafiros += $t->monto_zafiros_creador;
            $totalUsd += $t->monto_usd_creador;
            $filasTabla .= '
                <tr>
                    <td>'.date('d/m/Y H:i', strtotime($t->fecha_transaccion)).'</td>
                    <td>'.ucfirst(str_replace('_', ' ', $t->tipo_transaccion)).'</td>
                    <td>'.($t->espectador_usuario ?? 'N/A').'</td>
                    <td class="text-right">'.$t->monto_zafiros_creador.'</td>
                    <td class="text-right">$'.number_format($t->monto_usd_creador, 2).'</td>
                </tr>';
        }

        $fechaInicioF = date('d/m/Y', strtotime($fechaInicio));
        $fechaFinF = date('d/m/Y', strtotime($fechaFin));
        
        $logoPath = RUTA_PUBLIC . '/img/logo_enyooi.png';
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));


        return '
        <!DOCTYPE html><html><head><meta charset="utf-8"><title>'.$titulo.'</title>
        <style>
            body { font-family: "Helvetica", sans-serif; color: #333; } .container { width: 100%; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #6366f1; padding-bottom: 10px; } .header img { width: 150px; }
            h1 { color: #6366f1; text-align: center; } .info { margin-bottom: 20px; } .info p { margin: 5px 0; }
            .summary { background-color: #f3f4f6; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
            .summary h3 { margin-top: 0; color: #4f46e5; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; } th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #e0e7ff; color: #3730a3; } .text-right { text-align: right; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; }
        </style></head><body><div class="container">
            <div class="header"><img src="'.$logoBase64.'" alt="Enyooi Logo"></div>
            <h1>'.$titulo.'</h1>
            <div class="info">
                <p><strong>Creadora:</strong> '.$usuario->usuario.'</p><p><strong>Fecha de Emisión:</strong> '.date('d/m/Y').'</p>
                <p><strong>Periodo del Reporte:</strong> '.$fechaInicioF.' - '.$fechaFinF.'</p>
            </div>
            <div class="summary">
                <h3>Total Ingresos del Periodo</h3>
                <p style="font-size: 24px; margin: 10px 0; color: #1e1b4b;"><strong>'.number_format($totalZafiros).' Gemas</strong> ≈ $'.number_format($totalUsd, 2).' USD</p>
            </div>
            <h2>Detalle de Transacciones</h2>
            <table><thead><tr><th>Fecha</th><th>Tipo</th><th>Espectador</th><th class="text-right">Gemas</th><th class="text-right">Monto (USD)</th></tr></thead>
            <tbody>'.$filasTabla.'</tbody></table>
            <div class="footer"><p>Reporte generado por Enyooi.com</p></div>
        </div></body></html>';
    }
}