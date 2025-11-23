<?php
/*
    Core.php - Cerebro de la aplicación
    Mapea la URL: controlador / método / parámetros
*/

class Core
{
    protected $currentController = 'Home'; // Controlador por defecto
    protected $currentMethod = 'index';    // Método por defecto
    protected $parameters = [];            // Parámetros

    public function __construct()
    {
        $url = $this->getUrl();

        // ---------------------------------------------------------
        // 1. LIMPIEZA FORENSE DE URL
        // Elimina segmentos basura como 'enyooi', 'public', 'index.php'
        // ---------------------------------------------------------
        $basura = ['ENYOOI', 'public', 'index.php', 'app'];
        
        // Mientras el primer segmento sea basura (case-insensitive), lo quitamos
        while (isset($url[0]) && in_array(strtolower($url[0]), $basura)) {
            array_shift($url);
        }
        
        // Si la URL quedó vacía tras limpiar, es Home
        if (empty($url)) {
            $url[0] = 'Home';
        }

        // ---------------------------------------------------------
        // 2. CARGA DEL CONTROLADOR (CON FALLBACK DE SEGURIDAD)
        // ---------------------------------------------------------
        $controladorEncontrado = false;

        if (isset($url[0])) {
            $nombreArchivo = ucwords($url[0]);
            if (file_exists('../app/controller/' . $nombreArchivo . '.php')) {
                $this->currentController = $nombreArchivo;
                unset($url[0]);
                $controladorEncontrado = true;
            }
        }

        // Si no se encontró el controlador (ej: URL rara), forzamos Home
        // Esto evita el error "Controlador ENYOOI no encontrado"
        if (!$controladorEncontrado && !file_exists('../app/controller/' . $this->currentController . '.php')) {
            $this->currentController = 'Home'; 
        }

        // Requerir e instanciar
        require_once '../app/controller/' . $this->currentController . '.php';
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
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}
?>