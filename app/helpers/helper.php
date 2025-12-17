<?php

// Función para redirección interna robusta
function redirection($url)
{
    // Limpiamos la URL entrante de barras al inicio para evitar dobles barras //
    $cleanUrl = ltrim($url, '/');
    
    // URL_PROJECT ya tiene la barra al final (ver config.php), así que concatenamos seguro
    $fullUrl = URL_PROJECT . $cleanUrl;
    
    // Evitamos caché en redirecciones
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    header("Location: " . $fullUrl);
    exit();
}

// Función para redirigir a una URL externa o absoluta específica
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

// Función para sanitizar la entrada de texto
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si el usuario está autenticado
function isLoggedIn()
{
    return isset($_SESSION['logueando']); // Corregido para usar tu variable de sesión real
}

// Función para establecer un mensaje de sesión flash
function setFlashMessage($name, $message, $class = 'success')
{
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

// Función para mostrar el mensaje de sesión flash
function displayFlashMessage($name)
{
    if (!empty($name) && !empty($_SESSION[$name])) {
        $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'success';
        echo '<div class="alert alert-' . $class . '">' . $_SESSION[$name] . '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}

function format_time_ago($dateString) {
    $timestamp = strtotime($dateString);
    $now = time();
    $seconds = $now - $timestamp;

    if ($seconds < 10) {
        return "hace un momento";
    }
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);

    if ($seconds < 60) {
        return "hace $seconds segundos";
    } else if ($minutes < 60) {
        return "hace $minutes minuto" . ($minutes > 1 ? "s" : "");
    } else if ($hours < 24) {
        return "hace $hours hora" . ($hours > 1 ? "s" : "");
    } else if ($days < 7) {
        return "hace $days día" . ($days > 1 ? "s" : "");
    } else {
        return date('d M Y', $timestamp);
    }
}
?>