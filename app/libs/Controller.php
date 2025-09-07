<?php
// app/libs/Controller.php

class Controller
{
    public function model($model)
    {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $datos = [])
    {
        if (isset($_SESSION['logueando'])) {
            $usuarioModel = $this->model('Usuario');
            $publicarModel = $this->model('publicar');

            $usuarioActual = $usuarioModel->getUsuarioById($_SESSION['logueando']);
            // ✅ OBTENEMOS TAMBIÉN EL PERFIL DEL USUARIO
            $perfilActual = $usuarioModel->getPerfil($_SESSION['logueando']);

            $commonData = [
                'navbar_notificaciones' => $publicarModel->getNotificaciones($_SESSION['logueando']),
                'navbar_zafiros'        => $usuarioModel->getZafirosBalance($_SESSION['logueando']),
                'navbar_usuario_obj'    => $usuarioActual,
                'navbar_perfil_obj'     => $perfilActual // Pasamos el objeto de perfil
            ];

            $datos = array_merge($datos, $commonData);
        }

        $viewPath = '../app/view/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die('La vista no existe: ' . $viewPath);
        }
    }
}