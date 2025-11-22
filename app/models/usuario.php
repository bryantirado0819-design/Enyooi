<?php



require_once __DIR__ . '/../libs/Base.php';
class Usuario {
    
    private $db;

    public function verificarUsuario($usuario) {
        $this->db->query('SELECT 1 FROM usuarios WHERE usuario = :user LIMIT 1');
        $this->db->bind(':user', $usuario);
        return $this->db->single() ? true : false;
    }
    /**
 * Actualiza el rol de un usuario en la base de datos.
 *
 * @param int $idUsuario El ID del usuario a actualizar.
 * @param string $rol El nuevo rol ('creadora' o 'espectador').
 * @return bool Devuelve true si la actualización fue exitosa, false en caso contrario.
 */
public function actualizarRol($idUsuario, $rol)
{
    // Esta línea asume que tu clase base del modelo ya tiene una conexión
    // a la base de datos disponible como "$this->db".
    // Prepara la consulta SQL para evitar inyecciones SQL.
    $this->db->query('UPDATE usuarios SET rol = :rol WHERE idUsuario = :id');

    // Asigna los valores a los marcadores de posición en la consulta.
    $this->db->bind(':rol', $rol);
    $this->db->bind(':id', $idUsuario);

    // Ejecuta la consulta y devuelve el resultado.
    if ($this->db->execute()) {
        return true;
    } else {
        // Si hay un error, lo registramos para futura depuración.
        error_log('Error al actualizar el rol para el usuario ID: ' . $idUsuario);
        return false;
    }
}

// En app/models/usuario.php

/**
 * Obtiene una lista de todos los usuarios con su información de perfil.
 */

// En app/models/usuario.php

/**
 * ✅ FUNCIÓN CORREGIDA
 * Obtiene una lista de todas las CREADORAS usando la tabla 'planes_suscripcion' para el precio.
 */
// En app/models/usuario.php

/**
 * ✅ FUNCIÓN CORREGIDA Y OPTIMIZADA
 * Obtiene una lista de todas las CREADORAS usando la nueva columna 'precio_suscripcion' en la tabla 'perfil'.
 */
public function getCreators() {
    $this->db->query('
        SELECT 
            u.idUsuario,
            u.usuario,
            p.nickname_artistico,
            p.foto_perfil,
            p.banner_portada,
            p.bio,
            p.precio_suscripcion, -- Leemos directamente de la tabla perfil
            (SELECT COUNT(*) FROM publicaciones WHERE idUsuarioPublico = u.idUsuario) as total_publicaciones
        FROM usuarios u
        JOIN perfil p ON u.idUsuario = p.idusuario
        WHERE u.rol = "creadora" AND u.onboarding_creadora = 1
        ORDER BY p.nickname_artistico ASC
    ');
    return $this->db->registers();
}
public function buscarUsuarios($query)
    {
        $this->db->query('
            SELECT 
                u.usuario,
                p.nickname_artistico,
                p.foto_perfil
            FROM usuarios u
            LEFT JOIN perfil p ON u.idUsuario = p.idusuario
            WHERE u.usuario LIKE :query OR p.nickname_artistico LIKE :query
            LIMIT 5
        ');
        $this->db->bind(':query', '%' . $query . '%');
        return $this->db->registers();
    }

     public function crearPerfilBasico($idUsuario, $username) {
        $this->db->query("INSERT IGNORE INTO perfil (idusuario, nickname_artistico) VALUES (:id, :nick)");
        $this->db->bind(':id', $idUsuario);
        $this->db->bind(':nick', $username);
        return $this->db->execute();
    }
public function misLikes($idUsuario)
    {
        $this->db->query('SELECT idPublicacion FROM likes WHERE idUsuario = :id');
        $this->db->bind(':id', $idUsuario);
        return $this->db->registers(); // Devuelve un array de objetos
    }
    /**
     * ✅ NUEVO: Verifica si un usuario está suscrito a otro.
     */
    public function isSubscribed($subscriberId, $creatorId) {
        // Lógica de suscripción (ejemplo, debes adaptarla a tu tabla 'subscriptions')
        // Por ahora, devolvemos 'false' para que el muro de pago se muestre.
        /*
        $this->db->query("SELECT 1 FROM subscriptions WHERE subscriber_id = :sub AND creator_id = :creator AND status = 'active'");
        $this->db->bind(':sub', $subscriberId);
        $this->db->bind(':creator', $creatorId);
        return $this->db->single() ? true : false;
        */
        return false;
    }
    
    /**
     * ✅ NUEVO: Cuenta los mensajes no leídos para el contador del navbar.
     */
    public function getMensajesNoLeidos($idUsuario)
    {
        $this->db->query('SELECT COUNT(*) as unread FROM mensajes WHERE destinatario_id = :id AND leido = 0');
        $this->db->bind(':id', $idUsuario);
        $result = $this->db->single();
        return $result ? (int)$result->unread : 0;
    }

public function getAllUsers() {
    $this->db->query('
        SELECT 
            u.idUsuario,
            u.usuario,
            p.nickname_artistico,
            p.foto_perfil
        FROM usuarios u
        LEFT JOIN perfil p ON u.idUsuario = p.idusuario
        ORDER BY u.fecha_registro DESC
    ');
    return $this->db->registers();
}
// En app/models/usuario.php, dentro de la clase Usuario

public function getZafirosBalance($idUsuario) {
    $this->db->query('SELECT saldo_zafiros FROM usuarios WHERE idUsuario = :id');
    $this->db->bind(':id', $idUsuario);
    $resultado = $this->db->single();
    return $resultado ? (int)$resultado->saldo_zafiros : 0;
}
    public function registrar($d) {
        $this->db->query('
            INSERT INTO usuarios 
                (rol, correo, usuario, contrasena, cedula, fecha_nac, genero, ciudad, pais, documento)
            VALUES
                (:rol, :correo, :usuario, :contrasena, :cedula, :fecha_nac, :genero, :ciudad, :pais, :documento)
        ');
        $this->db->bind(':rol',        $d['rol']);
        $this->db->bind(':correo',     $d['correo']);
        $this->db->bind(':usuario',    $d['usuario']);
        $this->db->bind(':contrasena', $d['contrasena']);
        $this->db->bind(':cedula',     $d['cedula']);
        $this->db->bind(':fecha_nac',  $d['fecha_nac']);
        $this->db->bind(':genero',     $d['genero']);
        $this->db->bind(':ciudad',     $d['ciudad']);
        $this->db->bind(':pais',       $d['pais']);
        $this->db->bind(':documento',  $d['documento']);
        return $this->db->execute();
    }

    public function verificarCorreo($correo) {
        $this->db->query('SELECT * FROM usuarios WHERE correo = :correo');
        $this->db->bind(':correo', $correo);
        return $this->db->single() ? true : false;
    }

    public function verificarLikeUsuario($idPublicacion, $idUsuario) {
        $this->db->query('SELECT COUNT(*) as userLiked 
                          FROM likes 
                          WHERE idPublicacion = :idPublicacion 
                          AND idUsuario = :idUsuario');
        $this->db->bind(':idPublicacion', $idPublicacion);
        $this->db->bind(':idUsuario', $idUsuario);
        $result = $this->db->register();
        return $result->userLiked > 0;
    }

    public function getUsuarioById($id)
    {
        $this->db->query('SELECT * FROM usuarios WHERE idUsuario = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function verificarContrasena($datosUsuario, $contrasena) {
        return password_verify($contrasena, $datosUsuario->contrasena);
    }

    public function getUsuario($usuario) {
        $this->db->query('SELECT * FROM usuarios WHERE usuario = :user');
        $this->db->bind(':user', $usuario);
        return $this->db->register();
    }

    public function getPerfil($idusuario) {
        $this->db->query("SELECT * FROM perfil WHERE idUsuario = :id");
        $this->db->bind(':id', $idusuario);
        return $this->db->single();
    }

    public function crearPerfil($datos) {
        $this->db->query("INSERT INTO perfil (idUsuario, nickname_artistico, foto_perfil, banner_portada, bio) 
                          VALUES (:idusuario, :nickname, :foto, :banner, :bio)");
        $this->db->bind(':idusuario', $datos['idusuario']);
        $this->db->bind(':nickname', $datos['nickname']);
        $this->db->bind(':foto', $datos['foto']);
        $this->db->bind(':banner', $datos['banner']);
        $this->db->bind(':bio', $datos['bio']);
        return $this->db->execute();
    }

    public function actualizarPerfil($datos) {
        $this->db->query("UPDATE perfil SET nickname_artistico=:nickname, foto_perfil=:foto, banner_portada=:banner, bio=:bio 
                          WHERE idUsuario=:idusuario");
        $this->db->bind(':idusuario', $datos['idusuario']);
        $this->db->bind(':nickname', $datos['nickname']);
        $this->db->bind(':foto', $datos['foto']);
        $this->db->bind(':banner', $datos['banner']);
        $this->db->bind(':bio', $datos['bio']);
        return $this->db->execute();
    }

    public function insertarPerfil($datos) {
        $this->db->query('INSERT INTO perfil (idUsuario, foto_perfil, nickname_artistico) VALUES (:id, :rutaFoto, :nombre)');
        $this->db->bind(':id', $datos['idusuario']);
        $this->db->bind(':rutaFoto', $datos['ruta']);
        $this->db->bind(':nombre', $datos['nombre']);
        return $this->db->execute();
    }

    




    public function __construct() {
        $this->db = new Base;
    }

    public function getComentarios() {
        $this->db->query('
            SELECT 
                C.idComentario, 
                C.idPublicacion, 
                C.idUsuario, 
                C.contenidoComentario, 
                C.fechaComentario, 
                U.usuario, 
                Per.foto_perfil
            FROM comentarios C
            INNER JOIN usuarios U ON U.idUsuario = C.idUsuario
            LEFT JOIN perfil Per ON Per.idUsuario = C.idUsuario
            ORDER BY C.fechaComentario DESC
        ');
        return $this->db->registers();
    }

    public function publicarComentario($datos) {
        try {
            $this->db->query('INSERT INTO comentarios (idPublicacion, idUsuario, contenidoComentario, fechaComentario) 
                              VALUES (:idPublicacion, :idUsuario, :contenidoComentario, NOW())');
            $this->db->bind(':idPublicacion', $datos['idpublicacion']);
            $this->db->bind(':idUsuario', $datos['iduser']);
            $this->db->bind(':contenidoComentario', $datos['comentario']);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error al publicar comentario: ' . $e->getMessage());
            return false;
        }
    }
    

    public function getUsuarioByIde($id) {
        // Hacer un JOIN para obtener datos de la tabla perfil
        $this->db->query('
            SELECT u.usuario, p.nombreCompleto, p.foto_Perfil
            FROM usuarios u
            JOIN perfil p ON u.idUsuario = p.idUsuario
            WHERE u.idUsuario = :id
        ');
        $this->db->bind(':id', $id);
        return $this->db->register();  // Devuelve el resultado de la consulta
    }
    
    
    
   



    

    

    public function actualizarContrasena($idusuario, $nuevaContrasena) {
        $this->db->query("UPDATE usuarios SET contrasena = :contrasena WHERE idusuario = :id");
        $this->db->bind(':contrasena', password_hash($nuevaContrasena, PASSWORD_BCRYPT));
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }
    

    
    public function actualizarCorreo($idusuario, $nuevoCorreo) {
        $this->db->query("UPDATE usuarios SET correo = :correo WHERE idusuario = :id");
        $this->db->bind(':correo', $nuevoCorreo);
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }   

    public function actualizarDocumento($idusuario, $nuevoDocumento) {
        $this->db->query("UPDATE usuarios SET documento = :documento WHERE idusuario = :id");
        $this->db->bind(':documento', $nuevoDocumento);
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }

   // En app/models/usuario.php, dentro de la clase Usuario

/**
 * Guarda o actualiza los datos del perfil y usuario para el onboarding de una creadora.
 */
public function guardarOnboardingCreadora($uid, $nickname, $bio, $metodo, $foto, $banner, $doc)
{
    // 1. Insertar o actualizar el perfil
    $this->db->query("INSERT INTO perfil (idusuario, nickname_artistico, foto_perfil, banner_portada, bio)
                     VALUES (:uid, :nick, :foto, :banner, :bio)
                     ON DUPLICATE KEY UPDATE
                     nickname_artistico = VALUES(nickname_artistico),
                     foto_perfil = VALUES(foto_perfil),
                     banner_portada = VALUES(banner_portada),
                     bio = VALUES(bio)");

    $this->db->bind(':uid', $uid);
    $this->db->bind(':nick', $nickname);
    $this->db->bind(':foto', $foto);
    $this->db->bind(':banner', $banner);
    $this->db->bind(':bio', $bio);

    if (!$this->db->execute()) {
        return false; // Si falla aquí, detenemos
    }

    // 2. Actualizar la tabla de usuarios con el estado de onboarding y método de pago
    // Usamos COALESCE para no sobreescribir el documento si no se subió uno nuevo.
    $this->db->query("UPDATE usuarios 
                     SET onboarding_creadora=1, metodo_pago=:metodo, documento_identidad=COALESCE(:doc, documento_identidad) 
                     WHERE idusuario=:uid");

    $this->db->bind(':metodo', $metodo);
    $this->db->bind(':doc', ($doc === '') ? null : $doc); // Si doc está vacío, lo mandamos como NULL
    $this->db->bind(':uid', $uid);

    return $this->db->execute(); // Devuelve true si la última consulta fue exitosa
}

/**
 * Guarda o actualiza el perfil para el onboarding de un espectador.
 */
public function guardarOnboardingEspectador($uid, $nickname)
{
    $defaultAvatar = '/ENYOOI/public/img/defaults/default_avatar.png';

    // Usamos la misma lógica INSERT ... ON DUPLICATE KEY UPDATE para simplificar
    $this->db->query("INSERT INTO perfil (idusuario, nickname_artistico, foto_perfil, bio)
                     VALUES (:uid, :nick, :foto, '')
                     ON DUPLICATE KEY UPDATE
                     nickname_artistico = VALUES(nickname_artistico)");

    $this->db->bind(':uid', $uid);
    $this->db->bind(':nick', $nickname);
    $this->db->bind(':foto', $defaultAvatar);

    return $this->db->execute();
}


    public function actualizarOnboarding($idusuario, $estado) {
        $this->db->query("UPDATE usuarios SET onboarding_creadora = :estado WHERE idusuario = :id");
        $this->db->bind(':estado', $estado);
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }   

    public function actualizarMetodoPago($idusuario, $metodo) {
        $this->db->query("UPDATE usuarios SET metodo_pago = :metodo WHERE idusuario = :id");
        $this->db->bind(':metodo', $metodo);
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }


    public function actualizarDocumentoIdentidad($idusuario, $documento) {
        $this->db->query("UPDATE usuarios SET documento_identidad = :documento WHERE idusuario = :id");
        $this->db->bind(':documento', $documento);
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }

    public function eliminarCuenta($idusuario) {
        $this->db->query("DELETE FROM usuarios WHERE idusuario = :id");
        $this->db->bind(':id', $idusuario);
        return $this->db->execute();
    }

    public function obtenerCreadorasDestacadas($limite = 5) {
        $this->db->query("
            SELECT u.idusuario, p.nickname_artistico, p.foto_perfil, p.banner_portada, p.bio,
                   (SELECT COUNT(*) FROM publicaciones pub WHERE pub.idusuario = u.idusuario) AS num_publicaciones
            FROM usuarios u
            JOIN perfil p ON u.idusuario = p.idusuario
            WHERE u.rol = 'creadora' AND u.onboarding_creadora = 1
            ORDER BY num_publicaciones DESC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->registers();
    }
    
    public function buscarCreadoras($query) {
        $this->db->query("
            SELECT u.idusuario, p.nickname_artistico, p.foto_perfil, p.banner_portada, p.bio
            FROM usuarios u
            JOIN perfil p ON u.idusuario = p.idusuario
            WHERE u.rol = 'creadora' AND u.onboarding_creadora = 1
              AND (p.nickname_artistico LIKE :query OR p.bio LIKE :query)
            ORDER BY p.nickname_artistico ASC
        ");
        $this->db->bind(':query', '%' . $query . '%');
        return $this->db->registers();

    }


    public function getCreadoraById($idusuario) {
        $this->db->query("
            SELECT u.idusuario, u.correo, p.nickname_artistico, p.foto_perfil, p.banner_portada, p.bio
            FROM usuarios u
            JOIN perfil p ON u.idusuario = p.idusuario
            WHERE u.idusuario = :idusuario AND u.rol = 'creadora' AND u.onboarding_creadora = 1
        ");
        $this->db->bind(':idusuario', $idusuario);
        return $this->db->register();}


    

    public function getCreadoras($limite = 10) {
        $this->db->query("
            SELECT u.idusuario, p.nickname_artistico, p.foto_perfil, p.banner_portada, p.bio
            FROM usuarios u
            JOIN perfil p ON u.idusuario = p.idusuario
            WHERE u.rol = 'creadora' AND u.onboarding_creadora = 1
            ORDER BY p.nickname_artistico ASC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->registers();
    }

    public function getUsuarios($limite = 10) {
        $this->db->query("
            SELECT u.idusuario, u.usuario, p.nickname_artistico, p.foto_perfil
            FROM usuarios u
            LEFT JOIN perfil p ON u.idusuario = p.idusuario
            ORDER BY u.usuario ASC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->registers();}

    public function getTotalUsuarios() {
        $this->db->query("SELECT COUNT(*) as total FROM usuarios");

        $result = $this->db->register();
        return $result->total;}
    public function getTotalCreadoras() {
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'creadora' AND onboarding_creadora = 1");  
        $result = $this->db->register();
        return $result->total;}

    public function getTotalUsuariosPorRol($rol) {  
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = :rol");
        $this->db->bind(':rol', $rol);
        $result = $this->db->register();
        return $result->total;}
    public function getTotalPublicacionesPorUsuario($idusuario) {  
        $this->db->query("SELECT COUNT(*) as total FROM publicaciones WHERE idusuario = :idusuario");
        $this->db->bind(':idusuario', $idusuario);
        $result = $this->db->register();
        return $result->total;}

    public function getTotalComentariosPorUsuario($idusuario) {
        $this->db->query("SELECT COUNT(*) as total FROM comentarios WHERE idusuario = :idusuario");
        $this->db->bind(':idusuario', $idusuario);
        $result = $this->db->register();
        return $result->total;}
    public function getTotalLikesPorUsuario($idusuario) {
        $this->db->query("SELECT COUNT(*) as total FROM likes WHERE idusuario = :idusuario");
        $this->db->bind(':idusuario', $idusuario);  
        $result = $this->db->register();
        return $result->total;}
    public function getTotalLikesRecibidosPorUsuario($idusuario) {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM likes l
            JOIN publicaciones p ON l.idPublicacion = p.idPublicacion
            WHERE p.idusuario = :idusuario  
        ");
        $this->db->bind(':idusuario', $idusuario);
        $result = $this->db->register();
        return $result->total;
    }
    public function getTotalComentariosRecibidosPorUsuario($idusuario) {
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM comentarios c
            JOIN publicaciones p ON c.idPublicacion = p.idPublicacion
            WHERE p.idusuario = :idusuario  
        ");
        $this->db->bind(':idusuario', $idusuario);
        $result = $this->db->register();
        return $result->total;
    }
    public function getTotalPublicaciones() {
        $this->db->query("SELECT COUNT(*) as total FROM publicaciones");
        $result = $this->db->register();
        return $result->total;}
    public function getTotalComentarios() {
        $this->db->query("SELECT COUNT(*) as total FROM comentarios");  
        $result = $this->db->register();
        return $result->total;}
    public function getTotalLikes() {
        $this->db->query("SELECT COUNT(*) as total FROM likes");
        $result = $this->db->register();
        return $result->total;}

        
// ✅ CORRECCIÓN DEFINITIVA APLICADA AQUÍ
    // La columna en la tabla `perfil` es `idusuario`, no `id_usuario`.
    public function getUserLevelInfo($userId) {
        $this->db->query("SELECT level, xp FROM perfil WHERE idusuario = :id");
        $this->db->bind(':id', $userId);
        $profile = $this->db->register();

        $currentLevel = $profile->level ?? 1;
        $currentXp = $profile->xp ?? 0;
        
        $xpForNextLevel = $currentLevel * 100 * ($currentLevel * 0.5);
        $rewardForNextLevel = $currentLevel * 50; 
        
        $xpForCurrentLevel = ($currentLevel > 1) ? (($currentLevel - 1) * 100 * (($currentLevel - 1) * 0.5)) : 0;
        $xpInCurrentLevel = $currentXp - $xpForCurrentLevel;
        $xpNeededForLevelUp = $xpForNextLevel - $xpForCurrentLevel;
        
        $progressPercentage = ($xpNeededForLevelUp > 0) ? round(($xpInCurrentLevel / $xpNeededForLevelUp) * 100) : 0;
        $progressPercentage = max(0, min(100, $progressPercentage));

        return [
            'current_level' => $currentLevel,
            'current_xp' => (int)$currentXp,
            'xp_needed_for_next' => (int)$xpForNextLevel,
            'progress_percentage' => $progressPercentage,
            'next_level_reward' => (int)$rewardForNextLevel
        ];
    }


}
?>

