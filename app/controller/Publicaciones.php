<?php

class Publicaciones extends Controller
{
    private $publicarModel;
    private $notificacionModel;
    private $likesModel;
    private $comentariosModel;
    private $levelModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando'])) {
            redirection('/home/entrar');
        }
        
        $this->publicarModel = $this->model('PublicarModel'); 
        $this->notificacionModel = $this->model('notificacion');
        $this->likesModel = $this->model('LikesModel');
        $this->comentariosModel = $this->model('comentariosModel');
        $this->levelModel = $this->model('LevelModel');
    }

    private function notifySocketServer($endpoint, $data) {
        if (!defined('SOCKET_URL')) {
            error_log("ADVERTENCIA: La constante SOCKET_URL no está definida.");
            return;
        }
        $socketUrl = SOCKET_URL . $endpoint;
        $ch = curl_init($socketUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_exec($ch);
        if(curl_errno($ch)){
            error_log('Error de cURL al notificar al socket server: ' . curl_error($ch));
        }
        curl_close($ch);
    }
    /**
     * Tu método para publicar un post.
     * Se mantiene SIN CAMBIOS, ya que gestiona una subida de formulario tradicional.
     */
    public function publicar($idUsuario)
    {
        $tipoArchivo = 'texto';
        $rutaRelativaParaBD = 'sin archivo'; 

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = time() . '_' . basename($_FILES['archivo']['name']);
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            $tiposImagen = ['jpg', 'jpeg', 'png', 'gif'];
            $tiposVideo = ['mp4', 'mov', 'avi', 'webm'];

            if (in_array($extension, $tiposImagen)) {
                $tipoArchivo = 'imagen';
            } elseif (in_array($extension, $tiposVideo)) {
                $tipoArchivo = 'video';
            } else {
                redirection('/home'); 
                return;
            }

            $directorioDestino = RUTA_PUBLIC . '/img/Imagenes_Publicaciones/';

            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }
            
            $rutaCompletaDestino = $directorioDestino . $nombreArchivo;

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompletaDestino)) {
                $rutaRelativaParaBD = RUTA_URL . '/img/Imagenes_Publicaciones/' . $nombreArchivo;
            } else {
                error_log("Error al mover el archivo subido.");
                redirection('/home');
                return;
            }
        }

        $datos = [
            'iduser' => trim($idUsuario),
            'contenido' => trim($_POST['contenido']),
            'foto' => $rutaRelativaParaBD,
            'tipo_archivo' => $tipoArchivo,
        ];
        
        if ($this->publicarModel->crearPublicacion($datos)) {
            redirection('/home');
        } else {
            echo 'Algo ocurrió al publicar.';
        }
    }
    
    /**
     * Tu método para eliminar una publicación.
     * Se mantiene TU LÓGICA ORIGINAL para borrar el archivo físico.
     * No necesita notificar por socket, ya que la UI se actualiza solo para quien borra.
     */
    public function eliminar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        $idpublicacion = $data->id ?? null;

        if (!$idpublicacion) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de publicación no proporcionado.']);
            return;
        }

        $publicacion = $this->publicarModel->getPublicacionById($idpublicacion);

        if (!$publicacion || $publicacion->idUsuarioPublico != $_SESSION['logueando']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado para eliminar.']);
            return;
        }

        // --- LÓGICA CRÍTICA CONSERVADA ---
        if ($publicacion->fotoPublicacion && $publicacion->fotoPublicacion != 'sin archivo') {
            // Convierte la URL pública a una ruta de archivo local
            $rutaArchivoFisico = str_replace(RUTA_URL, rtrim(RUTA_PUBLIC, '/'), $publicacion->fotoPublicacion);
            if (file_exists($rutaArchivoFisico)) {
                @unlink($rutaArchivoFisico);
            }
        }
        // --- FIN DE LÓGICA CONSERVADA ---

        if ($this->publicarModel->eliminarPublicacion($idpublicacion, $_SESSION['logueando'])) {
            echo json_encode(['success' => true, 'message' => 'Publicación eliminada.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la publicación de la base de datos.']);
        }
    }


    public function darLike()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $idPublicacion = filter_var($input['idPublicacion'] ?? null, FILTER_VALIDATE_INT);
        $idUsuario = $_SESSION['logueando'];

        if (!$idPublicacion) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $propietario = $this->publicarModel->getPublicacionOwner($idPublicacion);
        if (!$propietario) {
             http_response_code(404);
             echo json_encode(['success' => false, 'message' => 'Publicación no encontrada.']);
             return;
        }
        $idPropietario = $propietario->idUsuario;

        $liked = $this->likesModel->toggleLike($idPublicacion, $idUsuario, $idPropietario);
        $nuevoConteoLikes = $this->likesModel->getLikeCount($idPublicacion);

        if ($liked && $idPropietario != $idUsuario) {
            // ✅ CORRECCIÓN: Usando el nombre de método correcto 'crearNotificacion'
            $this->notificacionModel->crearNotificacion([
                'idUsuario' => $idPropietario,
                'tipoNotificacion' => 1, // 1 = Like
                'usuarioAccion' => $idUsuario,
                'idPublicacion' => $idPublicacion
            ]);
            // --- ¡AQUÍ OTORGAMOS XP! ---
            $this->levelModel->addXpAndLevelUp($idPropietario, 1); // +1 XP por like
        }

        $this->notifySocketServer('/notify/like', [
            'postId' => $idPublicacion, 'newLikeCount' => $nuevoConteoLikes
        ]);

        echo json_encode(['success' => true]);
    }

     public function agregarComentario()
    {
        // Establecer el tipo de contenido desde el inicio.
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar si el JSON es válido
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido recibido.');
            }

            $idPublicacion = filter_var($input['idPublicacion'] ?? null, FILTER_VALIDATE_INT);
            $contenido = trim($input['contenido'] ?? '');
            $idUsuario = $_SESSION['logueando'];

            if (!$idPublicacion || empty($contenido)) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Datos incompletos: se requiere idPublicacion y contenido.']);
                return;
            }

            $propietario = $this->publicarModel->getPublicacionOwner($idPublicacion);
            if (!$propietario) {
                http_response_code(404); // Not Found
                echo json_encode(['success' => false, 'message' => 'La publicación no fue encontrada.']);
                return;
            }
            $idPropietario = $propietario->idUsuario;

            $datosComentario = [
                'idpublicacion' => $idPublicacion,
                'iduser' => $idUsuario,
                'comentario' => $contenido
            ];
            
            $idComentario = $this->comentariosModel->agregarComentario($datosComentario);

            if ($idComentario) {
                if ($idPropietario != $idUsuario) {
                     $this->notificacionModel->crearNotificacion([
                        'idUsuario' => $idPropietario,
                        'tipoNotificacion' => 2, // 2 = Comentario
                        'usuarioAccion' => $idUsuario,
                        'idPublicacion' => $idPublicacion
                     ]);
                     // --- ¡AQUÍ OTORGAMOS XP! ---
                 $this->levelModel->addXpAndLevelUp($idPropietario, 3);
                }
               
                $nuevoComentario = $this->comentariosModel->getComentarioById($idComentario);
                $nuevoConteoComentarios = $this->comentariosModel->getCommentCount($idPublicacion);
                
                $this->notifySocketServer('/notify/comment', [
                    'postId' => $idPublicacion,
                    'newComment' => $nuevoComentario,
                    'newCommentCount' => $nuevoConteoComentarios
                ]);
                
                // Respuesta exitosa
                echo json_encode(['success' => true, 'comment_id' => $idComentario]);

            } else {
                // Error controlado de base de datos
                http_response_code(500); // Internal Server Error
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar el comentario en la base de datos.']);
            }

        } catch (Exception $e) {
            // CAPTURA CUALQUIER OTRO ERROR INESPERADO
            error_log('Error en agregarComentario: ' . $e->getMessage()); // Guardar el error real para ti
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.']);
        }
    }



    
}