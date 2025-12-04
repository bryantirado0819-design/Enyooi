import mysql.connector
import os
from config import DB_CONFIG

def audit_passwords():
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("SELECT idUsuario, usuario, contrasena FROM usuarios")
    users = cursor.fetchall()
    
    weak_count = 0
    for user in users:
        hash_prefix = user['contrasena'][:4]
        if hash_prefix != '$2y$': 
            print(f"[WARN] Usuario {user['usuario']} usa un hash obsoleto o débil.")
            weak_count += 1
            
    print(f"Auditoría completada. {weak_count} cuentas requieren cambio de contraseña.")
    conn.close()

def audit_permissions(start_path):
    print(f"Auditando permisos en {start_path}...")
    for root, dirs, files in os.walk(start_path):
        for name in files:
            filepath = os.path.join(root, name)
            if 'config.php' in name or '.env' in name:
                st = os.stat(filepath)
                oct_perm = oct(st.st_mode)[-3:]
                if int(oct_perm) > 644: 
                    print(f"[CRITICO] Permisos inseguros ({oct_perm}) en: {filepath}")

if __name__ == "__main__":
    print("--- Enyooi Security Auditor ---")
    audit_passwords()
    audit_permissions('../app')