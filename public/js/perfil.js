/**
 * =================================================================
 * LÓGICA COMPLETA Y DEDICADA PARA LA PÁGINA DE PERFIL (perfil.php)
 * =================================================================
 * Este archivo contiene toda la funcionalidad necesaria para la página de perfil,
 * incluyendo likes, comentarios, eliminación de posts, modales, suscripciones y
 * desbloqueo de contenido. NO debe ser cargado junto con main.js.
 */
console.log('%c ✅ PERFIL.JS CARGADO CORRECTAMENTE ', 'background: #28a745; color: #ffffff; font-size: 14px; font-weight: bold; padding: 5px;');

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. CONFIGURACIÓN Y SELECTORES GLOBALES ---
    if (typeof URL_PROJECT === 'undefined' || typeof socket === 'undefined') {
        console.error("Error Crítico: Las variables globales URL_PROJECT o socket no están definidas. Revisa tu footer.php.");
        return;
    }
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
        
        show(config) {
            if (!this._modal || !this._panel || !this._icon || !this._title || !this._message || !this._actions) return;
            this._icon.innerHTML = config.icon;
            this._title.textContent = config.title;
            this._message.textContent = config.message;
            this._actions.innerHTML = config.buttons;
            
            this._modal.classList.remove('hidden');
            setTimeout(() => {
                this._panel.classList.remove('opacity-0', '-translate-y-4');
                this._panel.classList.add('opacity-100', 'translate-y-0');
            }, 10);

            if (config.onConfirm) {
                this._actions.querySelector('#modal-confirm-btn')?.addEventListener('click', () => { this.hide(); config.onConfirm(); }, { once: true });
            }
            this._actions.querySelector('#modal-cancel-btn')?.addEventListener('click', () => this.hide(), { once: true });
            this._backdrop.addEventListener('click', () => this.hide(), { once: true });
        },
        hide() {
            if (!this._modal || !this._panel) return;
            this._panel.classList.add('opacity-0', '-translate-y-4');
            setTimeout(() => this._modal.classList.add('hidden'), 300);
        },
        alert(title, msg, icon = 'ℹ️') {
            this.show({ icon, title, message: msg, buttons: `<button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Entendido</button>` });
        },
        confirm(title, msg, onConfirm) {
            this.show({ icon: '🤔', title, message: msg, buttons: `<button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-white/10 text-white">Cancelar</button><button id="modal-confirm-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Confirmar</button>`, onConfirm });
        }
    };
    // Hacemos el modal accesible globalmente por si otros scripts lo necesitan
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
        
        // --- 5. SOCKET.IO LISTENERS ---
        const findPostElement = (postId) => document.querySelector(`.publicacion[data-idpublicacion='${postId}']`);

        socket.on('likeUpdate', (data) => {
            const postElement = findPostElement(data.postId);
            if (postElement) postElement.querySelector('.like-count').textContent = data.newLikeCount;
        });
        
        socket.on('commentCountUpdate', (data) => {
            const postElement = findPostElement(data.postId);
            if (postElement) postElement.querySelector('.comment-count').textContent = data.newCommentCount;
        });

        socket.on('newComment', (data) => {
            const postElement = findPostElement(data.postId);
            if (postElement && data.newComment) {
                const listaComentarios = postElement.querySelector('.lista-comentarios');
                const comment = data.newComment;
                const avatarUrl = comment.foto_perfil.startsWith('http') ? comment.foto_perfil : `${URL_BASE}${comment.foto_perfil}`;
                const nuevoComentarioHTML = `<div class="flex items-start gap-3 mt-3"><img src="${avatarUrl}" alt="Avatar" class="w-8 h-8 rounded-full object-cover"><div class="bg-slate-700 rounded-lg p-2 flex-1"><p class="font-bold text-white text-sm">${escapeHTML(comment.usuario)}</p><p class="text-gray-300 text-sm mt-1">${nl2br(escapeHTML(comment.contenidoComentario))}</p></div></div>`;
                listaComentarios.insertAdjacentHTML('beforeend', nuevoComentarioHTML);
            }
        });
    }

    // --- 6. HELPERS Y OTROS EVENT LISTENERS ---
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.emoji-btn') && !e.target.closest('emoji-picker')) {
            document.querySelectorAll('.emoji-picker-container').forEach(p => p.classList.add('hidden'));
        }
    });
    
    function escapeHTML(str) { return str ? str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]) : ''; }
    function nl2br(str) { return str ? str.replace(/(\r\n|\n\r|\r|\n)/g, "<br>") : ''; }
});