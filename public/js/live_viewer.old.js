document.addEventListener('DOMContentLoaded', () => {
    console.log("📺 Viewer v2.0 Iniciado");

    const creatorId = document.getElementById('creator-id').value;
    const username = document.getElementById('username').value;
    const streamId = document.getElementById('stream-id').value;
    const userId = document.getElementById('user-id').value;
    
    // Fallback de URL
    const SOCKET_CONN = (typeof SOCKET_URL !== 'undefined') ? SOCKET_URL : window.location.origin;
    const API_BASE = (typeof URL_PROJECT !== 'undefined') ? URL_PROJECT : '/';

    // Socket Setup
    let socket;
    if (typeof io !== 'undefined') {
        socket = io(SOCKET_CONN, { path: '/socket.io/', transports: ['websocket', 'polling'] });
        socket.on('connect', () => {
            console.log("✅ Socket Conectado");
            socket.emit('join_stream_room', { streamId: creatorId }); // Unirse a la sala del creador
            initVideo(); // Iniciar video
        });
    }

    // --- CHAT ---
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatBox = document.getElementById('chat-messages');

    if (chatForm) {
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if (!msg) return;
            
            socket.emit('send_chat_message', {
                streamId: creatorId, userId, username, message: msg, isCreator: false
            });
            chatInput.value = '';
        });
    }

    if (socket) {
        socket.on('new_chat_message', (d) => {
            const div = document.createElement('div');
            div.className = `text-xs p-1 mb-1 ${d.isCreator ? 'text-pink-400 font-bold' : 'text-white'}`;
            div.innerHTML = `<span class="opacity-70">${d.username}:</span> ${d.message}`;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        socket.on('update_viewer_count', (d) => {
            document.getElementById('viewer-count').innerText = d.count;
        });
        
        socket.on('new_tip_alert', (d) => {
             const div = document.createElement('div');
             div.className = "text-xs p-2 bg-yellow-900/30 border border-yellow-500/50 rounded mb-1 text-yellow-200 text-center animate-pulse";
             div.innerHTML = `💎 <b>${d.username}</b> envió ${d.amount} zafiros!`;
             chatBox.appendChild(div);
             chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    // --- VIDEO (MEDIASOUP) ---
    async function initVideo() {
        try {
            const caps = await new Promise(r => socket.emit('getRouterRtpCapabilities', { streamId: creatorId }, r));
            const device = new mediasoupClient.Device();
            await device.load({ routerRtpCapabilities: caps });

            const transportInfo = await new Promise(r => socket.emit('createWebRtcTransport', { sender: false, streamId: creatorId }, r));
            const recvTransport = device.createRecvTransport(transportInfo.params);

            recvTransport.on('connect', ({ dtlsParameters }, cb, eb) => {
                socket.emit('connectWebRtcTransport', { dtlsParameters, transportId: recvTransport.id }, cb);
            });

            // Obtener productores
            socket.emit('get-producers', { streamId: creatorId }, async (ids) => {
                if(ids.length > 0) consume(ids[0], device, recvTransport);
                else console.log("⏳ Esperando video...");
            });
            
            socket.on('new-producer', ({ producerId }) => consume(producerId, device, recvTransport));

        } catch (e) { console.error("Error Video:", e); }
    }

    async function consume(producerId, device, transport) {
        try {
            const { rtpCapabilities } = device;
            const data = await new Promise(r => socket.emit('consume', { rtpCapabilities, producerId, transportId: transport.id, streamId: creatorId }, r));
            const consumer = await transport.consume(data.params);
            
            const stream = new MediaStream([consumer.track]);
            const video = document.getElementById('remote-video');
            video.srcObject = stream;
            document.getElementById('video-loading').classList.add('hidden');
            
            // Auto-play policy handling
            video.play().catch(() => document.getElementById('btn-play').classList.remove('hidden'));
            document.getElementById('btn-play')?.addEventListener('click', () => {
                video.play();
                document.getElementById('btn-play').classList.add('hidden');
            });
            
            socket.emit('resume', { consumerId: consumer.id });
        } catch(e) { console.error(e); }
    }

    // --- PROPINAS ---
    window.sendTip = async (id, amount, desc) => {
        if(!confirm(`¿Enviar ${amount} 💎?`)) return;
        try {
            const res = await fetch(`${API_BASE}live/processTip`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ amount, streamId: streamId, creatorId: creatorId })
            });
            const d = await res.json();
            if(d.success) {
                socket.emit('send_tip_alert', { streamId: creatorId, username: username, amount, message: desc });
                alert("¡Enviado!");
            } else alert(d.message);
        } catch(e) { console.error(e); }
    };
    
    // --- RULETA ---
    window.spinRoulette = async () => {
        if(!confirm(`¿Girar Ruleta?`)) return;
        try {
            const res = await fetch(`${API_BASE}live/processSpin`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ streamId: streamId })
            });
            const d = await res.json();
            if(d.success) {
                socket.emit('send_chat_message', { streamId: creatorId, username: 'SISTEMA', message: `${username} giró la ruleta!`, isCreator: false });
                alert("¡Girando!");
            } else alert(d.message);
        } catch(e) {}
    };
});