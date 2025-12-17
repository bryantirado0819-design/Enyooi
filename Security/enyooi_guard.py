import time
import sys
import mysql.connector
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import magic
import os
from config import DB_CONFIG, PATHS_TO_MONITOR, MALWARE_SIGNATURES, SECURITY_THRESHOLDS

class DatabaseGuard:
    def __init__(self):
        self.conn = None
        self.connect()

    def connect(self):
        try:
            self.conn = mysql.connector.connect(**DB_CONFIG)
            print("Conexión a BD establecida para vigilancia.")
        except Exception as e:
            print(f"Error conectando a BD: {e}")

    def check_financial_anomalies(self):
        if not self.conn or not self.conn.is_connected():
            self.connect()
        
        cursor = self.conn.cursor(dictionary=True)
        query = """
            SELECT id_usuario, SUM(monto_neto_usd) as total_movido 
            FROM transacciones_financieras 
            WHERE fecha >= NOW() - INTERVAL 1 HOUR 
            GROUP BY id_usuario 
            HAVING total_movido > %s
        """
        cursor.execute(query, (SECURITY_THRESHOLDS['transaction_velocity_limit_usd'],))
        results = cursor.fetchall()
        
        for row in results:
            self.flag_user(row['id_usuario'], f"Anomalía Financiera: Movimiento alto ${row['total_movido']}")
        
        cursor.close()

    def check_brute_force(self):
        if not self.conn or not self.conn.is_connected():
            self.connect()
            
        cursor = self.conn.cursor(dictionary=True)
        query = """
            SELECT ip_address, COUNT(*) as attempts 
            FROM user_sessions 
            WHERE login_at >= NOW() - INTERVAL 15 MINUTE 
            GROUP BY ip_address 
            HAVING attempts > %s
        """
        cursor.execute(query, (SECURITY_THRESHOLDS['max_login_attempts'] * 3,)) 
        results = cursor.fetchall()
        
        for row in results:
            self.block_ip(row['ip_address'], "Fuerza bruta detectada")
            
        cursor.close()

    def flag_user(self, user_id, reason):
        print(f"[ALERTA] Usuario {user_id} marcado: {reason}")
        cursor = self.conn.cursor()
        sql = "INSERT INTO user_reports (reporter_id, reported_id, motivo, detalles, estado) VALUES (1, %s, 'SYSTEM_AUTO_FLAG', %s, 'pendiente')"
        try:
            cursor.execute(sql, (user_id, reason))
            self.conn.commit()
        except:
            pass
        cursor.close()

    def block_ip(self, ip, reason):
        print(f"[BLOQUEO] IP {ip} bloqueada por: {reason}")
        with open('.htaccess_deny', 'a') as f:
            f.write(f"Deny from {ip}\n")

class FileSystemGuard(FileSystemEventHandler):
    def on_created(self, event):
        if not event.is_directory:
            self.scan_file(event.src_path)

    def on_modified(self, event):
        if not event.is_directory:
            self.scan_file(event.src_path)

    def scan_file(self, filepath):
        filename, ext = os.path.splitext(filepath)
        
        if "uploads" in filepath:
            mime = magic.from_file(filepath, mime=True)
            if "php" in mime or "script" in mime or ext.lower() == '.php':
                print(f"[PELIGRO] Archivo ejecutable detectado en uploads: {filepath}")
                os.rename(filepath, filepath + ".quarantine")
                return

            with open(filepath, 'rb') as f:
                content = f.read(2048) 
                for sig in MALWARE_SIGNATURES:
                    if sig in content:
                        print(f"[PELIGRO] Firma de malware encontrada en {filepath}")
                        f.close()
                        os.rename(filepath, filepath + ".quarantine")
                        return

def main():
    print("Iniciando Enyooi Security Shield...")
    
    db_guard = DatabaseGuard()
    event_handler = FileSystemGuard()
    observer = Observer()
    
    base_path = os.path.dirname(os.path.abspath(__file__))
    root_path = os.path.dirname(base_path)
    
    for path in PATHS_TO_MONITOR:
        full_path = os.path.join(root_path, path.replace('../', ''))
        if os.path.exists(full_path):
            observer.schedule(event_handler, full_path, recursive=True)
            print(f"Monitoreando: {full_path}")
    
    observer.start()

    try:
        while True:
            db_guard.check_financial_anomalies()
            db_guard.check_brute_force()
            time.sleep(60) 
    except KeyboardInterrupt:
        observer.stop()
    
    observer.join()

if __name__ == "__main__":
    main()