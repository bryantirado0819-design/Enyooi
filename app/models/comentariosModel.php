<?php

class comentariosModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * ✅ CORRECCIÓN DEFINITIVA
     * Se elimina la columna 'iduser_propietario' de la consulta INSERT
     * para que coincida con la estructura de tu tabla 'comentarios'.
     */
    public function agregarComentario($datos)
    {
        $this->db->query('
            INSERT INTO comentarios 
                (idPublicacion, idUsuario, contenidoComentario) 
            VALUES 
                (:idpublicacion, :iduser, :comentario)
        ');
        
        $this->db->bind(':idpublicacion', $datos['idpublicacion']);
        $this->db->bind(':iduser', $datos['iduser']);
        $this->db->bind(':comentario', $datos['comentario']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function getComentarioById($id) {
        $this->db->query('
            SELECT C.*, U.usuario, Per.foto_perfil 
            FROM comentarios C 
            JOIN usuarios U ON C.idUsuario = U.idUsuario 
            LEFT JOIN perfil Per ON U.idUsuario = Per.idUsuario 
            WHERE C.idComentario = :id
        ');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getCommentCount($idPublicacion)
    {
        $this->db->query("SELECT COUNT(*) as total FROM comentarios WHERE idPublicacion = :idPublicacion");
        $this->db->bind(':idPublicacion', $idPublicacion);
        $resultado = $this->db->single();
        return $resultado ? (int)$resultado->total : 0;
    }
}