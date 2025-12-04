


<?php
// public/index.php
 ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
// Inicia la sesión aquí, una sola vez para toda la aplicacióna
session_start();
ini_set('error_log', __DIR__ . '/../security/logs/php_errors.log');
require_once '../app/initializer.php'; // Cambiado para usar el inicializador

// Iniciar el núcleo de la aplicación
$core = new Core();