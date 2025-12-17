<?php
// app/models/LikesModel.php

class LikesModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * ✅ NEW & EFFICIENT: Gets an array of post IDs that a user has liked.
     * This is the function that was missing and caused the error.
     *
     * @param int $idUsuario The ID of the user.
     * @return array An array of integers (post IDs).
     */
    public function getLikedPostIdsByUser($idUsuario)
    {
        $this->db->query('SELECT idPublicacion FROM likes WHERE idUsuario = :idUsuario');
        $this->db->bind(':idUsuario', $idUsuario);
        
        $results = $this->db->registers();
        
        // Convert the array of objects to a simple array of IDs
        $idArray = [];
        foreach ($results as $row) {
            $idArray[] = (int)$row->idPublicacion;
        }
        return $idArray;
    }

    /**
     * Verifica si un usuario específico ya le ha dado "like" a una publicación.
     * Devuelve true si ya existe el like, false si no.
     */
    public function verificarLikeUsuario($idPublicacion, $idUsuario)
    {
        $this->db->query('SELECT 1 FROM likes WHERE idPublicacion = :idPublicacion AND idUsuario = :idUsuario');
        $this->db->bind(':idPublicacion', $idPublicacion);
        $this->db->bind(':idUsuario', $idUsuario);
        return $this->db->single() ? true : false;
    }
    
    /**
     * Obtiene el número total de "likes" de una publicación.
     */
    public function getLikeCount($idPublicacion)
    {
        $this->db->query("SELECT COUNT(*) as total FROM likes WHERE idPublicacion = :idPublicacion");
        $this->db->bind(':idPublicacion', $idPublicacion);
        $resultado = $this->db->single();
        return $resultado ? (int)$resultado->total : 0;
    }

    /**
     * Toggles a like on a post and returns the new like status.
     */
    public function toggleLike($idPublicacion, $idUsuario, $idPropietario)
    {
        if ($this->verificarLikeUsuario($idPublicacion, $idUsuario)) {
            // User has liked it, so unlike it
            $this->db->query('DELETE FROM likes WHERE idPublicacion = :idPublicacion AND idUsuario = :idUsuario');
            $this->db->bind(':idPublicacion', $idPublicacion);
            $this->db->bind(':idUsuario', $idUsuario);
            $this->db->execute();
            return false; // Liked status is now false
        } else {
            // User has not liked it, so like it
            $this->db->query('INSERT INTO likes (idPublicacion, idUsuario) VALUES (:idPublicacion, :idUsuario)');
            $this->db->bind(':idPublicacion', $idPublicacion);
            $this->db->bind(':idUsuario', $idUsuario);
            $this->db->execute();
            return true; // Liked status is now true
        }
    }
}