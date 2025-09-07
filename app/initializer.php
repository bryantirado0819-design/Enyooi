<?php

// app/initializer.php

// Configuración principal y conexión a la base de datos
require_once __DIR__ . '/config/config.php';

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

// Autoload Core Libraries
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

    die("No se pudo encontrar la clase: $className en las rutas especificadas.");
});
?>
