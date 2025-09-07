<?php
require_once __DIR__ . '/../libs/Base.php';

class publicar
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function publicar($datos)
    {
        $this->db->query('INSERT INTO publicaciones (idUsuarioPublico, contenidoPublicacion, fotoPublicacion, tipo_archivo) VALUES (:iduser, :contenido, :foto, :tipo_archivo)');
        $this->db->bind(':iduser', $datos['iduser']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':foto', $datos['foto']);
        $this->db->bind(':tipo_archivo', $datos['tipo_archivo']);
        return $this->db->execute();
    }

    public function getPublicaciones()
    {
        $this->db->query('
            SELECT 
                P.*,
                U.usuario, 
                Per.foto_perfil, 
                Per.nickname_artistico
            FROM publicaciones P
            INNER JOIN usuarios U ON U.idUsuario = P.idUsuarioPublico
            LEFT JOIN perfil Per ON Per.idUsuario = P.idUsuarioPublico
            ORDER BY P.fechaPublicacion DESC
        ');
        return $this->db->registers();
    }

    /**
     * ✅ MÉTODO QUE FALTABA, AHORA AÑADIDO
     * Obtiene todas las publicaciones de un usuario específico.
     */
    public function getPublicacionesUsuario($idUsuario) {
        $this->db->query('
            SELECT 
                P.*, 
                U.usuario, 
                Per.foto_perfil,
                Per.nickname_artistico
            FROM publicaciones P 
            INNER JOIN usuarios U ON U.idUsuario = P.idUsuarioPublico 
            LEFT JOIN perfil Per ON Per.idUsuario = P.idUsuarioPublico 
            WHERE P.idUsuarioPublico = :idUsuario 
            ORDER BY P.fechaPublicacion DESC
        ');
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->registers() ?: [];
    }

    public function getPublicacionById($id)
    {
        $this->db->query('SELECT * FROM publicaciones WHERE idPublicacion = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function eliminarPublicacion($publicacion) {
        $this->db->query('DELETE FROM publicaciones WHERE idPublicacion = :id');
        $this->db->bind(':id', $publicacion->idPublicacion);
        return $this->db->execute();
    }

    // --- Métodos de Likes ---

    public function verificarLikeUsuario($idPublicacion, $idUsuario) {
        $this->db->query('SELECT COUNT(*) as userLiked FROM likes WHERE idPublicacion = :idPublicacion AND idUsuario = :idUsuario');
        $this->db->bind(':idPublicacion', $idPublicacion);
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->single()->userLiked > 0;
    }

    public function rowlikes($datos) {
        $this->db->query('SELECT COUNT(*) as total FROM likes WHERE idPublicacion = :idPublicacion AND idUsuario = :idUsuario');
        $this->db->bind(':idPublicacion', $datos['idpublicacion']);
        $this->db->bind(':idUsuario', $datos['idUsuario']);
        return $this->db->single()->total > 0;
    }

    public function agregarlike($datos) {
        $this->db->query('INSERT INTO likes (idPublicacion, idUsuario, fechaLike) VALUES(:idPublicacion, :idUsuario, NOW())');
        $this->db->bind(':idPublicacion', $datos['idpublicacion']);
        $this->db->bind(':idUsuario', $datos['idUsuario']);
        return $this->db->execute();
    }

    public function eliminarlike($datos) {
        $this->db->query('DELETE FROM likes WHERE idPublicacion = :idPublicacion AND idUsuario = :idUsuario');
        $this->db->bind(':idPublicacion', $datos['idpublicacion']);
        $this->db->bind(':idUsuario', $datos['idUsuario']);
        return $this->db->execute();
    }

    public function getLikeCount($idpublicacion) {
        $this->db->query("SELECT COUNT(*) as total FROM likes WHERE idPublicacion = :idPublicacion");
        $this->db->bind(':idPublicacion', $idpublicacion);
        return $this->db->single()->total;
    }
    
    // --- Métodos de Comentarios ---

    public function getInformacionComentarios() {
        $this->db->query('
            SELECT 
                C.*, U.usuario, Per.foto_perfil 
            FROM comentarios C
            INNER JOIN usuarios U ON U.idUsuario = C.idUsuario
            LEFT JOIN perfil Per ON Per.idUsuario = C.idUsuario
            ORDER BY C.fechaComentario ASC
        ');
        return $this->db->registers();
    }

    public function getCommentCount($idpublicacion) {
        $this->db->query("SELECT COUNT(*) as total FROM comentarios WHERE idPublicacion = :idPublicacion");
        $this->db->bind(':idPublicacion', $idpublicacion);
        return $this->db->single()->total;
    }

    public function publicarComentario($datos) {
        $this->db->query('INSERT INTO comentarios (idPublicacion, idUsuario, contenidoComentario, fechaComentario) VALUES (:idPublicacion, :idUsuario, :contenidoComentario, NOW())');
        $this->db->bind(':idPublicacion', $datos['idpublicacion']);
        $this->db->bind(':idUsuario', $datos['iduser']);
        $this->db->bind(':contenidoComentario', $datos['comentario']);
        return $this->db->execute();
    }

    // --- Métodos de Notificaciones ---

    public function addNotificacion($datos) {
        $this->db->query('INSERT INTO notificaciones (idUsuario, usuarioAccion, tipoNotificacion, idPublicacion, leido) VALUES (:idUsuario, :usuarioAccion, :tipoNotificacion, :idPublicacion, 0)');
        $this->db->bind(':idUsuario', $datos['idUsuario']);
        $this->db->bind(':usuarioAccion', $datos['usuarioAccion']);
        $this->db->bind(':tipoNotificacion', $datos['tipoNotificacion']);
        $this->db->bind(':idPublicacion', $datos['idPublicacion'] ?? null);
        return $this->db->execute();
    }
    
    // --- Métodos de Utilidad ---

    public function obtenerUsuarioPorId($idUsuario) {
        $this->db->query('SELECT u.usuario, Per.foto_perfil FROM usuarios u LEFT JOIN perfil Per ON u.idUsuario = Per.idUsuario WHERE u.idUsuario = :idUsuario');
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->single();
    }

    public function misLikes($user)
    {
        $this->db->query('SELECT * FROM likes WHERE idUsuario = :id');
        $this->db->bind(':id', $user);
        return $this->db->registers();
    }
    // En app/models/publicar.php

    /**
     * Obtiene el número total de publicaciones de un usuario.
     */
    public function getPostCountForUser($idUsuario) {
        $this->db->query('SELECT COUNT(*) as postCount FROM publicaciones WHERE idUsuarioPublico = :idUsuario');
        $this->db->bind(':idUsuario', $idUsuario);
        $result = $this->db->single();
        return $result->postCount;
    }

    /**
     * Obtiene el número total de "Me Gusta" que ha recibido un usuario en todas sus publicaciones.
     */
    
    
    /**
     * ✅ NUEVO: Cuenta medios por tipo (imagen/video) para un usuario.
     */
    public function getMediaCountForUser($idUsuario, $tipo) {
        $this->db->query('SELECT COUNT(*) as count FROM publicaciones WHERE idUsuarioPublico = :idUsuario AND tipo_archivo = :tipo');
        $this->db->bind(':idUsuario', $idUsuario);
        $this->db->bind(':tipo', $tipo);
        $result = $this->db->single();
        return $result ? (int)$result->count : 0;
    }

    public function getComentariosPorPublicacion($idPublicacion)
    {
        $this->db->query('
            SELECT 
                C.contenidoComentario,
                C.fechaComentario,
                U.usuario, 
                Per.foto_perfil
            FROM comentarios C
            INNER JOIN usuarios U ON U.idUsuario = C.idUsuario
            LEFT JOIN perfil Per ON Per.idUsuario = C.idUsuario
            WHERE C.idPublicacion = :idPublicacion
            ORDER BY C.fechaComentario ASC
        ');
        $this->db->bind(':idPublicacion', $idPublicacion);
        return $this->db->registers();
    }
    /**
     * ✅ NUEVO: Suma todos los likes de las publicaciones de un usuario.
     */
    public function getTotalLikesForUserPosts($idUsuario) {
        $this->db->query('
            SELECT SUM(p.num_likes) as totalLikes 
            FROM publicaciones p
            WHERE p.idUsuarioPublico = :idUsuario
        ');
        $this->db->bind(':idUsuario', $idUsuario);
        $result = $this->db->single();
        return $result ? (int)$result->totalLikes : 0;
    }
     public function getNotificaciones($id)
    {
        $this->db->query('SELECT idnotificacion FROM notificaciones WHERE idUsuario=:id');
        $this->db->bind(':id',$id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}