<?php
// app/models/DashboardModel.php

class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * Obtiene todos los datos necesarios para el dashboard de la creadora en una sola llamada.
     * @param int $creatorId El ID de la creadora.
     * @return array Un array con todos los datos del dashboard.
     */
    public function getFullDashboardData($creatorId)
    {
        // Usamos métodos privados para organizar la obtención de cada pieza de datos.
        $kpi = $this->getKpiData($creatorId);
        $revenue30d = $this->getRecentEarnings($creatorId, 30);
        $revenue7d = $this->getRecentEarnings($creatorId, 7);
        $topContent = $this->getTopEngagedPosts($creatorId, 5);
        $revenueSources = $this->getRevenueSources($creatorId);
        $levelData = $this->getLevelData($creatorId);

        // Ensamblamos el array final que se enviará como JSON.
        return [
            'kpi' => [
                'revenue' => $revenue30d['total_net_usd'] ?? 0,
                'subs' => $kpi['active_subs'] ?? 0,
                'newSubs' => $kpi['new_subs_30d'] ?? 0,
                'balance' => $kpi['saldo_zafiros'] ?? 0
            ],
            'revenueChart' => [
                '30d' => $revenue30d['daily_data'],
                '7d' => $revenue7d['daily_data']
            ],
            'topContent' => $topContent,
            'revenueSource' => $revenueSources,
            'gamification' => $levelData
        ];
    }

    // --- Métodos privados para obtener cada pieza de datos ---

    private function getKpiData($creatorId)
    {
        // Saldo de Zafiros
        $this->db->query("SELECT saldo_zafiros FROM usuarios WHERE idUsuario = :id");
        $this->db->bind(':id', $creatorId);
        $saldo_zafiros = (int)($this->db->single()->saldo_zafiros ?? 0);

        // Suscriptores Activos
        $this->db->query("SELECT COUNT(*) as total FROM subscriptions WHERE creator_id = :id AND status = 'active' AND renewal_date > NOW()");
        $this->db->bind(':id', $creatorId);
        $active_subs = (int)($this->db->single()->total ?? 0);

        // Nuevos Suscriptores (últimos 30 días)
        $this->db->query("SELECT COUNT(*) as total FROM subscriptions WHERE creator_id = :id AND started_at >= CURDATE() - INTERVAL 30 DAY");
        $this->db->bind(':id', $creatorId);
        $new_subs_30d = (int)($this->db->single()->total ?? 0);

        return [
            'saldo_zafiros' => $saldo_zafiros,
            'active_subs' => $active_subs,
            'new_subs_30d' => $new_subs_30d
        ];
    }

    private function getRecentEarnings($creatorId, $days)
    {
        $this->db->query("
            SELECT SUM(monto_neto_usd) as total_net_usd, DATE(fecha) as date
            FROM transacciones_financieras
            WHERE id_usuario = :creatorId AND tipo LIKE 'ingreso_%' AND fecha >= CURDATE() - INTERVAL :days DAY
            GROUP BY DATE(fecha) ORDER BY DATE(fecha) ASC
        ");
        $this->db->bind(':creatorId', $creatorId);
        $this->db->bind(':days', $days);
        $dailyData = $this->db->resultSet();
        $total = array_sum(array_column($dailyData, 'total_net_usd'));
        return ['total_net_usd' => $total, 'daily_data' => $dailyData];
    }

    private function getRevenueSources($creatorId)
    {
        $this->db->query("
            SELECT 
                CASE
                    WHEN tipo = 'ingreso_suscripcion' THEN 'Suscripciones'
                    WHEN tipo IN ('ingreso_propina_chat', 'ingreso_propina_live') THEN 'Propinas'
                    WHEN tipo = 'ingreso_desbloqueo_chat' THEN 'Chats'
                    WHEN tipo = 'compra_contenido' THEN 'Venta Contenido'
                    ELSE 'Otros'
                END as source, SUM(monto_neto_usd) as total
            FROM transacciones_financieras
            WHERE id_usuario = :creatorId AND tipo LIKE 'ingreso_%' AND fecha >= CURDATE() - INTERVAL 30 DAY
            GROUP BY source
        ");
        $this->db->bind(':creatorId', $creatorId);
        $results = $this->db->resultSet();
        return ['labels' => array_column($results, 'source'), 'data' => array_column($results, 'total')];
    }

    private function getTopEngagedPosts($creatorId, $limit = 5)
    {
        $this->db->query("
            SELECT 
                p.idPublicacion, p.contenidoPublicacion, p.tipo_archivo, p.num_likes as likes,
                (SELECT COUNT(*) FROM comentarios WHERE idPublicacion = p.idPublicacion) as comentarios
            FROM publicaciones p
            WHERE p.idUsuarioPublico = :creatorId
            ORDER BY (p.num_likes + (SELECT COUNT(*) FROM comentarios WHERE idPublicacion = p.idPublicacion)) DESC
            LIMIT :limit
        ");
        $this->db->bind(':creatorId', $creatorId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
    
    private function getLevelData($creatorId)
    {
        // La lógica de LevelModel es autocontenida y compleja, la replicamos aquí.
        $this->db->query("SELECT xp, level FROM perfil WHERE idusuario = :id");
        $this->db->bind(':id', $creatorId);
        $profile = $this->db->single();

        if(!$profile) return null;

        $currentLevel = (int)$profile->level;
        $currentXp = (int)$profile->xp;
        
        // Definimos los niveles aquí para que el modelo sea autocontenido
        $levelsConfig = [
            1 => ['name' => 'Novata', 'xp_required' => 0], 2 => ['name' => 'Promesa', 'xp_required' => 100],
            3 => ['name' => 'Influencer', 'xp_required' => 300], 4 => ['name' => 'Celebridad', 'xp_required' => 800],
            5 => ['name' => 'Superestrella', 'xp_required' => 2000], 6 => ['name' => 'Icono', 'xp_required' => 5000],
            7 => ['name' => 'Leyenda', 'xp_required' => 10000],
        ];
        
        $levelInfo = $levelsConfig[$currentLevel] ?? $levelsConfig[1];
        $nextLevelInfo = $levelsConfig[$currentLevel + 1] ?? null;

        $xpForCurrentLevel = $levelInfo['xp_required'];
        $xpForNextLevel = $nextLevelInfo ? $nextLevelInfo['xp_required'] : $currentXp;
        $xpProgress = $currentXp - $xpForCurrentLevel;
        $xpNeededForNext = $xpForNextLevel - $xpForCurrentLevel;
        $progressPercentage = ($xpNeededForNext > 0) ? ($xpProgress / $xpNeededForNext) * 100 : 100;

        return [
            'level' => $currentLevel, 'levelName' => $levelInfo['name'], 'xp' => $currentXp,
            'nextLevelName' => $nextLevelInfo ? $nextLevelInfo['name'] : 'Máximo',
            'xpForNextLevel' => $nextLevelInfo ? $nextLevelInfo['xp_required'] : null,
            'progressPercentage' => round($progressPercentage)
        ];
    }
}