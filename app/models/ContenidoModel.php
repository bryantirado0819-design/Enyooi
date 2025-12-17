<?php

class ContenidoModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * Obtiene un array con los IDs de todas las publicaciones
     * que un usuario específico ha desbloqueado.
     */
    public function getContenidoDesbloqueadoPorUsuario($idUsuario)
    {
        $this->db->query("SELECT id_publicacion FROM contenido_desbloqueado WHERE id_usuario = :id_usuario");
        $this->db->bind(':id_usuario', $idUsuario);
        
        $resultados = $this->db->registers();
        
        // Convertimos el array de objetos a un array simple de IDs para fácil validación
        $idsDesbloqueados = [];
        foreach ($resultados as $fila) {
            $idsDesbloqueados[] = $fila->id_publicacion;
        }
        return $idsDesbloqueados;
    }
    
    // ... (Aquí va el método desbloquearContenido que creamos en el paso anterior) ...

    public function desbloquearContenido($idUsuario, $idPublicacion)
    {
        // 1. Obtener datos clave: precio del post, saldo del comprador y ID del creador
        $this->db->query("
            SELECT 
                p.precio_zafiros, 
                p.idUsuarioPublico as id_creadora,
                p.fotoPublicacion,
                p.tipo_archivo,
                u.saldo_zafiros 
            FROM publicaciones p
            JOIN usuarios u ON u.idUsuario = :idUsuario
            WHERE p.idPublicacion = :idPublicacion
        ");
        $this->db->bind(':idUsuario', $idUsuario);
        $this->db->bind(':idPublicacion', $idPublicacion);
        $datos = $this->db->single();

        if (!$datos) {
            return ['success' => false, 'message' => 'La publicación no existe.'];
        }
        if ($datos->id_creadora == $idUsuario) {
            return ['success' => false, 'message' => 'No puedes comprar tu propio contenido.'];
        }
        if ($datos->precio_zafiros <= 0) {
            return ['success' => false, 'message' => 'Este contenido es gratuito.'];
        }
        if ($datos->saldo_zafiros < $datos->precio_zafiros) {
            return ['success' => false, 'message' => 'No tienes suficientes zafiros para desbloquear este contenido.'];
        }

        // 2. Iniciar transacción para asegurar la integridad de los datos
        $this->db->beginTransaction();
        try {
            // Descontar zafiros al comprador
            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - :costo WHERE idUsuario = :idUsuario");
            $this->db->bind(':costo', $datos->precio_zafiros);
            $this->db->bind(':idUsuario', $idUsuario);
            $this->db->execute();

            // Acreditar zafiros a la creadora
            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + :costo WHERE idUsuario = :idCreadora");
            $this->db->bind(':costo', $datos->precio_zafiros);
            $this->db->bind(':idCreadora', $datos->id_creadora);
            $this->db->execute();

            // Registrar la transacción
            $this->db->query("INSERT INTO transacciones (idusuario, tipo, zafiros, estado, id_relacionado) VALUES (:idUsuario, 'compra_contenido', :costo, 'aprobado', :idPublicacion)");
            $this->db->bind(':idUsuario', $idUsuario);
            $this->db->bind(':costo', $datos->precio_zafiros);
            $this->db->bind(':idPublicacion', $idPublicacion);
            $this->db->execute();

            // Marcar el contenido como desbloqueado para el usuario
            $this->db->query("INSERT INTO contenido_desbloqueado (id_usuario, id_publicacion) VALUES (:idUsuario, :idPublicacion)");
            $this->db->bind(':idUsuario', $idUsuario);
            $this->db->bind(':idPublicacion', $idPublicacion);
            $this->db->execute();

            $this->db->commit();
            return [
                'success' => true,
                'media_url' => $datos->fotoPublicacion,
                'media_type' => $datos->tipo_archivo
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error en transacción de desbloqueo: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor. Inténtalo de nuevo.'];
        }
    }
}