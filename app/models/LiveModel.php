<?php
class LiveModel
{
    private $db;

    public function __construct()
    {
        // Se conecta a la base de datos usando la librería Base
        $this->db = new Base;
    }

    // =========================================================================
    // 📡 FUNCIONES PRINCIPALES DEL STREAM (ESPECTADOR / IVS)
    // =========================================================================

    // ✅ Obtiene todos los datos vitales para el reproductor (Player + Info Creador)
    public function getStreamAndCreatorInfo($streamId)
    {
        $defaultThumb = '/public/img/logo_enyooi.png';
        $defaultAvatar = '/public/img/defaults/default_avatar.png';
        
        $this->db->query("
            SELECT 
                s.*, 
                u.usuario, 
                -- Nickname: Usa el artístico si existe, sino el usuario, sino 'Creador'
                COALESCE(NULLIF(p.nickname_artistico, ''), u.usuario, 'Creador') as creator_nickname,
                -- Avatar: Usa foto perfil si existe, sino default
                COALESCE(NULLIF(p.foto_perfil, ''), :defaultAvatar) as creator_avatar,
                -- Thumbnail: Usa banner si existe, sino default
                COALESCE(NULLIF(p.banner_portada, ''), :defaultThumb) as thumbnail_url,
                p.bio,
                s.roulette_enabled,
                s.roulette_cost,
                u.saldo_zafiros,
                -- CAMPOS AWS IVS IMPORTANTES
                s.ivs_playback_url
            FROM streams s
            JOIN usuarios u ON s.creator_id = u.idUsuario
            LEFT JOIN perfil p ON s.creator_id = p.idusuario
            WHERE s.idstream = :id
        ");
        
        $this->db->bind(':id', $streamId);
        $this->db->bind(':defaultThumb', $defaultThumb);
        $this->db->bind(':defaultAvatar', $defaultAvatar);
        
        $result = $this->db->single();
        return $result ?: false;
    }

    // ✅ Listado de Streams Activos (Para la página /lives o Home)
    public function getActiveStreams()
    {
        $defaultThumb = '/public/img/logo_enyooi.png';
        
        $this->db->query("
            SELECT
                s.idstream AS stream_id,
                s.titulo AS stream_title,
                COALESCE(NULLIF(p.banner_portada, ''), '$defaultThumb') AS thumbnail_url,
                COALESCE(NULLIF(p.nickname_artistico, ''), u.usuario) AS creator_nickname,
                COALESCE(NULLIF(p.foto_perfil, ''), '/public/img/defaults/default_avatar.png') AS creator_avatar 
            FROM streams s
            JOIN usuarios u ON s.creator_id = u.idUsuario
            LEFT JOIN perfil p ON s.creator_id = p.idusuario
            WHERE s.estado = 'live'
            ORDER BY s.created_at DESC
        ");
        
        return $this->db->registers() ?: [];
    }

    // =========================================================================
    // ⚙️ FUNCIONES DE GESTIÓN Y CREDENCIALES (CREADOR)
    // =========================================================================

    // Generar llave de stream tradicional (OBS antiguo / Backup)
    public function createOrUpdateStreamKey($creatorId) {
        $this->db->query("SELECT idstream FROM streams WHERE creator_id = :id");
        $this->db->bind(':id', $creatorId);
        if (!$this->db->single()) {
            $key = bin2hex(random_bytes(16));
            $this->db->query("INSERT INTO streams (creator_id, stream_key) VALUES (:id, :key)");
            $this->db->bind(':id', $creatorId);
            $this->db->bind(':key', $key);
            $this->db->execute();
        }
    }
    
    // Obtener datos crudos del stream por ID de Creador
    public function getStreamDataByCreatorId($creatorId) {
        $this->db->query("SELECT * FROM streams WHERE creator_id = :id");
        $this->db->bind(':id', $creatorId);
        return $this->db->single();
    }
    
    // Obtener datos crudos del stream por ID de Stream (Helper para processSpin)
    public function getStreamDataById($id) { 
        $this->db->query("SELECT * FROM streams WHERE idstream = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // ✅ NUEVO: Guarda credenciales IVS (AWS) de forma persistente
    public function saveIvsCredentials($userId, $arn, $ingest, $key, $playback) {
        // Verifica si existe registro para hacer UPDATE o INSERT
        $exists = $this->getStreamDataByCreatorId($userId);
        if ($exists) {
            $this->db->query("UPDATE streams SET ivs_channel_arn=:arn, ivs_ingest_endpoint=:ing, ivs_stream_key=:key, ivs_playback_url=:play WHERE creator_id=:id");
        } else {
            $this->db->query("INSERT INTO streams (creator_id, ivs_channel_arn, ivs_ingest_endpoint, ivs_stream_key, ivs_playback_url, estado) VALUES (:id, :arn, :ing, :key, :play, 'offline')");
        }
        $this->db->bind(':arn', $arn);
        $this->db->bind(':ing', $ingest);
        $this->db->bind(':key', $key);
        $this->db->bind(':play', $playback);
        $this->db->bind(':id', $userId);
        $this->db->execute();
    }

    // Poner stream EN VIVO
    public function setStreamLive($userId, $title) {
        $this->db->query("UPDATE streams SET estado='live', titulo=:t, started_at=NOW(), ended_at=NULL WHERE creator_id=:id");
        $this->db->bind(':t', $title);
        $this->db->bind(':id', $userId);
        $this->db->execute();
        
        // Retornar el ID del stream activo
        $s = $this->getStreamDataByCreatorId($userId);
        return $s->idstream;
    }

    // Poner stream OFFLINE
    public function setStreamOffline($userId) {
        $this->db->query("UPDATE streams SET estado='offline', ended_at=NOW() WHERE creator_id=:id");
        $this->db->bind(':id', $userId);
        $this->db->execute();
    }

    // =========================================================================
    // 💎 MONETIZACIÓN Y HERRAMIENTAS (TIPS, RULETA, LOVENSE)
    // =========================================================================

    public function getTipOptions($creatorId) {
        $this->db->query("SELECT * FROM stream_tip_options WHERE creator_id = :id AND is_active = 1");
        $this->db->bind(':id', $creatorId);
        return $this->db->registers() ?: [];
    }

    public function getRouletteOptions($creatorId) {
        $this->db->query("SELECT * FROM stream_roulette_options WHERE creator_id = :id AND is_enabled = 1");
        $this->db->bind(':id', $creatorId);
        return $this->db->registers() ?: [];
    }

    public function getLovenseTipOptions($creatorId) { 
        // Implementar si tienes tabla específica de Lovense, por ahora array vacío para evitar error
        return []; 
    }

    public function getActiveTipGoal($creatorId) {
        try {
            $this->db->query("SELECT * FROM stream_objectives WHERE idcreadora = :id AND is_active = 1 LIMIT 1");
            $this->db->bind(':id', $creatorId);
            return $this->db->single();
        } catch(Exception $e) { return null; }
    }
    
    // --- CRUD Simplificado para Configuración ---

    public function addTipOption($creatorId, $zafiros, $descripcion) { 
        $this->db->query("INSERT INTO stream_tip_options (creator_id, zafiros, descripcion) VALUES (:id, :z, :d)"); 
        $this->db->bind(':id', $creatorId); 
        $this->db->bind(':z', $zafiros); 
        $this->db->bind(':d', $descripcion); 
        return $this->db->execute() ? $this->db->lastInsertId() : false; 
    }

    public function deleteTipOption($id, $creatorId) { 
        $this->db->query("DELETE FROM stream_tip_options WHERE id = :id AND creator_id = :cid"); 
        $this->db->bind(':id', $id); 
        $this->db->bind(':cid', $creatorId); 
        return $this->db->execute(); 
    }

    public function addRouletteOption($creatorId, $texto) { 
        $this->db->query("INSERT INTO stream_roulette_options (creator_id, option_text) VALUES (:id, :txt)"); 
        $this->db->bind(':id', $creatorId); 
        $this->db->bind(':txt', $texto); 
        return $this->db->execute() ? $this->db->lastInsertId() : false; 
    }

    public function deleteRouletteOption($id, $creatorId) { 
        $this->db->query("DELETE FROM stream_roulette_options WHERE id = :id AND creator_id = :cid"); 
        $this->db->bind(':id', $id); 
        $this->db->bind(':cid', $creatorId); 
        return $this->db->execute(); 
    }

    public function updateStreamSettings($creatorId, $data) { 
        $this->db->query("UPDATE streams SET titulo = :t, descripcion = :d WHERE creator_id = :id"); 
        $this->db->bind(':t', $data['title']); 
        $this->db->bind(':d', $data['description']); 
        $this->db->bind(':id', $creatorId); 
        return $this->db->execute(); 
    }

    public function updateTipGoalProgress($creatorId, $amount) { 
        try { 
            $this->db->query("UPDATE stream_objectives SET current_zafiros = current_zafiros + :a WHERE idcreadora = :id AND is_active = 1"); 
            $this->db->bind(':a', $amount); 
            $this->db->bind(':id', $creatorId); 
            return $this->db->execute(); 
        } catch(Exception $e) { return false; } 
    }
}