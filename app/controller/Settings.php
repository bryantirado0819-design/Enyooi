<?php
require_once '../app/helpers/mailer_helper.php';

class Settings extends Controller
{
    private $settingsModel;
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['logueando'])) {
            redirection('/home/entrar');
        }
        $this->settingsModel = $this->model('SettingsModel');
        $this->usuarioModel = $this->model('Usuario'); 
    }

    public function index()
    {
        $idUsuario = $_SESSION['logueando'];
        $datos = [
            'usuario' => $this->usuarioModel->getUsuarioById($idUsuario),
            'perfil' => $this->usuarioModel->getPerfil($idUsuario)
        ];
        $this->view('pages/settings', $datos);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idUsuario = $_SESSION['logueando'];
            $usuarioActual = $this->usuarioModel->getUsuarioById($idUsuario)->usuario;
            $nuevoPrecioChat = (int)($_POST['chat_precio'] ?? 0);
            $nuevoNickname = trim($_POST['nickname_artistico']);
            $nuevoUsuario = trim($_POST['usuario']);
            $nuevaBio = trim($_POST['bio']);

            if (empty($nuevoNickname) || empty($nuevoUsuario)) {
                 $_SESSION['settings_error'] = 'El nickname y el nombre de usuario son obligatorios.';
                 redirection('/settings');
                 return;
            }

            if ($nuevoUsuario != $usuarioActual && $this->usuarioModel->verificarUsuario($nuevoUsuario)) {
                $_SESSION['settings_error'] = 'Ese nombre de usuario ya está en uso. Por favor, elige otro.';
                redirection('/settings');
                return;
            }

            $datosUpdate = [
                'id' => $idUsuario,
                'nickname' => $nuevoNickname,
                'usuario' => $nuevoUsuario,
                'bio' => $nuevaBio,
                'chat_precio' => $nuevoPrecioChat
            ];

            if ($this->settingsModel->updateProfileData($datosUpdate)) {
                $_SESSION['usuario'] = $nuevoUsuario;
                $_SESSION['settings_success'] = '¡Tu perfil ha sido actualizado con éxito!';
            } else {
                $_SESSION['settings_error'] = 'Ocurrió un error al actualizar tu perfil.';
            }
            redirection('/settings');
        }
    }

    public function sendVerificationCode()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $idUsuario = $_SESSION['logueando'];
        $usuario = $this->usuarioModel->getUsuarioById($idUsuario);
        $type = $_POST['type'];
        $newValue = $_POST['value'] ?? null;
        $code = rand(100000, 999999);
        $emailSubject = '';
        $emailRecipient = $usuario->correo;
        $message = '';

        switch ($type) {
            case 'email_change':
                $newValue = trim($_POST['value'] ?? '');
                if (!filter_var($newValue, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'El formato del correo no es válido.']); return;
                }
                if ($this->usuarioModel->verificarCorreo($newValue)) {
                    echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado.']); return;
                }
                $emailSubject = 'Verifica tu nuevo correo en Enyooi';
                $emailRecipient = $newValue;
                $message = "Se envió un código de verificación a $emailRecipient.";
                break;
            
            case 'password_reset':
                 if (empty($_POST['new_password']) || strlen($_POST['new_password']) < 6) {
                    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']); return;
                }
                 if (!$this->usuarioModel->verificarContrasena($usuario, $_POST['current_password'])) {
                    echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']); return;
                }
                $newValue = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $emailSubject = 'Código para cambiar tu contraseña';
                $message = "Se envió un código de verificación a tu correo actual.";
                break;

            case 'account_verify':
                $emailSubject = 'Verifica tu cuenta de Enyooi';
                $message = "Se envió un código de verificación a tu correo: " . htmlspecialchars($usuario->correo);
                break;
        }

        if ($this->settingsModel->storeVerificationCode($idUsuario, $code, $type, $newValue)) {
            $emailSent = sendVerificationEmail($emailRecipient, $emailSubject, $code);
           
            if ($emailSent) {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                 echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo de verificación. Revisa la configuración del servidor.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo generar el código en la base de datos.']);
        }
    }
    
    public function verifyAndUpdate() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $idUsuario = $_SESSION['logueando'];
        $code = trim($_POST['code']);
        $type = trim($_POST['type']);

        $verification = $this->settingsModel->getVerificationCode($idUsuario, $code, $type);

        if ($verification) {
            $success = false;
            if ($type === 'email_change') {
                $success = $this->settingsModel->updateEmail($idUsuario, $verification->new_value);
            } elseif ($type === 'password_reset') {
                $success = $this->settingsModel->updatePassword($idUsuario, $verification->new_value);
            } elseif ($type === 'account_verify') {
                $success = $this->settingsModel->markAccountAsVerified($idUsuario);
            }

            if($success) {
                $this->settingsModel->deleteVerificationCode($verification->id);
                echo json_encode(['success' => true, 'message' => '¡Verificación exitosa! Tu información ha sido actualizada.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la información después de la verificación.']);
            }
        } else {
             echo json_encode(['success' => false, 'message' => 'El código es incorrecto o ha expirado.']);
        }
    }

    public function updateProfileImage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $idUsuario = $_SESSION['logueando'];
            $rutaImagen = $this->uploadImage($_FILES['profile_image'], 'Fotos');

            if ($rutaImagen) {
                $this->settingsModel->updateProfileImage($idUsuario, $rutaImagen);
                $_SESSION['settings_success'] = '¡Foto de perfil actualizada!';
            } else {
                $_SESSION['settings_error'] = 'Error al subir la nueva foto de perfil. Verifica el formato y tamaño.';
            }
             redirection('/settings');
        } else {
            $_SESSION['settings_error'] = 'No se seleccionó ninguna imagen o hubo un error en la subida.';
            redirection('/settings');
        }
    }

    public function updateBannerImage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $idUsuario = $_SESSION['logueando'];
            $rutaImagen = $this->uploadImage($_FILES['banner_image'], 'Fotos');

            if ($rutaImagen) {
                $this->settingsModel->updateBannerImage($idUsuario, $rutaImagen);
                $_SESSION['settings_success'] = '¡Tu banner ha sido actualizado!';
            } else {
                $_SESSION['settings_error'] = 'Error al subir el nuevo banner. Verifica el formato y tamaño.';
            }
             redirection('/settings');
        } else {
            $_SESSION['settings_error'] = 'No se seleccionó ninguna imagen o hubo un error en la subida.';
            redirection('/settings');
        }
    }

    private function uploadImage($file, $subfolder) {
        $uploadDir = 'public/img/' . $subfolder . '/';
        $fullUploadDir = dirname(URL_APP) . '/' . $uploadDir;
    
        if (!is_dir($fullUploadDir)) {
            if (!mkdir($fullUploadDir, 0777, true)) {
                error_log("No se pudo crear el directorio: " . $fullUploadDir);
                return false;
            }
        }
    
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $newFileName = uniqid('', true) . '.' . $imageFileType;
        $targetFile = $fullUploadDir . $newFileName;
    
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedTypes) || $file['size'] > 5000000) { // Límite de 5MB
            return false;
        }
    
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $uploadDir . $newFileName;
        }
    
        return false;
    }
}