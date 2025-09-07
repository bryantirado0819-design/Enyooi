<?php
require_once '../app/helpers/helper.php'; // Ajusta la ruta si es necesario

class Core {
    protected $currentController = 'Home'; // Controlador por defecto
    protected $currentMethod = 'index'; // Método por defecto
    protected $parameters = [];

    public function __construct() {
        $url = $this->getURL();

        if (empty($url)) {
            $url = ['Home', 'index']; // Si la URL está vacía, cargar Home/index
        }

        // Verificar si el controlador existe
        $controllerFile = '../app/controller/' . ucwords($url[0]) . '.php';
        if (file_exists($controllerFile)) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
        } else {
            echo "Controlador no encontrado: " . ucwords($url[0]);
            exit;
        }

        require_once $controllerFile;
        $this->currentController = new $this->currentController;

        // Verificar si se especificó un método y si existe en el controlador
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            } else {
                // Si el método no existe, llamar al método index y pasar el parámetro
                $this->currentMethod = 'index';
            }
        } else {
            $this->currentMethod = 'index';
        }

        // Obtener los parámetros de la URL
        $this->parameters = $url ? array_values($url) : [];

        // Llamar al método del controlador con los parámetros
        call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
    }

    public function getURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        } else {
            return [];
        }
    }
}
