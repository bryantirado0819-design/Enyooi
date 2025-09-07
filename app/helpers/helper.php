
<?php


function redirection($url)
{
    $fullUrl = URL_PROJECT . $url;
    header("Location: " . $fullUrl);
    exit();
}



// Función para redirigir a una URL específica
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
    return isset($_SESSION['user_id']);
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
        echo '<div class="' . $class . '">' . $_SESSION[$name] . '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}
?>
