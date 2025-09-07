<?php
require_once __DIR__ . '/../libs/Base.php';

// El nombre de la clase debe coincidir con el nombre del archivo
class PerfilModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    public function getPerfil($id)
    {
        $this->db->query('SELECT * FROM perfil WHERE idUsuario = :id');
        $this->db->bind(':id', $id);
        
        $perfil = $this->db->single();

        // Si no se encuentra un perfil, devolvemos un objeto con valores por defecto
        // para evitar errores en la vista si un usuario aún no ha completado su perfil.
        if (!$perfil) {
            return (object) [
                'foto_perfil' => 'public/img/defaults/default_avatar.png',
                'nickname_artistico' => 'Usuario Nuevo',
                'biografia' => ''
                // Puedes añadir más campos por defecto aquí si los necesitas
            ];
        }

        return $perfil;
    }
    public function editarfoto($datos)
    {
        // Corregido para usar el nombre de columna correcto 'foto_perfil' de tu BD
        $this->db->query("UPDATE perfil SET foto_perfil = :ruta WHERE idUsuario = :iduser");
        $this->db->bind(':ruta', $datos['ruta']);
        $this->db->bind(':iduser', $datos['idUsuario']);
        return $this->db->execute();
    }
}

