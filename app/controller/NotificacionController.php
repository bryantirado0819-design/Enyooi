<?php
// app/controller/NotificacionController.php

class NotificacionController extends Controller
{
    private $notificacionModel;

    public function __construct()
    {
        $this->notificacionModel = $this->model('NotificacionModel');
    }

    public function dropdown()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['logueando'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        $idUsuario = $_SESSION['logueando'];
        $notificaciones = $this->notificacionModel->obtenerNotificaciones($idUsuario);
        
        // Marcamos las notificaciones como leídas
        $this->notificacionModel->marcarComoLeidas($idUsuario);

        echo json_encode(['success' => true, 'notifications' => $notificaciones]);
    }
}