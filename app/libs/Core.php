<?php
/*
    Mapear la URL ingresada en el navegador,
    1- controlador
    2- método
    3- parámetro
*/

class Core
{
    protected $currentController = 'Home'; // Controlador por defecto
    protected $currentMethod = 'index';    // Método por defecto
    protected $parameters = [];            // Parámetros vacíos

    public function __construct()
    {
        $url = $this->getUrl();

        // ---------------------------------------------------------
        // 1. LIMPIEZA DE URL (FORENSE)
        // Elimina segmentos basura que causan el error 404
        // ---------------------------------------------------------
        $basura = ['enyooi', 'public', 'index.php', 'enyooi.com', 'app'];
        
        // Mientras el primer segmento sea basura, lo quitamos
        while (isset($url[0]) && in_array(strtolower($url[0]), $basura)) {
            array_shift($url);
        }

        // Si la URL quedó vacía, es el Home
        if (empty($url)) {
            $url[0] = 'Home';
        }

        // ---------------------------------------------------------
        // 2. CARGA DEL CONTROLADOR
        // ---------------------------------------------------------
        
        // Verificamos si el archivo del controlador existe
        if (isset($url[0])) {
            $posibleControlador = ucwords($url[0]);
            if (file_exists('../app/controller/' . $posibleControlador . '.php')) {
                $this->currentController = $posibleControlador;
                unset($url[0]);
            }
        }

        // Requerir el controlador
        require_once '../app/controller/' . $this->currentController . '.php';
        
        // Instanciar
        $this->currentController = new $this->currentController;

        // ---------------------------------------------------------
        // 3. CARGA DEL MÉTODO
        // ---------------------------------------------------------
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // ---------------------------------------------------------
        // 4. PARÁMETROS Y EJECUCIÓN
        // ---------------------------------------------------------
        $this->parameters = $url ? array_values($url) : [];

        call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
    }

    public function getUrl()
    {
        if (isset($_GET['url'])) {
            // Eliminamos barra final y sanitizamos
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}