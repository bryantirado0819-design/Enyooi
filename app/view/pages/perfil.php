<?php
include_once __DIR__ . '/../custom/header.php';
include_once __DIR__ . '/../custom/navbar.php';

// Variables para facilitar el acceso
$perfil = $datos['perfil'];
$usuario = $datos['usuario'];
$stats = $datos['stats'];
$esPropietario = $datos['esPropietario'];
$isSubscribed = $datos['isSubscribed'];
$puedeVerContenido = $datos['puedeVerContenido'];
$publicacionesDesbloqueadas = $datos['publicacionesDesbloqueadas'] ?? [];
$editProfileLink = $datos['editProfileLink'];
$likesModel = $datos['likesModel'];
$comentariosModel = $datos['comentariosModel'];
$misLikes = $datos['misLikes'] ?? [];
?>

<style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body { font-family: 'Poppins', sans-serif; } 
    .profile-banner { height: 35vh; background-size: cover; background-position: center; border-radius: 0 0 2rem 2rem; }
    .profile-card { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    .profile-avatar { margin-top: -75px; width: 150px; height: 150px; border: 4px solid #0f172a; object-fit: cover; }
    .stat-item { background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; padding: 0.75rem; text-align: center; }
    .action-button { background: linear-gradient(to right, var(--accent), var(--accent-2)); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .action-button:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    .post-card { display: flex; gap: 1rem; padding: 1.5rem 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
    .post-avatar { width: 48px; height: 48px; border-radius: 9999px; object-fit: cover; }
    .post-media { border-radius: 0.75rem; max-height: 500px; width: 100%; object-fit: cover; cursor: pointer; }
    .post-actions button { background: none; border: none; color: #94a3b8; cursor: pointer; transition: color 0.2s ease, transform 0.2s ease; }
    .post-actions button:hover { transform: scale(1.1); }
    .like-btn.like-active, .like-btn.like-active:hover { color: #f43f5e; }
    .btn-comentarios-toggle:hover { color: #38bdf8; }
    .post-media-locked { position: relative; overflow: hidden; border-radius: 0.75rem; }
    .post-media-locked .blur-backdrop { position: absolute; inset: 0; background-size: cover; background-position: center; filter: blur(20px) brightness(0.7); transform: scale(1.1); }
    .unlock-overlay { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1rem; text-align: center; min-height: 300px; }
    .unlock-button { background: linear-gradient(to right, #4f46e5, #7c3aed); color: white; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 9999px; border: none; cursor: pointer; transition: all 0.3s ease; }
    .comment-form-container { display: flex; align-items: center; gap: 0.75rem; margin-top: 1rem; }
    .comment-form { position: relative; flex-grow: 1; display: flex; align-items: center; background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 9999px; }
    .comment-form input { flex-grow: 1; background: transparent; border: none; padding: 10px 15px; color: #e2e8f0; outline: none; }
    .comment-form button { background: none; border: none; cursor: pointer; padding: 8px 12px; color: #94a3b8; transition: color 0.2s ease; }
    .comment-form button:hover { color: var(--accent-2); }
    .emoji-picker-container.hidden { display: none; }
</style>
<body class="page-perfil">
<div class="w-full">
    <!-- Banner y Tarjeta de Perfil (Sin cambios) -->
    <div class="profile-banner" style="background-image: url('<?php echo URL_PROJECT . htmlspecialchars($perfil->banner_portada ?: 'public/img/defaults/default_banner.jpg'); ?>');"></div>
    <main class="max-w-5xl mx-auto p-4 md:p-6 lg:p-8 -mt-24 relative z-10">
        <div class="profile-card rounded-2xl p-6" data-creator-id="<?php echo $usuario->idUsuario; ?>" data-creator-name="<?php echo htmlspecialchars($perfil->nickname_artistico); ?>" data-cost="<?php echo (int)($perfil->precio_suscripcion ?? 0); ?>">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="flex-shrink-0"><img src="<?php echo URL_PROJECT . htmlspecialchars($perfil->foto_perfil); ?>" alt="Avatar" class="profile-avatar rounded-full object-cover"></div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($perfil->nickname_artistico ?: $usuario->usuario); ?></h1>
                    <p class="text-sm text-gray-400">@<?php echo htmlspecialchars($usuario->usuario); ?></p>
                    <p class="text-gray-300 mt-2 text-sm max-w-lg mx-auto md:mx-0"><?php echo htmlspecialchars($perfil->bio); ?></p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-2">
                    <?php if ($esPropietario) : ?>
                        <a href="<?php echo $editProfileLink; ?>" class="action-button inline-block text-white font-semibold px-6 py-2 rounded-full">Editar Perfil</a>
                    <?php else : ?>
                        <button id="btn-subscribe" class="action-button text-white font-semibold px-6 py-2 rounded-full" <?php echo $isSubscribed ? 'disabled' : ''; ?>>
                            <?php echo $isSubscribed ? '<i class="fas fa-check mr-2"></i>Suscrito' : 'Suscribirse por 💎'.(int)($perfil->precio_suscripcion ?? 0).' al mes'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-6 border-t border-slate-700 pt-6">
                <div class="stat-item"><i class="fas fa-camera text-pink-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['fotos']; ?></span><span class="text-xs text-gray-400">Fotos</span></div>
                <div class="stat-item"><i class="fas fa-video text-blue-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['videos']; ?></span><span class="text-xs text-gray-400">Videos</span></div>
                <div class="stat-item"><i class="fas fa-heart text-red-400 mb-1"></i><span class="block text-xl font-bold text-white"><?php echo $stats['likes']; ?></span><span class="text-xs text-gray-400">Likes</span></div>
            </div>
        </div>

        <!-- Feed de Publicaciones -->
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-white mb-4">Publicaciones</h2>
            
            <?php if ($puedeVerContenido): ?>
                <div id="feed-publicaciones">
                    <?php if (!empty($datos['publicaciones'])): ?>
                        <?php foreach ($datos['publicaciones'] as $pub): ?>
                            <!-- Usamos la nueva tarjeta de publicación Glassmorphism -->
                            <div class="post-card-glass publicacion" data-idpublicacion="<?php echo $pub->idPublicacion; ?>">
                                <div class="post-header">
                                    <img src="<?php echo URL_PROJECT . htmlspecialchars($pub->foto_perfil); ?>" alt="Avatar" class="post-avatar">
                                    <div class="post-user-info">
                                        <a href="#" class="post-user-name"><?php echo htmlspecialchars($pub->nickname_artistico); ?></a>
                                        <span class="post-timestamp" data-timestamp="<?php echo $pub->fechaPublicacion; ?>"><?php echo format_time_ago($pub->fechaPublicacion); ?></span>
                                    </div>
                                    <?php if ($esPropietario): ?>
                                        <button class="post-delete-btn delete-btn"><i class="far fa-trash-alt"></i></button>
                                    <?php endif; ?>
                                </div>
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars($pub->contenidoPublicacion)); ?></p>
                                    <?php
                                    $esContenidoDePago = isset($pub->precio_zafiros) && $pub->precio_zafiros > 0;
                                    $haDesbloqueado = in_array($pub->idPublicacion, $publicacionesDesbloqueadas);
                                    $mostrarContenido = !$esContenidoDePago || $esPropietario || $haDesbloqueado;
                                    if ($pub->fotoPublicacion && $pub->fotoPublicacion !== 'sin archivo') : ?>
                                        <div class="mt-3">
                                            <?php if ($mostrarContenido): ?>
                                                <?php if ($pub->tipo_archivo === 'video') : ?><video src="<?php echo htmlspecialchars($pub->fotoPublicacion); ?>" controls class="post-media"></video>
                                                <?php else: ?><img src="<?php echo htmlspecialchars($pub->fotoPublicacion); ?>" alt="Imagen" class="post-media imagen-publicacion-usuario"><?php endif; ?>
                                            <?php else: ?>
                                                <div class="post-media-locked">
                                                    <div class="blur-backdrop" style="background-image: url('<?php echo htmlspecialchars($pub->fotoPublicacion); ?>');"></div>
                                                    <div class="unlock-overlay"><i class="fas fa-lock text-4xl text-white mb-4"></i><h4 class="text-lg font-bold text-white">Contenido Bloqueado</h4><button class="unlock-button mt-4" data-id="<?php echo $pub->idPublicacion; ?>" data-costo="<?php echo $pub->precio_zafiros ?? 0; ?>">Desbloquear por 💎 <?php echo $pub->precio_zafiros ?? 0; ?></button></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="post-actions">
                                    <button class="like-btn <?php echo in_array($pub->idPublicacion, $misLikes) ? 'like-active' : ''; ?>"><i class="fas fa-heart"></i><span class="ml-2 like-count"><?php echo $likesModel->getLikeCount($pub->idPublicacion); ?></span></button>
                                    <button class="btn-comentarios-toggle"><i class="far fa-comment"></i><span class="ml-2 comment-count"><?php echo $comentariosModel->getCommentCount($pub->idPublicacion); ?></span></button>
                                </div>
                                
                                <div class="comentarios-container" style="display: none;">
                                    <div class="comment-form-container">
                                        <img src="<?php echo URL_PROJECT . htmlspecialchars($datos['user_avatar']); ?>" alt="Tu avatar" class="comment-avatar-input">
                                        <form class="comment-form" method="POST" autocomplete="off">
                                            <input type="text" name="contenido" placeholder="Escribe un comentario..." required>
                                            <button type="button" class="emoji-btn" title="Añadir emoji"><i class="far fa-smile"></i></button>
                                            <button type="submit" title="Enviar"><i class="fas fa-paper-plane"></i></button>
                                            <div class="emoji-picker-container absolute bottom-full right-0 mb-2 hidden"><emoji-picker></emoji-picker></div>
                                        </form>
                                    </div>
                                    <div class="lista-comentarios">
                                        <?php $comentarios_del_post = $comentariosModel->getComentariosPorPublicacion($pub->idPublicacion);
                                        foreach ($comentarios_del_post as $comentario): ?>
                                            <div class="comment-item">
                                                <img src="<?php echo URL_PROJECT . htmlspecialchars($comentario->foto_perfil ?: 'public/img/defaults/default_avatar.png'); ?>" alt="Avatar" class="comment-avatar">
                                                <div class="comment-glass">
                                                    <div class="comment-header">
                                                        <a href="#" class="comment-user"><?php echo htmlspecialchars($comentario->usuario); ?></a>
                                                        <span class="comment-timestamp" data-timestamp="<?php echo $comentario->fechaComentario; ?>"><?php echo format_time_ago($comentario->fechaComentario); ?></span>
                                                    </div>
                                                    <p class="comment-content"><?php echo nl2br(htmlspecialchars($comentario->contenidoComentario)); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-400 py-16"><i class="fas fa-camera text-4xl mb-4"></i><p><?php echo $esPropietario ? 'Aún no has subido contenido.' : 'Esta creadora aún no tiene contenido.'; ?></p></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-300 py-20 bg-slate-800/50 rounded-2xl border border-dashed border-slate-600"><i class="fas fa-lock text-5xl text-pink-400 mb-4"></i><h3 class="text-xl font-bold text-white">Contenido Exclusivo</h3><p class="mt-2">Suscríbete a <?php echo htmlspecialchars($perfil->nickname_artistico); ?> para desbloquear todo su contenido.</p></div>
            <?php endif; ?>
        </div>
    </main>
</div>


<div id="enyooi-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden" style="--accent:#ff4fa3; --accent-2:#7c5cff;">
    <div id="enyooi-modal-backdrop" class="absolute inset-0"></div>
    <div id="enyooi-modal-panel" class="card-glass rounded-2xl p-6 sm:p-8 text-center max-w-sm w-11/12 transform transition-all opacity-0 -translate-y-4">
        <div id="enyooi-modal-icon" class="text-5xl mb-4"></div>
        <h3 id="enyooi-modal-title" class="text-2xl font-bold mb-2"></h3>
        <p id="enyooi-modal-message" class="text-blue-200 mb-6"></p>
        <div id="enyooi-modal-actions" class="flex gap-4"></div>
    </div>
</div>

<?php include_once __DIR__ . '/../custom/footer.php'; ?>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<script>
/**
 * =================================================================
 * SCRIPT AUTOCONTENIDO Y COMPLETO PARA LA PÁGINA DE PERFIL (perfil.php)
 * =================================================================
 * Contiene toda la funcionalidad: Likes, Comentarios, Emojis,
 * Tiempo Relativo, Modales, Suscripciones, Desbloqueo y Sockets.
 */

// ¡¡¡DEpurador INICIAL!!! Si ves este mensaje, el script de la VISTA PERFIL se está cargando.
console.log('%c ✅ SCRIPT DE PERFIL.PHP CARGADO CORRECTAMENTE ', 'background: #28a745; color: #ffffff; font-size: 14px; font-weight: bold; padding: 5px;');

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. CONFIGURACIÓN Y VALIDACIÓN DE VARIABLES GLOBALES ---
    if (typeof URL_PROJECT === 'undefined' || typeof socket === 'undefined') {
        console.error("❌ Error Crítico: Las variables globales URL_PROJECT o socket no están definidas. Revisa tu footer.php.");
        return;
    }
    console.log('🌍 Depurador: Variables globales URL_PROJECT y socket encontradas.');
    const URL_BASE = URL_PROJECT;
    const feed = document.getElementById('feed-publicaciones');

    // --- 2. MODAL GLOBAL DE NOTIFICACIONES ---
    const enyooiModal = {
        _modal: document.getElementById('enyooi-modal'),
        _panel: document.getElementById('enyooi-modal-panel'),
        _icon: document.getElementById('enyooi-modal-icon'),
        _title: document.getElementById('enyooi-modal-title'),
        _message: document.getElementById('enyooi-modal-message'),
        _actions: document.getElementById('enyooi-modal-actions'),
        _backdrop: document.getElementById('enyooi-modal-backdrop'),
        show(config) { if (!this._modal) return; this._icon.innerHTML = config.icon; this._title.textContent = config.title; this._message.textContent = config.message; this._actions.innerHTML = config.buttons; this._modal.classList.remove('hidden'); setTimeout(() => { this._panel.classList.remove('opacity-0', '-translate-y-4'); this._panel.classList.add('opacity-100', 'translate-y-0'); }, 10); if (config.onConfirm) { this._actions.querySelector('#modal-confirm-btn')?.addEventListener('click', () => { this.hide(); config.onConfirm(); }, { once: true }); } this._actions.querySelector('#modal-cancel-btn')?.addEventListener('click', () => this.hide(), { once: true }); this._backdrop.addEventListener('click', () => this.hide(), { once: true }); },
        hide() { if (!this._modal) return; this._panel.classList.add('opacity-0', '-translate-y-4'); setTimeout(() => this._modal.classList.add('hidden'), 300); },
        alert(title, msg, icon = 'ℹ️') { this.show({ icon, title, message: msg, buttons: `<button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Entendido</button>` }); },
        confirm(title, msg, onConfirm) { this.show({ icon: '🤔', title, message: msg, buttons: `<button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-white/10 text-white">Cancelar</button><button id="modal-confirm-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Confirmar</button>`, onConfirm }); }
    };
    window.enyooiModal = enyooiModal;

    // --- 3. LÓGICA DE SUSCRIPCIÓN ---
    const subscribeButton = document.getElementById('btn-subscribe');
    if (subscribeButton) {
        subscribeButton.addEventListener('click', () => {
            if (subscribeButton.disabled) return;
            const container = subscribeButton.closest('[data-creator-id]');
            const creatorId = container?.dataset.creatorId;
            const creatorName = container?.dataset.creatorName;
            const subscriptionCost = container?.dataset.cost;

            if (!creatorId || !creatorName || !subscriptionCost) {
                console.error("Faltan atributos de datos (data-*) para la suscripción en el .profile-card");
                return;
            }

            enyooiModal.confirm('Confirmar Suscripción', `¿Deseas suscribirte a ${creatorName} por 💎 ${subscriptionCost} Zafiros al mes?`, () => {
                subscribeButton.disabled = true;
                subscribeButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                fetch(`${URL_BASE}suscripcion/suscribirse/${creatorId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        enyooiModal.alert('¡Éxito!', data.message, '🎉');
                        setTimeout(() => window.location.reload(), 2500);
                    } else {
                        enyooiModal.alert('Error', data.message || 'Ocurrió un error.', '😥');
                        subscribeButton.disabled = false;
                        subscribeButton.innerHTML = `Suscribirse por 💎${subscriptionCost} al mes`;
                    }
                }).catch(err => {
                    enyooiModal.alert('Error de Conexión', 'No se pudo completar la solicitud.', '🌐');
                    subscribeButton.disabled = false;
                    subscribeButton.innerHTML = `Suscribirse por 💎${subscriptionCost} al mes`;
                });
            });
        });
    }

    // --- 4. LÓGICA DEL FEED (LIKES, COMENTARIOS, ELIMINAR, ETC.) ---
    if (feed) {
        console.log('👍 Depurador (perfil.php): Feed encontrado. Adjuntando listeners...');
        feed.addEventListener('click', (event) => {
            const likeBtn = event.target.closest('.like-btn');
            const deleteBtn = event.target.closest('.delete-btn');
            const commentToggleBtn = event.target.closest('.btn-comentarios-toggle');
            const unlockBtn = event.target.closest('.unlock-button');
            const emojiBtn = event.target.closest('.emoji-btn');

            if (likeBtn) {
                event.preventDefault();
                const publicacionDiv = likeBtn.closest('.publicacion');
                const idPublicacion = publicacionDiv?.dataset.idpublicacion;
                if (!idPublicacion) return;
                
                console.log(`❤️ Depurador: Click en "Me gusta" para publicación ID: ${idPublicacion}`);
                likeBtn.classList.toggle('like-active');
                fetch(`${URL_BASE}publicaciones/darLike`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idPublicacion })
                }).catch(err => console.error("Error de red al dar like:", err));
            }

            if (deleteBtn) {
                event.preventDefault();
                const publicacionDiv = deleteBtn.closest('.publicacion');
                const idPublicacion = publicacionDiv?.dataset.idpublicacion;
                if (!idPublicacion) return;

                enyooiModal.confirm('Eliminar Publicación', '¿Estás seguro de que quieres eliminar esta publicación? Esta acción no se puede deshacer.', () => {
                    fetch(`${URL_BASE}publicaciones/eliminar`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: idPublicacion })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            publicacionDiv.style.transition = 'opacity 0.5s ease';
                            publicacionDiv.style.opacity = '0';
                            setTimeout(() => publicacionDiv.remove(), 500);
                        } else {
                            enyooiModal.alert('Error', data.message || 'No se pudo eliminar la publicación.');
                        }
                    })
                    .catch(err => enyooiModal.alert('Error de Red', 'No se pudo conectar con el servidor.'));
                });
            }
            
            if (commentToggleBtn) {
                const container = commentToggleBtn.closest('.publicacion')?.querySelector('.comentarios-container');
                if (container) container.style.display = container.style.display === 'none' || container.style.display === '' ? 'block' : 'none';
            }

            if (unlockBtn && !unlockBtn.disabled) {
                const idPublicacion = unlockBtn.dataset.id;
                const costo = unlockBtn.dataset.costo;
                enyooiModal.confirm('Desbloquear Contenido', `¿Quieres desbloquear esto por 💎 ${costo} Zafiros?`, () => {
                    unlockBtn.disabled = true;
                    unlockBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    fetch(`${URL_BASE}publicaciones/desbloquear`, {
                        method: 'POST', headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ idPublicacion })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            enyooiModal.alert('¡Contenido Desbloqueado!', 'La página se recargará para mostrarlo.', '🔓');
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            enyooiModal.alert('Error', data.message, '😥');
                            unlockBtn.disabled = false;
                            unlockBtn.innerHTML = `Desbloquear por 💎 ${costo}`;
                        }
                    }).catch(err => {
                        enyooiModal.alert('Error de Conexión', err.message, '🌐');
                        unlockBtn.disabled = false;
                        unlockBtn.innerHTML = `Desbloquear por 💎 ${costo}`;
                    });
                });
            }

            if (emojiBtn) {
                event.stopPropagation();
                const pickerContainer = emojiBtn.closest('.comment-form')?.querySelector('.emoji-picker-container');
                if (pickerContainer) pickerContainer.classList.toggle('hidden');
            }
        });

        feed.addEventListener('submit', (event) => {
            if (event.target.classList.contains('comment-form')) {
                event.preventDefault();
                const form = event.target;
                const publicacionDiv = form.closest('.publicacion');
                const idPublicacion = publicacionDiv?.dataset.idpublicacion;
                const input = form.querySelector('input[name="contenido"]');
                const contenido = input?.value.trim();

                if (contenido && idPublicacion) {
                    console.log(`💬 Depurador: Enviando comentario "${contenido}" a publicación ID: ${idPublicacion}`);
                    fetch(`${URL_BASE}publicaciones/agregarComentario`, {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ idPublicacion, contenido })
                    })
                    .then(res => res.json())
                    .then(data => { if (data.success) input.value = ''; else alert(data.message); })
                    .catch(err => console.error("Error al comentar:", err));
                }
            }
        });
        
        document.querySelectorAll('emoji-picker').forEach(picker => {
            picker.addEventListener('emoji-click', event => {
                const form = picker.closest('.comment-form');
                if (form) {
                    const input = form.querySelector('input[name="contenido"]');
                    input.value += event.detail.unicode;
                }
            });
        });
        
        // --- 5. SOCKET.IO LISTENERS ---
        console.log('📡 Depurador (perfil.php): Configurando listeners de Socket.IO.');
        const findPostElement = (postId) => document.querySelector(`.publicacion[data-idpublicacion='${postId}']`);

        socket.on('likeUpdate', (data) => {
            console.log('📡 Socket [likeUpdate] recibido:', data);
            const postElement = findPostElement(data.postId);
            if (postElement) postElement.querySelector('.like-count').textContent = data.newLikeCount;
        });
        
        socket.on('commentCountUpdate', (data) => {
            console.log('📡 Socket [commentCountUpdate] recibido:', data);
            const postElement = findPostElement(data.postId);
            if (postElement) postElement.querySelector('.comment-count').textContent = data.newCommentCount;
        });

        socket.on('newComment', (data) => {
            console.log('📡 Socket [newComment] recibido:', data);
            const postElement = findPostElement(data.postId);
            if (postElement && data.newComment) {
                const listaComentarios = postElement.querySelector('.lista-comentarios');
                const comment = data.newComment;
                const avatarUrl = (comment.foto_perfil || '').startsWith('http') ? comment.foto_perfil : `${URL_BASE}${comment.foto_perfil}`;
                const tiempoRelativo = formatTimeAgo(comment.fechaComentario);

                const nuevoComentarioHTML = `
                <div class="flex items-start gap-3 mt-3">
                    <img src="${avatarUrl}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                    <div class="bg-slate-700 rounded-lg p-2 flex-1">
                        <div class="flex justify-between items-center">
                            <p class="font-bold text-white text-sm">${escapeHTML(comment.usuario)}</p>
                            <p class="text-xs text-gray-400">${tiempoRelativo}</p>
                        </div>
                        <p class="text-gray-300 text-sm mt-1">${nl2br(escapeHTML(comment.contenidoComentario))}</p>
                    </div>
                </div>`;
                listaComentarios.insertAdjacentHTML('beforeend', nuevoComentarioHTML);
            }
        });
    }

    // --- 6. HELPERS Y FUNCIONES ADICIONALES ---
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.emoji-btn') && !e.target.closest('emoji-picker')) {
            document.querySelectorAll('.emoji-picker-container').forEach(p => p.classList.add('hidden'));
        }
    });
    
    function escapeHTML(str) { return str ? str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]) : ''; }
    function nl2br(str) { return str ? str.replace(/(\r\n|\n\r|\r|\n)/g, "<br>") : ''; }

    function formatTimeAgo(dateString) {
        const date = new Date(dateString.replace(/-/g, '/'));
        const now = new Date();
        const seconds = Math.round((now - date) / 1000);
        const minutes = Math.round(seconds / 60);
        const hours = Math.round(minutes / 60);
        const days = Math.round(hours / 24);

        if (seconds < 10) return "hace un momento";
        if (seconds < 60) return `hace ${seconds} segundos`;
        if (minutes < 60) return `hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
        if (hours < 24) return `hace ${hours} hora${hours > 1 ? 's' : ''}`;
        if (days < 7) return `hace ${days} día${days > 1 ? 's' : ''}`;
        
        return date.toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric' });
    }

    document.querySelectorAll('.comment-timestamp').forEach(span => {
        const dateString = span.dataset.timestamp;
        if (dateString) {
            span.textContent = formatTimeAgo(dateString);
        }
    });
});
</script>

