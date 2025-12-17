import mysql.connector
import logging
from datetime import datetime
import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import DB_CONFIG, THRESHOLDS, BLOCKLIST_FILE

class FinancialShield:
    def __init__(self):
        self.setup_logging()

    def setup_logging(self):
        logging.basicConfig(filename='../security/logs/financial_shield.log', level=logging.INFO, 
                            format='%(asctime)s - %(levelname)s - %(message)s')

    def get_connection(self):
        return mysql.connector.connect(**DB_CONFIG)

    def detect_carding_attempts(self):
        """Detecta IPs probando múltiples tarjetas (BIN Attack)"""
        conn = self.get_connection()
        cursor = conn.cursor(dictionary=True)
        
        
        query = """
            SELECT ip_origen, 
                   COUNT(*) as total_attempts,
                   COUNT(DISTINCT tarjeta_hash) as distinct_cards,
                   SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as failed_attempts
            FROM historial_pagos 
            WHERE fecha_creacion >= NOW() - INTERVAL 1 HOUR
            GROUP BY ip_origen
            HAVING failed_attempts >= %s OR distinct_cards >= %s
        """
        
        cursor.execute(query, (THRESHOLDS['carding']['max_failures_per_ip'], THRESHOLDS['carding']['max_distinct_cards_per_ip']))
        results = cursor.fetchall()
        
        for row in results:
            self.mitigate_threat(row['ip_origen'], row, "BIN_ATTACK_DETECTED")
            
        cursor.close()
        conn.close()

    def mitigate_threat(self, ip, data, reason):
        msg = f"ALERTA CRÍTICA: {reason} desde IP {ip}. Fallos: {data['failed_attempts']}, Tarjetas Distintas: {data['distinct_cards']}"
        logging.critical(msg)
        print(f"[FINANCIAL] {msg}")
        
        # Acción 1: Bloquear IP en .htaccess (Capa WAF)
        self.block_ip_waf(ip)
        
        # Acción 2: Marcar usuarios asociados a esa IP para revisión manual
        self.flag_users_by_ip(ip, reason)

    def block_ip_waf(self, ip):
        try:
            # Verifica si ya está bloqueada para no duplicar
            is_blocked = False
            if os.path.exists(BLOCKLIST_FILE):
                with open(BLOCKLIST_FILE, 'r') as f:
                    if ip in f.read():
                        is_blocked = True
            
            if not is_blocked:
                with open(BLOCKLIST_FILE, 'a') as f:
                    f.write(f"Deny from {ip}\n")
                logging.info(f"IP {ip} añadida a la lista negra.")
        except Exception as e:
            logging.error(f"Error escribiendo en blocklist: {e}")

    def flag_users_by_ip(self, ip, reason):
        conn = self.get_connection()
        cursor = conn.cursor()
        # Congelar billeteras de usuarios logueados con esa IP sospechosa
        sql = "UPDATE wallet SET estado = 'congelado' WHERE id_usuario IN (SELECT idUsuario FROM user_sessions WHERE ip_address = %s)"
        cursor.execute(sql, (ip,))
        conn.commit()
        conn.close()

if __name__ == "__main__":
    shield = FinancialShield()
    shield.detect_carding_attempts()