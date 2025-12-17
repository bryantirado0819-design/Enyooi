<?php

class SetRoleController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('UserModel');
    }

    public function index()
    {
        // Verificar si hay sesión
        if (!isset($_SESSION['id_usuario'])) {
            redirection('home/login');
            return;
        }

        // Cargar vista
        $this->view('pages/role_select');
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Validar sesión
            if (!isset($_SESSION['id_usuario'])) {
                redirection('home/login');
                return;
            }

            $userId = $_SESSION['id_usuario'];
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';

            // Validar roles permitidos
            $allowedRoles = ['creadora', 'espectador'];
            if (!in_array($role, $allowedRoles)) {
                // Error: rol no válido
                redirection('setRoleController'); 
                return;
            }

            // Actualizar en BD
            if ($this->userModel->updateUserRole($userId, $role)) {
                // Actualizar sesión
                $_SESSION['rol'] = $role;

                // Redirección SEGURA: Usar solo el nombre del controlador/metodo
                if ($role === 'creadora') {
                    // IMPORTANTE: No poner 'ENYOOI/' al principio
                    redirection('CreatorDashboardController'); 
                } else {
                    redirection('home');
                }
            } else {
                die('Error al guardar el rol');
            }
        }
    }
}
?>