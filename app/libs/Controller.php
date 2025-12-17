<?php
// app/libs/Controller.php

class Controller
{
    public function model($model)
    {
        // Esto está correcto, no necesita cambios.
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $datos = [])
    {
        // Esta lógica centraliza la carga de datos para la navbar.
        if (isset($_SESSION['logueando'])) {
            $usuarioModel = $this->model('Usuario');
            $publicarModel = $this->model('publicar');

            $usuarioActual = $usuarioModel->getUsuarioById($_SESSION['logueando']);
            $perfilActual = $usuarioModel->getPerfil($_SESSION['logueando']);
            

            $commonData = [
                'navbar_notificaciones' => $publicarModel->getNotificaciones($_SESSION['logueando']),
                'navbar_zafiros'        => $usuarioActual->saldo_zafiros ?? 0, // Más eficiente
                'navbar_usuario_obj'    => $usuarioActual,
                'navbar_perfil_obj'     => $perfilActual
            ];

            // Fusiona los datos comunes con los específicos de la página.
            $datos = array_merge($commonData, $datos);
        }

        // --- SOLUCIÓN A LA RUTA DE LA VISTA ---
        // Construye la ruta completa a la carpeta 'pages'.
        $viewPath = '../app/view/pages/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Si no la encuentra, por si acaso, busca en la carpeta raíz de 'view'.
            $fallbackPath = '../app/view/' . $view . '.php';
            if (file_exists($fallbackPath)) {
                require_once $fallbackPath;
            } else {
                die('La vista no existe en: ' . $viewPath);
            }
        }
    }
}