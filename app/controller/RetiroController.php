<?php
// Usamos el helper de correo que ya tienes
require_once '../app/helpers/mailer_helper.php';

class RetiroController extends Controller
{
    private $retiroModel;
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando']) || $_SESSION['rol'] !== 'creadora') {
            redirection('/home');
        }
        $this->retiroModel = $this->model('RetiroModel');
        $this->usuarioModel = $this->model('usuario');
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        if (ob_get_level() > 0) ob_end_clean();
        header_remove();
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function index()
    {
        $idCreadora = $_SESSION['logueando'];
        $usuario = $this->usuarioModel->getUsuarioById($idCreadora);
        
        $datos = [
            'usuario' => $usuario,
            'saldo_zafiros' => $usuario->saldo_zafiros ?? 0,
        ];

        $this->view('retiro', $datos);
    }

    public function getFinancialData()
    {
        $idCreadora = $_SESSION['logueando'];
        $financialSummary = $this->retiroModel->getCreatorFinancialSummary($idCreadora);
        $this->jsonResponse($financialSummary);
    }

    public function enviarCodigoVerificacion()
    {
        $idCreadora = $_SESSION['logueando'];
        $usuario = $this->usuarioModel->getUsuarioById($idCreadora);
        $codigo = rand(100000, 999999);
        $asunto = 'Tu Código de Verificación para Retiro en Enyooi';

        if ($this->retiroModel->storeVerificationCode($idCreadora, $codigo)) {
            if (sendVerificationEmail($usuario->correo, $asunto, $codigo)) {
                $this->jsonResponse(['success' => true, 'message' => 'Se ha enviado un código a tu correo.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo enviar el correo.'], 500);
            }
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar el código en la base de datos.'], 500);
        }
    }
    
    public function solicitar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
        
        // 1. Verificación de reCAPTCHA
        $recaptchaSecret = '6LdjY9crAAAAAL7VtS5TBPhwYZ_qzGlB0RKBymT2'; // IMPORTANTE: Guardar esto de forma segura
        $recaptchaResponse = $data['g-recaptcha-response'] ?? '';
        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $recaptchaSecret, 'response' => $recaptchaResponse]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
    
        if (!$responseData['success']) {
            $this->jsonResponse(['success' => false, 'message' => 'Verificación reCAPTCHA fallida. Inténtalo de nuevo.']);
            return;
        }

        // 2. Verificación del código de correo
        $idCreadora = $_SESSION['logueando'];
        $codigo = $data['codigo_verificacion'] ?? '';
        
        if (!$this->retiroModel->verifyCode($idCreadora, $codigo)) {
            $this->jsonResponse(['success' => false, 'message' => 'El código de verificación es incorrecto o ha expirado.']);
            return;
        }

        // 3. Procesar la solicitud de retiro
        $resultado = $this->retiroModel->createWithdrawalRequest($idCreadora, $data['datos_formulario']);
    
        $this->jsonResponse($resultado);
    }
}
