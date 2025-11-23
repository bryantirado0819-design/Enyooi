// socket-server/server.js
// ================================================================
// 🚀 SERVIDOR MAESTRO ENYOOI: STREAMING + SOCKETS + MYSQL
// ================================================================

const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const mediasoup = require('mediasoup');
const cors = require('cors');
const mysql = require('mysql2/promise');
const fetch = require('node-fetch'); // Opcional si usas fetch interno

// ================================================================
// ⚙️ CONFIGURACIÓN DEL SISTEMA (¡EDITAR ESTO!)
// ================================================================

// 1. IP PÚBLICA (CRÍTICO PARA DEMO REMOTO)
// Si estás en local, usa "0.0.0.0".
// Si estás en un VPS/Servidor, pon la IP real (ej: "164.92.123.45")
const PUBLIC_IP = "127.0.0.1"; 

// 2. DATOS DE BASE DE DATOS (MySQL)
const dbConfig = {
    host: 'localhost',
    user: 'enyooi_user',
    password: 'Enyooi2025!', 
    database: 'enyooi'
};

// 3. PUERTO DEL SERVIDOR
const PORT = 3000;

// ================================================================
// 🔧 INICIALIZACIÓN DE SERVICIOS
// ================================================================

const app = express();
app.use(cors());
app.use(express.json()); // Para recibir JSON desde PHP

const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: "*", // Permitir acceso desde cualquier lugar para el demo
        methods: ["GET", "POST"]
    }
});

// Estado global en memoria
const onlineUsers = new Map(); // Para chat privado y notificaciones { userId: socketId }
const rooms = {}; // Estado de las salas de stream { streamId: { viewers: Set, producers: [], ... } }
const peers = {}; // Estado de transportes WebRTC { transportId: { transport, ... } }
let worker, router; // Referencias globales de Mediasoup

// ================================================================
// 🎥 CONFIGURACIÓN DE MEDIASOUP (WebRTC)
// ================================================================

const mediasoupConfig = {
    worker: {
        rtcMinPort: 40000,
        rtcMaxPort: 49999,
        logLevel: 'warn',
        logTags: ['info', 'ice', 'dtls', 'rtp', 'srtp', 'rtcp'],
    },
    router: {
        mediaCodecs: [
            { kind: 'video', mimeType: 'video/VP8', clockRate: 90000, parameters: { 'x-google-start-bitrate': 1000 } },
            { kind: 'audio', mimeType: 'audio/opus', clockRate: 48000, channels: 2 }
        ]
    },
    webRtcTransport: {
        listenIps: [{ 
            ip: '0.0.0.0', 
            announcedIp: PUBLIC_IP === "0.0.0.0" ? null : PUBLIC_IP 
        }],
        enableUdp: true,
        enableTcp: true,
        preferUdp: true
    }
};

// ================================================================
// 🔌 ARRANQUE DEL SISTEMA
// ================================================================

async function run() {
    try {
        // 1. Iniciar Mediasoup
        worker = await mediasoup.createWorker(mediasoupConfig.worker);
        worker.on('died', () => {
            console.error('❌ Mediasoup worker murió. Saliendo...');
            setTimeout(() => process.exit(1), 2000);
        });
        
        router = await worker.createRouter({ mediaCodecs: mediasoupConfig.router.mediaCodecs });
        console.log('🎬 Mediasoup Worker & Router iniciados');

        // 2. Conectar Base de Datos
        global.db = await mysql.createPool(dbConfig);
        console.log('💾 Conectado a MySQL exitosamente');

    } catch (e) {
        console.error('❌ Error crítico al iniciar:', e);
    }
}
run();

// ================================================================
// 📡 ENDPOINTS HTTP (COMPATIBILIDAD CON PHP)
// ================================================================
// Estos endpoints son llamados por tu código PHP antiguo para notificaciones

app.post('/notify/like', (req, res) => {
    const { postId, newLikeCount } = req.body;
    io.emit('likeUpdate', { postId, newLikeCount });
    res.json({ success: true });
});

app.post('/notify/comment', (req, res) => {
    const { postId, newComment, newCommentCount } = req.body;
    io.emit('newComment', { postId, newComment });
    io.emit('commentCountUpdate', { postId, newCommentCount });
    res.json({ success: true });
});

app.post('/emit-read', (req, res) => {
    const { readerId, writerId } = req.body;
    const writerSocketId = onlineUsers.get(writerId.toString());
    if (writerSocketId) {
        io.to(writerSocketId).emit('messages-read', { readerId });
    }
    res.json({ success: true });
});

// ================================================================
// ⚡ LÓGICA DE SOCKET.IO (EL CEREBRO)
// ================================================================

