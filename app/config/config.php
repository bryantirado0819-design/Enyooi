<?php
// =========================
// Configuración del proyecto
// =========================
define("DB_HOST", "localhost"); 
define("DB_NAME", "enyooi"); 
define("DB_USER", "enyooi_user"); 
define("DB_PASSWORD", "Enyooi2025!"); 
define('URL_PROJECT', 'http://enyooi.com/');

define('URL_APP', dirname(dirname(__FILE__))); 
define("PROJECT_NAME", "Enyooi");

define('RUTA_APP', dirname(dirname(__FILE__)));
// Site Name
define('SITENAME', 'Enyooi');
define('NODE_SERVER_URL', 'http://localhost:3000');
// ===================================================
// CLAVES DE API Y SECRETOS
// ===================================================

// Lovense Developer Token (Clave secreta, no exponer en el frontend)
define('LOVENSE_DEVELOPER_TOKEN', 'N9BuBePZ1MzWH9aCrIkSgVafg9RT8VL_N2ME09MMKCmvI9AuoPr5FBxwW03JSB2B');
// ✅ CORRECCIÓN CRÍTICA: Se define RUTA_URL primero y de forma limpia.
define('RUTA_URL', 'http://enyooi.com'); // Sin la barra al final

// Esta constante ya no es necesaria y causaba duplicados. La eliminamos.
// define('URL_PROJECT', 'http://localhost/ENYOOI/'); 

// Esta constante es para el servidor, no para el navegador.
define('RUTA_PUBLIC', dirname(URL_APP) . '/public');

// La URL del servidor de Sockets
define('SOCKET_URL', 'http://localhost:3000');

// =========================
// Conexión a la base de datos
// =========================
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Forzar charset UTF-8
if ($mysqli instanceof mysqli) {
    $mysqli->set_charset("utf8mb4");
}

// Validar errores
if ($mysqli->connect_errno) {
    error_log("❌ Error de conexión a la BD ({$mysqli->connect_errno}): {$mysqli->connect_error}");
    die("Error al conectar a la base de datos.");
}

return $mysqli;
?>
