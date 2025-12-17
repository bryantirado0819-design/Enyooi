<?php
// app/controller/Mensajes.php

class Mensajes extends Controller
{
    private $mensajesModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando'])) {
            if ($this->isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'No autenticado.']);
                exit;
            }
            redirection('/home/entrar');
        }
        $this->mensajesModel = $this->model('MensajesModel');
    }

    private function isApiRequest()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if (strpos($contentType, 'application/json') !== false) {
            return true;
        }
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function notifySocketServer($endpoint, $data) {
        $socketUrl = 'http://localhost:3000' . $endpoint;
        $ch = curl_init($socketUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // No esperar mucho
        curl_exec($ch);
        curl_close($ch);
    }

    public function index()
    {
        $this->view('pages/social');
    }

    public function contactos()
    {
        header('Content-Type: application/json');
        $idUsuarioActual = $_SESSION['logueando'];
        $query = $_GET['q'] ?? '';

        $contactos = $this->mensajesModel->getConversations($idUsuarioActual, $query);
        echo json_encode(['success' => true, 'contacts' => $contactos]);
    }
    
    public function conversacion($idOtroUsuario)
    {
        header('Content-Type: application/json');
        $idUsuarioActual = $_SESSION['logueando'];
        $idOtroUsuario = (int)$idOtroUsuario;

        // Marcar mensajes como leídos
        $this->mensajesModel->markMessagesAsRead($idOtroUsuario, $idUsuarioActual);
        
        // Notificar al otro usuario que sus mensajes fueron leídos a través del socket
        $this->notifySocketServer('/emit-read', [
            'readerId' => $idUsuarioActual,
            'writerId' => $idOtroUsuario
        ]);
        
        $mensajes = $this->mensajesModel->getMessagesBetweenUsers($idUsuarioActual, $idOtroUsuario);

        echo json_encode(['success' => true, 'messages' => $mensajes]);
    }


    public function enviarMensaje()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $remitenteId = $_SESSION['logueando'];
        $destinatarioId = (int)($data['destinatario_id'] ?? 0);
        $contenido = trim($data['contenido'] ?? '');

        if (empty($destinatarioId) || empty($contenido)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos para enviar el mensaje.']);
            return;
        }

        $messageData = [
            'remitente_id' => $remitenteId,
            'destinatario_id' => $destinatarioId,
            'contenido' => $contenido,
            'media_url' => null,
            'media_tipo' => null
        ];

        $messageId = $this->mensajesModel->createMessage($messageData);

        if ($messageId) {
            // Construimos el objeto de respuesta con los datos completos del mensaje
            $responseData = $messageData;
            $responseData['idMensaje'] = $messageId;
            $responseData['fechaMensaje'] = date('Y-m-d H:i:s');
            $responseData['leido'] = 0;

            echo json_encode(['success' => true, 'message_data' => $responseData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el mensaje en la BD.']);
        }
    }

    public function enviarImagen()
    {
        header('Content-Type: application/json');
        $remitenteId = $_SESSION['logueando'];
        $destinatarioId = (int)($_POST['destinatario_id'] ?? 0);
        
        if (empty($destinatarioId) || empty($_FILES['image'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
            return;
        }
        
        $uploadDir = 'public/img/chat_media/';
        $fullUploadPath = dirname(URL_APP) . '/' . $uploadDir;

        if (!is_dir($fullUploadPath)) {
            mkdir($fullUploadPath, 0777, true);
        }
        $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
        $targetFile = $fullUploadPath . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $messageData = [
                'remitente_id' => $remitenteId,
                'destinatario_id' => $destinatarioId,
                'contenido' => null,
                'media_url' => $uploadDir . $fileName,
                'media_tipo' => 'imagen'
            ];
            $messageId = $this->mensajesModel->createMessage($messageData);
            if ($messageId) {
                $messageData['idMensaje'] = $messageId;
                $messageData['fechaMensaje'] = date('Y-m-d H:i:s');
                echo json_encode(['success' => true, 'message_data' => $messageData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar en la BD.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
        }
    }

    public function desbloquearChat()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $usuarioId = $_SESSION['logueando'];
        $creadoraId = (int)($data['creator_id'] ?? 0);

        if (empty($creadoraId)) {
            echo json_encode(['success' => false, 'message' => 'ID de creadora no válido.']);
            return;
        }
        
        $resultado = $this->mensajesModel->unlockChat($usuarioId, $creadoraId);
        echo json_encode($resultado);
    }
}