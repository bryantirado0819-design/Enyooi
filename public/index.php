


<?php
// public/index.php

// Inicia la sesión aquí, una sola vez para toda la aplicación.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/initializer.php'; // Cambiado para usar el inicializador

// Iniciar el núcleo de la aplicación
$core = new Core();