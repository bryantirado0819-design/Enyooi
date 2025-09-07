<?php
// app/models/MensajesModel.php

class MensajesModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function createMessage($data)
    {
        $this->db->query('INSERT INTO mensajes (remitente_id, destinatario_id, contenido, media_url, media_tipo) VALUES (:remitente, :destinatario, :contenido, :media_url, :media_tipo)');
        $this->db->bind(':remitente', $data['remitente_id']);
        $this->db->bind(':destinatario', $data['destinatario_id']);
        $this->db->bind(':contenido', $data['contenido']);
        $this->db->bind(':media_url', $data['media_url'] ?? null);
        $this->db->bind(':media_tipo', $data['media_tipo'] ?? null);
        
        if ($this->db->execute()) {
            $this->db->query("SELECT LAST_INSERT_ID() as id");
            return $this->db->single()->id;
        }
        return false;
    }
    
    public function getMessagesBetweenUsers($userId1, $userId2)
    {
        $this->db->query('
            SELECT * FROM mensajes
            WHERE (remitente_id = :user1 AND destinatario_id = :user2)
               OR (remitente_id = :user2 AND destinatario_id = :user1)
            ORDER BY fechaMensaje ASC
        ');
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        return $this->db->registers();
    }

    public function markMessagesAsRead($remitente_id, $destinatario_id) {
        $this->db->query('UPDATE mensajes SET leido = 1 WHERE remitente_id = :remitente_id AND destinatario_id = :destinatario_id AND leido = 0');
        $this->db->bind(':remitente_id', $remitente_id);
        $this->db->bind(':destinatario_id', $destinatario_id);
        return $this->db->execute();
    }

    public function getConversations($userId, $query = '')
    {
        // ✅ **CORRECCIÓN**: Consulta reescrita para eficiencia y para obtener el conteo de no leídos.
        $sql = "
            SELECT 
                u.idUsuario as id,
                u.usuario,
                u.rol,
                p.nickname_artistico,
                p.foto_perfil,
                p.chat_precio,
                (SELECT 1 FROM chats_desbloqueados WHERE id_usuario = :userId AND id_creadora = u.idUsuario) as chat_desbloqueado,
                m.contenido as last_message_content,
                m.fechaMensaje as last_message_time,
                (SELECT COUNT(*) FROM mensajes WHERE remitente_id = u.idUsuario AND destinatario_id = :userId AND leido = 0) as unread_count
            FROM usuarios u
            LEFT JOIN perfil p ON u.idUsuario = p.idusuario
            LEFT JOIN (
                SELECT 
                    IF(remitente_id = :userId, destinatario_id, remitente_id) as conversation_partner,
                    MAX(idMensaje) as max_id
                FROM mensajes
                WHERE remitente_id = :userId OR destinatario_id = :userId
                GROUP BY conversation_partner
            ) AS last_msg_sub ON u.idUsuario = last_msg_sub.conversation_partner
            LEFT JOIN mensajes m ON m.idMensaje = last_msg_sub.max_id
            WHERE u.idUsuario != :userId";

        if (!empty($query)) {
            $sql .= " AND (u.usuario LIKE :query OR p.nickname_artistico LIKE :query)";
        } else {
             $sql .= " AND m.idMensaje IS NOT NULL"; // Por defecto, solo muestra usuarios con quienes hay chat
        }
        
        $sql .= " ORDER BY last_message_time DESC, u.usuario ASC";

        $this->db->query($sql);
        $this->db->bind(':userId', $userId);
        if (!empty($query)) {
            $this->db->bind(':query', '%' . $query . '%');
        }
        return $this->db->registers();
    }
    
    public function unlockChat($usuarioId, $creadoraId)
    {
        // ... (el resto de esta función se mantiene igual)
        $this->db->query("SELECT p.chat_precio, u.saldo_zafiros FROM perfil p JOIN usuarios u ON u.idUsuario = :usuarioId WHERE p.idusuario = :creadoraId");
        $this->db->bind(':usuarioId', $usuarioId);
        $this->db->bind(':creadoraId', $creadoraId);
        $data = $this->db->single();

        if (!$data || $data->chat_precio <= 0) {
            $this->db->query("INSERT INTO chats_desbloqueados (id_usuario, id_creadora) VALUES (:usuarioId, :creadoraId) ON DUPLICATE KEY UPDATE id_usuario=id_usuario");
            $this->db->bind(':usuarioId', $usuarioId);
            $this->db->bind(':creadoraId', $creadoraId);
            $this->db->execute();
            return ['success' => true];
        }

        if ($data->saldo_zafiros < $data->chat_precio) {
            return ['success' => false, 'message' => 'No tienes suficientes zafiros.'];
        }

        try {
            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - :precio WHERE idUsuario = :usuarioId");
            $this->db->bind(':precio', $data->chat_precio);
            $this->db->bind(':usuarioId', $usuarioId);
            $this->db->execute();

            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + :precio WHERE idUsuario = :creadoraId");
            $this->db->bind(':precio', $data->chat_precio);
            $this->db->bind(':creadoraId', $creadoraId);
            $this->db->execute();

            $this->db->query("INSERT INTO chats_desbloqueados (id_usuario, id_creadora) VALUES (:usuarioId, :creadoraId)");
            $this->db->bind(':usuarioId', $usuarioId);
            $this->db->bind(':creadoraId', $creadoraId);
            $this->db->execute();

            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en la transacción.'];
        }
    }
}