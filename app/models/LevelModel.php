<?php
class LevelModel
{
    private $db;

    // Define los niveles y la XP necesaria para alcanzarlos.
    // Es fácilmente escalable: solo añade más niveles aquí.
    private const LEVELS = [
        1 => ['name' => 'Novata', 'xp_required' => 0],
        2 => ['name' => 'Promesa', 'xp_required' => 100],
        3 => ['name' => 'Influencer', 'xp_required' => 300],
        4 => ['name' => 'Celebridad', 'xp_required' => 800],
        5 => ['name' => 'Superestrella', 'xp_required' => 2000],
        6 => ['name' => 'Icono', 'xp_required' => 5000],
        7 => ['name' => 'Leyenda', 'xp_required' => 10000],
    ];

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * Añade XP a una creadora y la sube de nivel si es necesario.
     * @param int $creatorId - El ID de la creadora.
     * @param int $xpToAdd - La cantidad de XP a añadir.
     * @return bool - True si la actualización fue exitosa.
     */
    public function addXpAndLevelUp($creatorId, $xpToAdd)
    {
        $this->db->beginTransaction();
        try {
            // 1. Obtener XP y nivel actual
            $this->db->query("SELECT xp, level FROM perfil WHERE idusuario = :id FOR UPDATE");
            $this->db->bind(':id', $creatorId);
            $profile = $this->db->single();

            if (!$profile) {
                // Si por alguna razón no tiene perfil, no podemos hacer nada.
                $this->db->rollBack();
                return false;
            }

            // 2. Calcular nueva XP y nuevo nivel
            $currentXp = (int)$profile->xp;
            $currentLevel = (int)$profile->level;
            $newXp = $currentXp + $xpToAdd;
            $newLevel = $this->getLevelForXp($newXp);

            // 3. Actualizar la base de datos
            $this->db->query("UPDATE perfil SET xp = :xp, level = :level WHERE idusuario = :id");
            $this->db->bind(':xp', $newXp);
            $this->db->bind(':level', $newLevel);
            $this->db->bind(':id', $creatorId);
            $this->db->execute();
            
            // 4. Si subió de nivel, crear una notificación (opcional pero recomendado)
            if ($newLevel > $currentLevel) {
                // Aquí podrías llamar a un NotificacionModel para notificar a la creadora.
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en addXpAndLevelUp: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los datos de nivel de una creadora.
     */
    public function getLevelData($creatorId) {
        $this->db->query("SELECT xp, level FROM perfil WHERE idusuario = :id");
        $this->db->bind(':id', $creatorId);
        $profile = $this->db->single();

        if(!$profile) return null;

        $currentLevel = (int)$profile->level;
        $currentXp = (int)$profile->xp;
        
        $levelInfo = self::LEVELS[$currentLevel] ?? self::LEVELS[1];
        $nextLevelInfo = self::LEVELS[$currentLevel + 1] ?? null;

        $xpForCurrentLevel = $levelInfo['xp_required'];
        $xpForNextLevel = $nextLevelInfo ? $nextLevelInfo['xp_required'] : $currentXp;
        
        $xpProgress = $currentXp - $xpForCurrentLevel;
        $xpNeededForNext = $xpForNextLevel - $xpForCurrentLevel;
        
        $progressPercentage = ($xpNeededForNext > 0) ? ($xpProgress / $xpNeededForNext) * 100 : 100;

        return [
            'level' => $currentLevel,
            'levelName' => $levelInfo['name'],
            'xp' => $currentXp,
            'nextLevelName' => $nextLevelInfo ? $nextLevelInfo['name'] : 'Máximo',
            'xpForNextLevel' => $nextLevelInfo ? $nextLevelInfo['xp_required'] : null,
            'progressPercentage' => round($progressPercentage)
        ];
    }
    
    /**
     * Determina el nivel basado en la cantidad de XP.
     */
    private function getLevelForXp($xp)
    {
        $currentLevel = 1;
        foreach (self::LEVELS as $level => $details) {
            if ($xp >= $details['xp_required']) {
                $currentLevel = $level;
            } else {
                break;
            }
        }
        return $currentLevel;
    }
}
