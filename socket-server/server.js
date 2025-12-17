// socket-server/server.js
// ================================================================
// 🚀 SERVIDOR MAESTRO ENYOOI: IVS + SOCKETS + MYSQL (NO MEDIASOUP)
// ================================================================

require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const cors = require('cors');
const mysql = require('mysql2/promise');

// 1. CONFIGURACIÓN
const PORT = 3000;
const dbConfig = {
    host: '100.127.0.29',
    user: 'enyooi_user',
    password: 'Enyooi2025!', 
    database: 'enyooi',
    waitForConnections: true,
    connectionLimit: 10
};

// 2. INICIALIZACIÓN
const app = express();
app.use(cors({ origin: "*" })); // Permite peticiones de PHP y cliente
app.use(express.json()); // Crucial para recibir notificaciones desde PHP

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    },
    pingTimeout: 60000
});

// Pool de Base de Datos Global
global.db = mysql.createPool(dbConfig);

// Estado en memoria
const onlineUsers = new Map(); // { userId: socketId }
const rooms = {}; // { streamId: { viewers: Set() } } (Ya no guarda producers)

// ================================================================
// 📡 ENDPOINTS HTTP (COMPATIBILIDAD CON TU PHP ACTUAL)
// ================================================================
// ESTO ES LO QUE MANTIENE VIVOS TUS LIKES Y COMENTARIOS DEL FEED

app.post('/notify/like', (req, res) => {
    // PHP llama a esto cuando alguien da like
    const { postId, newLikeCount } = req.body;
    io.emit('likeUpdate', { postId, newLikeCount });
    res.json({ success: true });
});

app.post('/notify/comment', (req, res) => {
    // PHP llama a esto cuando alguien comenta
    const { postId, newComment, newCommentCount } = req.body;
    io.emit('newComment', { postId, newComment });
    io.emit('commentCountUpdate', { postId, newCommentCount });
    res.json({ success: true });
});

app.post('/emit-read', (req, res) => {
    // PHP llama a esto para marcar mensajes leídos
    const { readerId, writerId } = req.body;
    const writerSocketId = onlineUsers.get(writerId.toString());
    if (writerSocketId) {
        io.to(writerSocketId).emit('messages-read', { readerId });
    }
    res.json({ success: true });
});

// Endpoint nuevo para notificar inicio de stream desde PHP (IVS)
app.post('/notify/stream-start', (req, res) => {
    const { streamId, userId } = req.body;
    io.emit('stream_started_notification', { streamId, userId });
    res.json({ success: true });
});

// ================================================================
// ⚡ LÓGICA DE SOCKET.IO (NEGOCIO + CHAT + LOVENSE)
// ================================================================

function broadcastAllViewerCounts() {
    const counts = {};
    for (const streamId in rooms) {
        if (rooms.hasOwnProperty(streamId) && rooms[streamId].viewers) {
            counts[streamId] = rooms[streamId].viewers.size;
        }
    }
    io.emit('all_viewer_counts', counts);
}

