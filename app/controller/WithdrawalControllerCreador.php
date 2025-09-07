<?php
// app/controllers/WithdrawalController.php
require_once __DIR__ . '/../models/WithdrawalModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
use Dompdf\Dompdf;

class WithdrawalController {
  private $m;
  public function __construct(){ $this->m = new WithdrawalModel(); }
  private function json($arr,$code=200){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($arr); }

  public function request(){
    $in = json_decode(file_get_contents('php://input'), true);
    $uid = (int)($in['user_id'] ?? 0); $amount = (float)($in['amount'] ?? 0); $bank = $in['bank'] ?? [];
    [$ok,$id] = $this->m->request($uid,$amount,$bank);
    return $this->json(['ok'=>$ok,'id'=>$id, 'error'=>$ok?null:'No se pudo crear']);
  }
  public function listUser(){
    $uid = (int)($_GET['user_id'] ?? 0);
    return $this->json(['ok'=>true,'data'=>$this->m->listUser($uid)]);
  }
  public function listAdmin(){
    $status = $_GET['status'] ?? null;
    return $this->json(['ok'=>true,'data'=>$this->m->listAdmin($status)]);
  }
  public function approve(){
    $in = json_decode(file_get_contents('php://input'), true);
    $id = (int)($in['id'] ?? 0); $txn = trim($in['txn'] ?? '');
    $w = $this->m->find($id); if(!$w) return $this->json(['ok'=>false,'error'=>'No existe'],404);
    // Generar PDF
    $logoPath = PUBLIC_PATH . '/assets/img/logo.png';
    $logo = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';
    $bank = json_decode($w['bank_json'] ?? '{}', true);
    $html = '<html><head><meta charset="utf-8"><style>body{font-family: DejaVu Sans, sans-serif; font-size:12px} .tag{background:#eef;padding:4px 8px;border-radius:6px;display:inline-block}</style></head><body>';
    $html .= '<div style="display:flex;align-items:center;gap:10px">'.($logo?'<img src="'.$logo.'" style="height:36px">':'').'<h2>Comprobante de Pago a Creador — ENYOOI — Banco Pichincha</h2></div>';
    $html .= '<p class="tag">Retiro #'.$w['id'].' — USD $'.number_format($w['amount_usd'],2).'</p>';
    $html .= '<p>Usuario ID: '.$w['user_id'].'<br>Cuenta: '.htmlspecialchars($bank['cuenta_bancaria'] ?? '').'<br>Titular: '.htmlspecialchars($bank['titular'] ?? '').'</p>';
    $html .= '<p>N° Transacción: <b>'.htmlspecialchars($txn).'</b> — Fecha: '.date('Y-m-d H:i').'</p>';
    $html .= '</body></html>';
    $dompdf = new Dompdf(); $dompdf->loadHtml($html); $dompdf->setPaper('A4','portrait'); $dompdf->render();
    if(!is_dir(STORAGE_PATH.'/payouts')) @mkdir(STORAGE_PATH.'/payouts',0775,true);
    $name = 'payouts/receipt_'.$w['id'].'_'.time().'.pdf';
    file_put_contents(STORAGE_PATH.'/'.$name, $dompdf->output());
    $ok = $this->m->approve($id,$txn,$name);
    return $this->json(['ok'=>$ok,'receipt'=>$name]);
  }
  public function reject(){
    $in = json_decode(file_get_contents('php://input'), true);
    $id = (int)($in['id'] ?? 0); $ok = $this->m->reject($id, $in['reason'] ?? '');
    return $this->json(['ok'=>$ok]);
  }
  public function download(){
    $id = (int)($_GET['id'] ?? 0); $w = $this->m->find($id); if(!$w || !$w['receipt_path']) { http_response_code(404); echo 'No encontrado'; return; }
    $file = STORAGE_PATH . '/' . $w['receipt_path']; if(!file_exists($file)){ http_response_code(404); echo 'No encontrado'; return; }
    header('Content-Type: application/pdf'); header('Content-Disposition: inline; filename="recibo_'.$id.'.pdf"'); readfile($file);
  }
}

$c = new WithdrawalController();
$action = $_GET['action'] ?? '';
switch($action){
  case 'request': $c->request(); break;
  case 'listUser': $c->listUser(); break;
  case 'listAdmin': $c->listAdmin(); break;
  case 'approve': $c->approve(); break;
  case 'reject': $c->reject(); break;
  case 'download': $c->download(); break;
  default: http_response_code(404); echo 'Not Found';
}
