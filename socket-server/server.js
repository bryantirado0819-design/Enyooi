const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
app.use(cors());
app.use(bodyParser.json());
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

const userSockets = new Map();

io.on('connection', (socket) => {
    const userId = socket.handshake.query.userId;
    if (userId) {
        userSockets.set(userId.toString(), socket.id);
        socket.broadcast.emit('user-status', { userId, online: true });
        console.log(`User ${userId} connected with socket ${socket.id}`);
    }

    socket.on('get-status', ({ userId: targetId }) => {
        const isOnline = Array.from(userSockets.keys()).includes(targetId.toString());
        socket.emit('user-status', { userId: targetId, online: isOnline });
    });

    socket.on('private-message', (data) => {
        const recipientSocketId = userSockets.get(data.destinatario_id.toString());
        if (recipientSocketId) {
            io.to(recipientSocketId).emit('private-message', data);
        }
    });

    socket.on('typing', ({ to }) => {
        const recipientSocketId = userSockets.get(to.toString());
        if (recipientSocketId) {
            io.to(recipientSocketId).emit('typing', { from: userId });
        }
    });
    
    socket.on('stop-typing', ({ to }) => {
        const recipientSocketId = userSockets.get(to.toString());
        if (recipientSocketId) {
            io.to(recipientSocketId).emit('stop-typing', { from: userId });
        }
    });

    socket.on('disconnect', () => {
        for (let [uid, sid] of userSockets.entries()) {
            if (sid === socket.id) {
                userSockets.delete(uid);
                socket.broadcast.emit('user-status', { userId: uid, online: false });
                console.log(`User ${uid} disconnected`);
                break;
            }
        }
    });
});

// Endpoint para que PHP notifique un nuevo like
app.post('/notify/like', (req, res) => {
    const { postId, newLikeCount, userLiked, liked } = req.body;
    console.log(`Notificación de Like recibida para post ${postId}:`, req.body);
    
    // Emitir a todos los clientes el cambio en el contador de likes
    io.emit('likeUpdate', { postId, newLikeCount });
    
    // También se podría emitir a un usuario específico si le gustó
    // io.to(userLikedSocketId).emit('myLikeUpdate', { postId, liked });

    res.status(200).send({ message: 'Notificación de like procesada' });
});

// Endpoint para que PHP notifique un nuevo comentario
app.post('/notify/comment', (req, res) => {
    const { postId, newComment, newCommentCount } = req.body;
    console.log(`Notificación de Comentario recibida para post ${postId}:`, newComment);

    // Emitir el nuevo comentario a todos
    io.emit('newComment', { postId, comment: newComment });

    // Emitir la actualización del contador de comentarios
    io.emit('commentCountUpdate', { postId, newCommentCount });
    
    res.status(200).send({ message: 'Notificación de comentario procesada' });
});

// Endpoint para marcar mensajes como leídos
app.post('/emit-read', (req, res) => {
    const { readerId, writerId } = req.body;
    if (!readerId || !writerId) {
        return res.status(400).json({ error: 'Faltan datos' });
    }
    const writerSocketId = userSockets.get(writerId.toString());
    if (writerSocketId) {
        io.to(writerSocketId).emit('messages-read', { readerId });
    }
    res.json({ success: true });
});

// ✅ Endpoint para que PHP envíe notificaciones en tiempo real
app.post('/notify', (req, res) => {
    const notification = req.body;
    if (!notification || !notification.idUsuario) {
        return res.status(400).json({ error: 'Faltan datos de la notificación' });
    }
    
    const targetSocketId = userSockets.get(notification.idUsuario.toString());
    
    if (targetSocketId) {
        io.to(targetSocketId).emit('new_notification', notification);
        console.log(`Notificación enviada al usuario ${notification.idUsuario}`);
    } else {
        console.log(`Usuario ${notification.idUsuario} no conectado para recibir notificación.`);
    }
    
    res.json({ success: true, message: "Notificación procesada." });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Servidor Socket.IO corriendo en el puerto ${PORT}`);
});