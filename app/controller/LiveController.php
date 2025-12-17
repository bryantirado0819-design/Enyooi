<?php
// Cargar SDK de AWS
// Asegúrate de que la ruta al autoload sea correcta según tu estructura
require_once APPROOT . '/../vendor/autoload.php';

use Aws\Ivs\IvsClient;
use Aws\Exception\AwsException;

class LiveController extends Controller
{
    private $liveModel;
    private $perfilModel;
    private $usuarioModel;
    private $walletModel;
    private $ivsClient; // Cliente AWS

    public function __construct()
    {
        // Verificar sesión
        if (!isset($_SESSION['logueando'])) {
            redirection('/home');
        }

        // Cargar modelos
        $this->liveModel = $this->model('LiveModel'); 
        $this->perfilModel = $this->model('PerfilModel');
        $this->usuarioModel = $this->model('usuario');
        $this->walletModel = $this->model('WalletModel');

        // Configuración AWS IVS
        // RECOMENDACIÓN DE SEGURIDAD:
        // Mueve 'key' y 'secret' a tu archivo app/config/config.php y úsalos como constantes (AWS_KEY, AWS_SECRET)
        $this->ivsClient = new IvsClient([
            'version' => 'latest',
            'region' => 'us-east-1', // Virginia del Norte (mejor latencia global)
            'credentials' => [
                'key'    => '', 
                'secret' => '', 
            ]
        ]);
    }

    // Página Principal: Listado de Lives activos
    public function index()
    {
        $activeStreams = $this->liveModel->getActiveStreams();
        $this->view('pages/lives', ['streams' => $activeStreams]);
    }

    // ✅ VISTA ESPECTADOR (Adaptada a IVS)
    public function watch($idstream = 0)
    {
        $idstream = (int)$idstream;
        if ($idstream <= 0) die("Error: ID inválido.");

        // Obtenemos datos del stream (incluyendo URLs de reproducción IVS)
        $streamData = $this->liveModel->getStreamAndCreatorInfo($idstream);

        // Validar que el stream exista y esté 'live'
        if (!$streamData || $streamData->estado !== 'live') {
             $_SESSION['stream_error'] = 'La transmisión ha finalizado.';
             redirection('/lives'); // Redirigir al listado
             exit;
        }

        // Obtener datos del espectador logueado
        $idEspectador = $_SESSION['logueando'] ?? null;
        $espectadorZafiros = 0;
        if ($idEspectador) {
            $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
            $espectadorZafiros = $espectador->saldo_zafiros ?? 0;
        }

        // Preparar datos para la vista
        $datos = [
            'stream' => $streamData,
            'espectador_zafiros' => $espectadorZafiros,
            'tip_options' => $this->liveModel->getTipOptions($streamData->creator_id),
            'roulette_options' => $this->liveModel->getRouletteOptions($streamData->creator_id),
            'active_tip_goal' => $this->liveModel->getActiveTipGoal($streamData->creator_id),
            'idUsuarioLogueado' => $idEspectador,
            'nombreUsuarioLogueado' => $_SESSION['usuario'] ?? 'Invitado',
            'page_script' => 'live_viewer.js'
        ];

        $this->view('pages/live_viewer', $datos);
    }

    // ✅ VISTA CREADOR (Panel de control)
    public function stream()
    {
        // Solo creadoras pueden acceder
        if (($_SESSION['rol'] ?? '') !== 'creadora') {
            redirection('/home');
        }
        
        $creatorId = $_SESSION['logueando'];
        
        // Obtenemos info del stream (credenciales guardadas, título, etc.)
        $stream = $this->liveModel->getStreamDataByCreatorId($creatorId);
        
        // Datos para el dashboard del live
        $datos = [
            'stream' => $stream,
            'perfil' => $this->perfilModel->getPerfil($creatorId),
            'usuario' => $this->usuarioModel->getUsuarioById($creatorId),
            'level_info' => $this->usuarioModel->getUserLevelInfo($creatorId), // Si tienes este método
            'tip_options' => $this->liveModel->getTipOptions($creatorId),
            'roulette_options' => $this->liveModel->getRouletteOptions($creatorId),
            'lovense_options' => method_exists($this->liveModel, 'getLovenseTipOptions') ? $this->liveModel->getLovenseTipOptions($creatorId) : [],
        ];

        $this->view('pages/live_creator', $datos);
    }

    // --- 🔥 API: INICIAR STREAM CON AWS IVS 🔥 ---
    public function start_ivs_stream() {
        header('Content-Type: application/json');
        
        // Validaciones básicas
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']); return;
        }

        $userId = $_SESSION['logueando'];
        $title = trim($_POST['title'] ?? 'Live Stream');

