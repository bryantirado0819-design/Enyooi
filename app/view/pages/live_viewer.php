<?php 
// No incluimos header/footer estándar porque esta vista es pantalla completa tipo app
// Pero necesitamos iniciar la sesión si no está iniciada (ya lo hace el controlador)
$stream = $datos['stream'];
$tipOptions = $datos['tip_options'];
$rouletteOptions = $datos['roulette_options'];
$viewerZafiros = $datos['espectador_zafiros'];
$currentUser = $_SESSION['usuario'] ?? 'Invitado';
$currentUserId = $_SESSION['logueando'] ?? 0;
$avatar = !empty($stream->creator_avatar) ? URL_PROJECT . $stream->creator_avatar : URL_PROJECT . 'public/img/defaults/default_avatar.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viendo a <?php echo htmlspecialchars($stream->creator_nickname); ?> - Enyooi Live</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo RUTA_URL; ?>/public/css/live_viewer.css">
    <!-- Importante: WebRTC y Socket.IO -->
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mediasoup-client@3/dist/mediasoup-client.min.js"></script>
</head>
<body class="overflow-hidden h-screen">

    <!-- Datos ocultos para JS -->
    <input type="hidden" id="stream-id" value="<?php echo $stream->idstream; ?>">
    <input type="hidden" id="creator-id" value="<?php echo $stream->creator_id; ?>">
    <input type="hidden" id="user-id" value="<?php echo $currentUserId; ?>">
    <input type="hidden" id="username" value="<?php echo htmlspecialchars($currentUser); ?>">

    <!-- Layout Principal -->
    <div class="live-layout flex h-full gap-4 p-2 md:p-4">

        <!-- COLUMNA IZQUIERDA: VIDEO -->
        <main class="video-column w-full lg:w-2/3 h-full flex flex-col gap-4 relative">
            
            <!-- Contenedor de Video -->
            <div class="relative w-full flex-grow rounded-2xl overflow-hidden glass-card bg-black flex items-center justify-center group">
                
                <!-- Elemento de Video WebRTC -->
                <video id="remote-video" autoplay playsinline class="w-full h-full object-contain"></video>
                
                <!-- Poster / Placeholder si no hay video -->
                <div id="video-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 z-0">
                    <img src="<?php echo $avatar; ?>" class="w-24 h-24 rounded-full mb-4 opacity-50 animate-pulse">
                    <p class="text-blue-200">Conectando con el stream...</p>
                </div>

                <!-- Overlay Superior (Info del Streamer) -->
                <div class="absolute top-0 left-0 w-full p-4 flex justify-between items-start bg-gradient-to-b from-black/80 via-black/40 to-transparent z-10 transition-opacity duration-300 opacity-100 group-hover:opacity-100 lg:opacity-0 lg:group-hover:opacity-100">
                    <div class="flex items-center gap-3">
                        <a href="<?php echo RUTA_URL; ?>perfil/<?php echo $stream->creator_nickname; ?>">
                            <img src="<?php echo $avatar; ?>" class="w-12 h-12 md:w-14 md:h-14 rounded-full border-2 border-purple-400 object-cover shadow-lg">
                        </a>
                        <div>
                            <h1 class="text-lg md:text-xl font-bold text-white text-shadow"><?php echo htmlspecialchars($stream->creator_nickname); ?></h1>
                            <p class="text-xs md:text-sm text-gray-300 bg-black/30 px-2 py-0.5 rounded-full backdrop-blur-sm truncate max-w-[150px]"><?php echo htmlspecialchars($stream->titulo); ?></p>
                        </div>
                        <button class="hidden md:block px-4 py-1.5 ml-3 rounded-full bg-white/20 hover:bg-white/30 transition text-white font-semibold text-sm backdrop-blur-md border border-white/10">
                            <i class="fas fa-plus mr-1"></i> Seguir
                        </button>
                    </div>
                    
                    <div class="flex flex-col items-end gap-2">
                        <div class="glass-card px-3 py-1 rounded-full flex items-center gap-2 text-xs font-bold bg-red-600/90 text-white shadow-red-glow">
                            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span> LIVE
                        </div>
                        <div class="glass-card px-3 py-1 rounded-full flex items-center gap-2 text-xs text-white bg-black/40 backdrop-blur-md">
                            <i class="fas fa-eye text-cyan-300"></i> <span id="viewer-count">0</span>
                        </div>
                    </div>
                </div>

                <!-- Botón de Like Flotante -->
                <button id="btn-like" class="like-btn-float absolute bottom-6 right-6 w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-white text-2xl md:text-3xl z-20 cursor-pointer">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            
            <!-- Barra de Meta (Debajo del video en Desktop) -->
            <div class="w-full glass-card rounded-xl p-3 md:p-4 flex-shrink-0 hidden lg:block">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-sm md:text-base"><i class="fas fa-bullseye text-purple-400 mr-2"></i>Meta del Stream</h3>
                    <p class="font-bold text-sm md:text-base text-purple-300" id="goal-text">Cargando...</p>
                </div>
                <div class="w-full bg-black/40 rounded-full h-3 overflow-hidden shadow-inner">
                    <div id="goal-bar" class="bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 h-3 rounded-full transition-all duration-1000 relative" style="width: 0%">
                        <div class="absolute inset-0 bg-white/20 animate-shimmer"></div>
                    </div>
                </div>
            </div>
        </main>

        <!-- COLUMNA DERECHA: CHAT Y PESTAÑAS -->
        <aside class="chat-column w-full lg:w-1/3 h-full glass-card rounded-2xl flex flex-col overflow-hidden shadow-2xl border border-white/10">
            
            <!-- Header de Pestañas -->
            <div class="flex-shrink-0 p-2 glass-card border-b border-white/5 z-10">
                <div class="flex justify-around bg-black/30 rounded-xl p-1 gap-1">
                    <button data-tab="chat-panel" class="tab-button active flex-1 py-2 rounded-lg text-sm font-medium"><i class="fas fa-comments mr-1"></i> Chat</button>
                    <button data-tab="tips-panel" class="tab-button flex-1 py-2 rounded-lg text-sm font-medium"><i class="fas fa-gem mr-1"></i> Propinas</button>
                    <button data-tab="roulette-panel" class="tab-button flex-1 py-2 rounded-lg text-sm font-medium"><i class="fas fa-dharmachakra mr-1"></i> Ruleta</button>
                </div>
            </div>

            <!-- Contenedor de Paneles -->
            <div class="flex-grow overflow-hidden relative bg-black/20">
                
                <!-- PANEL 1: CHAT -->
                <div id="chat-panel" class="tab-content active p-4 overflow-y-auto custom-scrollbar scroll-smooth">
                    <div class="text-center text-xs text-gray-500 my-4">Bienvenido al chat. Sé amable y respeta las normas.</div>
                    <div id="chat-messages" class="flex flex-col justify-end min-h-0">
                        <!-- Los mensajes se insertarán aquí vía JS -->
                    </div>
                </div>
                
                <!-- PANEL 2: PROPINAS -->
                <div id="tips-panel" class="tab-content p-4 overflow-y-auto custom-scrollbar">
                   <h3 class="font-bold text-lg mb-4 text-center text-white">Apoya a <?php echo htmlspecialchars($stream->creator_nickname); ?></h3>
                   
                   <div class="grid grid-cols-1 gap-3 mb-6">
                       <?php if(empty($tipOptions)): ?>
                           <p class="text-center text-gray-400 text-sm">No hay opciones de propina configuradas.</p>
                       <?php else: ?>
                           <?php foreach($tipOptions as $opt): ?>
                               <button class="btn-tip w-full text-left p-4 bg-gradient-to-r from-white/5 to-white/0 hover:from-white/10 hover:to-white/5 border border-white/5 rounded-xl transition-all group" onclick="selectTip(<?php echo $opt->zafiros; ?>, '<?php echo $opt->descripcion; ?>')">
                                   <div class="flex justify-between items-center">
                                       <span class="font-bold text-pink-400 text-lg">💎 <?php echo $opt->zafiros; ?></span>
                                       <i class="fas fa-chevron-right text-gray-600 group-hover:text-white transition"></i>
                                   </div>
                                   <div class="text-sm text-gray-300 mt-1"><?php echo htmlspecialchars($opt->descripcion); ?></div>
                               </button>
                           <?php endforeach; ?>
                       <?php endif; ?>
                   </div>
                   
                   <!-- Formulario Custom -->
                   <div class="bg-black/30 p-4 rounded-xl border border-white/5">
                       <label class="text-xs text-gray-400 uppercase font-bold mb-2 block">Monto Personalizado</label>
                       <div class="relative mb-3">
                           <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xl">💎</span>
                           <input type="number" id="custom-tip-amount" placeholder="Cantidad" class="w-full bg-black/50 border border-white/10 rounded-lg py-2 pl-10 pr-3 text-white focus:border-pink-500 focus:outline-none transition">
                       </div>
                       <input type="text" id="custom-tip-msg" placeholder="Mensaje (opcional)" class="w-full bg-black/50 border border-white/10 rounded-lg py-2 px-3 mb-3 text-white text-sm focus:border-pink-500 focus:outline-none transition">
                       <button id="btn-send-tip" class="w-full py-3 rounded-lg font-bold text-white bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 shadow-lg transform active:scale-95 transition">
                           Enviar Propina
                       </button>
                   </div>
                </div>

                <!-- PANEL 3: RULETA -->
                <div id="roulette-panel" class="tab-content p-6 flex flex-col items-center justify-center text-center">
                    <?php if (!empty($stream->roulette_enabled)): ?>
                        <div class="relative mb-6">
                            <i class="fas fa-dharmachakra text-8xl text-purple-500/80 animate-spin-slow" style="animation-duration: 10s;"></i>
                            <i class="fas fa-caret-down absolute -top-2 left-1/2 -translate-x-1/2 text-4xl text-yellow-400 drop-shadow-lg"></i>
                        </div>
                        <h3 class="font-bold text-2xl mb-2 text-white">Ruleta de la Suerte</h3>
                        <p class="text-sm text-gray-300 mb-6 max-w-xs mx-auto">¡Gira la ruleta por <strong>💎 <?php echo $stream->roulette_cost; ?></strong> y haz que <?php echo htmlspecialchars($stream->creator_nickname); ?> cumpla un reto!</p>
                        
                        <div class="bg-black/30 rounded-xl p-4 w-full mb-6 max-h-40 overflow-y-auto custom-scrollbar text-left">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-2">Premios posibles:</p>
                            <ul class="text-sm text-gray-300 space-y-1 list-disc pl-4">
                                <?php foreach($rouletteOptions as $opt): ?>
                                    <li><?php echo htmlspecialchars($opt->option_text); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <button id="btn-spin-roulette" class="w-full py-3 rounded-xl font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/20 transform active:scale-95 transition flex items-center justify-center gap-2">
                            <i class="fas fa-play"></i> Girar por 💎 <?php echo $stream->roulette_cost; ?>
                        </button>
                    <?php else: ?>
                        <div class="text-gray-500 flex flex-col items-center">
                            <i class="fas fa-ban text-4xl mb-2"></i>
                            <p>La ruleta está desactivada en este momento.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Input de Chat (Fijo abajo) -->
            <div id="chat-input-area" class="flex-shrink-0 p-3 md:p-4 border-t border-white/10 bg-black/40 backdrop-blur-md z-20">
                 <div class="flex justify-between items-center mb-2 text-xs md:text-sm px-1">
                    <span class="text-gray-400">Tu Saldo:</span>
                    <span class="font-bold text-yellow-400 flex items-center gap-1"><i class="fas fa-gem"></i> <span id="user-balance"><?php echo number_format($viewerZafiros); ?></span></span>
                </div>
                <form id="chat-form" class="relative flex items-center gap-2">
                    <div class="relative flex-grow">
                        <input type="text" id="chat-input" placeholder="Envía un mensaje..." class="w-full bg-white/5 border border-white/10 rounded-full py-2.5 pl-4 pr-10 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-black/50 transition-all">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-yellow-400 transition"><i class="far fa-smile"></i></button>
                    </div>
                    <button type="submit" class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white flex items-center justify-center shadow-lg hover:shadow-purple-500/30 transform active:scale-90 transition">
                        <i class="fas fa-paper-plane text-sm"></i>
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <!-- JavaScript Lógico -->
    <script>
        // Variables de entorno pasadas desde PHP
        const RUTA_URL = "<?php echo RUTA_URL; ?>"; 
        const NODE_SERVER_URL = "http://localhost:3000"; // Asegúrate que coincida con server.js
    </script>
    <script src="<?php echo RUTA_URL; ?>/public/js/live_viewer.js"></script>
</body>
</html>