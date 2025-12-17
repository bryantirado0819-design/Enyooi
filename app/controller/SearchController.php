<?php

class SearchController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = $this->model('usuario');
    }

    /**
     * Endpoint para la búsqueda de usuarios en tiempo real.
     * Recibe una consulta 'q' y devuelve resultados en JSON.
     */
    public function users()
    {
        header('Content-Type: application/json');
        
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            echo json_encode(['success' => true, 'users' => []]);
            return;
        }

        $users = $this->usuarioModel->buscarUsuarios($query);

        echo json_encode(['success' => true, 'users' => $users]);
    }
}
