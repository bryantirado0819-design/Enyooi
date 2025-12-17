import time
import threading
import schedule
from modules.financial_shield import FinancialShield
from modules.activity_integrity import ActivityIntegrity
from modules.login_guard import LoginGuard
from enyooi_guard import FileSystemGuard 
from watchdog.observers import Observer
from config import PATHS_TO_MONITOR
import os

print("""
  ______  _   _ __   __  ____    ____  _____ 
 |  ____|| \ | |\ \ / / / __ \  / __ \|_   _|
 | |__   |  \| | \ V / | |  | || |  | | | |  
 |  __|  | . ` |  > <  | |  | || |  | | | |  
 | |____ | |\  | / . \ | |__| || |__| |_| |_ 
 |______||_| \_|/_/ \_\ \____/  \____/|_____|
 SECURITY  V2.0 - ACTIVE
""")

def run_financial_audit():
    try:
        shield = FinancialShield()
        shield.detect_carding_attempts()
    except Exception as e:
        print(f"[ERROR] Financial Module: {e}")

def run_integrity_audit():
    try:
        integrity = ActivityIntegrity()
        integrity.run_checks()
    except Exception as e:
        print(f"[ERROR] Integrity Module: {e}")

def run_access_audit():
    try:
        guard = LoginGuard()
        guard.analyze_patterns()
    except Exception as e:
        print(f"[ERROR] Access Module: {e}")

# Configurar planificadores
schedule.every(30).seconds.do(run_access_audit)      # Chequeo rápido de logins
schedule.every(1).minutes.do(run_integrity_audit)    # Chequeo medio de likes/bots
schedule.every(5).minutes.do(run_financial_audit)    # Chequeo profundo de finanzas

def scheduler_loop():
    while True:
        schedule.run_pending()
        time.sleep(1)

def file_monitor_loop():
    event_handler = FileSystemGuard()
    observer = Observer()
    base_path = os.path.dirname(os.path.abspath(__file__))
    root_path = os.path.dirname(base_path)
    
    for path in PATHS_TO_MONITOR:
        full_path = os.path.join(root_path, path.replace('../', ''))
        if os.path.exists(full_path):
            observer.schedule(event_handler, full_path, recursive=True)
            print(f" -> Vigilando sistema de archivos: {full_path}")
    
    observer.start()
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()

if __name__ == "__main__":
    
    t_files = threading.Thread(target=file_monitor_loop)
    t_files.daemon = True
    t_files.start()

    print(" -> Módulos de auditoría iniciados.")
    scheduler_loop()