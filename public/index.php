


<?php
// public/index.php
 ini_set('display_errors', 0);
    ini_set('log_errors', 1);
// Inicia la sesión aquí, una sola vez para toda la aplicacióna
session_start();

require_once '../app/initializer.php'; // Cambiado para usar el inicializador

// Iniciar el núcleo de la aplicación
$core = new Core();