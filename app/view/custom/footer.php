<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"></script>
    
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <script>
        const socket = io("<?php echo SOCKET_URL; ?>");
        const URL_PROJECT = "<?php echo RUTA_URL; ?>";
    </script>

    <?php
    // ✅ **LÓGICA DE CARGA DE SCRIPT CORREGIDA Y SIMPLIFICADA**
    if (isset($datos['page_script']) && !empty($datos['page_script'])) {
        // Si la vista definió un script específico (ej. 'perfil.js'), lo cargamos.
        echo '<script src="' . RUTA_URL . '/js/' . htmlspecialchars($datos['page_script']) . '"></script>';
    } else {
        // De lo contrario, cargamos el script principal 'main.js'.
        echo '<script src="' . RUTA_URL . '/js/main.js' . '"></script>';
    }
    ?>
  </body>
</html>