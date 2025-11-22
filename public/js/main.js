document.addEventListener('DOMContentLoaded', () => {
    // 1. --- CONFIGURACIÓN GLOBAL ---
    const URL_BASE = (typeof URL_PROJECT !== 'undefined' && URL_PROJECT.endsWith('/') ? URL_PROJECT : URL_PROJECT + '/');

    // --- 2. LÓGICA PARA EL FEED DE PUBLICACIONES ---
    const feed = document.getElementById('feed-publicaciones');
    
    // ✅ **SOLUCIÓN DEFINITIVA ANTI-CONFLICTO**
    // Si el 'feed-publicaciones' existe, significa que estamos en la página de perfil.
    // En ese caso, este script NO debe manejar los eventos del feed, ya que
    // el script autocontenido en perfil.php se encargará de ello.
    if (document.body.classList.contains('page-perfil')) {
        console.log('Depurador (main.js): Página de perfil detectada. La lógica del feed en main.js ha sido desactivada para evitar conflictos.');
        // No adjuntamos ningún listener del feed y continuamos con el resto del script si fuera necesario.
    } else if (feed) {
        // Si el feed existe Y NO estamos en la página de perfil (ej. en el home), se activa la lógica.
        console.log('Depurador (main.js): Feed detectado en una página que no es de perfil. Activando listeners...');

        if (typeof socket === 'undefined') {
            console.error("Error Crítico: La variable 'socket' no está definida. Revisa tu footer.php.");
            return;
        }

        // Delegación de eventos para manejar todos los clics dentro del feed
        feed.addEventListener('click', (event) => {
            const likeBtn = event.target.closest('.like-btn');
            const deleteBtn = event.target.closest('.delete-btn');
            const commentToggleBtn = event.target.closest('.btn-comentarios-toggle');
            const imageToOpen = event.target.closest('.imagen-publicacion-usuario');

            // --- Lógica para Likes ---
            if (likeBtn) {
                event.preventDefault();
                const publicacionDiv = likeBtn.closest('.publicacion');
                if (!publicacionDiv) return;

                const idPublicacion = publicacionDiv.dataset.idpublicacion;
                const heartIcon = likeBtn.querySelector('.fa-heart');

                likeBtn.classList.toggle('like-active');
                if (heartIcon && likeBtn.classList.contains('like-active')) {
                    heartIcon.classList.add('beating');
                    heartIcon.addEventListener('animationend', () => heartIcon.classList.remove('beating'), { once: true });
                }

                fetch(`${URL_BASE}publicaciones/darLike`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idPublicacion })
                }).catch(err => console.error("Error de red al dar like:", err));
            }

            // --- Lógica para Eliminar Publicación ---
            if (deleteBtn) {
                event.preventDefault();
                const publicacionDiv = deleteBtn.closest('.publicacion');
                if (!publicacionDiv) return;
                const idPublicacion = publicacionDiv.dataset.idpublicacion;

                if (confirm('¿Estás seguro de que quieres eliminar esta publicación?')) {
                    fetch(`${URL_BASE}publicaciones/eliminar`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: idPublicacion })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            publicacionDiv.style.transition = 'opacity 0.5s ease';
                            publicacionDiv.style.opacity = '0';
                            setTimeout(() => publicacionDiv.remove(), 500);
                        } else {
                            alert(data.message || 'No se pudo eliminar la publicación.');
                        }
                    }).catch(err => console.error("Error de red al eliminar:", err));
                }
            }

            // --- Lógica para Mostrar/Ocultar Comentarios ---
            if (commentToggleBtn) {
                const publicacionDiv = commentToggleBtn.closest('.publicacion');
                if (!publicacionDiv) return;
                const container = publicacionDiv.querySelector('.comentarios-container');
                if (container) {
                    const isHidden = container.style.display === 'none' || container.style.display === '';
                    container.style.display = isHidden ? 'block' : 'none';
                }
            }

            // --- Lógica para Abrir Imagen en Modal ---
            if (imageToOpen) {
                const mainContent = document.getElementById("main-container");
                const imageModal = document.getElementById("modalImagen");
                const imgModalContent = imageModal ? imageModal.querySelector(".modal-content") : null;
                if (imageModal && imgModalContent && mainContent) {
                    imageModal.style.display = "flex";
                    imgModalContent.src = imageToOpen.src;
                    mainContent.classList.add("blur");
                }
            }
        });

        // Delegación de eventos para el envío de formularios de comentarios
        feed.addEventListener('submit', (event) => {
            if (event.target.classList.contains('comment-form')) {
                event.preventDefault();
                const form = event.target;
                const publicacionDiv = form.closest('.publicacion');
                if (!publicacionDiv) return;
                
                const idPublicacion = publicacionDiv.dataset.idpublicacion;
                const input = form.querySelector('input[name="contenido"]');
                const contenido = input.value.trim();

                if (contenido && idPublicacion) {
                    fetch(`${URL_BASE}publicaciones/agregarComentario`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ idPublicacion, contenido })
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.text().then(text => { throw new Error(`Error ${res.status} en el servidor: ${text}`); });
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            input.value = '';
                        } else {
                            alert(`No se pudo enviar el comentario: ${data.message}`);
                        }
                    })
                    .catch(err => {
                        console.error("Error crítico al enviar comentario:", err);
                        alert("No se pudo enviar el comentario. Revisa la consola para más detalles.");
                    });
                }
            }
        });

        // --- Escucha de eventos WebSocket para el feed ---
        socket.on('likeUpdate', (data) => {
            const { postId, newLikeCount } = data;
            const publicacionDiv = document.querySelector(`.publicacion[data-idpublicacion='${postId}']`);
            if (publicacionDiv) {
                const likeCountSpan = publicacionDiv.querySelector('.like-count');
                if(likeCountSpan) likeCountSpan.textContent = newLikeCount;
            }
        });

        socket.on('newComment', (data) => {
            const { postId, comment } = data;
            const publicacionDiv = document.querySelector(`.publicacion[data-idpublicacion='${postId}']`);
            if (publicacionDiv && comment) {
                const listaComentarios = publicacionDiv.querySelector('.lista-comentarios');
                if(listaComentarios) {
                    const avatarUrl = comment.foto_perfil 
                        ? `${URL_BASE}${comment.foto_perfil.replace(URL_PROJECT, '')}` 
                        : `${URL_BASE}img/defaults/default_avatar.png`;

                    const nuevoComentarioHTML = `
                        <div class="container-contenido-comentarios animate-fade-in">
                            <img src="${avatarUrl}" alt="Avatar de ${escapeHTML(comment.usuario)}" class="image-border mr-2">
                            <div class="contenido-comentario-usuario">
                                <a href="${URL_BASE}perfil/${escapeHTML(comment.usuario)}" class="big mr-2">${escapeHTML(comment.usuario)}</a>
                                <span>Ahora mismo</span>
                                <p>${nl2br(escapeHTML(comment.contenidoComentario))}</p>
                            </div>
                        </div>`;
                    listaComentarios.insertAdjacentHTML('beforeend', nuevoComentarioHTML);
                }
            }
        });

        socket.on('commentCountUpdate', (data) => {
            const { postId, newCommentCount } = data;
            const publicacionDiv = document.querySelector(`.publicacion[data-idpublicacion='${postId}']`);
            if (publicacionDiv) {
                const commentCountSpan = publicacionDiv.querySelector('.comment-count');
                if(commentCountSpan) commentCountSpan.textContent = newCommentCount;
            }
        });
    }

    // --- 3. NUEVA LÓGICA PARA LA CUADRÍCULA DE LIVES ---

    const livesGrid = document.getElementById('lives-grid');
    if (livesGrid) {
        // Si la cuadrícula de lives existe, se ejecuta esta lógica.
        fetch(`${URL_BASE}live/getActiveStreams`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error de red: ${response.statusText}`);
                }
                return response.json();
            })
            .then(streams => {
                if (streams.length === 0) {
                    livesGrid.innerHTML = '<p style="color: white; text-align: center;">No hay transmisiones en vivo en este momento.</p>';
                    return;
                }
                streams.forEach(stream => {
                    const card = document.createElement('div');
                    card.className = 'live-card';
                    card.onclick = () => { window.location.href = `${URL_BASE}live/watch/${stream.idstream}`; };
                    
                    // Usar imagen por defecto si no hay thumbnail aún
                    const thumbnail = stream.thumbnail_url || `${URL_BASE}public/img/defaults/default_live.jpg`;

                    card.innerHTML = `
                        <img src="${thumbnail}" alt="Live stream thumbnail">
                        <div class="live-card-info">
                            <h3>${escapeHTML(stream.titulo)}</h3>
                            <p>${escapeHTML(stream.usuario)}</p>
                        </div>
                    `;
                    livesGrid.appendChild(card);
                });
            })
            .catch(err => {
                console.error("Error al cargar los streams:", err);
                livesGrid.innerHTML = '<p style="color: red; text-align: center;">No se pudieron cargar las transmisiones. Intenta de nuevo más tarde.</p>';
            });
    }

    // --- 4. FUNCIONALIDAD DEL MODAL DE IMAGEN (Global) ---
    const imageModal = document.getElementById("modalImagen");
    if (imageModal) {
        const mainContent = document.getElementById("main-container");
        const closeModalBtn = imageModal.querySelector(".close");

        function cerrarModalImagen() {
            imageModal.style.display = "none";
            if (mainContent) mainContent.classList.remove("blur");
        }
        
        if (closeModalBtn) closeModalBtn.addEventListener('click', cerrarModalImagen);
        
        imageModal.addEventListener('click', (event) => {
            if (event.target === imageModal) {
                cerrarModalImagen();
            }
        });
    }

    // --- 5. FUNCIONES DE UTILIDAD (Globales) ---
    function escapeHTML(str) {
        if (!str) return '';
        const p = document.createElement("p");
        p.textContent = str;
        return p.innerHTML;
    }

    function nl2br(str) {
        if (!str) return '';
        return str.replace(/(\r\n|\n\r|\r|\n)/g, "<br>");
    }

    const enyooiModal = {
    _modal: document.getElementById('enyooi-modal'),
    _panel: document.getElementById('enyooi-modal-panel'),
    _icon: document.getElementById('enyooi-modal-icon'),
    _title: document.getElementById('enyooi-modal-title'),
    _message: document.getElementById('enyooi-modal-message'),
    _actions: document.getElementById('enyooi-modal-actions'),
    _backdrop: document.getElementById('enyooi-modal-backdrop'),
    
    show(config) {
        this._icon.innerHTML = config.icon;
        this._title.textContent = config.title;
        this._message.textContent = config.message;
        this._actions.innerHTML = config.buttons;
        
        this._modal.classList.remove('hidden');
        setTimeout(() => {
            this._panel.classList.remove('opacity-0', '-translate-y-4');
            this._panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);

        // Asignar eventos a los nuevos botones
        if (config.onConfirm) {
            this._actions.querySelector('#modal-confirm-btn')?.addEventListener('click', () => {
                this.hide();
                config.onConfirm();
            });
        }
        this._actions.querySelector('#modal-cancel-btn')?.addEventListener('click', () => this.hide());
        this._backdrop.addEventListener('click', () => this.hide(), { once: true });
    },

    hide() {
        this._panel.classList.add('opacity-0', '-translate-y-4');
        setTimeout(() => this._modal.classList.add('hidden'), 300);
    },

    alert(title, message, icon = 'ℹ️') {
        this.show({
            icon: icon,
            title: title,
            message: message,
            buttons: `<button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Entendido</button>`
        });
    },

    confirm(title, message, onConfirmCallback) {
        this.show({
            icon: '🤔',
            title: title,
            message: message,
            buttons: `
                <button id="modal-cancel-btn" class="flex-1 py-2 rounded-full bg-white/10 text-white">Cancelar</button>
                <button id="modal-confirm-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[var(--accent)] to-[var(--accent-2)] text-white font-semibold">Confirmar</button>
            `,
            onConfirm: onConfirmCallback
        });
    }
};
    window.enyooiModal = enyooiModal;

    

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

    if (isLoggedIn) {
        const bellButton = document.getElementById('notification-bell-button');
        const dropdownPanel = document.getElementById('notification-dropdown-panel');
        const listContainer = document.getElementById('notification-list-container');
        const countBadge = document.getElementById('notification-count-badge');
        
        const fetchAndRenderNotifications = async () => {
            if (!listContainer) return;
            listContainer.innerHTML = '<p class="text-sm text-center p-4 text-slate-400">Cargando...</p>';
            try {
                const response = await fetch(`${URL_BASE}notificacion/dropdown`);
                const data = await response.json();
                if (data.success) {
                    renderNotifications(data.notifications);
                    updateUnreadCount(0);
                } else {
                    listContainer.innerHTML = `<p class="text-sm text-center p-4 text-red-400">${data.message || 'Error al cargar.'}</p>`;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
                listContainer.innerHTML = `<p class="text-sm text-center p-4 text-red-400">Error de conexión.</p>`;
            }
        };

        const renderNotifications = (notifications) => {
            listContainer.innerHTML = '';
            if (notifications.length === 0) {
                listContainer.innerHTML = '<p class="text-sm text-center p-4 text-slate-400">No hay notificaciones nuevas.</p>';
                return;
            }
            notifications.forEach(notif => {
                const notifElement = createNotificationElement(notif);
                listContainer.insertAdjacentHTML('beforeend', notifElement);
            });
        };

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
        
        const createNotificationElement = (notif) => {
            const userPhoto = notif.usuarioAccionFoto ? `${URL_BASE}${notif.usuarioAccionFoto}` : `${URL_BASE}public/img/defaults/default_avatar.png`;
            return `
                <a href="${URL_BASE}perfil/${notif.usuarioAccionNombre}" class="flex items-start gap-3 p-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50">
                    <img src="${userPhoto}" alt="Avatar" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    <div class="text-sm flex-1">
                        <p class="text-gray-200">
                            <strong class="font-semibold text-white">${notif.usuarioAccionNombre}</strong> ${notif.mensajeNotificacion}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">${formatTimeAgo(notif.fechaNotificacion)}</p>
                    </div>
                </a>`;
        };
        
        const updateUnreadCount = (count) => {
            if (!countBadge) return;
            countBadge.textContent = count > 9 ? '9+' : count;
            countBadge.classList.toggle('hidden', !count || count === 0);
        };
        
        const addNewNotificationFromSocket = (notif) => {
            if (!listContainer) return;
            const notifElement = createNotificationElement(notif);
            const placeholder = listContainer.querySelector('p');
            if(placeholder && listContainer.children.length === 1) {
                placeholder.remove();
            }
            listContainer.insertAdjacentHTML('afterbegin', notifElement);
            while(listContainer.children.length > 7) {
                listContainer.lastElementChild.remove();
            }
            let currentCount = parseInt(countBadge.textContent || '0');
            updateUnreadCount(currentCount + 1);
        };
        
        // ✅ SOLUCIÓN: El evento de clic ahora se maneja desde el script del navbar.
        // Aquí solo nos aseguramos de que cuando el panel se abra (por el toggle de la clase 'open'),
        // se carguen las notificaciones.
        if (bellButton && dropdownPanel) {
            // Observador para detectar cuándo se abre el panel
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName === 'class' && dropdownPanel.classList.contains('open')) {
                        console.log("🔔 Panel de notificaciones abierto. Cargando...");
                        fetchAndRenderNotifications();
                    }
                });
            });
            observer.observe(dropdownPanel, { attributes: true });
        }
        
        // Conexión con Socket.IO (sin cambios)
        if (typeof socket !== 'undefined' && userId) {
            socket.on('connect', () => {
                // Solicitar conteo inicial al conectar
                fetch(`${URL_BASE}notificacion/countUnread`).then(res=>res.json()).then(data=>{
                    if(data.success) updateUnreadCount(data.count);
                });
            });

            socket.on('nueva_notificacion', (data) => {
                if (data.idUsuario == userId) {
                    addNewNotificationFromSocket(data);
                }
            });
        }
    }
    

});