<?php
// app/controllers/CreatorDashboardController.php
require_once __DIR__ . '/../models/AnalyticsModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
use Dompdf\Dompdf;

class CreatorDashboardController {
  private $a;
  public function __construct(){ $this->a = new AnalyticsModel(); }
  private function json($arr,$code=200){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($arr); }

  public function getAnalytics(){
    $uid = (int)($_GET['user_id'] ?? 0);
    $split = $this->a->revenueSplitCreator();
    $z = $this->a->creatorBalanceZafiros($uid);
    $usd_gross = $z / 7.0;
    $usd_net = $usd_gross * ($split/100.0);
    $streams = $this->a->creatorStreams($uid);
    $geoC = $this->a->viewersByGeo($uid);
    $geoCity = $this->a->viewersByCity($uid);
    $this->json(['ok'=>true,'zafiros'=>$z,'usd_gross'=>$usd_gross,'usd_net'=>$usd_net,'split'=>$split,'streams'=>$streams,'geoCountry'=>$geoC,'geoCity'=>$geoCity]);
  }

  public function exportPdf(){
    $in = json_decode(file_get_contents('php://input'), true);
    $chart1 = $in['chart_geo'] ?? ''; $chart2 = $in['chart_streams'] ?? ''; $uid = (int)($in['user_id'] ?? 0);
    $split = $this->a->revenueSplitCreator(); $z = $this->a->creatorBalanceZafiros($uid);
    $usd_gross = $z/7.0; $usd_net = $usd_gross * ($split/100.0);
    $logoPath = PUBLIC_PATH . '/assets/img/logo.png';
    $logo = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';
    $html = '<html><head><meta charset="utf-8"><style>body{font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111}</style></head><body>';
    $html .= '<div style="display:flex;align-items:center;gap:10px">'.($logo?'<img src="'.$logo.'" style="height:36px">':'').'<h2>Reporte de Creador — ENYOOI</h2></div>';
    $html .= '<p>Zafiros: <b>'.number_format($z).'</b> — USD bruto: $'.number_format($usd_gross,2).' — USD neto ('.$split.'%): $'.number_format($usd_net,2).'</p>';
    if($chart1) $html.='<h3>Audiencia</h3><img src="'.$chart1.'" style="max-width:100%">';
    if($chart2) $html.='<h3>Ingresos por transmisión</h3><img src="'.$chart2.'" style="max-width:100%">';
    $html .= '<div style="margin-top:14px;text-align:center;color:#666">Generado '.date('Y-m-d H:i').'</div></body></html>';
    $dompdf = new Dompdf(); $dompdf->loadHtml($html); $dompdf->setPaper('A4','portrait'); $dompdf->render();
    if(!is_dir(STORAGE_PATH.'/reports')) @mkdir(STORAGE_PATH.'/reports',0775,true);
    $name = 'reports/creator_report_'.time().'.pdf'; file_put_contents(STORAGE_PATH.'/'.$name, $dompdf->output());
    header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="creator_report.pdf"'); echo file_get_contents(STORAGE_PATH.'/'.$name);
  }
}

$ctl = new CreatorDashboardController();
$action = $_GET['action'] ?? '';
switch($action){
  case 'getAnalytics': $ctl->getAnalytics(); break;
  case 'exportPdf': $ctl->exportPdf(); break;
  default: http_response_code(404); echo 'Not Found';
}
