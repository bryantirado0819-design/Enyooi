<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logueando'])) {
    header('Location: /ENYOOI/home/entrar');
    exit;
}
$USER_ID = (int)$_SESSION['logueando'];
$SOCKET_HOST = 'http://localhost:3000';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Enyooi — Social</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --accent:#ff4fa3; --accent-2:#7c5cff; }
    body {
        font-family:'Poppins', sans-serif;
        background:radial-gradient(1200px 600px at 10% 10%,rgba(124,92,255,0.12),transparent 8%),
                   radial-gradient(1000px 600px at 90% 80%,rgba(255,79,163,0.08),transparent 8%),
                   linear-gradient(180deg,#020617 0%,#081127 100%);
        color:#e6eef8;
        /* La siguiente línea ha sido eliminada */
        /* overflow: hidden; */ 
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: rgba(124, 92, 255, 0.5); border-radius: 5px; }
    .bubble-me { background: linear-gradient(90deg, var(--accent), var(--accent-2)); }
    .bubble-other { background: rgba(255, 255, 255, 0.1); }
    .seen-icon { font-size: 0.8rem; }
    .seen-icon.sent { color: #9ca3af; } /* Gris para enviado */
    .seen-icon.seen { color: #60a5fa; } /* Azul para visto */
  </style>
</head>
<body class="h-screen flex items-center justify-center p-2 md:p-4">

  <main class="grid grid-cols-12 gap-4 w-full h-full max-w-7xl mx-auto">
    <!-- Columna de Contactos -->
    <aside class="col-span-12 md:col-span-4 glass-card rounded-2xl p-4 flex flex-col h-full">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-white font-semibold text-lg">Chats</h2>
        <div class="text-sm text-blue-200">ID: <span id="userIdDisplay"><?= $USER_ID ?></span></div>
      </div>
      <div class="relative mb-3">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input id="searchUser" type="search" placeholder="Buscar usuario..." class="w-full p-2 pl-10 rounded-full bg-black/30 border border-white/10 focus:outline-none focus:ring-2 focus:ring-pink-500">
      </div>
      <div id="contactsList" class="flex-1 overflow-y-auto scrollbar-thin space-y-2 pr-2">
        <!-- Los contactos se cargarán aquí -->
      </div>
      <!-- Botones de Navegación -->
      <div class="mt-4 pt-4 border-t border-white/10 flex gap-2">
          <a href="<?php echo URL_PROJECT; ?>home" class="flex-1 text-center py-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors"><i class="fas fa-home mr-2"></i>Inicio</a>
          <a href="<?php echo URL_PROJECT; ?>live" class="flex-1 text-center py-2 rounded-lg bg-white/5 hover:bg-white/10 transition-colors"><i class="fas fa-video mr-2"></i>Live</a>
      </div>
    </aside>

    <!-- Columna de Chat -->
    <section class="col-span-12 md:col-span-8 glass-card rounded-2xl flex flex-col h-full">
        <div id="chat-welcome" class="flex-1 flex flex-col items-center justify-center text-center p-4">
            <i class="fas fa-comments text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold">Bienvenido a Enyooi Social</h3>
            <p class="text-slate-400">Selecciona una conversación para empezar a chatear.</p>
        </div>

        <div id="chat-area" class="hidden h-full flex-col">
            <!-- Cabecera del Chat -->
            <div class="flex items-center justify-between border-b border-white/10 p-4 flex-shrink-0">
              <div class="flex items-center gap-3">
                  <img id="chat-avatar" src="" class="w-12 h-12 rounded-full object-cover">
                  <div>
                      <h3 id="chat-title" class="text-lg font-semibold"></h3>
                      <div id="chat-status" class="text-xs text-green-400 flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-green-400"></div>En línea</div>
                  </div>
              </div>
            </div>

            <!-- Contenedor con scroll -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin">
              <!-- Mensajes aquí -->
            </div>

            <!-- Indicador de "Escribiendo..." -->
            <div id="typingIndicator" class="text-sm text-blue-200 h-5 px-4 hidden animate-pulse">
              está escribiendo...
            </div>

            <!-- Área de Composición de Mensaje -->
            <div class="mt-auto p-4 flex-shrink-0">
              <div class="flex gap-3 items-center bg-black/30 rounded-full p-2">
                  <input id="destinatario-id" type="hidden">
                  <textarea id="messageInput" placeholder="Escribe un mensaje..." class="flex-1 bg-transparent p-2 focus:outline-none resize-none" rows="1"></textarea>
                  <button id="emoji-btn" class="text-gray-400 hover:text-pink-400 text-xl"><i class="far fa-smile"></i></button>
                  <label for="image-upload" class="text-gray-400 hover:text-pink-400 text-xl cursor-pointer"><i class="fas fa-paperclip"></i></label>
                  <input type="file" id="image-upload" class="hidden" accept="image/*">
                  <button id="sendBtn" class="px-5 py-2 rounded-full bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff] text-white font-semibold"><i class="fas fa-paper-plane"></i></button>
              </div>
            </div>
        </div>
    </section>
  </main>

  <!-- Modal para Desbloquear Chat -->
  <div id="unlock-chat-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
      <div class="glass-card rounded-2xl p-8 text-center max-w-sm">
          <h3 class="text-2xl font-bold mb-2">Desbloquear Chat</h3>
          <p class="text-blue-200 mb-4">Para chatear con <strong id="unlock-creator-name"></strong>, necesitas pagar una tarifa única.</p>
          <p class="text-4xl font-bold mb-6">💎 <span id="unlock-chat-price"></span></p>
          <div class="flex gap-4">
              <button id="cancel-unlock-btn" class="flex-1 py-2 rounded-full bg-white/10">Cancelar</button>
              <button id="confirm-unlock-btn" class="flex-1 py-2 rounded-full bg-gradient-to-r from-[#ff4fa3] to-[#7c5cff]">Pagar y Chatear</button>
          </div>
      </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js" type="module"></script>
  <script src="<?= $SOCKET_HOST ?>/socket.io/socket.io.js"></script>
  <script>
    const USER_ID = <?= $USER_ID ?>;
    const URL_PROJECT = "<?php echo URL_PROJECT; ?>";
    const SOCKET_HOST = '<?= $SOCKET_HOST ?>';
  </script>
  <script src="/ENYOOI/public/js/social_chat.js"></script>
</body>
</html>
