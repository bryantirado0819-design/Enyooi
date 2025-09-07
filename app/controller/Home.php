<?php
// session global para todo el controlador
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

class Home extends Controller
{
    private $usuarioModel;
    private $publicarModel;
    private $likesModel;
    private $comentariosModel; // ✅ Modelo añadido

    public function __construct()
    {
        // Unified models for clarity
        $this->usuarioModel = $this->model('Usuario');
        $this->publicarModel = $this->model('PublicarModel'); 
        $this->likesModel = $this->model('LikesModel'); 
        $this->comentariosModel = $this->model('comentariosModel'); // ✅ Modelo cargado
    }
    
    public function index()
    {
        if (empty($_SESSION['logueando'])) {
            header('Location: ' . RUTA_URL . '/home/entrar');
            exit;
        }
    
        $uid = (int) $_SESSION['logueando'];
        $datosUsuario = $this->usuarioModel->getUsuarioById($uid);
    
        if (!$datosUsuario) {
            session_destroy();
            header('Location: ' . RUTA_URL . '/home/entrar');
            exit;
        }
        
        // --- Onboarding logic ---
        $rol = strtolower(trim($datosUsuario->rol ?? ''));
        $onboarding = (int) ($datosUsuario->onboarding_creadora ?? 0);
    
        if ($rol === 'creadora' && $onboarding === 0) {
            header('Location: ' . RUTA_URL . '/home/creadora_onboarding');
            exit;
        }
    
        if ($rol === '' || $rol === 'usuario') {
            header('Location: ' . RUTA_URL . '/home/role_select');
            exit;
        }
        
        // --- Age Modal Logic ---
        $showAgeModal = false;
        if (!isset($_SESSION['age_verified'])) {
            $showAgeModal = true;
            $_SESSION['age_verified'] = true; 
        }
        
        // --- Final Data Preparation ---
        $publicaciones = $this->publicarModel->getPublicaciones();
        $misLikesIds = $this->likesModel->getLikedPostIdsByUser($uid);
        $informacionComentarios = $this->publicarModel->getInformacionComentarios();
        
        $comentariosAgrupados = [];
        if (is_array($informacionComentarios)) {
            foreach ($informacionComentarios as $comentario) {
                $comentariosAgrupados[$comentario->idPublicacion][] = $comentario;
            }
        }

        $datosParaVista = [
            'usuario'             => $datosUsuario,
            'perfil'              => $this->usuarioModel->getPerfil($uid),
            'publicaciones'       => $publicaciones,
            'comentarios'         => $comentariosAgrupados,
            'misLikes'            => $misLikesIds,
            'showAgeModal'        => $showAgeModal,
            'totalPublicaciones'  => $this->publicarModel->getPostCountForUser($uid),
            'totalLikesRecibidos' => $this->publicarModel->getTotalLikesForUserPosts($uid),
            // ✅ Se pasan los modelos a la vista por si son necesarios
            'likesModel'          => $this->likesModel,
            'comentariosModel'    => $this->comentariosModel
        ];

        $this->view('pages/home', $datosParaVista);
    }
    // ... REST OF THE METHODS (role_select, entrar, etc.) GO HERE ...
    // (No changes are needed for the other methods in Home.php)
    
    public function role_select()
    {
        if (empty($_SESSION['logueando'])) {
            header('Location: /ENYOOI/home/entrar');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid  = (int) $_SESSION['logueando'];
            $role = trim($_POST['role'] ?? '');

            if (!in_array($role, ['creadora', 'espectador'], true)) {
                $_SESSION['roleError'] = 'Rol inválido.';
                header('Location: /ENYOOI/home/role_select');
                exit;
            }

            $saved = $this->usuarioModel->actualizarRol($uid, $role);

            if (!$saved) {
                $_SESSION['roleError'] = 'No se pudo guardar el rol. Intenta de nuevo.';
                header('Location: /ENYOOI/home/role_select');
                exit;
            }

            $datosUsuario = $this->usuarioModel->getUsuarioById($uid);
            $_SESSION['rol'] = $datosUsuario->rol ?? $role;
            error_log("✅ Rol guardado como {$role} para user {$uid}");

            if ($role === 'creadora') {
                header('Location: /ENYOOI/home/creadora_onboarding');
                exit;
            }

            header('Location: /ENYOOI/home/espectador_onboarding');
            exit;
        }

        $this->view('pages/role_select');
    }
 
    public function creadora_onboarding()
    {
        if (empty($_SESSION['logueando'])) {
            header('Location: /ENYOOI/home/entrar');
            exit;
        }

        $uid = (int) $_SESSION['logueando'];
        $datosUsuario = $this->usuarioModel->getUsuarioById($uid);

        $this->view('pages/creadora_onboarding', [
            'usuario' => $datosUsuario
        ]);
    }

