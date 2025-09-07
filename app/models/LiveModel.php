<?php
// Modelo Live completo
require_once __DIR__ . '/../libs/Base.php';

class LiveModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function createLive($d)
    {
        $this->db->query("INSERT INTO live_streams (idUsuario, title, mode, rtmp_key, vertical, status)
                          VALUES (:u, :t, :m, :k, :v, 'active')");
        $this->db->bind(':u', $d['idUsuario']);
        $this->db->bind(':t', $d['title']);
        $this->db->bind(':m', $d['mode']);
        $this->db->bind(':k', $d['rtmp_key']);
        $this->db->bind(':v', $d['vertical']);
        $this->db->execute();

        // Obtener el último ID
        $this->db->query("SELECT LAST_INSERT_ID() as id");
        $row = $this->db->single();
        return (int)$row->id;
    }

    public function endLive($liveId, $idUsuario)
    {
        $this->db->query("UPDATE live_streams SET status='ended', ended_at=NOW()
                          WHERE id=:id AND idUsuario=:u");
        $this->db->bind(':id', $liveId);
        $this->db->bind(':u', $idUsuario);
        return $this->db->execute();
    }

    public function getLive($liveId)
    {
        $this->db->query("SELECT * FROM live_streams WHERE id=:id");
        $this->db->bind(':id', $liveId);
        return $this->db->single();
    }

    public function saveChatMessage($liveId, $idUsuario, $message)
    {
        $this->db->query("INSERT INTO live_chat_messages (live_id, idUsuario, message)
                          VALUES (:l,:u,:m)");
        $this->db->bind(':l', $liveId);
        $this->db->bind(':u', $idUsuario);
        $this->db->bind(':m', $message);
        return $this->db->execute();
    }

    public function addLike($liveId, $idUsuario)
    {
        $this->db->query("INSERT INTO live_likes (live_id, idUsuario) VALUES (:l,:u)");
        $this->db->bind(':l', $liveId);
        $this->db->bind(':u', $idUsuario);
        return $this->db->execute();
    }

    public function processDonation($liveId, $idUsuario, $amount)
    {
        // Ver saldo
        $this->db->query("SELECT balance FROM zafiro_wallet WHERE idUsuario=:u");
        $this->db->bind(':u', $idUsuario);
        $w = $this->db->single();
        if (!$w || (int)$w->balance < $amount) {
            return ['success' => false, 'message' => 'Saldo ZAFIRO insuficiente'];
        }

        // Descontar saldo y registrar
        $this->db->query("UPDATE zafiro_wallet SET balance = balance - :a WHERE idUsuario=:u");
        $this->db->bind(':a', $amount);
        $this->db->bind(':u', $idUsuario);
        $this->db->execute();

        $this->db->query("INSERT INTO zafiro_tx (idUsuario, type, amount, ref_type, ref_id)
                          VALUES (:u, 'spend', :a, 'live', :l)");
        $this->db->bind(':u', $idUsuario);
        $this->db->bind(':a', $amount);
        $this->db->bind(':l', $liveId);
        $this->db->execute();

        $this->db->query("INSERT INTO live_donations (live_id, idUsuario, amount_zafiro)
                          VALUES (:l,:u,:a)");
        $this->db->bind(':l', $liveId);
        $this->db->bind(':u', $idUsuario);
        $this->db->bind(':a', $amount);
        $this->db->execute();

        return ['success' => true, 'message' => '¡Gracias por donar!'];
    }

    public function saveLovenseRules($liveId, array $rules)
    {
        // rules: [{min_zafiro, max_zafiro, intensity, duration_ms}, ...]
        foreach ($rules as $r) {
            $this->db->query("INSERT INTO live_lovense_rules
               (live_id, min_zafiro, max_zafiro, intensity, duration_ms)
               VALUES (:l,:minz,:maxz,:i,:d)");
            $this->db->bind(':l', $liveId);
            $this->db->bind(':minz', (int)$r['min_zafiro']);
            $this->db->bind(':maxz', (int)$r['max_zafiro']);
            $this->db->bind(':i', (int)$r['intensity']);
            $this->db->bind(':d', (int)$r['duration_ms']);
            $this->db->execute();
        }
    }

    public function saveLovenseToken($idUsuario, $token, $apiBase)
    {
        $this->db->query("REPLACE INTO user_lovense (idUsuario, access_token, api_base)
                          VALUES (:u,:t,:a)");
        $this->db->bind(':u', $idUsuario);
        $this->db->bind(':t', $token);
        $this->db->bind(':a', $apiBase);
        return $this->db->execute();
    }

    public function triggerLovense($liveId, $amount)
    {
        // Buscar reglas aplicables
        $this->db->query("SELECT intensity, duration_ms
                          FROM live_lovense_rules
                          WHERE live_id=:l AND :amt BETWEEN min_zafiro AND max_zafiro
                          ORDER BY min_zafiro DESC LIMIT 1");
        $this->db->bind(':l', $liveId);
        $this->db->bind(':amt', $amount);
        $rule = $this->db->single();
        if (!$rule) return false;

        // Obtener el creador
        $this->db->query("SELECT idUsuario FROM live_streams WHERE id=:l");
        $this->db->bind(':l', $liveId);
        $row = $this->db->single();
        if (!$row) return false;

        // Token Lovense
        $this->db->query("SELECT access_token, api_base FROM user_lovense WHERE idUsuario=:u");
        $this->db->bind(':u', $row->idUsuario);
        $lv = $this->db->single();
        if (!$lv) return false;

        // Llamada HTTP a Lovense (simple cURL)
        $payload = [
            'command'  => 'Vibrate',
            'strength' => (int)$rule->intensity,
            'time'     => (int)$rule->duration_ms
        ];
        $url = rtrim($lv->api_base, '/') . '/api/command?token=' . urlencode($lv->access_token);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return true;
    }
}

?>