<?php
require_once __DIR__ . '/../libs/Base.php';
class SuscripcionModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    /**
     * ✅ LÓGICA COMPLETA PARA CREAR UNA SUSCRIPCIÓN Y TRANSFERIR ZAFIROS
     * Maneja la transacción completa de una suscripción.
     *
     * @param int $idSuscriptor El ID del usuario que paga.
     * @param int $idCreadora El ID de la creadora que recibe.
     * @return array Un array con el estado del éxito y un mensaje.
     */
    public function crearSuscripcion($idSuscriptor, $idCreadora)
    {
        // 1. Obtener datos clave: precio, saldo del suscriptor y si ya está suscrito.
        $this->db->query("
            SELECT 
                p.precio_suscripcion, 
                u.saldo_zafiros,
                (SELECT 1 FROM subscriptions s WHERE s.subscriber_id = :id_suscriptor AND s.creator_id = :id_creadora AND s.status = 'active' AND s.renewal_date > NOW()) as ya_suscrito
            FROM perfil p
            JOIN usuarios u ON u.idUsuario = :id_suscriptor
            WHERE p.idusuario = :id_creadora
        ");
        $this->db->bind(':id_suscriptor', $idSuscriptor);
        $this->db->bind(':id_creadora', $idCreadora);
        $datos = $this->db->single();

        // 2. Validaciones iniciales
        if (!$datos) {
            return ['success' => false, 'message' => 'La creadora no existe o no ha configurado un precio.'];
        }
        if ($datos->ya_suscrito) {
            return ['success' => false, 'message' => 'Ya estás suscrito a esta creadora.'];
        }
        if ($datos->saldo_zafiros < $datos->precio_suscripcion) {
            return ['success' => false, 'message' => 'No tienes suficientes Zafiros. Por favor, recarga tu saldo.'];
        }
        $costoZafiros = (int)$datos->precio_suscripcion;

        // 3. Iniciar transacción de base de datos para seguridad
        $this->db->beginTransaction();

        try {
            // 3.1. Descontar Zafiros al suscriptor
            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros - :costo WHERE idUsuario = :id_suscriptor");
            $this->db->bind(':costo', $costoZafiros);
            $this->db->bind(':id_suscriptor', $idSuscriptor);
            $this->db->execute();

            // 3.2. Acreditar Zafiros a la creadora
            $this->db->query("UPDATE usuarios SET saldo_zafiros = saldo_zafiros + :costo WHERE idUsuario = :id_creadora");
            $this->db->bind(':costo', $costoZafiros);
            $this->db->bind(':id_creadora', $idCreadora);
            $this->db->execute();

            // 3.3. Crear o actualizar el registro de suscripción (válida por 1 mes)
            // ✅ CORRECCIÓN APLICADA AQUÍ
            $this->db->query("
                INSERT INTO subscriptions (subscriber_id, creator_id, zafiros, status, started_at, renewal_date)
                VALUES (:subscriber, :creator, :zafiros_insert, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
                ON DUPLICATE KEY UPDATE 
                status = 'active', 
                renewal_date = DATE_ADD(NOW(), INTERVAL 1 MONTH),
                zafiros = :zafiros_update;
            ");
            $this->db->bind(':subscriber', $idSuscriptor);
            $this->db->bind(':creator', $idCreadora);
            // Se usan nombres diferentes para el mismo valor para evitar el error de PDO
            $this->db->bind(':zafiros_insert', $costoZafiros);
            $this->db->bind(':zafiros_update', $costoZafiros);
            $this->db->execute();
            
            // 3.4. (Opcional pero recomendado) Registrar en la tabla de transacciones
            $this->db->query("INSERT INTO transacciones (idusuario, creator_id, tipo, zafiros, estado) VALUES (:id_usuario, :id_creadora, 'subscription', :zafiros, 'aprobado')");
            $this->db->bind(':id_usuario', $idSuscriptor);
            $this->db->bind(':id_creadora', $idCreadora);
            $this->db->bind(':zafiros', $costoZafiros);
            $this->db->execute();


            // 4. Si todo salió bien, confirmar los cambios
            $this->db->commit();
            return ['success' => true, 'message' => '¡Suscripción exitosa!'];

        } catch (Exception $e) {
            // 5. Si algo falla, revertir todo para no dejar datos inconsistentes
            $this->db->rollBack();
            error_log('Error en transacción de suscripción: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error durante la transacción. Inténtalo de nuevo.'];
        }
    }

    /**
     * Verifica si un usuario está actualmente suscrito a una creadora.
     * Devuelve true si la suscripción está activa, false en caso contrario.
     */
    public function verificarSuscripcion($idSuscriptor, $idCreadora)
    {
        // ✅ CORREGIDO para usar la tabla `subscriptions`
        $this->db->query("
            SELECT 1 FROM subscriptions 
            WHERE subscriber_id = :id_suscriptor 
              AND creator_id = :id_creadora 
              AND status = 'active' 
              AND renewal_date > NOW()
        ");
        $this->db->bind(':id_suscriptor', $idSuscriptor);
        $this->db->bind(':id_creadora', $idCreadora);
        
        return $this->db->single() ? true : false;
    }
}