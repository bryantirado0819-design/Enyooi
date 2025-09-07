<?php
// app/models/NotificacionModel.php

class NotificacionModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function obtenerNotificaciones($idUsuario)
    {
        $this->db->query('
            SELECT 
                N.*, 
                U.usuario, 
                P.foto_perfil,
                CASE 
                    WHEN N.tipoNotificacion = 1 THEN "le ha dado Me Gusta a tu publicación."
                    WHEN N.tipoNotificacion = 2 THEN "ha comentado tu publicación."
                    ELSE "ha interactuado contigo."
                END as mensajeNotificacion
            FROM notificaciones N
            JOIN usuarios U ON U.idUsuario = N.usuarioAccion
            LEFT JOIN perfil P ON P.idUsuario = U.idUsuario
            WHERE N.idUsuario = :idUsuario
            ORDER BY N.fecha DESC
            LIMIT 10
        ');
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->registers();
    }

    public function marcarComoLeidas($idUsuario)
    {
        $this->db->query('UPDATE notificaciones SET leido = 1 WHERE idUsuario = :idUsuario AND leido = 0');
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->execute();
    }

    public function contarNoLeidas($idUsuario)
    {
        $this->db->query('SELECT COUNT(*) as total FROM notificaciones WHERE idUsuario = :id AND leido = 0');
        $this->db->bind(':id', $idUsuario);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
}