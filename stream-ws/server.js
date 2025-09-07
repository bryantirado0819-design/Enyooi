// Servidor WS con Socket.IO
// npm i express socket.io cors axios
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const axios = require('axios');

const app = express();
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: "*" } });

// Opcional: validar token/jwt de tu sesión PHP en handshake

io.on('connection', (socket) => {
  socket.on('joinLive', ({ liveId, userId, username }) => {
    socket.join(`live:${liveId}`);
    io.to(`live:${liveId}`).emit('activity', `${username} se unió`);
  });

  socket.on('sendMessage', async ({ liveId, userId, message }) => {
    // Persistir en PHP (opcional)
    try {
      await axios.post('https://TU_BACKEND/live/chat', { live_id: liveId, message }, { withCredentials: true });
    } catch(e){}
    io.to(`live:${liveId}`).emit('newMessage', { userId, message });
  });

  socket.on('sendLike', ({ liveId, userId }) => {
    io.to(`live:${liveId}`).emit('newLike', { userId });
  });

  socket.on('sendDonation', async ({ liveId, userId, amount }) => {
    // Procesa en backend (ZAFIRO + Lovense)
    try {
      const r = await axios.post('https://TU_BACKEND/live/donate', { live_id: liveId, amount }, { withCredentials: true });
      if (r.data && r.data.success) {
        io.to(`live:${liveId}`).emit('newDonation', { userId, amount });
      } else {
        socket.emit('donationError', r.data.message || 'Error');
      }
    } catch(e) {
      socket.emit('donationError', 'Error de red');
    }
  });
});

server.listen(4000, () => console.log('WS listo en :4000'));
