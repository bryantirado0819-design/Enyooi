<?php
// app/models/AnalyticsModelCreador.php

// ✅ CORRECCIÓN: Se eliminó la línea "require_once" que causaba el error.
// El archivo initializer.php ya carga la configuración por nosotros.

class AnalyticsModelCreador
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function creatorBalanceZafiros($idUsuario)
    {
        try {
            $this->db->query("SELECT saldo_zafiros FROM usuarios WHERE idUsuario = :id_usuario");
            $this->db->bind(':id_usuario', $idUsuario);
            $resultado = $this->db->single();
            return $resultado ? (int)$resultado->saldo_zafiros : 0;
        } catch (Exception $e) {
            error_log('Error en creatorBalanceZafiros: ' . $e->getMessage());
            return 0;
        }
    }

    public function getRecentEarnings($creatorId, $days)
    {
        $this->db->query("
            SELECT 
                SUM(monto_neto_usd) as total_net_usd,
                DATE(fecha) as date
            FROM transacciones_financieras
            WHERE id_usuario = :creatorId 
              AND tipo LIKE 'ingreso_%'
              AND fecha >= CURDATE() - INTERVAL :days DAY
            GROUP BY DATE(fecha)
            ORDER BY DATE(fecha) ASC
        ");
        $this->db->bind(':creatorId', $creatorId);
        $this->db->bind(':days', $days);
        $dailyData = $this->db->resultSet();

        $total = array_sum(array_column($dailyData, 'total_net_usd'));

        return [
            'total_net_usd' => $total,
            'daily_data' => $dailyData
        ];
    }
    
    public function getRevenueSources($creatorId)
    {
        $this->db->query("
            SELECT 
                CASE
                    WHEN tipo = 'ingreso_suscripcion' THEN 'Suscripciones'
                    WHEN tipo = 'ingreso_contenido_pago' THEN 'Venta Contenido'
                    WHEN tipo = 'ingreso_propina_chat' OR tipo = 'ingreso_propina_live' THEN 'Propinas'
                    WHEN tipo = 'ingreso_desbloqueo_chat' THEN 'Chats'
                    ELSE 'Otros'
                END as source,
                SUM(monto_neto_usd) as total
            FROM transacciones_financieras
            WHERE id_usuario = :creatorId AND tipo LIKE 'ingreso_%' AND fecha >= CURDATE() - INTERVAL 30 DAY
            GROUP BY source
        ");
        $this->db->bind(':creatorId', $creatorId);
        $results = $this->db->resultSet();
        
        $labels = array_column($results, 'source');
        $data = array_column($results, 'total');

        return ['labels' => $labels, 'data' => $data];
    }
}