import mysql.connector
import logging
import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import DB_CONFIG, THRESHOLDS

class ActivityIntegrity:
    def __init__(self):
        logging.basicConfig(filename='../security/logs/integrity.log', level=logging.INFO)

    def run_checks(self):
        conn = mysql.connector.connect(**DB_CONFIG)
        self.check_superhuman_speed(conn)
        self.check_spam_comments(conn)
        conn.close()

    def check_superhuman_speed(self, conn):
        """Detecta usuarios dando likes más rápido de lo humanamente posible"""
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT id_usuario, COUNT(*) as velocity 
            FROM likes 
            WHERE fecha >= NOW() - INTERVAL 1 MINUTE 
            GROUP BY id_usuario 
            HAVING velocity > %s
        """
        cursor.execute(query, (THRESHOLDS['integrity']['max_likes_per_minute'],))
        bots = cursor.fetchall()
        
        for bot in bots:
            self.sanction_user(conn, bot['id_usuario'], 'BOT_LIKE_VELOCITY', f"{bot['velocity']} likes/min")
        
        cursor.close()

    def check_spam_comments(self, conn):
        """Detecta comentarios idénticos repetidos masivamente"""
        cursor = conn.cursor(dictionary=True)
        query = """
            SELECT id_usuario, comentario, COUNT(*) as repetition 
            FROM comentarios 
            WHERE fecha >= NOW() - INTERVAL 10 MINUTE 
            GROUP BY id_usuario, comentario 
            HAVING repetition >= %s
        """
        cursor.execute(query, (THRESHOLDS['integrity']['duplicate_comment_limit'],))
        spammers = cursor.fetchall()
        
        for spammer in spammers:
            self.sanction_user(conn, spammer['id_usuario'], 'SPAM_COMMENT_FLOOD', f"Repitió comentario {spammer['repetition']} veces")
            
        cursor.close()

    def sanction_user(self, conn, user_id, type, details):
        print(f"[INTEGRITY] Sancionando usuario {user_id} por {type}")
        logging.warning(f"Usuario {user_id} sancionado: {type} - {details}")
        
        cursor = conn.cursor()
        # 1. Shadowban (reducir visibilidad o impedir nuevas acciones)
        
        try:
            sql = "UPDATE usuarios SET estado = 'revision' WHERE idUsuario = %s"
            cursor.execute(sql, (user_id,))
            
            # 2. Registrar reporte automático
            sql_report = "INSERT INTO reportes (id_usuario_reportado, motivo, descripcion, estado) VALUES (%s, 'SISTEMA', %s, 'pendiente')"
            cursor.execute(sql_report, (user_id, f"AUTO-BAN: {type} {details}"))
            
            conn.commit()
        except Exception as e:
            logging.error(f"Error sancionando: {e}")
        cursor.close()