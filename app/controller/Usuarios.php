<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

class Usuarios extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        // Cargamos el modelo de usuario que ya tenemos
        $this->usuarioModel = $this->model('Usuario');
    }

    // Este es el método que se ejecuta por defecto al ir a /usuarios
    public function index()
    {
        // Verificamos si el usuario ha iniciado sesión
        if (!isset($_SESSION['logueando'])) {
            redirection('/home/entrar');
            return;
        }

        // ✅ AHORA LLAMAMOS AL MÉTODO CORRECTO DEL MODELO
        $listaCreadoras = $this->usuarioModel->getCreators();

        // Preparamos los datos para enviarlos a la vista
        $datos = [
            'creadoras' => $listaCreadoras
        ];

        // Cargamos la vista que mostrará la lista
        $this->view('pages/usuarios', $datos);
    }
}