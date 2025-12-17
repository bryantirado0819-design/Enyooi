<?php
class HistorialModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    // Obtener todas las transacciones de ingresos de una creadora
    public function getIngresos($idCreadora)
    {
        // ✅ SOLUCIÓN: Cambiado 't.id_usuario_creador' por 't.creator_id' y 't.id_usuario_comprador' por 't.idusuario'
        $this->db->query("SELECT t.*, u.usuario as espectador_usuario 
                          FROM transacciones t
                          LEFT JOIN usuarios u ON t.idusuario = u.idUsuario
                          WHERE t.creator_id = :id_creadora 
                          AND t.tipo IN ('subscription', 'purchase', 'tip')
                          ORDER BY t.created_at DESC");
        $this->db->bind(':id_creadora', $idCreadora);
        return $this->db->registers();
    }

    // Obtener todos los retiros de una creadora
    public function getRetiros($idCreadora)
    {
        // ✅ SOLUCIÓN: Usamos la tabla 'solicitudes_retiro' correcta
        $this->db->query("SELECT * FROM solicitudes_retiro WHERE id_creadora = :id_creadora ORDER BY fecha_solicitud DESC");
        $this->db->bind(':id_creadora', $idCreadora);
        return $this->db->registers();
    }
    
    // Obtener datos para el resumen en PDF
    public function getDatosParaResumen($idCreadora, $fechaInicio, $fechaFin) {
        // ✅ SOLUCIÓN: Cambiado 't.id_usuario_creador' por 't.creator_id' y 't.id_usuario_comprador' por 't.idusuario'
        $this->db->query("SELECT t.*, u.usuario as espectador_usuario 
                          FROM transacciones t
                          LEFT JOIN usuarios u ON t.idusuario = u.idUsuario
                          WHERE t.creator_id = :id_creadora 
                          AND t.tipo IN ('subscription', 'purchase', 'tip')
                          AND t.created_at BETWEEN :fecha_inicio AND :fecha_fin
                          ORDER BY t.created_at DESC");
        $this->db->bind(':id_creadora', $idCreadora);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        return $this->db->registers();
    }
}


