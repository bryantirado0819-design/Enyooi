import os

# --- CONEXIÓN A BASE DE DATOS ---
DB_CONFIG = {
    'user': 'enyooi_user',
    'password': 'Enyooi2025!',
    'host': 'localhost',
    'database': 'enyooi',
    'raise_on_warnings': False
}

# --- UMBRALES DE SEGURIDAD (Tweak these based on traffic) ---
THRESHOLDS = {
    
    'carding': {
        'max_failures_per_ip': 5,          # Max transacciones fallidas por IP en 1 hora
        'max_distinct_cards_per_ip': 3,    # Max tarjetas diferentes usadas por una IP en 1 hora
        'velocity_check_window': 3600      # Ventana de tiempo en segundos (1 hora)
    },
    

    'integrity': {
        'max_likes_per_minute': 60,        # Humano imposible: >1 like por segundo sostenido
        'duplicate_comment_limit': 5,      # Mismo comentario repetido 5 veces en corto tiempo
        'account_creation_velocity': 10    # Max cuentas creadas desde misma IP por día
    },
    
   
    'access': {
        'login_attempts_soft_ban': 5,      # Bloqueo temporal (15 min)
        'login_attempts_hard_ban': 15,     # Bloqueo permanente (requiere admin)
        'admin_area_attempts': 3           # Tolerancia cero para área admin
    }
}

# --- RUTAS DE LOGS Y CUARENTENA ---
LOG_DIR = os.path.join(os.path.dirname(__file__), 'logs')
QUARANTINE_DIR = os.path.join(os.path.dirname(__file__), 'quarantine')
BLOCKLIST_FILE = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'public', '.htaccess_deny')

if not os.path.exists(LOG_DIR):
    os.makedirs(LOG_DIR)