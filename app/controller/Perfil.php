<?php

class Perfil extends Controller
{
    private $usuarioModel;
    private $publicarModel;
    private $suscripcionModel;
    private $contenidoModel;

    public function __construct()
    {
        // ✅ SOLUCIÓN: Cargando los modelos con los nombres exactos de tus archivos.
        $this->usuarioModel = $this->model('usuario');
        $this->publicarModel = $this->model('publicar'); 
        $this->suscripcionModel = $this->model('SuscripcionModel');
        $this->contenidoModel = $this->model('ContenidoModel');
    }

    public function index($username = '')
    {
        if (!isset($_SESSION['logueando']) || empty($username)) {
            redirection('/home/entrar');
        }
        
        $usuarioVisitado = $this->usuarioModel->getUsuario($username);
        if (!$usuarioVisitado) {
            redirection('/home');
        }

        $idUsuarioVisitado = $usuarioVisitado->idUsuario;
        $idUsuarioLogueado = $_SESSION['logueando'];
        
        $esPropietario = ($idUsuarioLogueado == $idUsuarioVisitado);

        $isSubscribed = $this->suscripcionModel->verificarSuscripcion($idUsuarioLogueado, $idUsuarioVisitado);
        $puedeVerContenido = $esPropietario || $isSubscribed;
        $publicacionesDesbloqueadas = $this->contenidoModel->getContenidoDesbloqueadoPorUsuario($idUsuarioLogueado);
        $perfilLogueado = $this->usuarioModel->getPerfil($idUsuarioLogueado);

        // Obtenemos los IDs de las publicaciones que le gustan al usuario logueado
        $misLikesObj = $this->usuarioModel->misLikes($idUsuarioLogueado);
        $misLikesIds = [];
        foreach ($misLikesObj as $like) {
            $misLikesIds[] = $like->idPublicacion;
        }

        $stats = [
            'fotos' => $this->publicarModel->getMediaCountForUser($idUsuarioVisitado, 'imagen'),
            'videos' => $this->publicarModel->getMediaCountForUser($idUsuarioVisitado, 'video'),
            'likes' => $this->publicarModel->getTotalLikesForUserPosts($idUsuarioVisitado)
        ];
        
        $datos = [
            'perfil' => $this->usuarioModel->getPerfil($idUsuarioVisitado), 
            'usuario' => $usuarioVisitado, 
            'publicaciones' => $this->publicarModel->getPublicacionesUsuario($idUsuarioVisitado),
            'stats' => $stats, 
            'esPropietario' => $esPropietario,
            'likesModel' => $this->publicarModel, 
            'comentariosModel' => $this->publicarModel,
            'puedeVerContenido' => $puedeVerContenido,
            'isSubscribed' => $isSubscribed,
            'publicacionesDesbloqueadas' => $publicacionesDesbloqueadas,
            'editProfileLink' => RUTA_URL . '/settings',
            'misLikes' => $misLikesIds,
            'user_avatar' => $perfilLogueado->foto_perfil ?? 'public/img/defaults/default_avatar.png'
        ];

        $this->view('pages/perfil', $datos);
    }
    
    // ... (El resto de tu controlador se mantiene igual)
    public function cambiarImagen()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['logueando'])) {
            redirection('/home');
        }

        $idUsuario = $_SESSION['logueando'];
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
            $directorioDestino = RUTA_PUBLIC . '/img/Fotos/';
            $rutaDestinoBD = 'img/Fotos/' . $nombreArchivo;
            
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }
            $rutaCompleta = $directorioDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
                $perfilModel = $this->model('PerfilModel');
                if ($perfilModel->editarfoto(['idUsuario' => $idUsuario, 'ruta' => $rutaDestinoBD])) {
                    redirection('/perfil/' . $_SESSION['usuario']);
                } else {
                    die("Error al actualizar la base de datos.");
                }
            } else {
                die("Error al mover el archivo subido.");
            }
        } else {
            redirection('/perfil/' . $_SESSION['usuario']);
        }
    }
}