io.on('connection', (socket) => {
    // Registro de usuario (Chat privado y presencia)
    const userId = socket.handshake.query.userId;
    if (userId) {
        onlineUsers.set(userId.toString(), socket.id);
        io.emit('user-status', { userId, online: true });
    }

    // ------------------------------------------------------------
    // 💬 1. CHAT GLOBAL Y PRIVADO (INTACTO)
    // ------------------------------------------------------------
    
    // Mensajes en el Stream
    socket.on('send_chat_message', (data) => {
        // data: { streamId, userId, username, message, avatar... }
        // Reenviar a la sala del stream
        io.to(data.streamId.toString()).emit('new_chat_message', data);
    });

    // Mensajes Privados
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
    // 📺 2. GESTIÓN DE SALAS DE STREAM (ADAPTADO A IVS)
    // ------------------------------------------------------------
    
    // Tanto el creador como el espectador se unen aquí para recibir el chat
    socket.on('join_stream_room', ({ streamId, username, isCreator }) => {
        const sId = streamId.toString();
        socket.join(sId);
        
        socket.currentStreamId = sId; // Guardar referencia para desconexión
        socket.isCreator = isCreator || false;

        // Inicializar sala si no existe
        if (!rooms[sId]) {
            rooms[sId] = { viewers: new Set() };
        }
        
        // Solo contamos viewers, no al creador
        if (!isCreator) {
            rooms[sId].viewers.add(socket.id);
        }

        // Notificar conteo
        io.to(sId).emit('update_viewer_count', { count: rooms[sId].viewers.size });
        broadcastAllViewerCounts();
    });

    // ------------------------------------------------------------
    // 💎 3. DONACIONES, LOVENSE Y ALERTAS (INTACTO)
    // ------------------------------------------------------------

    socket.on('send_tip_alert', (data) => {
        // data: { streamId, username, amount, message }
        console.log(`💰 Tip recibido en stream ${data.streamId}: ${data.amount}`);
        
        // 1. Alertar al chat (mensaje visual)
        io.to(data.streamId.toString()).emit('new_tip_alert', data);
        
        // 2. Alertar específicamente al creador (para Lovense JS Trigger)
        // El frontend del creador escuchará 'trigger_lovense'
        io.to(data.streamId.toString()).emit('trigger_lovense', {
            amount: data.amount,
            level: data.amount >= 100 ? 20 : (data.amount >= 50 ? 10 : 2) // Lógica simple de intensidad
        });
    });

    // ------------------------------------------------------------
    // 🛠️ 4. CRUD EN TIEMPO REAL (CONFIGURACIÓN DEL CREADOR)
    // ------------------------------------------------------------
    // Mantenemos esto 100% igual para que tus menús de configuración funcionen

    socket.on('settings:addTip', async (data) => {
        try {
            const [result] = await global.db.execute(
                'INSERT INTO stream_tip_options (creator_id, zafiros, descripcion) VALUES (?, ?, ?)',
                [data.creatorId, data.zafiros, data.descripcion]
            );
            socket.emit('settings:tipAdded', { id: result.insertId, ...data });
            io.to(data.creatorId.toString()).emit('settings:updateTips', { 
                action: 'add', item: { id: result.insertId, ...data } 
            });
        } catch (err) { console.error('DB Error:', err); }
    });

    socket.on('settings:deleteTip', async (data) => {
        try {
            await global.db.execute('DELETE FROM stream_tip_options WHERE id = ?', [data.id]);
            io.to(data.creatorId.toString()).emit('settings:updateTips', { action: 'delete', id: data.id });
        } catch (err) { console.error('DB Error:', err); }
    });

    socket.on('settings:addRoulette', async (data) => {
        try {
            const [result] = await global.db.execute(
                'INSERT INTO stream_roulette_options (creator_id, option_text) VALUES (?, ?)',
                [data.creatorId, data.texto]
            );
            socket.emit('settings:rouletteAdded', { id: result.insertId, texto: data.texto });
            io.to(data.creatorId.toString()).emit('settings:updateRoulette', { 
                action: 'add', item: { id: result.insertId, option_text: data.texto } 
            });
        } catch (err) { console.error('DB Error:', err); }
    });

    socket.on('settings:deleteRoulette', async (data) => {
        try {
            await global.db.execute('DELETE FROM stream_roulette_options WHERE id = ?', [data.id]);
            io.to(data.creatorId.toString()).emit('settings:updateRoulette', { action: 'delete', id: data.id });
        } catch (err) { console.error('DB Error:', err); }
    });

    socket.on('settings:updateGoal', (data) => {
        io.to(data.creatorId.toString()).emit('settings:goalUpdated', data);
    });

    // ------------------------------------------------------------
    // 🚪 5. DESCONEXIÓN Y LIMPIEZA
    // ------------------------------------------------------------

    socket.on('disconnect', async () => {
        // 1. Limpiar usuario online
        if (userId) {
            onlineUsers.delete(userId.toString());
            io.emit('user-status', { userId, online: false });

            // SI EL CREADOR SE DESCONECTA: Poner OFFLINE en BD
            // (Nota: IVS sigue corriendo unos segundos, pero aquí cerramos la lógica de app)
            if (socket.isCreator && socket.currentStreamId) {
                try {
                    console.log(`🔴 Creador ${userId} desconectado. Stream offline.`);
                    await global.db.execute(
                        "UPDATE streams SET estado = 'offline', ended_at = NOW() WHERE creator_id = ?", 
                        [userId]
                    );
                    io.to(socket.currentStreamId).emit('stream_ended');
                } catch (e) { console.error("Error BD offline:", e); }
            }
        }

        // 2. Limpiar Viewer Count
        const sId = socket.currentStreamId;
        if (sId && rooms[sId] && rooms[sId].viewers) {
            if (rooms[sId].viewers.has(socket.id)) {
                rooms[sId].viewers.delete(socket.id);
                io.to(sId).emit('update_viewer_count', { count: rooms[sId].viewers.size });
                broadcastAllViewerCounts();
            }
        }
    });
});

server.listen(PORT, () => {
    console.log(`✅ Servidor Enyooi (Mode: IVS Support) corriendo en puerto ${PORT}`);
    console.log(`✅ Endpoints de notificación de Likes/Comentarios activos`);
});