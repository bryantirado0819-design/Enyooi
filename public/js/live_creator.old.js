/**
 * public/js/live_creator.js
 * Lógica completa del "Comando Central": Streaming, Chat, Alertas, Lovense y CRUD Sockets.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ============================================================
    // 1. CONFIGURACIÓN Y SELECTORES
    // ============================================================
    
    // Datos inyectados en el HTML
    const body = document.querySelector('body');
    const creatorId = body.getAttribute('data-creator-id');
    const username = body.getAttribute('data-username');
    
    // URL del Servidor Node.js (¡CAMBIAR A TU IP PÚBLICA EN PRODUCCIÓN!)
    const NODE_SERVER_URL = 'http://localhost:3000'; 

    // Elementos de Video
    const videoElement = document.getElementById('local-video');
    const videoPlaceholder = document.getElementById('video-placeholder');
    const startBtn = document.getElementById('start-webrtc-btn');
    const stopBtn = document.getElementById('stop-webrtc-btn');
    const streamStatus = document.getElementById('stream-status');
    const btnToggleCam = document.getElementById('btn-toggle-cam');
    const btnToggleMic = document.getElementById('btn-toggle-mic');

    // Elementos de Chat y Feed
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const activityFeed = document.getElementById('activity-feed');
    const viewerCountBadge = document.getElementById('viewer-count-badge');

    // Elementos de Configuración (CRUD)
    const tipList = document.getElementById('tip-list');
    const rouletteList = document.getElementById('roulette-list');
    const addTipBtn = document.getElementById('add-tip-btn');
    const addRouletteBtn = document.getElementById('add-roulette-btn');
    
    // Lovense
    const btnGetQr = document.getElementById('get-lovense-qr-btn');
    const qrContainer = document.getElementById('lovense-qrcode');

    // Variables WebRTC
    let device;
    let sendTransport;
    let localStream;
    let videoProducer;
    let audioProducer;

    // ============================================================
    // 2. INICIALIZACIÓN DE SOCKETS
    // ============================================================
    
    const socket = io(NODE_SERVER_URL, {
        query: { userId: creatorId } 
    });

    socket.on('connect', () => {
        console.log("✅ Conectado al Servidor de Streaming");
        // Unirse a la sala propia
        socket.emit('join_stream_room', { streamId: creatorId, username: username });
    });

    // ============================================================
    // 3. CÁMARA Y MICRÓFONO (PREVIEW)
    // ============================================================

    async function initCamera() {
        try {
            // Pedir permisos de Audio y Video HD
            localStream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: { ideal: 1280 }, height: { ideal: 720 } }, 
                audio: true 
            });
            
            // Mostrar en el elemento video
            videoElement.srcObject = localStream;
            
            // Ocultar placeholder
            if(videoPlaceholder) videoPlaceholder.classList.add('hidden');
            
            console.log("🎥 Cámara iniciada correctamente");

        } catch (err) {
            console.error("Error de Cámara:", err);
            Swal.fire({
                title: 'Error de Cámara',
                text: 'No pudimos acceder a tu cámara/micrófono. Verifica los permisos del navegador y que estés usando HTTPS (o localhost).',
                icon: 'error',
                background: '#1e1e1e',
                color: '#fff'
            });
        }
    }
    
    // Iniciar cámara al cargar
    initCamera();

    // Toggle Cámara (Mute Video)
    if(btnToggleCam) {
        btnToggleCam.addEventListener('click', () => {
            const track = localStream.getVideoTracks()[0];
            if(track) {
                track.enabled = !track.enabled;
                btnToggleCam.classList.toggle('bg-red-600'); // Indicador visual
                btnToggleCam.innerHTML = track.enabled ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
            }
        });
    }

    // Toggle Micrófono (Mute Audio)
    if(btnToggleMic) {
        btnToggleMic.addEventListener('click', () => {
            const track = localStream.getAudioTracks()[0];
            if(track) {
                track.enabled = !track.enabled;
                btnToggleMic.classList.toggle('bg-red-600');
                btnToggleMic.innerHTML = track.enabled ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
            }
        });
    }

    // ============================================================
    // 4. STREAMING WEBRTC (MEDIASOUP)
    // ============================================================

    startBtn.addEventListener('click', async () => {
        if(!localStream) return Swal.fire('Error', 'No se detecta cámara', 'error');
        
        // UI Loading
        startBtn.disabled = true;
        const originalText = startBtn.innerHTML;
        startBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Iniciando...';

        try {
            // A. Obtener Capacidades del Router
            const routerRtpCapabilities = await new Promise(res => socket.emit('getRouterRtpCapabilities', res));
            
            // B. Cargar Dispositivo Mediasoup
            device = new mediasoupClient.Device();
            await device.load({ routerRtpCapabilities });

            // C. Crear Transporte de Envío en Servidor
            const transportParams = await new Promise((resolve, reject) => {
                socket.emit('createWebRtcTransport', { sender: true }, (data) => {
                    if(data.error) reject(data.error);
                    else resolve(data);
                });
            });

            // D. Crear Transporte Local
            sendTransport = device.createSendTransport(transportParams.params);

            // Eventos del transporte
            sendTransport.on('connect', async ({ dtlsParameters }, callback, errback) => {
                try {
                    await new Promise(res => socket.emit('connectWebRtcTransport', {
                        dtlsParameters,
                        transportId: sendTransport.id
                    }, res));
                    callback();
                } catch (error) { errback(error); }
            });

            sendTransport.on('produce', async ({ kind, rtpParameters }, callback, errback) => {
                try {
                    const { id } = await new Promise(res => socket.emit('produce', {
                        kind,
                        rtpParameters,
                        transportId: sendTransport.id,
                        streamId: creatorId // Importante: ID de la sala
                    }, res));
                    callback({ id });
                } catch (error) { errback(error); }
            });

            // E. Producir Video y Audio
            const videoTrack = localStream.getVideoTracks()[0];
            const audioTrack = localStream.getAudioTracks()[0];

            if(videoTrack) videoProducer = await sendTransport.produce({ track: videoTrack });
            if(audioTrack) audioProducer = await sendTransport.produce({ track: audioTrack });

            // F. Actualizar UI
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
            
            // Actualizar Badge de Estado
            streamStatus.classList.remove('offline', 'text-gray-500');
            streamStatus.classList.add('text-red-500', 'animate-pulse');
            streamStatus.innerHTML = `<span class="w-2 h-2 rounded-full bg-red-500"></span> EN VIVO`;

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '¡Estás transmitiendo en vivo!',
                showConfirmButton: false,
                timer: 3000,
                background: '#10b981',
                color: '#fff'
            });

        } catch (err) {
            console.error("Error Streaming:", err);
            startBtn.innerHTML = originalText;
            startBtn.disabled = false;
            Swal.fire('Error', 'No se pudo conectar con el servidor de streaming.', 'error');
        }
    });

    stopBtn.addEventListener('click', () => {
        window.location.reload();
    });

    // ============================================================
    // 5. CHAT Y FEED DE ACTIVIDAD
    // ============================================================

    // Recibir Mensaje
    socket.on('new_chat_message', (data) => {
        addMessageToChat(data);
    });

    // Enviar Mensaje
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if(!text) return;

        socket.emit('send_chat_message', {
            streamId: creatorId,
            userId: creatorId,
            username: username,
            message: text,
            isCreator: true
        });
        
        chatInput.value = '';
    });

    function addMessageToChat(data) {
        const isMe = data.isCreator;
        const div = document.createElement('div');
        // Estilos diferenciados para el creador
        div.className = `text-xs p-2 rounded mb-1 break-words ${isMe ? 'bg-fuchsia-900/30 border-l-2 border-fuchsia-500' : 'bg-white/5'}`;
        div.innerHTML = `
            <span class="font-bold ${isMe ? 'text-fuchsia-400' : 'text-gray-400'}">${data.username}:</span> 
            <span class="text-gray-300">${data.message}</span>
        `;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll
    }

    // ============================================================
    // 6. ALERTAS DE DONACIÓN (TOASTS + FEED + LOVENSE)
    // ============================================================

    socket.on('new_tip_alert', (data) => {
        // A. Mostrar Toast Flotante
        const toast = document.createElement('div');
        toast.className = "alert-toast glass-card p-3 bg-black/80 border-l-4 border-pink-500 rounded text-white shadow-xl mb-2 pointer-events-auto flex items-center gap-3";
        toast.innerHTML = `
            <div class="bg-pink-600 rounded-full w-8 h-8 flex items-center justify-center"><i class="fas fa-gem"></i></div>
            <div>
                <div class="font-bold text-sm">${data.username}</div>
                <div class="text-yellow-400 text-xs font-bold">+${data.amount} Zafiros</div>
                ${data.message ? `<div class="text-gray-400 text-[10px] italic">"${data.message}"</div>` : ''}
            </div>
        `;
        const container = document.getElementById('toast-container');
        if(container) container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 5000);

        // B. Agregar al Feed de Actividad
        const feedItem = document.createElement('div');
        feedItem.className = "text-xs p-2 bg-white/5 rounded border-l-2 border-yellow-400 text-gray-300 animate-pulse";
        feedItem.innerHTML = `<strong>${data.username}</strong> envió <span class="text-yellow-400 font-bold">${data.amount}</span>`;
        if(activityFeed) activityFeed.prepend(feedItem);

        // C. Actualizar Contador de Ganancias de la Sesión
        const sessionEl = document.getElementById('session-earnings');
        if(sessionEl) {
            let currentText = sessionEl.innerText.replace(/\D/g, ''); 
            let current = parseInt(currentText) || 0;
            let total = current + parseInt(data.amount);
            sessionEl.innerHTML = `<i class="fas fa-gem"></i> ${total}`;
        }

        // D. Integración LOVENSE (Simulación)
        console.log(`[Lovense] Triggering vibration for ${data.amount} tokens`);
    });

    // Actualizar contador de espectadores
    socket.on('update_viewer_count', (data) => {
        if(viewerCountBadge) {
            viewerCountBadge.innerText = data.count;
            viewerCountBadge.classList.remove('hidden');
        }
    });

    // ============================================================
    // 7. CRUD REAL-TIME (PROPINAS Y RULETA)
    // ============================================================

    // --- PROPINAS ---
    
    if(addTipBtn) {
        addTipBtn.addEventListener('click', async () => {
            const { value: formValues } = await Swal.fire({
                title: 'Nueva Opción de Propina',
                html: `
                    <input id="swal-zaf" class="swal2-input" placeholder="Cantidad de Zafiros" type="number">
                    <input id="swal-desc" class="swal2-input" placeholder="Acción (ej: Saludar)">
                `,
                focusConfirm: false,
                background: '#1e1e1e', color: '#fff',
                preConfirm: () => [
                    document.getElementById('swal-zaf').value,
                    document.getElementById('swal-desc').value
                ]
            });

            if (formValues && formValues[0] && formValues[1]) {
                socket.emit('settings:addTip', { 
                    creatorId, 
                    zafiros: formValues[0], 
                    descripcion: formValues[1] 
                });
            }
        });
    }

    socket.on('settings:tipAdded', (item) => {
        if(!tipList) return;
        const div = document.createElement('div');
        div.className = "flex justify-between items-center p-2 bg-black/30 rounded text-xs group mb-1";
        div.id = `tip-${item.id}`;
        div.innerHTML = `
            <span class="text-gray-300"><span class="text-yellow-400 font-bold">💎${item.zafiros}</span>: ${item.descripcion}</span>
            <button onclick="window.deleteTip(${item.id})" class="text-red-500 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-times"></i></button>
        `;
        tipList.appendChild(div);
    });

    window.deleteTip = (id) => {
        socket.emit('settings:deleteTip', { creatorId, id });
        const el = document.getElementById(`tip-${id}`);
        if(el) el.remove();
    };

    // --- RULETA ---

    if(addRouletteBtn) {
        addRouletteBtn.addEventListener('click', () => {
            const input = document.getElementById('roulette-option-input');
            const text = input.value.trim();
            if(text) {
                socket.emit('settings:addRoulette', { creatorId, texto: text });
                input.value = '';
            }
        });
    }

    socket.on('settings:rouletteAdded', (item) => {
        if(!rouletteList) return;
        const div = document.createElement('div');
        div.className = "flex justify-between items-center p-2 bg-black/30 rounded text-xs group mb-1";
        div.id = `roulette-${item.id}`;
        div.innerHTML = `
            <span class="text-gray-300">${item.texto}</span>
            <button onclick="window.deleteRoulette(${item.id})" class="text-red-500 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-times"></i></button>
        `;
        rouletteList.appendChild(div);
    });

    window.deleteRoulette = (id) => {
        socket.emit('settings:deleteRoulette', { creatorId, id });
        const el = document.getElementById(`roulette-${id}`);
        if(el) el.remove();
    };
    
    // --- ACTUALIZAR META (GOAL) ---
    
    window.updateGoal = () => {
        const targetInput = document.getElementById('tip-goal-target');
        const currentEl = document.getElementById('current-goal-val');
        const currentVal = currentEl ? currentEl.innerText : 0;
        const goalAmount = targetInput.value;
        
        if(goalAmount > 0) {
            socket.emit('settings:updateGoal', {
                creatorId,
                goal_amount: goalAmount,
                current: currentVal,
                description: "Meta del Stream"
            });
            
            Swal.fire({
                toast: true, position: 'top', icon: 'success', 
                title: 'Meta actualizada', showConfirmButton: false, timer: 1500,
                background: '#10b981', color: '#fff'
            });
        }
    };


    // ============================================================
    // 8. INTEGRACIÓN LOVENSE (QR REAL)
    // ============================================================

    if(btnGetQr) {
        btnGetQr.addEventListener('click', async () => {
            btnGetQr.disabled = true;
            const originalText = btnGetQr.innerHTML;
            btnGetQr.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            
            try {
                const response = await fetch(`${RUTA_URL}/live/getLovenseAuthToken`, { method: 'POST' });
                const data = await response.json();

                if(data.success && data.token) {
                    const qrContent = `lovense_connect://${data.token}`; 
                    const qrUrl = `https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=${encodeURIComponent(qrContent)}`;
                    
                    qrContainer.innerHTML = `<img src="${qrUrl}" alt="QR Lovense" class="rounded shadow-lg">`;
                    qrContainer.classList.remove('hidden');
                    btnGetQr.innerHTML = 'QR Generado';
                    btnGetQr.classList.replace('bg-pink-600', 'bg-green-600');
                } else {
                    throw new Error(data.message || 'No se recibió token');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error Lovense', 'No se pudo conectar con Lovense API.', 'error');
                btnGetQr.disabled = false;
                btnGetQr.innerHTML = originalText;
            }
        });
    }

    // Botones de Test de Vibración
    document.querySelectorAll('.btn-test-lovense').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const time = btn.dataset.time;
            
            // Feedback visual
            btn.classList.add('bg-pink-500', 'text-white');
            setTimeout(() => btn.classList.remove('bg-pink-500', 'text-white'), 500);

            console.log(`[Lovense Test] Enviando comando: ${action} durante ${time}s`);
        });
    });


    // ============================================================
    // 9. INTERFAZ UI (TABS & MOBILE)
    // ============================================================

    // Manejo de Pestañas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active', 'text-white'));
            tabContents.forEach(c => c.classList.add('hidden'));
            
            btn.classList.add('active', 'text-white');
            const target = document.getElementById(btn.dataset.tab);
            if(target) target.classList.remove('hidden');
        });
    });

    // Guardar Ajustes (Botón Genérico)
    const saveBtn = document.getElementById('save-settings-btn');
    if(saveBtn) {
        saveBtn.addEventListener('click', () => {
            const title = document.getElementById('stream-title').value;
            const desc = document.getElementById('stream-desc').value;
            
            fetch(`${RUTA_URL}/live/saveSettings`, {
                method: 'POST',
                body: JSON.stringify({ title, description: desc })
            }).then(res => res.json()).then(d => {
                if(d.success) Swal.fire({ toast: true, icon: 'success', title: 'Guardado', position: 'top-end', showConfirmButton: false, timer: 1500 });
            });
        });
    }

});