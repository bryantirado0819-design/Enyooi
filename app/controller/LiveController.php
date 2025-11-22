<?php

class LiveController extends Controller
{
    private $liveModel;
    private $perfilModel;
    private $usuarioModel;
    private $walletModel; // Para transacciones

    public function __construct()
    {
        if (!isset($_SESSION['logueando'])) {
            redirection('/home');
        }
        $this->liveModel = $this->model('LiveModel');
        $this->perfilModel = $this->model('PerfilModel');
        $this->usuarioModel = $this->model('usuario');
        $this->walletModel = $this->model('WalletModel'); // Inicializa el modelo Wallet
    }
    public function index()
    {
        // Obtener los streams activos desde el modelo
        $activeStreams = $this->liveModel->getActiveStreams();

        // Preparamos los datos para la vista
        $datos = [
            'streams' => $activeStreams,
            // Aquí puedes añadir más datos si los necesitas en la vista
        ];

        // Carga la vista 'pages/lives' y le pasa los datos
        $this->view('pages/lives', $datos);
    }

    /**
     * ✅ NUEVO: VISTA DEL ESPECTADOR
     * Carga los datos necesarios para ver un stream.
     */
    public function watch($idstream = 0)
    {
        $idstream = (int)$idstream;
        if ($idstream <= 0) {
            redirection('/live');
        }

        $streamData = $this->liveModel->getStreamAndCreatorInfo($idstream);

        if (!$streamData || $streamData->estado !== 'live') {
             $_SESSION['stream_error'] = 'Esta transmisión no está activa.';
             redirection('/live');
             exit;
        }

        $idEspectador = $_SESSION['logueando'] ?? null;
        $espectadorZafiros = 0;
        if ($idEspectador) {
            $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
            $espectadorZafiros = $espectador->saldo_zafiros ?? 0;
        }

        $datos = [
            'stream' => $streamData,
            'espectador_zafiros' => $espectadorZafiros,
            'tip_options' => $this->liveModel->getTipOptions($streamData->creator_id),
            'roulette_options' => $this->liveModel->getRouletteOptions($streamData->creator_id),
            'active_tip_goal' => $this->liveModel->getActiveTipGoal($streamData->creator_id),
            'idUsuarioLogueado' => $idEspectador,
            'nombreUsuarioLogueado' => $_SESSION['usuario'] ?? 'Invitado_' . rand(100, 999),
            'page_script' => 'live_viewer.js' // Para cargarlo en el footer
        ];

        $this->view('pages/live_watch', $datos);
    }

      // --- ✅ NUEVOS ENDPOINTS DE API PARA JS ---

    /**
     * API: Procesa una propina.
     * Valida el saldo y transfiere zafiros.
     */
    public function processTip() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $idEspectador = $_SESSION['logueando'];
        $amount = (int)($data['amount'] ?? 0);
        $idStream = (int)($data['streamId'] ?? 0);
        $creatorId = (int)($data['creatorId'] ?? 0);

        if ($amount <= 0 || $idStream <= 0 || $creatorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        // 1. Validar y procesar la transacción
        $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
        if ($espectador->saldo_zafiros < $amount) {
            echo json_encode(['success' => false, 'message' => 'Saldo de Zafiros insuficiente.']);
            exit;
        }

        // 2. Ejecutar transacción
        $result = $this->walletModel->realizarTransaccion(
            $idEspectador,
            $creatorId,
            $amount,
            'propina_stream',
            $idStream, // Usar idStream como referencia
            'Propina para stream ' . $idStream
        );

        if ($result['success']) {
            // 3. Actualizar meta de propinas (si aplica)
            $this->liveModel->updateTipGoalProgress($creatorId, $amount);

            // 4. Devolver éxito
            echo json_encode([
                'success' => true, 
                'newBalance' => $result['nuevoSaldoEmisor'],
                'message' => '¡Propina enviada!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Error en la transacción.']);
        }
        exit;
    }

