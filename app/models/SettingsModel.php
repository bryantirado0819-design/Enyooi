<?php

class SettingsModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function updateProfileData($datos)
    {
        $this->db->query('UPDATE usuarios SET usuario = :usuario WHERE idUsuario = :id');
        $this->db->bind(':usuario', $datos['usuario']);
        $this->db->bind(':id', $datos['id']);
        $this->db->execute();
// ... dentro de public function updateProfileData($datos) ...
$this->db->query('UPDATE perfil SET nickname_artistico = :nickname, bio = :bio, chat_precio = :chat_precio WHERE idUsuario = :id');
$this->db->bind(':nickname', $datos['nickname']);
$this->db->bind(':bio', $datos['bio']);
$this->db->bind(':chat_precio', $datos['chat_precio']); // Añadir esto
$this->db->bind(':id', $datos['id']);
// ...
        $this->db->query('UPDATE perfil SET nickname_artistico = :nickname, bio = :bio WHERE idUsuario = :id');
        $this->db->bind(':nickname', $datos['nickname']);
        $this->db->bind(':bio', $datos['bio']);
        $this->db->bind(':id', $datos['id']);
        
        return $this->db->execute();
    }

    public function updateEmail($idUsuario, $nuevoCorreo) {
        $this->db->query("UPDATE usuarios SET correo = :correo WHERE idUsuario = :id");
        $this->db->bind(':correo', $nuevoCorreo);
        $this->db->bind(':id', $idUsuario);
        return $this->db->execute();
    }
    
    public function updatePassword($idUsuario, $nuevaContrasenaHash) {
        $this->db->query("UPDATE usuarios SET contrasena = :contrasena WHERE idUsuario = :id");
        $this->db->bind(':contrasena', $nuevaContrasenaHash);
        $this->db->bind(':id', $idUsuario);
        return $this->db->execute();
    }

    public function storeVerificationCode($idUsuario, $code, $type, $newValue = null) {
        $this->db->query("DELETE FROM verification_codes WHERE user_id = :uid AND type = :type");
        $this->db->bind(':uid', $idUsuario);
        $this->db->bind(':type', $type);
        $this->db->execute();

        $this->db->query("INSERT INTO verification_codes (user_id, code, type, new_value, expires_at) VALUES (:uid, :code, :type, :new_value, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $this->db->bind(':uid', $idUsuario);
        $this->db->bind(':code', $code);
        $this->db->bind(':type', $type);
        $this->db->bind(':new_value', $newValue);
        return $this->db->execute();
    }
    
    public function getVerificationCode($idUsuario, $code, $type) {
        $this->db->query("SELECT * FROM verification_codes WHERE user_id = :uid AND code = :code AND type = :type AND expires_at > NOW()");
        $this->db->bind(':uid', $idUsuario);
        $this->db->bind(':code', $code);
        $this->db->bind(':type', $type);
        return $this->db->single();
    }

    public function deleteVerificationCode($id) {
        $this->db->query("DELETE FROM verification_codes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    public function markAccountAsVerified($idUsuario) {
        $this->db->query("UPDATE usuarios SET cuenta_verificada = 1 WHERE idUsuario = :id");
        $this->db->bind(':id', $idUsuario);
        return $this->db->execute();
    }

    public function updateProfileImage($idUsuario, $ruta) {
        $this->db->query('UPDATE perfil SET foto_perfil = :ruta WHERE idUsuario = :id');
        $this->db->bind(':ruta', $ruta);
        $this->db->bind(':id', $idUsuario);
        return $this->db->execute();
    }
    
    public function updateBannerImage($idUsuario, $ruta) {
        $this->db->query('UPDATE perfil SET banner_portada = :ruta WHERE idUsuario = :id');
        $this->db->bind(':ruta', $ruta);
        $this->db->bind(':id', $idUsuario);
        return $this->db->execute();
    }
}