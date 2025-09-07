<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';

// Variables para facilitar el acceso
$perfil = $datos['perfil'];
$usuario = $datos['usuario'];
$stats = $datos['stats'];
$esPropietario = $datos['esPropietario'];
$puedeVerContenido = $datos['puedeVerContenido'];
$isSubscribed = $datos['isSubscribed'];
$editProfileLink = $datos['editProfileLink'];
$likesModel = $datos['likesModel'];
$comentariosModel = $datos['comentariosModel'];
?>

<!-- Estilos autocontenidos para la página de perfil y el nuevo feed -->
<style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body { font-family: 'Poppins', sans-serif; } 
    .profile-banner { height: 35vh; background-size: cover; background-position: center; border-radius: 0 0 2rem 2rem; }
    .profile-card { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    .profile-avatar { margin-top: -75px; width: 150px; height: 150px; border: 4px solid #0f172a; }
    .stat-item { background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; padding: 0.75rem; text-align: center; }
    .action-button { background: linear-gradient(to right, var(--accent), var(--accent-2)); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .action-button:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    .secondary-button { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); transition: background-color 0.2s ease; }
    .secondary-button:hover { background: rgba(255, 255, 255, 0.2); }

    .post-card { display: flex; gap: 1rem; padding: 1.5rem 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .post-avatar { width: 48px; height: 48px; border-radius: 9999px; object-fit: cover; }
    .post-media { border-radius: 0.75rem; max-height: 500px; width: 100%; object-fit: cover; cursor: pointer; }
    .post-actions button { background: none; border: none; color: #94a3b8; cursor: pointer; transition: color 0.2s ease, transform 0.2s ease; }
    .post-actions button:hover { transform: scale(1.1); }
    .like-btn.like-active, .like-btn.like-active:hover { color: #f43f5e; }
    .btn-comentarios-toggle:hover { color: #38bdf8; }
    
    .post-actions button:focus, .action-button:focus, .secondary-button:focus { outline: none; box-shadow: none; }
    #comentariosModal.hidden { display: none; }
    .modal-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    .modal-content-slide-up { animation: slideUp 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    #emoji-btn { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; cursor: pointer; font-size: 1.25rem; }
    emoji-picker { position: absolute; bottom: 80px; right: 10px; z-index: 100; }

    /* ✅ NUEVOS ESTILOS PARA EL TEXTAREA */
    .autoresize-textarea {
        border-radius: 18px !important; /* Bordes más redondeados */
        line-height: 1.5;
        max-height: 110px; /* Altura máxima antes de mostrar scroll */
        overflow-y: auto; /* Scroll vertical cuando se excede la altura */
    }
</style>

<div class="w-full">
    <!-- BANNER -->
    <div class="profile-banner" style="background-image: url('<?php echo URL_PROJECT . htmlspecialchars($perfil->banner_portada ?: 'public/img/defaults/default_banner.jpg'); ?>');"></div>

    <main class="max-w-5xl mx-auto p-4 md:p-6 lg:p-8 -mt-24 relative z-10">
        <!-- TARJETA PRINCIPAL DEL PERFIL -->
        <div class="profile-card rounded-2xl p-6">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="flex-shrink-0">
                    <img src="<?php echo URL_PROJECT . htmlspecialchars($perfil->foto_perfil); ?>" alt="Avatar" class="profile-avatar rounded-full object-cover">
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($perfil->nickname_artistico ?: $usuario->usuario); ?></h1>
                    <p class="text-sm text-gray-400">@<?php echo htmlspecialchars($usuario->usuario); ?></p>
                    <p class="text-gray-300 mt-2 text-sm max-w-lg mx-auto md:mx-0"><?php echo htmlspecialchars($perfil->bio); ?></p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-2">
                    <?php if ($esPropietario) : ?>
                        <a href="<?php echo $editProfileLink; ?>" class="action-button inline-block text-white font-semibold px-6 py-2 rounded-full">Editar Perfil</a>
                    <?php else : ?>
                        <a href="<?php echo URL_PROJECT; ?>mensajes/chat/<?php echo $usuario->idUsuario; ?>" class="secondary-button inline-block text-white font-semibold px-6 py-2 rounded-full">Mensaje</a>
                        <button class="action-button text-white font-semibold px-6 py-2 rounded-full">
                            <?php if ($isSubscribed): ?>
                                <i class="fas fa-check mr-2"></i>Suscrito
                            <?php else: ?>
                                Suscribirse (💎<?php echo (int)($perfil->precio_suscripcion ?? 0); ?>/mes)
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ESTADÍSTICAS -->
            <div class="grid grid-cols-3 gap-4 mt-6 border-t border-slate-700 pt-6">
                <div class="stat-item"><i class="fas fa-camera text-pink-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['fotos']; ?></span><span class="text-xs text-gray-400">Fotos</span></div>
                <div class="stat-item"><i class="fas fa-video text-blue-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['videos']; ?></span><span class="text-xs text-gray-400">Videos</span></div>
                <div class="stat-item"><i class="fas fa-heart text-red-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['likes']; ?></span><span class="text-xs text-gray-400">Likes</span></div>
            </div>
        </div>

        <!-- SECCIÓN DE CONTENIDO -->
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-white mb-4">Publicaciones</h2>
            
            <?php if ($puedeVerContenido): ?>
                <?php if (!empty($datos['publicaciones'])): ?>
                    <div id="feed-publicaciones" class="space-y-4">
                        <?php foreach ($datos['publicaciones'] as $pub): ?>
                            <div class="post-card" id="publicacion-<?php echo $pub->idPublicacion; ?>">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo URL_PROJECT . htmlspecialchars($pub->foto_perfil); ?>" alt="Avatar" class="post-avatar">
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-white"><?php echo htmlspecialchars($pub->nickname_artistico); ?></span>
                                            <span class="text-sm text-gray-500 ml-2"><?php echo htmlspecialchars($pub->fechaPublicacion); ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-gray-300">
                                        <p><?php echo nl2br(htmlspecialchars($pub->contenidoPublicacion)); ?></p>
                                        <?php if ($pub->fotoPublicacion && $pub->fotoPublicacion !== 'sin archivo') : ?>
                                            <div class="mt-3">
                                                <?php if ($pub->tipo_archivo === 'video') : ?>
                                                    <video src="<?php echo htmlspecialchars($pub->fotoPublicacion); ?>" controls class="post-media"></video>
                                                <?php else: ?>
                                                    <img src="<?php echo htmlspecialchars($pub->fotoPublicacion); ?>" alt="Imagen" class="post-media">
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-actions flex items-center gap-6 mt-4 text-gray-400">
                                        <button class="like-btn <?php echo $likesModel->verificarLikeUsuario($pub->idPublicacion, $_SESSION['logueando']) ? 'like-active' : ''; ?>" data-id="<?php echo $pub->idPublicacion; ?>" data-user="<?php echo $_SESSION['logueando']; ?>" data-owner="<?php echo $pub->idUsuarioPublico; ?>">
                                            <i class="fas fa-heart text-lg"></i>
                                            <span class="text-sm ml-2 like-count"><?php echo $likesModel->getLikeCount($pub->idPublicacion); ?></span>
                                        </button>
                                        <button class="btn-comentarios-toggle" data-id="<?php echo $pub->idPublicacion; ?>">
                                            <i class="far fa-comment text-lg"></i>
                                            <span class="text-sm ml-2 comment-count"><?php echo $comentariosModel->getCommentCount($pub->idPublicacion); ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-400 py-16">
                        <i class="fas fa-camera text-4xl mb-4"></i>
                        <p><?php echo $esPropietario ? 'Aún no has subido contenido.' : 'Esta creadora aún no tiene contenido.'; ?></p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Muro de Suscripción -->
                <div class="text-center text-gray-300 py-20 bg-slate-800/50 rounded-2xl border border-dashed border-slate-600">
                    <i class="fas fa-lock text-5xl text-pink-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-white">Contenido Exclusivo</h3>
                    <p class="mt-2">Suscríbete a <?php echo htmlspecialchars($perfil->nickname_artistico); ?> para desbloquear todo su contenido.</p>
                    <button class="action-button text-white font-semibold px-8 py-3 rounded-full mt-6">
                        Suscribirse por 💎<?php echo (int)($perfil->precio_suscripcion ?? 0); ?> al mes
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div id="comentariosModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div id="modalBackdrop" class="absolute inset-0"></div>
    <div id="modalContent" class="modal-content-slide-up bg-slate-800 rounded-2xl shadow-xl w-11/12 md:w-2/3 lg:w-1/2 max-w-2xl flex flex-col border border-slate-700" style="max-height: 90vh;">
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-slate-900/50 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Comentarios</h3>
            <button id="closeModalBtn" class="text-gray-400 hover:text-white text-3xl leading-none">&times;</button>
        </div>
        <div id="lista-comentarios" class="p-4 overflow-y-auto flex-1"></div>
        <div class="p-4 border-t border-slate-700 relative">
             <emoji-picker class="hidden"></emoji-picker>
            <form id="form-comentario" class="flex items-center gap-3">
                <input type="hidden" id="idPublicacionComentario" name="idpublicacion">
                <input type="hidden" id="idUserPropietario" name="iduserPropietario">
                <div class="relative w-full">
                    <textarea id="comentario-textarea" name="comentario" class="autoresize-textarea w-full bg-slate-700 text-white p-3 pr-12 focus:outline-none focus:ring-2 focus:ring-pink-500 resize-none" rows="1" placeholder="Escribe un comentario..."></textarea>
                    <i id="emoji-btn" class="far fa-smile"></i>
                </div>
                <button type="submit" class="action-button text-white font-semibold px-5 py-2 rounded-full h-12 w-12 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const URL_BASE = "<?php echo URL_PROJECT; ?>";
    const feed = document.getElementById('feed-publicaciones');
    if (!feed) return;

    // --- MANEJADOR DE EVENTOS DELEGADO PARA TODO EL FEED ---
    feed.addEventListener('click', function(e) {
        const likeBtn = e.target.closest('.like-btn');
        const commentToggleBtn = e.target.closest('.btn-comentarios-toggle');

        // LÓGICA PARA LIKES
        if (likeBtn && !likeBtn.disabled) {
            likeBtn.disabled = true;
            const idPublicacion = likeBtn.dataset.id;
            const idUsuario = likeBtn.dataset.user;
            const idPropietario = likeBtn.dataset.owner;
            const url = `${URL_BASE}publicaciones/megusta/${idPublicacion}/${idUsuario}/${idPropietario}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        likeBtn.classList.toggle('like-active', data.likeStatus);
                        likeBtn.querySelector('.like-count').textContent = data.nuevoConteoLikes;
                    }
                })
                .catch(error => console.error('Error en fetch de like:', error))
                .finally(() => { likeBtn.disabled = false; });
        }

        // LÓGICA PARA ABRIR MODAL DE COMENTARIOS
        if (commentToggleBtn) {
            const idPublicacion = commentToggleBtn.dataset.id;
            const ownerBtn = commentToggleBtn.closest('.post-card').querySelector('.like-btn');
            if (ownerBtn) {
                const idPropietario = ownerBtn.dataset.owner;
                abrirModalComentarios(idPublicacion, idPropietario);
            }
        }
    });

    // --- LÓGICA DEL MODAL DE COMENTARIOS ---
    const modal = document.getElementById('comentariosModal');
    const formComentario = document.getElementById('form-comentario');
    const comentarioTextarea = document.getElementById('comentario-textarea');
    const listaComentarios = document.getElementById('lista-comentarios');

    function abrirModalComentarios(idPublicacion, idPropietario) {
        document.getElementById('idPublicacionComentario').value = idPublicacion;
        document.getElementById('idUserPropietario').value = idPropietario;
        listaComentarios.innerHTML = '<p class="text-center text-gray-400">Cargando...</p>';
        modal.classList.remove('hidden');

        fetch(`${URL_BASE}publicaciones/getComentarios/${idPublicacion}`)
            .then(res => res.json())
            .then(data => {
                listaComentarios.innerHTML = ''; // Limpiar
                if (data.success && data.comentarios.length > 0) {
                    data.comentarios.forEach(c => listaComentarios.appendChild(crearElementoComentario(c)));
                } else {
                    listaComentarios.innerHTML = '<p class="text-center text-gray-400">No hay comentarios.</p>';
                }
            })
            .catch(err => {
                listaComentarios.innerHTML = '<p class="text-center text-red-400">Error al cargar comentarios.</p>';
                console.error(err);
            });
    }

    function crearElementoComentario(comentario) {
        const div = document.createElement('div');
        div.className = 'flex items-start gap-3 mb-4';
        div.innerHTML = `
            <img src="${URL_BASE}${comentario.foto_perfil || 'public/img/defaults/default_avatar.png'}" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
            <div class="bg-slate-700 rounded-lg p-3 flex-1">
                <div class="flex items-baseline gap-2">
                    <p class="font-bold text-white text-sm">${escapeHTML(comentario.usuario)}</p>
                    <p class="text-gray-400 text-xs">${comentario.fechaComentario}</p>
                </div>
                <p class="text-gray-300 text-sm mt-1 whitespace-pre-wrap">${escapeHTML(comentario.contenidoComentario).replace(/\n/g, '<br>')}</p>
            </div>`;
        return div;
    }

    formComentario.addEventListener('submit', function(e) {
        e.preventDefault();
        const texto = comentarioTextarea.value.trim();
        if (!texto) return;

        const formData = new FormData(formComentario);
        fetch(`${URL_BASE}publicaciones/comentar`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const comentarioNuevo = {
                        usuario: data.nombreUsuario,
                        foto_perfil: data.fotoPerfil,
                        fechaComentario: 'Ahora mismo',
                        contenidoComentario: texto
                    };
                    // Limpiar "No hay comentarios" si existe
                    if (listaComentarios.querySelector('p')) listaComentarios.innerHTML = '';
                    listaComentarios.appendChild(crearElementoComentario(comentarioNuevo));
                    listaComentarios.scrollTop = listaComentarios.scrollHeight;
                    comentarioTextarea.value = '';
                    
                    // Actualizar contador
                    const idPub = formData.get('idpublicacion');
                    const contadorSpan = document.querySelector(`#publicacion-${idPub} .comment-count`);
                    if(contadorSpan) contadorSpan.textContent = parseInt(contadorSpan.textContent || 0) + 1;
                }
            })
            .catch(err => console.error('Error al enviar comentario:', err));
    });

    // Cerrar modal
    document.getElementById('closeModalBtn').addEventListener('click', () => modal.classList.add('hidden'));
    document.getElementById('modalBackdrop').addEventListener('click', () => modal.classList.add('hidden'));
    
    // Emoji Picker
    const emojiPicker = document.querySelector('emoji-picker');
    document.getElementById('emoji-btn').addEventListener('click', () => emojiPicker.classList.toggle('hidden'));
    emojiPicker.addEventListener('emoji-click', e => comentarioTextarea.value += e.detail.unicode);
    
    function escapeHTML(str) {
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }
});
</script>