// Función auxiliar para broadcast de contadores globales
function broadcastAllViewerCounts() {
    const counts = {};
    for (const streamId in rooms) {
        if (rooms.hasOwnProperty(streamId) && rooms[streamId].viewers) {
            counts[streamId] = rooms[streamId].viewers.size;
        }
    }
    io.emit('all_viewer_counts', counts);
}

// Función auxiliar para crear transporte WebRTC
async function createWebRtcTransport(callback) {
    try {
        const transport = await router.createWebRtcTransport(mediasoupConfig.webRtcTransport);
        peers[transport.id] = { transport };
        
        callback({
            params: {
                id: transport.id,
                iceParameters: transport.iceParameters,
                iceCandidates: transport.iceCandidates,
                dtlsParameters: transport.dtlsParameters
            }
        });
        return transport;
    } catch (err) {
        console.error(err);
        callback({ error: err.message });
    }
}

io.on('connection', (socket) => {
    // Registro de usuario (para chat privado y presencia)
    const userId = socket.handshake.query.userId;
    if (userId) {
        onlineUsers.set(userId.toString(), socket.id);
        io.emit('user-status', { userId, online: true });
        // console.log(`👤 Usuario conectado: ${userId}`);
    }

    // ------------------------------------------------------------
    // 💬 1. CHAT GLOBAL Y PRIVADO
    // ------------------------------------------------------------

    // Mensajes en el Stream (Público)
    socket.on('send_chat_message', (data) => {
        // Reenviar a todos en la sala del stream
        // data.streamId debe ser el ID del creador/sala
        io.to(data.streamId).emit('new_chat_message', data);
    });

    // Mensajes Privados (Sistema antiguo)
    socket.on('private-message', (message) => {
        const recipientSocketId = onlineUsers.get(message.destinatario_id.toString());
        if (recipientSocketId) {
            io.to(recipientSocketId).emit('private-message', message);
        }
    });

    socket.on('typing', ({ to }) => {
        const recipientSocketId = onlineUsers.get(to.toString());
        if (recipientSocketId) io.to(recipientSocketId).emit('typing', { from: userId });
    });

    socket.on('stop-typing', ({ to }) => {
        const recipientSocketId = onlineUsers.get(to.toString());
        if (recipientSocketId) io.to(recipientSocketId).emit('stop-typing', { from: userId });
    });

    // ------------------------------------------------------------
    // 📺 2. STREAMING (SALAS Y MEDIASOUP)
    // ------------------------------------------------------------

    socket.on('join_stream_room', ({ streamId, username }) => {
        socket.join(streamId);
        
        // Inicializar sala si no existe
        if (!rooms[streamId]) {
            rooms[streamId] = { viewers: new Set(), producers: [] };
        }
        rooms[streamId].viewers.add(socket.id);

        // Notificar a la sala el nuevo conteo
        io.to(streamId).emit('update_viewer_count', { count: rooms[streamId].viewers.size });
        
        // Actualizar lista global de streams (para /lives)
        broadcastAllViewerCounts();
    });

    // Eventos WebRTC (Standard Mediasoup)
    socket.on('getRouterRtpCapabilities', (callback) => {
        callback(router.rtpCapabilities);
    });

    socket.on('createWebRtcTransport', async ({ sender }, callback) => {
        const transport = await createWebRtcTransport(callback);
        socket.transportId = transport.id;
    });

    socket.on('connectWebRtcTransport', async ({ dtlsParameters, transportId }, callback) => {
        const peer = peers[transportId || socket.transportId];
        if (peer && peer.transport) {
            await peer.transport.connect({ dtlsParameters });
            callback();
        }
    });

    socket.on('produce', async ({ kind, rtpParameters, streamId }, callback) => {
        const peer = peers[socket.transportId];
        if (peer && peer.transport) {
            const producer = await peer.transport.produce({ kind, rtpParameters });
            
            // Guardar productor en la sala
            if (!rooms[streamId]) rooms[streamId] = { viewers: new Set(), producers: [] };
            rooms[streamId].producers.push(producer);

            // Anunciar a los espectadores que hay nuevo video/audio
            socket.to(streamId).emit('new-producer', { producerId: producer.id });

            callback({ id: producer.id });
        }
    });

    socket.on('consume', async ({ rtpCapabilities, producerId }, callback) => {
        if (!router.canConsume({ producerId, rtpCapabilities })) {
            return callback({ error: 'No se puede consumir' });
        }

        const peer = peers[socket.transportId];
        if (peer && peer.transport) {
            const consumer = await peer.transport.consume({
                producerId,
                rtpCapabilities,
                paused: true
            });

            callback({
                params: {
                    id: consumer.id,
                    producerId: producerId,
                    kind: consumer.kind,
                    rtpParameters: consumer.rtpParameters
                }
            });

            await consumer.resume();
        }
    });

    socket.on('get-producers', ({ streamId }, callback) => {
        const room = rooms[streamId];
        if (room && room.producers) {
            callback(room.producers.map(p => p.id));
        } else {
            callback([]);
        }
    });

    // ------------------------------------------------------------
    // 💎 3. DONACIONES Y ALERTAS
    // ------------------------------------------------------------

    socket.on('send_tip_alert', (data) => {
        // data: { streamId, username, amount, message }
        io.to(data.streamId).emit('new_tip_alert', data);
    });

    // ------------------------------------------------------------
    // 🛠️ 4. CRUD EN TIEMPO REAL (MYSQL)
    // ------------------------------------------------------------

    // Agregar Propina
    socket.on('settings:addTip', async (data) => {
        try {
            const [result] = await global.db.execute(
                'INSERT INTO stream_tip_options (creator_id, zafiros, descripcion) VALUES (?, ?, ?)',
                [data.creatorId, data.zafiros, data.descripcion]
            );
            // Confirmar al creador
            socket.emit('settings:tipAdded', { id: result.insertId, ...data });
            // Avisar a todos los espectadores
            io.to(data.creatorId.toString()).emit('settings:updateTips', { 
                action: 'add', 
                item: { id: result.insertId, ...data } 
            });
        } catch (err) { console.error('DB Error:', err); }
    });

    // Borrar Propina
    socket.on('settings:deleteTip', async (data) => {
        try {
            await global.db.execute('DELETE FROM stream_tip_options WHERE id = ?', [data.id]);
            io.to(data.creatorId.toString()).emit('settings:updateTips', { action: 'delete', id: data.id });
        } catch (err) { console.error('DB Error:', err); }
    });

    // Agregar Ruleta
    socket.on('settings:addRoulette', async (data) => {
        try {
            const [result] = await global.db.execute(
                'INSERT INTO stream_roulette_options (creator_id, option_text) VALUES (?, ?)',
                [data.creatorId, data.texto]
            );
            socket.emit('settings:rouletteAdded', { id: result.insertId, texto: data.texto });
            io.to(data.creatorId.toString()).emit('settings:updateRoulette', { 
                action: 'add', 
                item: { id: result.insertId, option_text: data.texto } 
            });
        } catch (err) { console.error('DB Error:', err); }
    });

    // Borrar Ruleta
    socket.on('settings:deleteRoulette', async (data) => {
        try {
            await global.db.execute('DELETE FROM stream_roulette_options WHERE id = ?', [data.id]);
            io.to(data.creatorId.toString()).emit('settings:updateRoulette', { action: 'delete', id: data.id });
        } catch (err) { console.error('DB Error:', err); }
    });

    // Actualizar Meta
    socket.on('settings:updateGoal', (data) => {
        // Simplemente retransmitimos a la sala para actualización visual inmediata
        io.to(data.creatorId.toString()).emit('settings:goalUpdated', data);
    });

    // ------------------------------------------------------------
    // 🚪 5. DESCONEXIÓN Y LIMPIEZA
    // ------------------------------------------------------------

    socket.on('disconnect', () => {
        // 1. Limpiar usuario online
        if (userId) {
            onlineUsers.delete(userId.toString());
            io.emit('user-status', { userId, online: false });
        }

        // 2. Limpiar de las salas de stream
        for (const streamId in rooms) {
            if (rooms.hasOwnProperty(streamId) && rooms[streamId].viewers) {
                if (rooms[streamId].viewers.has(socket.id)) {
                    rooms[streamId].viewers.delete(socket.id);
                    
                    // Avisar a la sala
                    io.to(streamId).emit('update_viewer_count', { count: rooms[streamId].viewers.size });
                    
                    // Avisar al mundo (Home)
                    broadcastAllViewerCounts();
                }
            }
        }

        // 3. Limpiar recursos de Mediasoup
        if (socket.transportId) {
            const peer = peers[socket.transportId];
            if (peer && peer.transport) peer.transport.close();
            delete peers[socket.transportId];
        }
    });
});

// ================================================================
// 🚀 INICIO DEL SERVIDOR
// ================================================================

server.listen(PORT, () => {
    console.log(`✅ Servidor Enyooi Master corriendo en puerto ${PORT}`);
    console.log(`📡 IP Anunciada para Streaming: ${mediasoupConfig.webRtcTransport.listenIps[0].announcedIp || 'Localhost (Solo Local)'}`);
});