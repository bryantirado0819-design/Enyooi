<?php
/*
    Mapear la URL ingresada en el navegador,
    1- controlador
    2- método
    3- parámetro
    Ejemplo: /articulo/actualizar/4
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
        // PARCHE FORENSE: Limpieza de URL Sucia
        // ---------------------------------------------------------
        // Si la URL empieza con el nombre del proyecto o 'public', lo eliminamos.
        // Esto arregla el error: Controlador 'ENYOOI' no encontrado.
        if (isset($url[0])) {
            $badSegments = ['enyooi', 'public', 'index.php'];
            if (in_array(strtolower($url[0]), $badSegments)) {
                array_shift($url); // Elimina el segmento basura
            }
        }
        
        // Si después de limpiar la URL queda vacía, forzamos Home
        if (empty($url)) {
            $url[0] = 'Home';
        }
        // ---------------------------------------------------------

        // 1. Buscar si existe el controlador en la carpeta controllers
        if (isset($url[0])) {
            if (file_exists('../app/controller/' . ucwords($url[0]) . '.php')) {
                // Si existe, se setea como controlador actual
                $this->currentController = ucwords($url[0]);
                unset($url[0]);
            }
        }

        // Requerir el controlador
        require_once '../app/controller/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // 2. Verificar la segunda parte de la url (el método)
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // 3. Obtener los parámetros restantes
        $this->parameters = $url ? array_values($url) : [];

        // Llamar al callback con los parámetros
        call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
    }

    public function getUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}