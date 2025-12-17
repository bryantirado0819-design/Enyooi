<?php

class SuscripcionController extends Controller
{
    private $suscripcionModel;
     private $levelModel;

    public function __construct()
    {
        // La validación de sesión se hará en el método para respuestas de API limpias.
        $this->suscripcionModel = $this->model('SuscripcionModel');
        $this->levelModel = $this->model('LevelModel');
    }

    /**
     * Envía una respuesta JSON y detiene la ejecución de forma segura.
     * Esta es la versión blindada que nos protegerá del error.
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        // ✅ LA BALA DE PLATA: ob_end_clean()
        // Esta función busca cualquier salida de texto que se haya generado
        // (como el "Controlado...") y la BORRA por completo antes de continuar.
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header_remove(); // Reinicia cualquier header que se haya enviado por error.
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit; // Detiene el script para garantizar que nada más se envíe.
    }

    public function suscribirse($idCreadora)
    {
        // 1. Validar que el usuario haya iniciado sesión.
        if (!isset($_SESSION['logueando'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión para suscribirte.'], 401);
        }

        // 2. Validar que se use el método POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
        }

        // 3. Ejecutar la lógica de negocio.
        $idSuscriptor = $_SESSION['logueando'];
        $resultado = $this->suscripcionModel->crearSuscripcion((int)$idSuscriptor, (int)$idCreadora);

        if ($resultado['success']) {
            // --- ¡AQUÍ OTORGAMOS XP! ---
            $this->levelModel->addXpAndLevelUp((int)$idCreadora, 50); // +50 XP por nuevo suscriptor
            // --- FIN DEL CAMBIO ---
            $this->jsonResponse($resultado, 200);
        } else {
            $this->jsonResponse($resultado, 400);
        }

        
    }
}
