<?php
class SetRoleController extends Controller
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = $this->model('Usuario');
    }

    // Muestra la vista para seleccionar rol
    public function index()
    {
        if (!isset($_SESSION['logueando'])) {
            header('Location: ' . URL . 'login');
            exit;
        }

        $this->view('onboarding/set_role');
    }

    // Procesa y redirige según el rol
    public function saveRole()
    {
        if (!isset($_SESSION['logueando'])) {
            header('Location: ' . URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid  = $_SESSION['logueando'];
            $role = $_POST['role'] ?? null;

            if ($role && in_array($role, ['creadora', 'espectador'])) {
                $this->usuario->setRole($uid, $role);

                if ($role === 'creadora') {
                    header('Location: ' . URL . 'creadora_onboarding');
                } else {
                    header('Location: ' . URL . 'espectador_onboarding');
                }
                exit;
            }
        }

        // Si algo falla, vuelve al selector
        header('Location: ' . URL . 'setrole');
        exit;
    }
}
