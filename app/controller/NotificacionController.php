<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

class NotificacionController extends Controller
{
    private $notificacionModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando'])) {
            // Si es una petición de API, respondemos con JSON, si no, redirigimos.
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'No autenticado.']);
                exit;
            } else {
                redirection('/home/entrar');
            }
        }
        $this->notificacionModel = $this->model('NotificacionModel');
    }

    /**
     * Endpoint API para obtener las notificaciones del dropdown.
     */
    public function dropdown()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $idUsuario = $_SESSION['logueando'];
        // ✅ SOLUCIÓN: Corregido de '.' a '->'
        $notificaciones = $this->notificacionModel->getRecentNotifications($idUsuario, 7);
        
        // Marcamos todas las notificaciones como leídas al abrir el dropdown
        // ✅ SOLUCIÓN: Corregido de '.' a '->'
        $this->notificacionModel->markAllAsRead($idUsuario);

        echo json_encode(['success' => true, 'notifications' => $notificaciones]);
    }
    
    /**
     * Muestra la página con todas las notificaciones.
     */
    public function verTodas()
    {
        $idUsuario = $_SESSION['logueando'];
        // ✅ SOLUCIÓN: Corregido de '.' a '->'
        $todasLasNotificaciones = $this->notificacionModel->getAllNotifications($idUsuario);
        
        $datos = [
            'notificaciones' => $todasLasNotificaciones
        ];
        
        // ✅ SOLUCIÓN: Corregido de '.' a '->'
        $this->view('pages/notificaciones', $datos);
    }

    /**
     * Endpoint API para obtener el conteo de no leídas.
     * Útil para el socket.
     */
    public function countUnread()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = $_SESSION['logueando'];
        // ✅ SOLUCIÓN: Corregido de '.' a '->'
        $count = $this->notificacionModel->countUnread($idUsuario);
        echo json_encode(['success' => true, 'count' => $count]);
    }
}

