<?php
class WalletModel {
    private $db;

    public function __construct() {
        $this->db = new Base;
    }

    public function crearTransaccionRecarga($idUsuario, $monto, $zafiros) {
        $this->db->query('INSERT INTO transacciones (idusuario, tipo, monto, zafiros, currency, estado, metodo) 
                         VALUES (:idusuario, "recharge", :monto, :zafiros, "USD", "pendiente", "datafast")');
        
        $this->db->bind(':idusuario', $idUsuario);
        $this->db->bind(':monto', $monto);
        $this->db->bind(':zafiros', $zafiros);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function marcarTransaccionComoAprobada($idTransaccion) {
        $this->db->beginTransaction();
        try {
            $this->db->query('SELECT * FROM transacciones WHERE idtransaccion = :id AND estado = "pendiente"');
            $this->db->bind(':id', $idTransaccion);
            $transaccion = $this->db->single();

            if (!$transaccion) throw new Exception("Transacción no válida.");

            $this->db->query('UPDATE transacciones SET estado = "aprobado" WHERE idtransaccion = :id');
            $this->db->bind(':id', $idTransaccion);
            $this->db->execute();

            $this->db->query('UPDATE usuarios SET saldo_zafiros = saldo_zafiros + :zafiros WHERE idUsuario = :uid');
            $this->db->bind(':zafiros', $transaccion->zafiros);
            $this->db->bind(':uid', $transaccion->idusuario);
            $this->db->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al procesar pago: ' . $e->getMessage());
            return false;
        }
    }
    
    public function marcarTransaccionComoRechazada($idTransaccion) {
        $this->db->query('UPDATE transacciones SET estado = "rechazado" WHERE idtransaccion = :id AND estado = "pendiente"');
        $this->db->bind(':id', $idTransaccion);
        return $this->db->execute();
    }
}