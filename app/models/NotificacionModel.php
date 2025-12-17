<?php
class NotificacionModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * Obtiene las notificaciones más recientes para un usuario (para el dropdown).
     * @param int $idUsuario El ID del usuario.
     * @param int $limit El número de notificaciones a obtener.
     * @return array
     */
    public function getRecentNotifications($idUsuario, $limit = 10)
    {
        $this->db->query("
            SELECT 
                n.*, 
                u_accion.usuario as usuarioAccionNombre, 
                p_accion.foto_perfil as usuarioAccionFoto,
                tn.mensajeNotificacion 
            FROM notificaciones n
            JOIN usuarios u_accion ON u_accion.idUsuario = n.usuarioAccion
            LEFT JOIN perfil p_accion ON p_accion.idusuario = n.usuarioAccion
            JOIN tiposnotificaciones tn ON tn.idTiposNotificaciones = n.tipoNotificacion
            WHERE n.idUsuario = :idUsuario
            ORDER BY n.fechaNotificacion DESC
            LIMIT :limit
        ");
        $this->db->bind(':idUsuario', $idUsuario);
        $this->db->bind(':limit', $limit);
        return $this->db->registers();
    }

    /**
     * Obtiene TODAS las notificaciones para un usuario (para la página completa).
     * @param int $idUsuario El ID del usuario.
     * @return array
     */
    public function getAllNotifications($idUsuario)
    {
        // Esta consulta es similar pero sin el LIMIT para obtener todo el historial.
        $this->db->query("
            SELECT 
                n.*, 
                u_accion.usuario as usuarioAccionNombre, 
                p_accion.foto_perfil as usuarioAccionFoto,
                tn.mensajeNotificacion 
            FROM notificaciones n
            JOIN usuarios u_accion ON u_accion.idUsuario = n.usuarioAccion
            LEFT JOIN perfil p_accion ON p_accion.idusuario = n.usuarioAccion
            JOIN tiposnotificaciones tn ON tn.idTiposNotificaciones = n.tipoNotificacion
            WHERE n.idUsuario = :idUsuario
            ORDER BY n.fechaNotificacion DESC
        ");
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->registers();
    }

    /**
     * Marca todas las notificaciones no leídas de un usuario como leídas.
     * @param int $idUsuario El ID del usuario.
     * @return bool
     */
    public function markAllAsRead($idUsuario)
    {
        $this->db->query("UPDATE notificaciones SET leido = 1 WHERE idUsuario = :idUsuario AND leido = 0");
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->execute();
    }

    /**
     * Cuenta las notificaciones no leídas de un usuario.
     * @param int $idUsuario El ID del usuario.
     * @return int
     */
    public function countUnread($idUsuario)
    {
        $this->db->query('SELECT COUNT(*) as total FROM notificaciones WHERE idUsuario = :id AND leido = 0');
        $this->db->bind(':id', $idUsuario);
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
}

