<?php
// Controlador Live completo
session_start();

class Live extends Controller
{
    private $liveModel;
    private $userModel;

    public function __construct()
    {
        $this->liveModel = $this->model('LiveModel');
        $this->userModel = $this->model('Usuario');
    }

    // Panel del creador
    public function index()
    {
        if (!isset($_SESSION['logueando'])) {
            redirection('/home/entrar');
        }
        $user = $this->userModel->getUsuarioById($_SESSION['logueando']);
        $this->view('pages/live_creator', ['usuario' => $user]);
    }

    // Crea la transmisión
    public function start()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            redirection('/home');
        }

        $title = trim($_POST['title'] ?? 'Mi Live');
        $mode  = trim($_POST['mode'] ?? 'webrtc'); // 'webrtc' | 'rtmp'
        $vertical = isset($_POST['vertical']) ? 1 : 0;

        $rtmpKey = null;
        if ($mode === 'rtmp') {
            $rtmpKey = bin2hex(random_bytes(16));
        }

        $liveId = $this->liveModel->createLive([
            'idUsuario' => $_SESSION['logueando'],
            'title'     => $title,
            'mode'      => $mode,
            'rtmp_key'  => $rtmpKey,
            'vertical'  => $vertical
        ]);

        // Reglas Lovense iniciales opcionales
        if (!empty($_POST['lovense_rules'])) {
            $rules = json_decode($_POST['lovense_rules'], true);
            if (is_array($rules)) {
                $this->liveModel->saveLovenseRules($liveId, $rules);
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'live_id' => $liveId,
            'rtmp' => $rtmpKey ? [
                'url' => 'rtmp://TU_HOST/live',
                'key' => $rtmpKey
            ] : null
        ]);
        exit();
    }

    // Termina la transmisión
    public function stop($liveId = null)
    {
        if (!isset($_SESSION['logueando']) || !$liveId) {
            redirection('/home');
        }
        $this->liveModel->endLive((int)$liveId, $_SESSION['logueando']);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // Vista del espectador
    public function viewer($liveId = null)
    {
        if (!$liveId) { redirection('/home'); }
        $live = $this->liveModel->getLive((int)$liveId);
        if (!$live || $live->status !== 'active') {
            $this->view('pages/live_ended', ['live' => $live]);
            return;
        }
        $this->view('pages/live_viewer', ['live' => $live]);
    }

    // Donar ZAFIRO
    public function donate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            redirection('/home');
        }
        $liveId = (int)($_POST['live_id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);

        if ($amount <= 0 || !$liveId) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']); exit();
        }

        $ok = $this->liveModel->processDonation($liveId, $_SESSION['logueando'], $amount);
        if ($ok['success']) {
            // Disparo Lovense según reglas
            $this->liveModel->triggerLovense($liveId, $amount);
        }

        header('Content-Type: application/json');
        echo json_encode($ok);
        exit();
    }

    // Guardar mensaje de chat (cuando WS no escribe a BD)
    public function chat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            redirection('/home');
        }
        $liveId = (int)($_POST['live_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        if (!$liveId || $message === '') {
            echo json_encode(['success' => false]); exit();
        }
        $this->liveModel->saveChatMessage($liveId, $_SESSION['logueando'], $message);
        echo json_encode(['success' => true]);
        exit();
    }

    // Vincular token Lovense del creador
    public function linkLovense()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            redirection('/home');
        }
        $token = trim($_POST['access_token'] ?? '');
        $api = trim($_POST['api_base'] ?? 'https://api.lovense.com');
        $this->liveModel->saveLovenseToken($_SESSION['logueando'], $token, $api);
        echo json_encode(['success' => true]);
        exit();
    }
}

?>