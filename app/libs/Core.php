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

        // =======================================================================
        // 🚑 PARCHE DE EMERGENCIA: LIMPIEZA AGRESIVA DE URL
        // =======================================================================
        // Esto detecta si la URL empieza con 'enyooi', 'public', 'index.php' 
        // y lo elimina para llegar al controlador real (ej: 'home').
        // Funciona para: enyooi.com/ENYOOI/home/role_select -> Carga Home
        
        $segmentos_basura = ['enyooi', 'public', 'index.php', 'enyooi.com', 'app'];

        // Usamos un bucle por si la URL es muy sucia (ej: /enyooi/public/home)
        while (isset($url[0]) && in_array(strtolower($url[0]), $segmentos_basura)) {
            array_shift($url); // Elimina el segmento basura y recorre el array
        }

        // Si después de limpiar no queda nada, vamos al Home
        if (empty($url)) {
            $url[0] = 'Home';
        }
        // =======================================================================

        // 1. Buscar si existe el controlador en la carpeta controllers
        // Buscamos el archivo Capitalizando la primera letra (Home.php, Login.php)
        if (isset($url[0])) {
            if (file_exists('../app/controller/' . ucwords($url[0]) . '.php')) {
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
            // Eliminamos la barra final
            $url = rtrim($_GET['url'], '/');
            // Filtramos caracteres raros
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Dividimos en array
            $url = explode('/', $url);
            return $url;
        }
        return []; // Retorna array vacío si no hay url
    }
}