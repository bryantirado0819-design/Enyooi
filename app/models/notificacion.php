<?php
class Notificacion
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    } 
    

    public function crearNotificacion($datos)
    {
        $this->db->query('INSERT INTO notificaciones (idUsuario, usuarioAccion, tipoNotificacion, idPublicacion) VALUES (:idUsuario, :usuarioAccion, :tipoNotificacion, :idPublicacion)');
        $this->db->bind(':idUsuario', $datos['idUsuario']);
        $this->db->bind(':usuarioAccion', $datos['usuarioAccion']);
        $this->db->bind(':tipoNotificacion', $datos['tipoNotificacion']);
        $this->db->bind(':idPublicacion', $datos['idPublicacion']);
        
        if ($this->db->execute()) {
             $this->db->query("SELECT LAST_INSERT_ID() as id");
             $idNotif = $this->db->single()->id;
             return $this->getNotificacionById($idNotif);
        }
        return false;
    }

    public function getNotificaciones($idUsuario)
    {
        $this->db->query("
            SELECT 
                n.*, 
                u.usuario as usuarioAccionNombre, 
                p.foto_perfil as usuarioAccionFoto,
                tn.mensajeNotificacion 
            FROM notificaciones n
            JOIN usuarios u ON u.idUsuario = n.usuarioAccion
            JOIN perfil p ON p.idusuario = n.usuarioAccion
            JOIN tiposnotificaciones tn ON tn.idTiposNotificaciones = n.tipoNotificacion
            WHERE n.idUsuario = :idUsuario
            ORDER BY n.fechaNotificacion DESC
            LIMIT 20
        ");
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->registers();
    }
    
    public function getNotificacionById($id)
    {
        $this->db->query("
            SELECT 
                n.*, 
                u.usuario as usuarioAccionNombre, 
                p.foto_perfil as usuarioAccionFoto,
                tn.mensajeNotificacion 
            FROM notificaciones n
            JOIN usuarios u ON u.idUsuario = n.usuarioAccion
            JOIN perfil p ON p.idusuario = n.usuarioAccion
            JOIN tiposnotificaciones tn ON tn.idTiposNotificaciones = n.tipoNotificacion
            WHERE n.idNotificacion = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function marcarComoLeidas($idUsuario)
    {
        $this->db->query("UPDATE notificaciones SET leido = 1 WHERE idUsuario = :idUsuario AND leido = 0");
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->execute();
    }
}