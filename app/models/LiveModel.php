<?php
class LiveModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function getStreamDataByCreatorId($creatorId)
    {
        // CORREGIDO: La tabla `streams` usa `creator_id`
        $this->db->query("SELECT * FROM streams WHERE creator_id = :id");
        $this->db->bind(':id', $creatorId);
        return $this->db->single();
    }

    public function createOrUpdateStreamKey($creatorId)
    {
        // CORREGIDO: La tabla `streams` usa `creator_id`
        $this->db->query("SELECT idstream FROM streams WHERE creator_id = :id");
        $this->db->bind(':id', $creatorId);
        if (!$this->db->single()) {
            $streamKey = bin2hex(random_bytes(16));
            // CORREGIDO: La tabla `streams` usa `creator_id`
            $this->db->query("INSERT INTO streams (creator_id, stream_key) VALUES (:id, :key)");
            $this->db->bind(':id', $creatorId);
            $this->db->bind(':key', $streamKey);
            return $this->db->execute();
        }
        return true;
    }
    
    // --- MÉTODOS COMPLETOS PARA MONETIZACIÓN (CORREGIDOS) ---

    public function getTipOptions($creatorId) {
        // CORREGIDO: La tabla `stream_tip_options` usa `creator_id`
        $this->db->query("SELECT * FROM stream_tip_options WHERE creator_id = :id AND is_active = 1");
        $this->db->bind(':id', $creatorId);
        return $this->db->registers();
    }

    public function getRouletteOptions($creatorId) {
        // CORREGIDO: La tabla `stream_roulette_options` usa `creator_id`
        $this->db->query("SELECT * FROM stream_roulette_options WHERE creator_id = :id AND is_enabled = 1");
        $this->db->bind(':id', $creatorId);
        return $this->db->registers();
    }
    
    public function getLovenseTipOptions($creatorId) {
        // CORREGIDO: La tabla `lovense_tip_options` usa `creator_id`
        $this->db->query("SELECT * FROM lovense_tip_options WHERE creator_id = :id AND is_active = 1");
        $this->db->bind(':id', $creatorId);
        return $this->db->registers();
    }
    
    public function addTipOption($creatorId, $zafiros, $descripcion) {
        // CORREGIDO: La tabla `stream_tip_options` usa `creator_id`
        $this->db->query("INSERT INTO stream_tip_options (creator_id, zafiros, descripcion) VALUES (:id, :z, :d)");
        $this->db->bind(':id', $creatorId);
        $this->db->bind(':z', $zafiros);
        $this->db->bind(':d', $descripcion);
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    public function deleteTipOption($optionId, $creatorId) {
        // CORREGIDO: La tabla `stream_tip_options` usa `creator_id`
        $this->db->query("DELETE FROM stream_tip_options WHERE id = :id AND creator_id = :cid");
        $this->db->bind(':id', $optionId);
        $this->db->bind(':cid', $creatorId);
        return $this->db->execute();
    }

    public function addRouletteOption($creatorId, $texto) {
        // CORREGIDO: La tabla `stream_roulette_options` usa `creator_id`
        $this->db->query("INSERT INTO stream_roulette_options (creator_id, option_text) VALUES (:id, :txt)");
        $this->db->bind(':id', $creatorId);
        $this->db->bind(':txt', $texto);
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    public function deleteRouletteOption($optionId, $creatorId) {
        // CORREGIDO: La tabla `stream_roulette_options` usa `creator_id`
        $this->db->query("DELETE FROM stream_roulette_options WHERE id = :id AND creator_id = :cid");
        $this->db->bind(':id', $optionId);
        $this->db->bind(':cid', $creatorId);
        return $this->db->execute();
    }
    
    public function addLovenseTipOption($creatorId, $zafiros, $duration, $intensity) {
        // CORREGIDO: La tabla `lovense_tip_options` usa `creator_id`.
        $this->db->query("INSERT INTO lovense_tip_options (creator_id, zafiros, duration_seconds, intensity_level) VALUES (:id, :z, :d, :i)");
        $this->db->bind(':id', $creatorId);
        $this->db->bind(':z', $zafiros);
        $this->db->bind(':d', $duration);
        $this->db->bind(':i', $intensity);
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    public function deleteLovenseTipOption($optionId, $creatorId) {
        // CORREGIDO: La tabla `lovense_tip_options` usa `creator_id`.
        $this->db->query("DELETE FROM lovense_tip_options WHERE id = :id AND creator_id = :cid");
        $this->db->bind(':id', $optionId);
        $this->db->bind(':cid', $creatorId);
        return $this->db->execute();
    }
    
    public function updateStreamSettings($creatorId, $data) {
        // CORREGIDO: La tabla `streams` usa `creator_id`
        $this->db->query("UPDATE streams SET titulo = :title, descripcion = :desc, roulette_enabled = :r_enabled, roulette_cost = :r_cost WHERE creator_id = :id");
        $this->db->bind(':title', $data['titulo'] ?? 'En vivo');
        $this->db->bind(':desc', $data['descripcion'] ?? '');
        $this->db->bind(':r_enabled', $data['roulette_enabled'] ? 1 : 0);
        $this->db->bind(':r_cost', $data['roulette_cost'] ?? 50);
        $this->db->bind(':id', $creatorId);
        return $this->db->execute();
    }

    public function getActiveStreams()
    {
        // ✅ CORRECCIÓN: Eliminada la subconsulta de tags
        $sql = "
            SELECT
                s.idstream AS stream_id,
                s.titulo AS stream_title,
                s.thumbnail_url,
                u.nickname AS creator_nickname,
                p.foto_perfil AS creator_avatar 
                -- Eliminada la subconsulta de tags ya que la tabla stream_tag_relations no existe
            FROM
                streams s
            JOIN
                usuarios u ON s.creator_id = u.idUsuario
            LEFT JOIN
                perfil p ON s.creator_id = p.idusuario
            WHERE
                s.estado = 'live'
            ORDER BY
                s.created_at DESC
        ";

        $this->db->query($sql);
        $results = $this->db->registers(); // registers() devuelve un array o false

        // ✅ CORRECCIÓN: Asegurarse de devolver siempre un array
        return $results ?: []; // Si registers() devuelve false, devolvemos un array vacío
    }
} 
