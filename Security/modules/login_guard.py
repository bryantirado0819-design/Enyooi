import mysql.connector
import logging
import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import DB_CONFIG, THRESHOLDS, BLOCKLIST_FILE

class LoginGuard:
    def __init__(self):
        logging.basicConfig(filename='../security/logs/access.log', level=logging.INFO)

    def analyze_patterns(self):
        conn = mysql.connector.connect(**DB_CONFIG)
        self.detect_ip_bruteforce(conn)
        self.detect_distributed_attack(conn)
        conn.close()

    def detect_ip_bruteforce(self, conn):
        """Una sola IP atacando muchas cuentas o una sola cuenta"""
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT ip_address, COUNT(*) as failures 
            FROM login_logs 
            WHERE status = 'failed' AND timestamp >= NOW() - INTERVAL 15 MINUTE
            GROUP BY ip_address 
            HAVING failures >= %s
        """
        cursor.execute(query, (THRESHOLDS['access']['login_attempts_soft_ban'],))
        attackers = cursor.fetchall()
        
        for attacker in attackers:
            self.ban_ip(attacker['ip_address'], "BRUTE_FORCE_IP")
        cursor.close()

    def detect_distributed_attack(self, conn):
        """Muchas IPs diferentes atacando al MISMO usuario (Distributed Brute Force)"""
        cursor = conn.cursor(dictionary=True)
        query = """
            SELECT usuario_objetivo, COUNT(DISTINCT ip_address) as unique_ips 
            FROM login_logs 
            WHERE status = 'failed' AND timestamp >= NOW() - INTERVAL 30 MINUTE
            GROUP BY usuario_objetivo 
            HAVING unique_ips >= 5
        """
        cursor.execute(query)
        victims = cursor.fetchall()
        
        for victim in victims:
            self.lock_account(conn, victim['usuario_objetivo'])
        cursor.close()

    def ban_ip(self, ip, reason):
        print(f"[ACCESS] Bloqueando IP {ip}: {reason}")
        with open(BLOCKLIST_FILE, 'a') as f:
            f.write(f"Deny from {ip}\n")

    def lock_account(self, conn, username):
        print(f"[ACCESS] Bloqueando cuenta {username} por ataque distribuido")
        cursor = conn.cursor()
        sql = "UPDATE usuarios SET estado = 'bloqueado_seguridad' WHERE usuario = %s"
        cursor.execute(sql, (username,))
        conn.commit()
        cursor.close()