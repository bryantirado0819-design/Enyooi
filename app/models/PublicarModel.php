<?php
require_once __DIR__ . '/../libs/Base.php';

class PublicarModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function getPublicaciones()
    {
        $this->db->query("
            SELECT 
                p.idPublicacion, p.contenidoPublicacion, p.fotoPublicacion, p.fechaPublicacion, p.idUsuarioPublico,
                u.usuario, pr.nickname_artistico, pr.foto_perfil,
                (SELECT COUNT(*) FROM likes WHERE idPublicacion = p.idPublicacion) as num_likes,
                (SELECT COUNT(*) FROM comentarios WHERE idPublicacion = p.idPublicacion) as num_comentarios
            FROM publicaciones p
            JOIN usuarios u ON p.idUsuarioPublico = u.idUsuario
            LEFT JOIN perfil pr ON u.idUsuario = pr.idusuario
            ORDER BY p.fechaPublicacion DESC
        ");
        return $this->db->registers();
    }

    public function getInformacionComentarios()
    {
        $this->db->query("
            SELECT c.idPublicacion, c.contenidoComentario, c.fechaComentario, u.usuario, pr.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON c.idUsuario = u.idUsuario
            LEFT JOIN perfil pr ON u.idUsuario = pr.idusuario
            ORDER BY c.fechaComentario ASC
        ");
        return $this->db->registers();
    }

    /**
     * ✅ FUNCIÓN CORREGIDA Y AÑADIDA
     * Obtiene todas las publicaciones de un usuario específico.
     * Esta era la función que causaba el error fatal en Perfil.php.
     */
    public function getPublicacionesUsuario($idUsuario) {
        $this->db->query('
            SELECT P.*, U.usuario, Per.foto_perfil, Per.nickname_artistico
            FROM publicaciones P 
            INNER JOIN usuarios U ON U.idUsuario = P.idUsuarioPublico 
            LEFT JOIN perfil Per ON Per.idUsuario = P.idUsuarioPublico 
            WHERE P.idUsuarioPublico = :idUsuario 
            ORDER BY P.fechaPublicacion DESC
        ');
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->registers() ?: [];
    }

    public function getPostCountForUser($idUsuario)
    {
        $this->db->query('SELECT COUNT(*) as total FROM publicaciones WHERE idUsuarioPublico = :idUsuario');
        $this->db->bind(':idUsuario', $idUsuario);
        return (int)$this->db->single()->total;
    }

    public function getTotalLikesForUserPosts($idUsuario)
    {
        $this->db->query('
            SELECT COUNT(*) as total FROM likes l
            JOIN publicaciones p ON l.idPublicacion = p.idPublicacion
            WHERE p.idUsuarioPublico = :idUsuario
        ');
        $this->db->bind(':idUsuario', $idUsuario);
        return (int)$this->db->single()->total;
    }

    /**
     * ✅ FUNCIÓN AÑADIDA
     * Cuenta los archivos de un tipo específico (imagen o video) para un usuario.
     * Esto previene el siguiente error que ibas a tener en Perfil.php.
     */
    public function getMediaCountForUser($idUsuario, $tipo)
    {
        $this->db->query('SELECT COUNT(*) as total FROM publicaciones WHERE idUsuarioPublico = :idUsuario AND tipo_archivo = :tipo');
        $this->db->bind(':idUsuario', $idUsuario);
        $this->db->bind(':tipo', $tipo);
        return (int)$this->db->single()->total;
    }

    public function getPublicacionById($id)
    {
        $this->db->query('SELECT * FROM publicaciones WHERE idPublicacion = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getPublicacionOwner($idPublicacion) {
        $this->db->query('SELECT idUsuarioPublico as idUsuario FROM publicaciones WHERE idPublicacion = :id');
        $this->db->bind(':id', $idPublicacion);
        return $this->db->single();
    }
    
    public function crearPublicacion($datos) {
        $this->db->query('INSERT INTO publicaciones (idUsuarioPublico, contenidoPublicacion, fotoPublicacion, tipo_archivo) VALUES (:iduser, :contenido, :foto, :tipo_archivo)');
        $this->db->bind(':iduser', $datos['iduser']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':foto', $datos['foto']);
        $this->db->bind(':tipo_archivo', $datos['tipo_archivo']);
        return $this->db->execute();
    }

    public function eliminarPublicacion($idPublicacion, $idUsuario) {
        $this->db->query('DELETE FROM publicaciones WHERE idPublicacion = :id AND idUsuarioPublico = :uid');
        $this->db->bind(':id', $idPublicacion);
        $this->db->bind(':uid', $idUsuario);
        return $this->db->execute();
    }
}