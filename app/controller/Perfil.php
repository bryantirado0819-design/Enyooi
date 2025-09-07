<?php

class Perfil extends Controller
{
    private $usuarioModel;
    private $publicarModel;
    private $likesModel;
    private $comentariosModel;

    public function __construct()
    {
        // Load necessary models cleanly
        $this->usuarioModel = $this->model('usuario');
        $this->publicarModel = $this->model('PublicarModel');
        $this->likesModel = $this->model('LikesModel');
        $this->comentariosModel = $this->model('comentariosModel');
    }

    public function index($username = '')
    {
        if (!isset($_SESSION['logueando']) || empty($username)) {
            redirection('/home/entrar');
        }
        
        // Get data for the user whose profile is being visited
        $usuarioVisitado = $this->usuarioModel->getUsuario($username);
        if (!$usuarioVisitado) {
            redirection('/home'); // Redirect home if the user doesn't exist
        }

        $idUsuarioVisitado = $usuarioVisitado->idUsuario;
        $idUsuarioLogueado = $_SESSION['logueando'];
        
        $datosPerfil = $this->usuarioModel->getPerfil($idUsuarioVisitado);
        $publicaciones = $this->publicarModel->getPublicacionesUsuario($idUsuarioVisitado);
        
        $stats = [
            'fotos' => $this->publicarModel->getMediaCountForUser($idUsuarioVisitado, 'imagen'),
            'videos' => $this->publicarModel->getMediaCountForUser($idUsuarioVisitado, 'video'),
            'likes' => $this->publicarModel->getTotalLikesForUserPosts($idUsuarioVisitado)
        ];
        
        // ✅ CORRECTION: The missing array keys are now included.
        $datos = [
            'perfil' => $datosPerfil, 
            'usuario' => $usuarioVisitado, 
            'publicaciones' => $publicaciones,
            'stats' => $stats, 
            'esPropietario' => ($idUsuarioLogueado == $idUsuarioVisitado),
            'likesModel' => $this->likesModel, 
            'comentariosModel' => $this->comentariosModel,
            
            // --- MISSING KEYS ADDED ---
            'puedeVerContenido' => true, // Placeholder logic: you can change this based on subscription status
            'isSubscribed' => false,     // Placeholder logic: you can change this based on subscription status
            'editProfileLink' => RUTA_URL . '/settings' // Dynamic link to the settings page
        ];

        $this->view('pages/perfil', $datos);
    }
    
    public function cambiarImagen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['logueando'])) {
            redirection('/home');
        }

        $idUsuario = $_SESSION['logueando'];
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
            // Corrected to use RUTA_PUBLIC for the physical path
            $directorioDestino = RUTA_PUBLIC . '/img/Fotos/';
            $rutaDestinoBD = 'img/Fotos/' . $nombreArchivo; // Relative path for the DB
            
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }
            $rutaCompleta = $directorioDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
                // We assume the PerfilModel has a method for editing the photo
                $perfilModel = $this->model('PerfilModel');
                if ($perfilModel->editarfoto(['idUsuario' => $idUsuario, 'ruta' => $rutaDestinoBD])) {
                    redirection('/perfil/' . $_SESSION['usuario']);
                } else {
                    die("Error updating the database.");
                }
            } else {
                die("Error moving the uploaded file.");
            }
        } else {
            redirection('/perfil/' . $_SESSION['usuario']);
        }
    }
}
