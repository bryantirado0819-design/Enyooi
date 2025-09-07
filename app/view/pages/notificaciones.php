<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';

?>


<div class="container-mt-2">
    <div class="container-notificaciones-usuario">
        <h3 class="text-center">Tienes <?php echo $datos['misNotificaciones'] ?> Notificacion</h3>
        <hr>
    </div>

    <div class="container-notificacion-usuario-revisar">

    <?php foreach($datos['notificacion']as $datosNotificacion): ?>
    <a href="<?php echo URL_PROJECT; ?>/notificaciones/eliminar/<?php echo $datosNotificacion->idnotificacion  ?>" class="link-notificacion mt-1"><div class="alert alert-succes">
        <?php echo $datosNotificacion->usuario.' '.$datosNotificacion->mensajeNotificacion?>

    </div></a>
    <?php endforeach ?>
    </div>
</div>



<?php

include_once __DIR__ . '/../custom/footer.php';
?>