        try {
            // 1. Verificar si el usuario ya tiene credenciales IVS en BD
            $stream = $this->liveModel->getStreamDataByCreatorId($userId);
            
            $arn = $stream->ivs_channel_arn ?? null;
            $ingest = $stream->ivs_ingest_endpoint ?? null;
            $key = $stream->ivs_stream_key ?? null;
            $playback = $stream->ivs_playback_url ?? null;

            // 2. Si NO tiene credenciales, creamos el canal en AWS (Solo se hace una vez por usuario)
            if (!$arn || !$key) {
                $result = $this->ivsClient->createChannel([
                    'latencyMode' => 'LOW', // Latencia baja es vital para Lovense y Chat
                    'name' => 'enyooi_' . $userId . '_' . uniqid(),
                    'type' => 'STANDARD' // Calidad HD/Full HD
                ]);

                // Extraer datos de la respuesta de AWS
                $arn = $result['channel']['arn'];
                $ingest = $result['channel']['ingestEndpoint'];
                $playback = $result['channel']['playbackUrl'];
                $key = $result['streamKey']['value'];

                // Guardar credenciales en la BD para siempre (ahorra costos y tiempo)
                $this->liveModel->saveIvsCredentials($userId, $arn, $ingest, $key, $playback);
            }

            // 3. Actualizar estado del stream a 'live' en la BD
            $streamId = $this->liveModel->setStreamLive($userId, $title);

            // 4. Devolver las credenciales al Frontend (JS) para iniciar la transmisión
            echo json_encode([
                'success' => true,
                'stream_id' => $streamId,
                'ingest_endpoint' => $ingest,
                'stream_key' => $key,
                'playback_url' => $playback
            ]);

        } catch (AwsException $e) {
            // Error de AWS (ej: credenciales mal puestas)
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'AWS Error: ' . $e->getAwsErrorMessage()]);
        } catch (Exception $e) {
            // Error general
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // API: Detener Stream
    public function stop_stream() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        // Poner estado en offline en BD
        $this->liveModel->setStreamOffline($_SESSION['logueando']);
        
        echo json_encode(['success' => true]);
    }

    // --- 💰 API: PROCESAMIENTO DE PAGOS (WALLET) ---

    // Procesar Propina (Tips)
    public function processTip() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $idEspectador = $_SESSION['logueando'];
        $amount = (int)($data['amount'] ?? 0);
        $creatorId = (int)($data['creatorId'] ?? 0);
        $idStream = (int)($data['streamId'] ?? 0);

        if ($amount <= 0 || $creatorId <= 0) { 
            echo json_encode(['success'=>false,'message'=>'Datos inválidos']); exit; 
        }

        // Verificar saldo
        $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
        if ($espectador->saldo_zafiros < $amount) { 
            echo json_encode(['success'=>false,'message'=>'Saldo insuficiente']); exit; 
        }

        // Realizar transacción
        $res = $this->walletModel->realizarTransaccion(
            $idEspectador, 
            $creatorId, 
            $amount, 
            'propina_stream', 
            $idStream, 
            'Propina stream '.$idStream
        );

        if ($res['success']) {
            // Actualizar meta visual
            $this->liveModel->updateTipGoalProgress($creatorId, $amount);
            echo json_encode(['success'=>true, 'newBalance'=>$res['nuevoSaldoEmisor'], 'message'=>'¡Enviado!']);
        } else {
            echo json_encode(['success'=>false, 'message'=>$res['message']]);
        }
    }

    // Procesar Giro de Ruleta
    public function processSpin() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $idEspectador = $_SESSION['logueando'];
        $idStream = (int)($data['streamId'] ?? 0);
        
        // Validar configuración del stream
        $stream = $this->liveModel->getStreamDataById($idStream);
        if (!$stream || !$stream->roulette_enabled) { 
            echo json_encode(['success'=>false, 'message'=>'Ruleta inactiva']); exit; 
        }
        
        $cost = $stream->roulette_cost;
        $espectador = $this->usuarioModel->getUsuarioById($idEspectador);
        
        if ($espectador->saldo_zafiros < $cost) { 
            echo json_encode(['success'=>false,'message'=>'Saldo insuficiente']); exit; 
        }

        // Descontar saldo
        $res = $this->walletModel->descontarZafiros($idEspectador, $cost, 'spin_roulette', $idStream, 'Giro Ruleta');
        
        if ($res['success']) {
            echo json_encode(['success'=>true, 'newBalance'=>$res['newBalance'], 'message'=>'¡Girando!']);
        } else {
            echo json_encode(['success'=>false, 'message'=>$res['message']]);
        }
    }

    // --- ⚙️ API: GESTIÓN DE AJUSTES (CREADOR) ---
    
    // Helper para evitar repetir código en los métodos CRUD
    private function proxyAjax($method, $fields) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $d = json_decode(file_get_contents("php://input"));
            $args = [$_SESSION['logueando']]; // Primer argumento siempre es el ID del creador
            foreach($fields as $f) {
                $args[] = $d->$f ?? null;
            }
            // Llamar dinámicamente al modelo
            $res = call_user_func_array([$this->liveModel, $method], $args);
            echo json_encode(['success' => (bool)$res, 'newId' => $res]);
        }
    }

    // Agregar opción de propina
    public function addTip() { 
        $this->proxyAjax('addTipOption', ['zafiros', 'descripcion']); 
    }

    // Eliminar opción de propina
    public function deleteTip() { 
        $this->proxyAjax('deleteTipOption', ['id']); 
    }

    // Agregar opción de ruleta
    public function addRoulette() { 
        $this->proxyAjax('addRouletteOption', ['texto']); 
    }

    // Eliminar opción de ruleta
    public function deleteRoulette() { 
        $this->proxyAjax('deleteRouletteOption', ['id']); 
    }
    
    // Guardar título y descripción del stream
    public function saveSettings() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = json_decode(file_get_contents("php://input"), true);
            $success = $this->liveModel->updateStreamSettings($_SESSION['logueando'], $datos);
            echo json_encode(['success' => $success]);
        }
    }

    // Obtener Token de Lovense (Si usas la API remota)
    public function getLovenseAuthToken() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        // Aquí iría tu lógica real de conexión con Lovense si usas el Developer Dashboard
        // Por ahora devolvemos un placeholder o error si no está configurado
        echo json_encode(['success' => false, 'message' => 'Configuración de Lovense API pendiente']);
    }
}