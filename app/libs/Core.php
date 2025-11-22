<?php
// app/libs/Core.php (Versión Mejorada)
require_once '../app/helpers/helper.php';

class Core {
    protected $currentController = 'Home';
    protected $currentMethod = 'index';
    protected $parameters = [];

    public function __construct() {
        $url = $this->getURL();

        if (empty($url)) {
            $url = ['Home', 'index'];
        }

        // ✅ INICIO DE LA SOLUCIÓN: Lógica de enrutamiento flexible
        $controllerName = ucwords($url[0]);
        
        // Intenta encontrar el archivo con el sufijo "Controller" (ej: SuscripcionController.php)
        $controllerFileWithSuffix = '../app/controller/' . $controllerName . 'Controller.php';
        
        // Intenta encontrar el archivo sin el sufijo (ej: Suscripcion.php)
        $controllerFileSimple = '../app/controller/' . $controllerName . '.php';

        if (file_exists($controllerFileWithSuffix)) {
            $this->currentController = $controllerName . 'Controller';
            require_once $controllerFileWithSuffix;
            unset($url[0]);
        } elseif (file_exists($controllerFileSimple)) {
            $this->currentController = $controllerName;
            require_once $controllerFileSimple;
            unset($url[0]);
        } else {
            // Si no encuentra ninguna de las dos opciones, es un error 404.
            // En un entorno de producción, aquí redirigirías a una página de error.
            die("Error 404: El controlador '" . htmlspecialchars($controllerName) . "' no fue encontrado.");
        }
        
        $this->currentController = new $this->currentController;
        // ✅ FIN DE LA SOLUCIÓN

        // El resto del código no necesita cambios
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        $this->parameters = $url ? array_values($url) : [];

        call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
    }

    public function getURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}