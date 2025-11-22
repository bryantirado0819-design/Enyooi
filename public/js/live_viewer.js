
document.addEventListener('DOMContentLoaded', () => {
    
    // ============================================================
    // 1. VARIABLES Y SELECTORES
    // ============================================================
    const streamId = document.getElementById('stream-id').value;   // ID del Stream (generalmente ID del creador)
    const creatorId = document.getElementById('creator-id').value; // ID numérico del creador
    const userId = document.getElementById('user-id').value;
    const username = document.getElementById('username').value;
    
    // UI Elements
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const btnLike = document.getElementById('btn-like');
    const btnSendTip = document.getElementById('btn-send-tip');
    const btnSpinRoulette = document.getElementById('btn-spin-roulette');
    const viewerCountEl = document.getElementById('viewer-count');
    const userBalanceEl = document.getElementById('user-balance');
    const videoElement = document.getElementById('remote-video');
    const videoPlaceholder = document.getElementById('video-placeholder');

    // ============================================================
    // 2. CONEXIÓN SOCKET.IO
    // ============================================================
    // NODE_SERVER_URL viene definido en el PHP (ej: http://localhost:3000)
    const socket = io(NODE_SERVER_URL);
    
    // Variables WebRTC
    let device;
    let recvTransport;
    let consumer;

    socket.on('connect', () => {
        console.log("✅ Conectado al servidor de sockets");
        
        // Unirse a la sala del stream
        socket.emit('join_stream_room', { 
            streamId: creatorId, // Usamos creatorId como identificador de la sala
            username: username 
        });
        
        // Iniciar proceso de recepción de video
        startWebRTC();
    });

    // ============================================================
    // 3. INTERFAZ DE USUARIO (TABS)
    // ============================================================
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remover estilos activos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Activar pestaña actual
            button.classList.add('active');
            const targetId = button.dataset.tab;
            document.getElementById(targetId).classList.add('active');
            
            // Auto-scroll chat si volvemos a él
            if (targetId === 'chat-panel') {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    });

    // ============================================================
    // 4. LÓGICA DEL CHAT
    // ============================================================
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        const msgData = {
            streamId: creatorId,
            userId: userId,
            username: username,
            message: text,
            isCreator: false
        };

        socket.emit('send_chat_message', msgData);
        chatInput.value = '';
    });

    socket.on('new_chat_message', (data) => {
        const isMe = data.userId == userId;
        const isCreator = data.isCreator;
        
        const div = document.createElement('div');
        div.className = `chat-message ${isCreator ? 'creator' : ''} ${isMe ? 'own-message' : ''} p-2 rounded-lg bg-white/5 text-sm`;
        
        const nameColor = isCreator ? 'text-[#ff4fa3]' : (isMe ? 'text-[#7c5cff]' : 'text-gray-400');
        
        div.innerHTML = `
            <span class="font-bold ${nameColor}">${data.username}:</span>
            <span class="text-gray-200">${data.message}</span>
        `;
        
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });

    // Alerta de Donación en Chat
    socket.on('new_tip_alert', (data) => {
        const div = document.createElement('div');
        div.className = `chat-message donation my-2 animate-bounce`;
        div.innerHTML = `💎 <strong>${data.username}</strong> envió ${data.amount} zafiros!`;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Actualizar meta visualmente (si aplica)
        updateGoalUI(data.amount);
    });

    // ============================================================
    // 5. INTERACCIONES (LIKES Y CONTADORES)
    // ============================================================
    if (btnLike) {
        btnLike.addEventListener('click', () => {
            const icon = btnLike.querySelector('i');
            icon.classList.add('text-red-500', 'scale-125');
            setTimeout(() => icon.classList.remove('text-red-500', 'scale-125'), 200);
            
            // Aquí podrías emitir un evento socket si quieres contar likes globales
            // socket.emit('send_like', { streamId: creatorId }); 
        });
    }

    socket.on('update_viewer_count', (data) => {
        if(viewerCountEl) viewerCountEl.innerText = data.count;
    });

    // ============================================================
    // 6. TRANSACCIONES (PROPINAS Y RULETA)
    // ============================================================
    
    // Función global para seleccionar propina desde la lista
    window.selectTip = (amount, desc) => {
        const inputAmount = document.getElementById('custom-tip-amount');
        const inputMsg = document.getElementById('custom-tip-msg');
        if(inputAmount) {
            inputAmount.value = amount;
            inputAmount.focus();
        }
        if(inputMsg) {
            inputMsg.value = desc ? `¡${desc}!` : '';
        }
        
        // Cambiar a pestaña de formulario (simulado visualmente si es necesario)
        // En este diseño el formulario está debajo de los botones, así que ya es visible
    };

    const processTransaction = async (endpoint, payload) => {
        try {
            const res = await fetch(`${RUTA_URL}live/${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                userBalanceEl.innerText = parseInt(data.newBalance).toLocaleString();
                // Swal no está cargado por defecto en viewer, usamos alert nativo o podrías incluir Swal
                alert(data.message);
                return true;
            } else {
                alert(data.message || 'Error en la transacción');
                return false;
            }
        } catch (e) {
            console.error(e);
            alert('Error de conexión');
            return false;
        }
    };

    // Enviar Propina
    if (btnSendTip) {
        btnSendTip.addEventListener('click', async () => {
            const amountInput = document.getElementById('custom-tip-amount');
            const msgInput = document.getElementById('custom-tip-msg');
            const amount = amountInput.value;
            const msg = msgInput.value;
            
            if (!amount || amount <= 0) return alert('Ingresa una cantidad válida');
            
            // Deshabilitar botón temporalmente
            btnSendTip.disabled = true;
            
            const success = await processTransaction('processTip', {
                amount: amount,
                streamId: streamId, // ID de stream (BD)
                creatorId: creatorId // ID de usuario creador
            });

            btnSendTip.disabled = false;

            if (success) {
                // Emitir alerta visual a todos (Server lo retransmite)
                socket.emit('send_tip_alert', { 
                    streamId: creatorId, 
                    username: username, 
                    amount: amount,
                    message: msg 
                });
                amountInput.value = '';
                msgInput.value = '';
            }
        });
    }
    
    // Girar Ruleta
    if (btnSpinRoulette) {
        btnSpinRoulette.addEventListener('click', async () => {
            btnSpinRoulette.disabled = true;
            const success = await processTransaction('processSpin', {
                streamId: streamId
            });
            
            if (!success) btnSpinRoulette.disabled = false;
            else {
                // Animación o lógica post-giro
                setTimeout(() => btnSpinRoulette.disabled = false, 2000);
            }
        });
    }

    // ============================================================
    // 7. ACTUALIZACIONES EN TIEMPO REAL (CRUD SOCKETS)
    // ============================================================
    
    // A. Escuchar cambios en Propinas
    socket.on('settings:updateTips', (data) => {
        // Buscamos el grid dentro del panel de tips
        const container = document.querySelector('#tips-panel .grid'); 

        if (data.action === 'add') {
            const btn = document.createElement('button');
            btn.className = "btn-tip w-full text-left p-4 bg-gradient-to-r from-white/5 to-white/0 hover:from-white/10 hover:to-white/5 border border-white/5 rounded-xl transition-all group animate-fade-in";
            btn.id = `viewer-tip-${data.item.id}`;
            btn.onclick = () => selectTip(data.item.zafiros, data.item.descripcion);
            btn.innerHTML = `
               <div class="flex justify-between items-center">
                   <span class="font-bold text-pink-400 text-lg">💎 ${data.item.zafiros}</span>
                   <i class="fas fa-chevron-right text-gray-600 group-hover:text-white transition"></i>
               </div>
               <div class="text-sm text-gray-300 mt-1">${data.item.descripcion}</div>
            `;
            
            // Si había mensaje de "No hay opciones", quitarlo
            const emptyMsg = container.querySelector('p.text-center');
            if(emptyMsg && emptyMsg.innerText.includes('No hay')) emptyMsg.remove();

            container.appendChild(btn);
            
        } else if (data.action === 'delete') {
            const el = document.getElementById(`viewer-tip-${data.id}`);
            if (el) {
                el.style.opacity = '0'; // Efecto visual de desaparición
                setTimeout(() => el.remove(), 300);
            }
        }
    });

    // B. Escuchar cambios en Ruleta
    socket.on('settings:updateRoulette', (data) => {
        const list = document.querySelector('#roulette-panel ul');
        
        if (data.action === 'add') {
            const li = document.createElement('li');
            li.id = `viewer-roulette-${data.item.id}`;
            li.innerText = data.item.option_text;
            li.className = "animate-fade-in"; // Clase CSS opcional para animación
            list.appendChild(li);
        } else if (data.action === 'delete') {
            const el = document.getElementById(`viewer-roulette-${data.id}`);
            if(el) el.remove();
        }
    });

    // C. Escuchar cambios de Meta
    socket.on('settings:goalUpdated', (data) => {
        const goalTextEl = document.getElementById('goal-text');
        if(goalTextEl) {
            goalTextEl.innerText = `${data.current || 0} / ${data.goal_amount} - ${data.description}`;
        }
        // Actualización de barra de progreso si existe
        updateGoalUI(0, data.goal_amount, data.current);
    });

    function updateGoalUI(amountAdded, totalGoal, currentTotal) {
        const bar = document.getElementById('goal-bar');
        if (!bar) return;
        
        // Esta lógica es visual simple. Para precisión, el servidor debería mandar el % exacto.
        // Aquí hacemos una aproximación si no tenemos el total exacto
        // ... lógica de actualización visual
    }

    // ============================================================
    // 8. WEBRTC (CONSUMIR VIDEO) - MEDIASOUP
    // ============================================================
    async function startWebRTC() {
        try {
            // 1. Get Router Capabilities
            const routerRtpCapabilities = await new Promise(res => socket.emit('getRouterRtpCapabilities', res));
            device = new mediasoupClient.Device();
            await device.load({ routerRtpCapabilities });

            // 2. Create Receive Transport
            const transportParams = await new Promise(res => 
                socket.emit('createWebRtcTransport', { sender: false }, res)
            );
            
            if (transportParams.error) throw new Error(transportParams.error);

            recvTransport = device.createRecvTransport(transportParams.params);

            recvTransport.on('connect', ({ dtlsParameters }, callback, errback) => {
                socket.emit('connectWebRtcTransport', { 
                    dtlsParameters, 
                    transportId: recvTransport.id 
                }, callback);
            });

            // 3. Consume
            // Primero obtenemos los productores disponibles en la sala
            const producerIds = await new Promise(res => socket.emit('get-producers', { streamId: creatorId }, res));
            
            if (producerIds.length > 0) {
                // Consumimos el primer productor (asumimos que es el video/audio principal)
                await consumeProducer(producerIds[0]);
            } else {
                console.log("⌛ Esperando que el creador inicie transmisión...");
            }

            // Escuchar nuevos productores que entren (ej: si el creador inicia después)
            socket.on('new-producer', async ({ producerId }) => {
                console.log("📹 Nuevo productor detectado:", producerId);
                await consumeProducer(producerId);
            });

        } catch (err) {
            console.error("❌ Error WebRTC:", err);
        }
    }

    async function consumeProducer(producerId) {
        try {
            const { rtpCapabilities } = device;
            const data = await new Promise(res => socket.emit('consume', { 
                rtpCapabilities, 
                producerId,
                transportId: recvTransport.id
            }, res));

            if (data.error) {
                console.error("No se puede consumir:", data.error);
                return;
            }

            consumer = await recvTransport.consume({
                id: data.params.id,
                producerId: data.params.producerId,
                kind: data.params.kind,
                rtpParameters: data.params.rtpParameters
            });

            const { track } = consumer;
            
            // Manejo de Pistas de Video/Audio
            if (track.kind === 'video') {
                videoElement.srcObject = new MediaStream([track]);
                videoPlaceholder.classList.add('hidden'); // Ocultar placeholder
                socket.emit('resume', { consumerId: consumer.id }); // Resume en server (opcional según impl)
            } else if (track.kind === 'audio') {
                // Si el audio viene en track separado, lo añadimos al stream existente
                 const stream = videoElement.srcObject || new MediaStream();
                 stream.addTrack(track);
                 videoElement.srcObject = stream;
            }

            // Reiniciar si se pierde la conexión
            consumer.on('transportclose', () => {
                videoElement.srcObject = null;
                videoPlaceholder.classList.remove('hidden');
            });

        } catch (err) {
            console.error("Error consumiendo stream:", err);
        }
    }
});