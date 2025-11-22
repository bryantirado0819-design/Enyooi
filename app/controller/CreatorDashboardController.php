<?php
// app/controller/CreatorDashboardController.php

class CreatorDashboardController extends Controller
{
    private $dashboardModel; // Única dependencia del modelo

    public function __construct()
    {
        if (!isset($_SESSION['logueando']) || ($_SESSION['rol'] ?? '') !== 'creadora') {
            redirection('/home');
        }
        // Cargamos nuestro nuevo y único modelo para este controlador
        $this->dashboardModel = $this->model('DashboardModel');
    }

    /**
     * Envía una respuesta JSON limpia y segura.
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        if (ob_get_level() > 0) ob_end_clean();
        header_remove();
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Carga la vista principal del dashboard (sin cambios)
    public function index()
    {
        $this->view('pages/creator_dashboard');
    }

    // Endpoint de API para obtener todos los datos del dashboard
    public function getDashboardData()
    {
        try {
            $creatorId = $_SESSION['logueando'];
            
            // ✅ ¡Llamada única y limpia a nuestro nuevo modelo!
            $dashboardData = $this->dashboardModel->getFullDashboardData($creatorId);

            // Añadimos datos que no vienen de la BD
            $dashboardData['success'] = true;
            $dashboardData['creatorName'] = $_SESSION['usuario'] ?? 'Creadora';

            $this->jsonResponse($dashboardData);

        } catch (Exception $e) {
            error_log("Error en getDashboardData: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al obtener los datos del dashboard.'], 500);
        }
    }
}