    public function espectador_onboarding()
    {
        if (empty($_SESSION['logueando'])) {
            header('Location: /ENYOOI/home/entrar');
            exit;
        }

        $uid = (int) $_SESSION['logueando'];
        $datosUsuario = $this->usuarioModel->getUsuarioById($uid);

        $this->view('pages/espectador_onboarding', [
            'usuario' => $datosUsuario
        ]);
    }

    public function save_onboarding()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['logueando'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'unauthorized']);
            exit;
        }

        $uid = (int) $_SESSION['logueando'];
        $datosUsuario = $this->usuarioModel->getUsuarioById($uid);

        if (!$datosUsuario || $datosUsuario->rol !== 'creadora') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'role_not_creator']);
            exit;
        }

        $nickname = trim($_POST['nickname'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $metodo   = trim($_POST['pago'] ?? 'transferencia');
        
        if (empty($nickname)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'nickname_required']);
            exit;
        }

        $nickname = mb_substr($nickname, 0, 60);
        $allowedMethods = ['transferencia', 'stripe', 'paypal'];
        if (!in_array($metodo, $allowedMethods, true)) {
            $metodo = 'transferencia';
        }

        $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/ENYOOI/public/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $save_upload = function ($field, $prefix) use ($uploadDir, $uid) {
            if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($_FILES[$field]['tmp_name']);
            $ext   = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => ''
            };
            if ($ext === '') return '';
            $name = $prefix . '_' . $uid . '_' . time() . '.' . $ext;
            $dest = $uploadDir . $name;
            return move_uploaded_file($_FILES[$field]['tmp_name'], $dest) ? '/ENYOOI/public/uploads/' . $name : '';
        };

        $foto   = $save_upload('foto', 'foto');
        $banner = $save_upload('banner', 'banner');
        $doc    = $save_upload('documento', 'doc');

        if ($foto === '') {
            $foto = 'public/img/defaults/default_avatar.png';
        }

        $onboardingGuardado = $this->usuarioModel->guardarOnboardingCreadora($uid, $nickname, $bio, $metodo, $foto, $banner, $doc);

        if ($onboardingGuardado) {
            $_SESSION['onboarding_creadora'] = 1;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['foto_perfil'] = $foto;
            echo json_encode(['ok' => true, 'redirect' => '/ENYOOI/home']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'db_save_failed']);
        }
        exit;
    }

    public function save_espectador()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['logueando'])) {
            header('Location: /ENYOOI/home/entrar');
            exit;
        }

        $uid = (int) $_SESSION['logueando'];

        $this->usuarioModel->actualizarRol($uid, 'espectador');

        $nickname = trim($_POST['nickname'] ?? '');
        if ($nickname === '') {
            $nickname = 'user' . substr(bin2hex(random_bytes(3)), 0, 6);
        }
        $nickname = mb_substr($nickname, 0, 60);

        if ($this->usuarioModel->guardarOnboardingEspectador($uid, $nickname)) {
            $_SESSION['nickname']    = $nickname;
            $_SESSION['foto_perfil'] = 'public/img/defaults/default_avatar.png';
            header('Location: /ENYOOI/home');
        } else {
            $_SESSION['roleError'] = 'Hubo un problema al guardar tu perfil. Inténtalo de nuevo.';
            header('Location: /ENYOOI/home/role_select');
        }
        exit;
    }
    
    public function entrar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            error_log('Login form submitted');

            $datosLogin = [
                'usuario'    => trim($_POST['usuario']),
                'contrasena' => trim($_POST['contrasena'])
            ];

            $datosUsuario = $this->usuarioModel->getUsuario($datosLogin['usuario']);

            if ($datosUsuario) {
                if ($this->usuarioModel->verificarContrasena($datosUsuario, $datosLogin['contrasena'])) {
                    $_SESSION['logueando'] = $datosUsuario->idUsuario;
                    $_SESSION['usuario']   = $datosUsuario->usuario;

                    error_log("Login successful for user: " . $_SESSION['usuario']);
                    header('Location: /ENYOOI/home');
                    exit();
                } else {
                    $_SESSION['errorLogin'] = 'El usuario o la contraseña son incorrectos';
                    error_log("Incorrect password for user: " . $datosLogin['usuario']);
                    $this->view('pages/login');
                }
            } else {
                $_SESSION['errorLogin'] = 'El usuario no existe';
                error_log("User not found: " . $datosLogin['usuario']);
                $this->view('pages/login');
            }
        } else {
            error_log('Login form displayed');
            $this->view('pages/login');
        }
    }

    public function registrar()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            if (isset($_SESSION['logueando'])) {
                header('Location: /ENYOOI/home');
                exit();
            }
            $this->view('pages/register');
            return;
        }

        $correo     = trim($_POST['email'] ?? '');
        $usuario    = trim($_POST['usuario'] ?? '');
        $rawPass    = trim($_POST['contrasena'] ?? '');
        $contrasena = password_hash($rawPass, PASSWORD_DEFAULT);
        $cedula     = trim($_POST['cedula'] ?? '');
        $fecha_nac  = $_POST['fecha_nacimiento'] ?? '';
        $genero     = $_POST['genero'] ?? '';
        $ciudad     = trim($_POST['ciudad'] ?? '');
        $pais       = $_POST['pais'] ?? '';
        $documento  = $_FILES['documento'] ?? null;

        if (empty($correo) || empty($usuario) || empty($rawPass) || empty($fecha_nac) || empty($genero) || empty($ciudad) || empty($pais)) {
            $_SESSION['usuarioError'] = "Todos los campos son obligatorios.";
            $this->view('pages/register');
            return;
        }

        try {
            $fn = new DateTime($fecha_nac);
            $edad = (new DateTime())->diff($fn)->y;
            if ($edad < 18) {
                $_SESSION['menorEdad'] = true;
                $this->view('pages/register');
                return;
            }
        } catch (\Exception $e) {
            $_SESSION['usuarioError'] = "Fecha de nacimiento inválida.";
            $this->view('pages/register');
            return;
        }

        if ($pais === "Ecuador") {
            if (!$this->validarCedulaEcuador($cedula)) {
                $_SESSION['usuarioError'] = "Número de cédula ecuatoriana inválido.";
                $this->view('pages/register');
                return;
            }
        } else {
            if (empty($documento['name'])) {
                $_SESSION['usuarioError'] = "Debe subir la imagen de su documento si no es de Ecuador.";
                $this->view('pages/register');
                return;
            }
        }

        $rutaDocumento = null;
        if ($pais !== "Ecuador" && !empty($documento['name'])) {
            $carpeta = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/ENYOOI/public/img/documentos/';
            if (!is_dir($carpeta) && !mkdir($carpeta, 0777, true)) {
                $_SESSION['usuarioError'] = "Error en el servidor al subir el documento.";
                $this->view('pages/register'); return;
            }
            $nombre  = basename($documento['name']);
            $destino = $carpeta . $nombre;
            if (!move_uploaded_file($documento['tmp_name'], $destino)) {
                $_SESSION['usuarioError'] = "Error al subir el documento.";
                $this->view('pages/register'); return;
            }
            $rutaDocumento = 'img/documentos/' . $nombre;
        }

        $userExists  = $this->usuarioModel->verificarUsuario($usuario);
        $emailExists = $this->usuarioModel->verificarCorreo($correo);

        if ($userExists) {
            $_SESSION['usuarioError'] = 'El usuario no está disponible, intenta con otro nombre de usuario.';
            $this->view('pages/register'); return;
        }
        if ($emailExists) {
            $_SESSION['usuarioError'] = 'El correo ya está registrado. Intenta con otro.';
            $this->view('pages/register'); return;
        }

        $datosRegistro = [
            'rol'        => 'usuario',
            'correo'     => $correo,
            'usuario'    => $usuario,
            'contrasena' => $contrasena,
            'cedula'     => $cedula,
            'fecha_nac'  => $fecha_nac,
            'genero'     => $genero,
            'ciudad'     => $ciudad,
            'pais'       => $pais,
            'documento'  => $rutaDocumento
        ];

        if ($this->usuarioModel->registrar($datosRegistro)) {
            $_SESSION['loginComplete'] = 'Tu registro se ha completado satisfactoriamente';
            header('Location: /ENYOOI/home/entrar'); exit();
        } else {
            $_SESSION['usuarioError'] = "Error interno al registrar.";
            $this->view('pages/register'); return;
        }
    }

    private function validarCedulaEcuador($cedula) {
        if (strlen($cedula) != 10 || !ctype_digit($cedula)) return false;
        $digito_provincia = (int)substr($cedula, 0, 2);
        if ($digito_provincia < 1 || $digito_provincia > 24) return false;
        $coef = [2,1,2,1,2,1,2,1,2];
        $suma = 0;
        for ($i=0; $i<9; $i++) {
            $res = $coef[$i] * intval($cedula[$i]);
            if ($res >= 10) $res -= 9;
            $suma += $res;
        }
        $digito_verificador = (10 - ($suma % 10)) % 10;
        return $digito_verificador == intval($cedula[9]);
    }

    public function insertarRegistrosPerfil()
    {
        $carpeta = 'C:/xampp/htdocs/ENYOOI/public/img/Fotos/';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreArchivo = basename($_FILES['imagen']['name']);
        $rutaImagen = 'img/Fotos/' . $nombreArchivo;
        $ruta = $carpeta . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
            $datos = [
                'idusuario' => trim($_POST['id_user']),
                'nombre'    => trim($_POST['nombre']),
                'ruta'      => $rutaImagen
            ];

            if ($this->usuarioModel->insertarPerfil($datos)) {
                header('Location: /ENYOOI/home');
                exit();
            } else {
                echo "Error al insertar el perfil en la base de datos.";
            }
        } else {
            echo "Error al subir la imagen.";
        }
    }

    public function salir()
    {
        $_SESSION = [];
        session_destroy();
        header('Location: /ENYOOI/home/entrar');
        exit();
    }
}