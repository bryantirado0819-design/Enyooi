/**
 * VERSIÓN FINAL Y COMPLETA - ENYOOI JavaScript con WebSockets y Animaciones
 * Esta versión incluye manejo robusto de errores, URLs corregidas y animaciones mejoradas.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. --- CONFIGURACIÓN INICIAL ---

    // Aseguramos que la variable base de la URL siempre termine con una barra ('/').
    // Esto previene errores 404 al construir las rutas para las peticiones fetch.
    const URL_BASE = (typeof URL_PROJECT !== 'undefined' && URL_PROJECT.endsWith('/') ? URL_PROJECT : URL_PROJECT + '/');

    if (typeof socket === 'undefined' || typeof URL_PROJECT === 'undefined') {
        console.error("Error Crítico: Las variables 'socket' o 'URL_PROJECT' no están definidas. Revisa tu archivo footer.php.");
        return;
    }

    const feed = document.getElementById('feed-publicaciones');
    if (!feed) {
        // Si no estamos en la página de publicaciones, no se ejecuta nada más.
        return;
    }

    // --- 2. MANEJO DE ACCIONES DEL USUARIO (CLICKS Y SUBMITS) ---

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

            // Mejora de la experiencia de usuario: la UI reacciona al instante.
            likeBtn.classList.toggle('like-active');
            if (heartIcon && likeBtn.classList.contains('like-active')) {
                heartIcon.classList.add('beating');
                heartIcon.addEventListener('animationend', () => heartIcon.classList.remove('beating'), { once: true });
            }

            // Petición asíncrona al servidor para registrar el like
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

    // Delegación de eventos para manejar el envío de todos los formularios de comentarios
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
                    // Si la respuesta del servidor no es exitosa (ej. 404, 500),
                    // la procesamos como texto para poder ver el error de PHP.
                    if (!res.ok) {
                        return res.text().then(text => { 
                            throw new Error(`Error ${res.status} en el servidor: ${text}`);
                        });
                    }
                    // Si la respuesta es exitosa, la procesamos como JSON.
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        input.value = ''; // Limpiar el input solo si todo fue bien.
                    } else {
                        // Muestra el error controlado que PHP envió en el JSON.
                        alert(`No se pudo enviar el comentario: ${data.message}`);
                    }
                })
                .catch(err => {
                    // Captura errores de red o el error de PHP lanzado en el .then()
                    console.error("Error crítico al enviar comentario:", err);
                    alert("No se pudo enviar el comentario. Revisa la consola para más detalles técnicos.");
                });
            }
        }
    });

    // --- 3. ESCUCHA DE EVENTOS WEBSOCKET (Recepción en tiempo real desde Node.js) ---

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
    
    // --- 4. FUNCIONALIDAD DEL MODAL DE IMAGEN ---
    const imageModal = document.getElementById("modalImagen");
    if (imageModal) {
        const mainContent = document.getElementById("main-container");
        const closeModalBtn = imageModal.querySelector(".close");

        function cerrarModalImagen() {
            imageModal.style.display = "none";
            mainContent.classList.remove("blur");
        }
        
        if (closeModalBtn) closeModalBtn.addEventListener('click', cerrarModalImagen);
        
        imageModal.addEventListener('click', (event) => {
            if (event.target === imageModal) {
                cerrarModalImagen();
            }
        });
    }

    // --- 5. FUNCIONES DE UTILIDAD ---
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
});