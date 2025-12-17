<?php
// app/controllers/AdminDashboardController.php
require_once __DIR__ . '/../models/AnalyticsModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
use Dompdf\Dompdf;

class AdminDashboardController {
  private $a; private $u;
  public function __construct(){ $this->a = new AnalyticsModel(); $this->u = new UserModel(); }

  public function getAnalytics(){
    $days = (int)($_GET['days'] ?? 7);
    $reg = $this->a->dailyRegistrations($days);
    $logins = $this->a->todaysLoginsByCountry();
    $totalZ = $this->a->globalZafirosTotal();
    $split = $this->a->revenueSplitCreator();
    $this->json(['ok'=>true,'registrations'=>$reg,'todaysLogins'=>$logins,'globalZafiros'=>$totalZ,'revenueSplitCreator'=>$split]);
  }
  public function updateUser(){
    $in = json_decode(file_get_contents('php://input'), true);
    $id = (int)($in['id'] ?? 0); if(!$id) return $this->json(['ok'=>false,'error'=>'ID requerido'],400);
    $ok = $this->u->updateAccount($id, $in); return $this->json(['ok'=>$ok]);
  }
  public function setZafiros(){
    $in = json_decode(file_get_contents('php://input'), true);
    $id = (int)($in['id'] ?? 0); $bal = (int)($in['balance'] ?? 0);
    if(!$id) return $this->json(['ok'=>false,'error'=>'ID requerido'],400);
    $ok = $this->u->setZafiros($id,$bal); return $this->json(['ok'=>$ok]);
  }
  public function setRevenueSplit(){
    $in = json_decode(file_get_contents('php://input'), true);
    $pct = (float)($in['pct'] ?? 60); $ok = $this->a->setRevenueSplitCreator($pct);
    return $this->json(['ok'=>$ok,'pct'=>$pct]);
  }
  public function exportPdf(){
    $in = json_decode(file_get_contents('php://input'), true);
    $chart1 = $in['chart_reg'] ?? ''; $chart2 = $in['chart_login'] ?? '';
    $totalZ = (int)($in['globalZafiros'] ?? 0); $split = (float)($in['revenueSplitCreator'] ?? 60);
    $logoPath = PUBLIC_PATH . '/assets/img/logo.png';
    $logo = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';
    $html = '<html><head><meta charset="utf-8"><style>body{font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111}</style></head><body>';
    $html .= '<div style="display:flex;align-items:center;gap:10px">'.($logo?'<img src="'.$logo.'" style="height:36px">':'').'<h2>Reporte Administrativo — ENYOOI</h2></div>';
    $html .= '<p>Total zafiros globales: <b>'.number_format($totalZ).'</b> — Split creador: <b>'.$split.'%</b></p>';
    if($chart1) $html.='<h3>Registros diarios</h3><img src="'.$chart1.'" style="max-width:100%">';
    if($chart2) $html.='<h3>Logins de hoy por país</h3><img src="'.$chart2.'" style="max-width:100%">';
    $html .= '<div style="margin-top:14px;text-align:center;color:#666">Generado '.date('Y-m-d H:i').'</div></body></html>';
    $dompdf = new Dompdf(); $dompdf->loadHtml($html); $dompdf->setPaper('A4','portrait'); $dompdf->render();
    if(!is_dir(STORAGE_PATH.'/reports')) @mkdir(STORAGE_PATH.'/reports',0775,true);
    $name = 'reports/admin_report_'.time().'.pdf'; file_put_contents(STORAGE_PATH.'/'.$name, $dompdf->output());
    header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="admin_report.pdf"'); echo file_get_contents(STORAGE_PATH.'/'.$name);
  }
  private function json($arr,$code=200){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($arr); }
}
$ctl = new AdminDashboardController();
$action = $_GET['action'] ?? '';
switch($action){
  case 'getAnalytics': $ctl->getAnalytics(); break;
  case 'updateUser': $ctl->updateUser(); break;
  case 'setZafiros': $ctl->setZafiros(); break;
  case 'setRevenueSplit': $ctl->setRevenueSplit(); break;
  case 'exportPdf': $ctl->exportPdf(); break;
  default: http_response_code(404); echo 'Not Found';
}
