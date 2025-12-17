<?php
class RetiroModel
{
    private $db;
    private const TASA_CONVERSION = 7; // 7 zafiros = 1 USD
    private const COMISION_PLATAFORMA = 0.10; // 10%
    private const MONTO_MINIMO_RETIRO = 20.00; // USD

    public function __construct()
    {
        $this->db = new Base;
    }

    public function getCreatorFinancialSummary($idCreadora)
    {
        // Saldo actual
        $this->db->query('SELECT saldo_zafiros FROM usuarios WHERE idUsuario = :id');
        $this->db->bind(':id', $idCreadora);
        $saldoZafiros = (int)($this->db->single()->saldo_zafiros ?? 0);

        // Ingresos de los últimos 7 días
        $this->db->query("
            SELECT DATE(fecha) as dia, SUM(monto_neto_usd) as total_ingresos
            FROM transacciones_financieras
            WHERE id_usuario = :id_creadora AND tipo LIKE 'ingreso_%' AND fecha >= CURDATE() - INTERVAL 7 DAY
            GROUP BY DATE(fecha)
            ORDER BY fecha ASC
        ");
        $this->db->bind(':id_creadora', $idCreadora);
        $ingresosDiarios = $this->db->registers();
        
        // Historial de retiros
        $this->db->query("
            SELECT monto_final_usd, estado, fecha_solicitud 
            FROM solicitudes_retiro 
            WHERE id_creadora = :id_creadora 
            ORDER BY fecha_solicitud DESC LIMIT 5
        ");
        $this->db->bind(':id_creadora', $idCreadora);
        $historialRetiros = $this->db->registers();

        return [
            'saldo_zafiros' => $saldoZafiros,
            'saldo_usd_disponible' => floor($saldoZafiros / self::TASA_CONVERSION),
            'ingresos_ultimos_7_dias' => $ingresosDiarios,
            'historial_retiros' => $historialRetiros
        ];
    }
    
    public function storeVerificationCode($idCreadora, $codigo)
    {
        $this->db->query("INSERT INTO codigos_verificacion_retiro (id_creadora, codigo, expires_at) VALUES (:id, :codigo, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $this->db->bind(':id', $idCreadora);
        $this->db->bind(':codigo', $codigo);
        return $this->db->execute();
    }

    public function verifyCode($idCreadora, $codigo)
    {
        $this->db->query("SELECT id FROM codigos_verificacion_retiro WHERE id_creadora = :id AND codigo = :codigo AND expires_at > NOW()");
        $this->db->bind(':id', $idCreadora);
        $this->db->bind(':codigo', $codigo);
        $result = $this->db->single();
        if ($result) {
            $this->db->query("DELETE FROM codigos_verificacion_retiro WHERE id = :id_codigo");
            $this->db->bind(':id_codigo', $result->id);
            $this->db->execute();
            return true;
        }
        return false;
    }

    public function createWithdrawalRequest($idCreadora, $datosFormulario)
    {
        $montoUSD = (float)($datosFormulario['amount'] ?? 0);
        
        $this->db->query('SELECT saldo_zafiros FROM usuarios WHERE idUsuario = :id');
        $this->db->bind(':id', $idCreadora);
        $saldoZafiros = (int)($this->db->single()->saldo_zafiros ?? 0);
        $saldoEquivalenteUSD = floor($saldoZafiros / self::TASA_CONVERSION);

        if ($montoUSD < self::MONTO_MINIMO_RETIRO) {
            return ['success' => false, 'message' => 'El monto mínimo para retirar es de $' . self::MONTO_MINIMO_RETIRO . ' USD.'];
        }
        if ($montoUSD > $saldoEquivalenteUSD) {
            return ['success' => false, 'message' => 'No tienes saldo suficiente para retirar ese monto.'];
        }

        $montoZafirosADebitar = ceil($montoUSD * self::TASA_CONVERSION);
        $comision = $montoUSD * self::COMISION_PLATAFORMA;
        $montoFinal = $montoUSD - $comision;
        $datosBancariosJSON = json_encode($datosFormulario);

        $this->db->query("
            INSERT INTO solicitudes_retiro (id_creadora, monto_zafiros, monto_usd, comision_usd, monto_final_usd, datos_bancarios)
            VALUES (:id, :monto_zafiros, :monto_usd, :comision, :monto_final, :datos_bancarios)
        ");
        $this->db->bind(':id', $idCreadora);
        $this->db->bind(':monto_zafiros', $montoZafirosADebitar);
        $this->db->bind(':monto_usd', $montoUSD);
        $this->db->bind(':comision', $comision);
        $this->db->bind(':monto_final', $montoFinal);
        $this->db->bind(':datos_bancarios', $datosBancariosJSON);
        
        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Tu solicitud de retiro ha sido enviada con éxito y está en revisión.'];
        }
        return ['success' => false, 'message' => 'Error al registrar la solicitud.'];
    }
}
