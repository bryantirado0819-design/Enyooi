<?php

class Wallet extends Controller {
    private $walletModel;
    private $userModel;

    // --- CREDENCIALES DE PRUEBA DE DATAFAST ---
    private const DATAFAST_CHECKOUT_URL = 'https://test.datapago.com/v1/checkouts';
    private const DATAFAST_AUTH_TOKEN = 'OGE4Mjk0MTc4MGRjY2QxMjAxODFkMjE1NGExMjA3MTR8Z2trMjJucDhUNg=='; // Reemplaza con tu Token
    private const DATAFAST_ENTITY_ID = '8a82941780dccd120181d2154a120710'; // Reemplaza con tu Entity ID

    public function __construct() {
        if (!isset($_SESSION['logueando'])) {
            redirection('/home/entrar');
        }
        $this->walletModel = $this->model('WalletModel');
        $this->userModel = $this->model('usuario');
    }

    public function index($checkoutId = null) {
        $usuario = $this->userModel->getUsuarioById($_SESSION['logueando']);
        
        $payment_status = $_SESSION['payment_status'] ?? null;
        unset($_SESSION['payment_status']); // Limpiar el mensaje para que no se muestre de nuevo

        $data = [
            'usuario' => $usuario,
            'saldo_zafiros' => $usuario->saldo_zafiros ?? 0,
            'checkoutId' => $checkoutId,
            'payment_status' => $payment_status // Pasar el estado del pago a la vista
        ];
        
        $this->view('recargar', $data);
    }
    
    public function preparePayment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idUsuario = $_SESSION['logueando'];
            $zafiros = (int)($_POST['zafiros'] ?? 0);
            $monto = number_format((float)($_POST['monto'] ?? 0), 2, '.', '');

            if (empty($zafiros) || $monto <= 0) {
                redirection('/wallet');
            }

            $idTransaccion = $this->walletModel->crearTransaccionRecarga($idUsuario, $monto, $zafiros);
            if (!$idTransaccion) {
                die('Error al crear la transacción local.');
            }

            // ... (El resto de la lógica de cURL para Datafast no cambia)
            $datafastPayload = http_build_query([
                'entityId' => self::DATAFAST_ENTITY_ID,
                'amount' => $monto,
                'currency' => 'USD',
                'merchantTransactionId' => $idTransaccion,
                'paymentType' => 'DB'
            ]);

            $ch = curl_init(self::DATAFAST_CHECKOUT_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datafastPayload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . self::DATAFAST_AUTH_TOKEN]);
            
            $response = curl_exec($ch);
            $responseData = json_decode($response, true);
            curl_close($ch);

            if (isset($responseData['id'])) {
                redirection('/wallet/index/' . $responseData['id']);
            } else {
                $errorMessage = $responseData['result']['description'] ?? 'Error de comunicación con Datafast.';
                // Redirigimos a nuestro propio método de error
                redirection('/wallet/error?msg=' . urlencode($errorMessage));
            }
        }
    }

    public function callback() {
        // Esta función no necesita cambios
        $raw_post_data = file_get_contents('php://input');
        $data = json_decode($raw_post_data, true);
        if (isset($data['merchantTransactionId']) && isset($data['result']['code'])) {
            $idTransaccion = $data['merchantTransactionId'];
            $codigoResultado = $data['result']['code'];
            if (preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $codigoResultado)) {
                $this->walletModel->marcarTransaccionComoAprobada($idTransaccion);
            } else {
                $this->walletModel->marcarTransaccionComoRechazada($idTransaccion);
            }
        }
        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    // ✅ MÉTODOS ACTUALIZADOS PARA USAR MODALES
    public function success() { 
        $_SESSION['payment_status'] = [
            'type' => 'success',
            'message' => '¡Recarga exitosa! Tus zafiros han sido añadidos a tu cuenta.'
        ];
        redirection('/wallet');
    }

    public function error() { 
        $_SESSION['payment_status'] = [
            'type' => 'error',
            'message' => htmlspecialchars($_GET['msg'] ?? 'Tu pago no pudo ser procesado.')
        ];
        redirection('/wallet');
    }
}