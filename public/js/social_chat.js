document.addEventListener('DOMContentLoaded', () => {
    const el = (selector) => document.querySelector(selector);

    // --- Elementos del DOM ---
    const contactsList = el('#contactsList');
    const searchUser = el('#searchUser');
    const messagesContainer = el('#messagesContainer');
    const chatWelcome = el('#chat-welcome');
    const chatArea = el('#chat-area');
    const chatTitle = el('#chat-title');
    const chatAvatar = el('#chat-avatar');
    const chatStatus = el('#chat-status');
    const messageInput = el('#messageInput');
    const sendBtn = el('#sendBtn');
    const destinatarioIdInput = el('#destinatario-id');
    const typingIndicator = el('#typingIndicator');
    const emojiBtn = el('#emoji-btn');
    const imageUploadInput = el('#image-upload');
    const unlockModal = el('#unlock-chat-modal');
    const unlockCreatorName = el('#unlock-creator-name');
    const unlockChatPrice = el('#unlock-chat-price');
    const cancelUnlockBtn = el('#cancel-unlock-btn');
    const confirmUnlockBtn = el('#confirm-unlock-btn');

    // --- Estado ---
    let socket;
    let currentChatUser = null;
    let typingTimeout = null;
    let emojiPicker;
    let userToUnlock = null;

    // --- Funciones ---
    const escapeHTML = (str) => str ? str.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]) : '';

    function formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    async function fetchContacts(query = '') {
        try {
            const response = await fetch(`${URL_PROJECT}mensajes/contactos?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            if (data.success) renderContacts(data.contacts);
        } catch (error) { console.error('Error fetching contacts:', error); }
    }

    function renderContacts(contacts) {
        contactsList.innerHTML = '';
        if (!contacts.length) {
            contactsList.innerHTML = `<p class="text-center text-slate-400 text-sm p-4">No se encontraron usuarios.</p>`;
            return;
        }
        contacts.forEach(contact => {
            const contactEl = document.createElement('div');
            contactEl.className = 'flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-white/10 transition-colors';
            contactEl.dataset.userId = contact.id;
            const lastMessage = contact.last_message_content ? escapeHTML(contact.last_message_content) : 'No hay mensajes...';
            
            let unreadBadge = '';
            if (contact.unread_count > 0) {
                unreadBadge = `<span class="bg-pink-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">${contact.unread_count}</span>`;
            }

            contactEl.innerHTML = `
                <div class="relative">
                    <img src="${URL_PROJECT}${contact.foto_perfil}" class="w-12 h-12 rounded-full object-cover">
                    <div id="status-${contact.id}" class="absolute bottom-0 right-0 w-3 h-3 bg-slate-500 rounded-full border-2 border-slate-800"></div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="flex justify-between items-center">
                        <h4 class="font-semibold truncate">${escapeHTML(contact.nickname_artistico || contact.usuario)}</h4>
                        <span class="text-xs text-slate-400">${formatTime(contact.last_message_time)}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-slate-400 truncate">${lastMessage}</p>
                        ${unreadBadge}
                    </div>
                </div>
            `;
            contactEl.addEventListener('click', () => openChat(contact));
            contactsList.appendChild(contactEl);
        });
    }

    async function openChat(contact) {
        if (contact.rol === 'creadora' && contact.chat_precio > 0 && !contact.chat_desbloqueado) {
            userToUnlock = contact;
            unlockCreatorName.textContent = contact.nickname_artistico || contact.usuario;
            unlockChatPrice.textContent = contact.chat_precio;
            unlockModal.classList.remove('hidden');
            unlockModal.classList.add('flex');
            return;
        }
        currentChatUser = contact;
        chatWelcome.classList.add('hidden');
        chatArea.classList.remove('hidden');
        chatArea.classList.add('flex');
        chatTitle.textContent = contact.nickname_artistico || contact.usuario;
        chatAvatar.src = `${URL_PROJECT}${contact.foto_perfil}`;
        destinatarioIdInput.value = contact.id;
        await fetchMessages(contact.id);
        socket.emit('get-status', { userId: contact.id });
        fetchContacts(searchUser.value);
    }

    async function fetchMessages(contactId) {
        messagesContainer.innerHTML = '<p class="text-center text-slate-400">Cargando...</p>';
        try {
            const response = await fetch(`${URL_PROJECT}mensajes/conversacion/${contactId}`);
            const data = await response.json();
            if (data.success) {
                renderMessages(data.messages);
            } else {
                 messagesContainer.innerHTML = `<p class="text-center text-red-400">${data.message || 'Error al cargar mensajes.'}</p>`;
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
            messagesContainer.innerHTML = '<p class="text-center text-red-400">Error de conexión al cargar mensajes.</p>';
        }
    }

    function renderMessages(messages) {
        messagesContainer.innerHTML = '';
        messages.forEach(msg => addMessageToUI(msg));
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addMessageToUI(message) {
        const isMe = parseInt(message.remitente_id) === USER_ID;
        const messageEl = document.createElement('div');
        messageEl.className = `flex items-end gap-2 ${isMe ? 'justify-end' : 'justify-start'}`;
        messageEl.dataset.messageId = message.idMensaje;
        
        let contentHTML = message.contenido ? `<p class="text-sm break-words">${escapeHTML(message.contenido)}</p>` : '';
        if (message.media_url) {
            contentHTML += `<img src="${URL_PROJECT}${message.media_url}" class="mt-2 rounded-lg max-w-xs cursor-pointer" onclick="openImageModal(this.src)">`;
        }
        
        let seenHTML = '';
        if (isMe) {
            const iconClass = message.leido == 1 ? 'fa-solid fa-check-double seen-icon seen' : 'fa-solid fa-check seen-icon sent';
            seenHTML = `<span class="seen-status">${formatTime(message.fechaMensaje)} <i class="${iconClass}"></i></span>`;
        } else {
            seenHTML = formatTime(message.fechaMensaje);
        }

        messageEl.innerHTML = `
            <div class="max-w-md">
                <div class="px-4 py-2 rounded-2xl ${isMe ? 'bubble-me text-white' : 'bubble-other'}">${contentHTML}</div>
                <div class="text-xs text-slate-400 mt-1 px-2 ${isMe ? 'text-right' : 'text-left'}">${seenHTML}</div>
            </div>
        `;
        messagesContainer.appendChild(messageEl);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function sendMessage() {
        const content = messageInput.value.trim();
        const destinatarioId = destinatarioIdInput.value;
        if (!content || !destinatarioId) return;

        const messageToSend = {
            destinatario_id: destinatarioId,
            contenido: content
        };
        
        messageInput.value = '';
        messageInput.style.height = 'auto';

        fetch(`${URL_PROJECT}mensajes/enviarMensaje`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(messageToSend)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                addMessageToUI(data.message_data);
                socket.emit('private-message', data.message_data);
                fetchContacts(searchUser.value);
            } else {
                console.error('Error al enviar el mensaje:', data.message);
                alert('No se pudo enviar el mensaje.');
            }
        })
        .catch(err => {
            console.error("Error de red al enviar el mensaje:", err);
            alert('Error de conexión. No se pudo enviar el mensaje.');
        });
    }

    function sendImage(file) {
        const destinatarioId = destinatarioIdInput.value;
        if (!file || !destinatarioId) return;
        const formData = new FormData();
        formData.append('image', file);
        formData.append('destinatario_id', destinatarioId);
        fetch(`${URL_PROJECT}mensajes/enviarImagen`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    socket.emit('private-message', data.message_data);
                    addMessageToUI(data.message_data);
                }
            })
            .catch(err => console.error("Error al subir imagen:", err));
    }

    function setupUnlockModal() {
        cancelUnlockBtn.addEventListener('click', () => {
            unlockModal.classList.add('hidden');
            unlockModal.classList.remove('flex');
            userToUnlock = null;
        });
        confirmUnlockBtn.addEventListener('click', async () => {
            if (!userToUnlock) return;
            try {
                const response = await fetch(`${URL_PROJECT}mensajes/desbloquearChat`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ creator_id: userToUnlock.id })
                });
                const data = await response.json();
                if (data.success) {
                    userToUnlock.chat_desbloqueado = true;
                    openChat(userToUnlock);
                } else { alert(`Error: ${data.message}`); }
            } catch (error) {
                console.error("Error al desbloquear el chat:", error);
                alert('Hubo un problema de conexión.');
            } finally {
                unlockModal.classList.add('hidden');
                unlockModal.classList.remove('flex');
            }
        });
    }

    function initSocket() {
        socket = io(SOCKET_HOST, { query: { userId: USER_ID } });
        socket.on('connect', () => console.log('Socket conectado'));
        socket.on('user-status', ({ userId, online }) => {
            const statusIndicator = el(`#status-${userId}`);
            if (statusIndicator) {
                statusIndicator.classList.toggle('bg-green-400', online);
                statusIndicator.classList.toggle('bg-slate-500', !online);
            }
            if (currentChatUser && parseInt(currentChatUser.id) === parseInt(userId)) {
                chatStatus.innerHTML = `<div class="h-2 w-2 rounded-full ${online ? 'bg-green-400' : 'bg-slate-500'}"></div> ${online ? 'En línea' : 'Desconectado'}`;
                chatStatus.classList.toggle('text-green-400', online);
                chatStatus.classList.toggle('text-slate-400', !online);
            }
        });
        socket.on('private-message', (message) => {
            if (currentChatUser && parseInt(message.remitente_id) === parseInt(currentChatUser.id)) {
                addMessageToUI(message);
                 fetch(`${URL_PROJECT}mensajes/conversacion/${currentChatUser.id}`);
            } else {
                 fetchContacts(searchUser.value);
            }
        });
        socket.on('typing', ({ from }) => {
            if (currentChatUser && parseInt(from) === parseInt(currentChatUser.id)) {
                typingIndicator.classList.remove('hidden');
            }
        });
        socket.on('stop-typing', ({ from }) => {
            if (currentChatUser && parseInt(from) === parseInt(currentChatUser.id)) {
                typingIndicator.classList.add('hidden');
            }
        });
        socket.on('messages-read', ({ readerId }) => {
            if (currentChatUser && parseInt(readerId) === parseInt(currentChatUser.id)) {
                document.querySelectorAll('.seen-status .sent').forEach(icon => {
                    icon.classList.remove('fa-check', 'sent');
                    icon.classList.add('fa-check-double', 'seen');
                });
            }
        });
    }

    // --- Event Listeners ---
    searchUser.addEventListener('input', () => {
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => fetchContacts(searchUser.value), 300);
    });
    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = (messageInput.scrollHeight) + 'px';
    });
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        } else if (currentChatUser) {
            socket.emit('typing', { to: currentChatUser.id });
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => socket.emit('stop-typing', { to: currentChatUser.id }), 1500);
        }
    });
    imageUploadInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) sendImage(e.target.files[0]);
    });
    emojiBtn.addEventListener('click', () => {
        if (!emojiPicker) {
            emojiPicker = document.createElement('emoji-picker');
            emojiPicker.classList.add('absolute', 'bottom-20', 'right-5', 'z-10');
            chatArea.appendChild(emojiPicker);
            emojiPicker.addEventListener('emoji-click', e => messageInput.value += e.detail.unicode);
        }
        emojiPicker.classList.toggle('hidden');
    });

    // --- Inicialización ---
    fetchContacts();
    initSocket();
    setupUnlockModal();
});

function openImageModal(src) {
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.top = 0;
    modal.style.left = 0;
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.85)';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = 1000;
    modal.innerHTML = `<img src="${src}" style="max-height: 90%; max-width: 90%; border-radius: 8px;">`;
    modal.addEventListener('click', () => document.body.removeChild(modal));
    document.body.appendChild(modal);
}