<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';

// Helper function to build correct image URLs
function build_image_url($path) {
    if (empty($path) || strpos($path, 'http') === 0) {
        return htmlspecialchars($path);
    }
    // Remove potential duplicate parts of the URL
    $baseUrl = rtrim(URL_PROJECT, '/');
    $imagePath = ltrim($path, '/');
    
    // Check if the path already contains the base directory name
    $projectName = basename(rtrim(URL_PROJECT, '/'));
    if (strpos($imagePath, $projectName . '/') === 0) {
        return 'http://localhost/' . $imagePath;
    }
    
    return $baseUrl . '/' . $imagePath;
}
?>

<style>
    /* Estilos para el botón de like activo */
    .like-btn.like-active, .like-btn.like-active i {
        color: #e53e3e; /* Rojo para indicar "me gusta" */
        font-weight: bold;
    }
    /* ✅ NUEVA ANIMACIÓN PARA LIKES */
@keyframes heart-beat {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.like-btn .fa-heart.beating {
    animation: heart-beat 0.5s ease-in-out;
}
    /* El resto de tus estilos permanecen aquí */
    .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.85); align-items: center; justify-content: center; }
    .modal-content { margin: auto; display: block; max-width: 90%; max-height: 85%; }
    .blur { filter: blur(5px); }
    .btn-eliminar-post { background: none; border: none; padding: 0; cursor: pointer; }
    .form-comentario-usuario { width: 100%; background-color: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 9999px; padding: 10px 15px; color: #e2e8f0; outline: none; transition: all 0.2s ease; }
    .form-comentario-usuario:focus { border-color: #ff7bbd; box-shadow: 0 0 0 2px rgba(255, 123, 189, 0.3); }
    .btn-enviar-comentario { background: none; border: none; cursor: pointer; padding: 8px; margin-left: 8px; display: flex; align-items: center; justify-content: center; }
    .btn-enviar-comentario i { font-size: 22px; color: #94a3b8; transition: color 0.3s ease, transform 0.2s ease; }
    .btn-enviar-comentario:hover i { color: #7c5cff; transform: scale(1.1); }
    .formulario-comentarios { display: flex; align-items: center; width: 100%; }
    .formulario-comentarios .form-comentario-usuario { flex-grow: 1; }
</style>

<div class="container mt-3" id="main-container">
    <div class="row">
        <!-- Columna de Perfil Lateral (Sin cambios) -->
        <div class="col-md-3">
            <div class="container-style-main">
                <div class="perfil-usuario-main">
                    <div class="background-usuario-main"></div>
                    <img src="<?php echo build_image_url($datos['perfil']->foto_perfil); ?>" alt="Foto de perfil">
                    <div class="foto-separation"></div>
                    <a href="<?php echo URL_PROJECT ?>perfil/<?php echo htmlspecialchars($datos['usuario']->usuario) ?>">
                        <div class="text-center nombre-perfil"><?php echo htmlspecialchars($datos['perfil']->nickname_artistico) ?></div>
                    </a>
                    <div class="tabla-estadisticas">
                        <a>Publicaciones <br> <?php echo $datos['totalPublicaciones']; ?></a>
                        <a>Me gustas <br> <?php echo $datos['totalLikesRecibidos']; ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Central del Feed -->
        <div class="col-md-6">
            <!-- Formulario de Publicar (Sin cambios) -->
            <div class="container-style-main">
                <div class="container-usuario-publicar">
                    <a href="<?php echo URL_PROJECT ?>perfil/<?php echo htmlspecialchars($datos['usuario']->usuario) ?>">
                        <img src="<?php echo build_image_url($datos['perfil']->foto_perfil); ?>" class="image-border" alt="">
                    </a>
                    <form action="<?php echo URL_PROJECT ?>publicaciones/publicar/<?php echo $datos['usuario']->idUsuario ?>" method="POST" class="form-publicar ml-2" enctype="multipart/form-data">
                        <textarea name="contenido" class="published mb-0" placeholder="¿Qué estás pensando?" required></textarea>
                        <div class="image-upload-file">
                            <div class="upload-photo">
                                <img src="<?php echo URL_PROJECT ?>img/image.png" alt="" class="image-public">
                                <label for="archivo" class="ml-1" style="cursor: pointer;">Subir foto/video</label>
                                <input type="file" name="archivo" id="archivo" accept="image/*,video/*" style="display: none;">
                            </div>
                            <button type="submit" class="btn-publi">Publicar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Feed de Publicaciones Rediseñado -->
            <div id="feed-publicaciones" class="mt-4">
                <?php if (!empty($datos['publicaciones'])) : ?>
                    <?php foreach ($datos['publicaciones'] as $pub) : ?>
                        <div class="post-card-glass publicacion" data-idpublicacion="<?php echo $pub->idPublicacion; ?>">
                            <!-- Encabezado de la Publicación -->
                            <div class="post-header">
                                <img src="<?php echo build_image_url($pub->foto_perfil); ?>" alt="Avatar" class="post-avatar">
                                <div class="post-user-info">
                                    <a href="<?php echo URL_PROJECT ?>perfil/<?php echo htmlspecialchars($pub->usuario) ?>" class="post-user-name"><?php echo ucwords(htmlspecialchars($pub->nickname_artistico)) ?></a>
                                    <span class="post-timestamp" data-timestamp="<?php echo $pub->fechaPublicacion; ?>"><?php echo format_time_ago($pub->fechaPublicacion); ?></span>
                                </div>
                                <?php if ($pub->idUsuarioPublico == $_SESSION['logueando']) : ?>
                                    <button class="post-delete-btn delete-btn"><i class="far fa-trash-alt"></i></button>
                                <?php endif; ?>
                            </div>

                            <!-- Contenido de la Publicación -->
                            <div class="post-content">
                                <p><?php echo nl2br(htmlspecialchars($pub->contenidoPublicacion)) ?></p>
                                <?php if ($pub->fotoPublicacion && $pub->fotoPublicacion !== 'sin archivo') : ?>
                                    <?php $ext = pathinfo(parse_url($pub->fotoPublicacion, PHP_URL_PATH), PATHINFO_EXTENSION); ?>
                                    <?php if ($ext == 'mp4' || $ext == 'webm') : ?>
                                        <video controls class="post-media"><source src="<?php echo trim($pub->fotoPublicacion); ?>" type="video/<?php echo $ext; ?>"></video>
                                    <?php else : ?>
                                        <img src="<?php echo trim($pub->fotoPublicacion); ?>" alt="Imagen de la publicación" class="post-media imagen-publicacion-usuario">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Acciones de la Publicación -->
                            <div class="post-actions">
                                <?php $userHasLiked = in_array($pub->idPublicacion, $datos['misLikes']); ?>
                                <button class="like-btn <?php echo $userHasLiked ? 'like-active' : ''; ?>">
                                    <i class="fas fa-heart"></i>&nbsp;
                                    <span class="like-count"><?php echo htmlspecialchars($pub->num_likes ?? 0); ?></span>
                                </button>
                                <button class="btn-comentarios-toggle">
                                    <i class="far fa-comment"></i>&nbsp;<span class="comment-count"><?php echo htmlspecialchars($pub->num_comentarios ?? 0); ?></span>
                                </button>
                            </div>

                            <!-- Sección de Comentarios (Oculta por defecto) -->
                            <div class="comentarios-container" style="display:none;">
                                <div class="comment-form-container">
                                    <img src="<?php echo build_image_url($datos['perfil']->foto_perfil); ?>" alt="Tu avatar" class="comment-avatar-input">
                                    <form class="comment-form" method="POST" autocomplete="off">
                                        <input type="text" name="contenido" placeholder="Escribe un comentario..." required>
                                        <button type="submit" title="Enviar"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>
                                <div class="lista-comentarios">
                                    <?php if (isset($datos['comentarios'][$pub->idPublicacion])) : ?>
                                        <?php foreach ($datos['comentarios'][$pub->idPublicacion] as $comment) : ?>
                                            <div class="comment-item">
                                                <img src="<?php echo build_image_url($comment->foto_perfil); ?>" alt="Avatar" class="comment-avatar">
                                                <div class="comment-glass">
                                                    <div class="comment-header">
                                                        <a href="<?php echo URL_PROJECT ?>perfil/<?php echo htmlspecialchars($comment->usuario) ?>" class="comment-user"><?php echo htmlspecialchars($comment->usuario) ?></a>
                                                        <span class="comment-timestamp" data-timestamp="<?php echo $comment->fechaComentario; ?>"><?php echo format_time_ago($comment->fechaComentario); ?></span>
                                                    </div>
                                                    <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment->contenidoComentario)) ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 mt-8">No hay publicaciones para mostrar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar imágenes -->
<div id="modalImagen" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="imagenModal">
</div>

<div id="modalImagen" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="imagenModal">
</div>

<?php
include_once __DIR__ . '/../custom/footer.php';
?>