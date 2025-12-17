<?php
// app/config/config.php
// ==========================================
// CONFIGURACIÓN PRINCIPAL - ENYOOI
// ==========================================

// 1. CREDENCIALES DE BASE DE DATOS
define("DB_HOST", "localhost");
define("DB_NAME", "enyooi");
define("DB_USER", "enyooi_user");
define("DB_PASSWORD", "Enyooi2025!");

// 2. URLs DEL PROYECTO
// IMPORTANTE: Sin slash al final para evitar errores de //
define('URL_PROJECT', 'https://enyooi.com'); 
define('RUTA_URL', 'https://enyooi.com');    

// Configuración de Sockets
define('NODE_SERVER_URL', 'https://enyooi.com'); 
define('SOCKET_URL', 'https://socket.enyooi.com');
define('NODE_INTERNAL_URL', 'http://72.61.75.91:3000');

// 3. RUTAS DE CARPETAS (Directorios del sistema)
define('URL_APP', dirname(dirname(__FILE__))); 
define('RUTA_APP', dirname(dirname(__FILE__)));
define('APPROOT', dirname(dirname(__DIR__))); 
define('DIR_PUBLIC', APPROOT . '/public');
define('RUTA_PUBLIC', DIR_PUBLIC); 

// 4. CONFIGURACIÓN GENERAL
define("PROJECT_NAME", "Enyooi");
define('SITENAME', 'Enyooi');
define('LOVENSE_DEVELOPER_TOKEN', 'N9BuBePZ1MzWH9aCrIkSgVafg9RT8VL_N2ME09MMKCmvI9AuoPr5FBxwW03JSB2B');

// ==========================================
// CONEXIÓN A LA BASE DE DATOS
// ==========================================
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli instanceof mysqli) {
    $mysqli->set_charset("utf8mb4");
}

if ($mysqli->connect_errno) {
    error_log("Error MySQL: " . $mysqli->connect_error);
    die("Error crítico: No se pudo conectar a la base de datos.");
}

return $mysqli;