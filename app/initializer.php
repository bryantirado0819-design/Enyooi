<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
// app/initializer.php
if (session_status() === PHP_SESSION_NONE) {
    // Configuración segura de sesión antes de iniciar
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Configuración principal y conexión a la base de datos
require_once __DIR__ . '/config/config.php';

// Cargar librerías base
require_once 'config/config.php';

// Autoload para clases Core (Helpers, Libs)
spl_autoload_register(function($className){
    // Intentamos cargar desde libs primero
    $libPath = 'libs/' . $className . '.php';
    if (file_exists(__DIR__ . '/' . $libPath)) {
        require_once $libPath;
        return;
    }
    
    // Si no, intentar controllers (a veces pasa)
    $ctrlPath = 'controller/' . $className . '.php';
    if (file_exists(__DIR__ . '/' . $ctrlPath)) {
        require_once $ctrlPath;
        return;
    }
});


require_once 'helpers/mailer_helper.php';

// Helpers
require_once __DIR__ . '/helpers/helper.php';

// Autocarga de las librerías principales del framework
require_once __DIR__ . '/libs/Base.php';
require_once __DIR__ . '/libs/Controller.php';
require_once __DIR__ . '/libs/Core.php';
// Configuración del archivo de configuración
require_once __DIR__ . '/config/config.php';

// Incluir helpers
$helperPath = __DIR__ . '/helpers/helper.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
} else {
    die("El archivo helper.php no se encuentra en la ruta especificada: $helperPath");
}

// ✅ MEJORA: Autoloader a prueba de errores JSON
spl_autoload_register(function($className) {
    $paths = [
        __DIR__ . '/libs/' . $className . '.php',
        __DIR__ . '/models/' . $className . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
    require_once __DIR__ . '/libs/Security.php';
Security::init(); 

    // En lugar de 'die()', devolvemos un JSON de error.
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => "Error Crítico del Servidor: No se pudo encontrar la clase '{$className}'.",
        'detail' => 'Autoloader failed.'
    ]);
    exit();
});
?>