    /**
     * API: Procesa un giro de ruleta.
     * Valida saldo y descuenta el costo.
     */
    public function processSpin() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['logueando'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $idEspectador = $_SESSION['logueando'];
        $idStream = (int)($data['streamId'] ?? 0);

        $stream = $this->liveModel->getStreamDataById($idStream); // Necesitarás crear este método simple
        if (!$stream || !$stream->roulette_enabled || $stream->roulette_cost <= 0) {
            echo json_encode(['success' => false, 'message' => 'La ruleta no está activa.']);
            exit;
        }
        $cost = $stream->roulette_cost;

        // 1. Validar saldo
        $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
        if ($espectador->saldo_zafiros < $cost) {
            echo json_encode(['success' => false, 'message' => 'Zafiros insuficientes para girar.']);
            exit;
        }

        // 2. Ejecutar transacción (descuento)
        $result = $this->walletModel->descontarZafiros(
            $idEspectador,
            $cost,
            'spin_roulette',
            $idStream,
            'Giro de ruleta en stream ' . $idStream
        );

        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'newBalance' => $result['newBalance'],
                'message' => '¡Girando!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Error al descontar Zafiros.']);
        }
        exit;
    }

    /**
     * API: Proporciona datos de la sala a Node.js
     * (Llamado por server.js)
     */
    public function apiGetRoomData($creatorId = 0) {
        header('Content-Type: application/json');
        if ($creatorId <= 0) {
            echo json_encode(['error' => 'ID de creador inválido']);
            exit;
        }

        $data = [
            'rouletteOptions' => $this->liveModel->getRouletteOptions($creatorId) ?? [],
            'activeTipGoal' => $this->liveModel->getActiveTipGoal($creatorId) ?? null,
        ];
        echo json_encode($data);
        exit;
    }
    
    


    public function stream()
    {
        if ($_SESSION['rol'] !== 'creadora') {
            redirection('/home');
        }
        $creatorId = $_SESSION['logueando'];
        $this->liveModel->createOrUpdateStreamKey($creatorId);
        
        $levelInfo = $this->usuarioModel->getUserLevelInfo($creatorId);

        $datos = [
            'stream' => $this->liveModel->getStreamDataByCreatorId($creatorId),
            'perfil' => $this->perfilModel->getPerfil($creatorId),
            'usuario' => $this->usuarioModel->getUsuarioById($creatorId),
            'level_info' => $levelInfo,
            'tip_options' => $this->liveModel->getTipOptions($creatorId),
            'roulette_options' => $this->liveModel->getRouletteOptions($creatorId),
            'lovense_options' => $this->liveModel->getLovenseTipOptions($creatorId),
        ];

        $this->view('pages/live_creator', $datos);
    }

    // --- ENDPOINTS AJAX ---

    public function addLovenseTip() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $newId = $this->liveModel->addLovenseTipOption($_SESSION['logueando'], $datos->zafiros, $datos->duration, $datos->intensity);
            echo json_encode(['success' => (bool)$newId, 'newId' => $newId]);
        }
    }

    public function deleteLovenseTip() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $success = $this->liveModel->deleteLovenseTipOption($datos->id, $_SESSION['logueando']);
            echo json_encode(['success' => $success]);
        }
    }
    
    public function addTip() {
        header('Content-Type: application/json');
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $newId = $this->liveModel->addTipOption($_SESSION['logueando'], $datos->zafiros, $datos->descripcion);
            echo json_encode(['success' => (bool)$newId, 'newId' => $newId]);
        }
    }

    public function deleteTip() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $success = $this->liveModel->deleteTipOption($datos->id, $_SESSION['logueando']);
            echo json_encode(['success' => $success]);
        }
    }

    public function addRoulette() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $newId = $this->liveModel->addRouletteOption($_SESSION['logueando'], $datos->texto);
            echo json_encode(['success' => (bool)$newId, 'newId' => $newId]);
        }
    }
    
    public function deleteRoulette() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"));
            $success = $this->liveModel->deleteRouletteOption($datos->id, $_SESSION['logueando']);
            echo json_encode(['success' => $success]);
        }
    }

    public function saveSettings() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"), true);
            $success = $this->liveModel->updateStreamSettings($_SESSION['logueando'], $datos);
            echo json_encode(['success' => $success, 'message' => $success ? 'Ajustes guardados.' : 'Error al guardar.']);
        }
    }

    public function getLovenseAuthToken() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['rol'] ?? '') !== 'creadora') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            return;
        }

        $creatorId = "enyooi_user_" . $_SESSION['logueando']; 
        $developerToken = 'N9BuBePZ1MzWH9aCrIkSgVafg9RT8VL_N2ME09MMKCmvI9AuoPr5FBxwW03JSB2B';
        
        $url = 'https://api.lovense-api.com/api/basicApi/getToken';
        $postData = json_encode([
            'token' => $developerToken,
            'uid' => $creatorId
        ]);

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de cURL: No se pudo conectar con la API de Lovense.']);
            return;
        }

        $response = json_decode($result, true);

        if ($httpcode == 200 && isset($response['data']['authToken'])) {
            echo json_encode(['success' => true, 'token' => $response['data']['authToken']]);
        } else {
            http_response_code($httpcode);
            $errorMessage = $response['message'] ?? 'Respuesta inválida de la API de Lovense.';
            echo json_encode(['success' => false, 'message' => $errorMessage, 'api_response' => $response]);
        }
    }
}